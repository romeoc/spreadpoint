<?php

/**
 * Entrant Model
 *
 * @module     Campaign
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */


namespace Campaign\Model;

use Doctrine\ORM\Query;

use Base\Model\AbstractModel;
use Base\Model\Session;
use Base\Model\Mail;

use Campaign\Model\ChanceModel;
use User\Helper\UserHelper;

class EntrantModel extends AbstractModel
{
    // Initialize Entrant Model
    public function __construct() 
    {
        $this->init('Campaign\Entity\CampaignEntrant');
    }
    
    /**
     * Add a new entrant for a campaign
     * 
     * @param int $campaignId
     * @param array $entrantData
     */
    public function add($campaignId, $entrantData)
    {
        $campaign = $this->loadCampaign($campaignId);
        $widgetId = $entrantData['id'];
        unset($entrantData['id']);
        
        $entrantData['campaign'] = $campaign;
        $entrant = false;
        
        $referenceId = $this->getCookie('reference');
        if ($referenceId) {
            $entrantData['reference'] = $this->loadEntrant($referenceId);
        }
        
        if ($this->validate($entrantData) && !$this->isRegistered($campaignId, $entrantData['email'], true)) {
            $entrant = $this->save($entrantData);
        }
        
        if ($entrant) {
            $this->setCookie('entrant', $entrant->get('id'));
            $this->addChance($entrant, $widgetId);
            $this->sendWelcomeEmail($entrant);
            $this->sendNotificationEmail($campaign, $entrant);
        }
    }
    
    public function sendWelcomeEmail($entrant) 
    {        
        $campaign = $entrant->get('campaign');
        
        $sendWelcomeEmail = $campaign->get('sendWelcomeEmail');
        $body = $campaign->get('welcomeEmail');
        
        if ($sendWelcomeEmail == 1 && $body) {
            $fullname = $entrant->get('email');
            $email = $entrant->get('email');
            
            $title = $campaign->get('title');
            $subject = "You succesfully entered the '{$title}' competition";
            
            Mail::send($body, $subject, Mail::EMAIL, Mail::NAME, $email, $fullname);
        }
    }
    
    /**
     * Notify User that someone entered their contest
     */
    public function sendNotificationEmail($campaign, $entrant)
    {
        $user = $campaign->get('user');
        
        $uri = $this->getServiceLocator()->get('request')->getUri();
        $domain =  sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
        
        if ($user->get('notifications') == 1) {
            $body = "Someone just entered your contest titles '{$campaign->get('title')}'!" 
                    . PHP_EOL . "Check it out at {$domain}";
            $subject = 'Aww Yeah! Your contest is spreading wildfire';
            
            $fullname = $user->get('firstname') . ' ' . $user->get('lastname');
            $email = $user->get('email');
            
            Mail::send($body, $subject, Mail::EMAIL, Mail::NAME, $email, $fullname);
        }
    }
    
    /**
     * Check if a user is already registered to a campaign
     * 
     * @param int $campaign
     * @param int $email
     * @param bool $setCookie | wheter to update entry cokkie
     */
    public function isRegistered($campaign, $email, $setCookie = false)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $entrant = $queryBuilder->select('e')
            ->from($this->entity, 'e')
            ->where('e.campaign = :campaign')
            ->andWhere('e.email= :email')
            ->setParameter('campaign', $campaign)
            ->setParameter('email', $email)
            ->getQuery()
            ->setMaxResults(1)
            ->getResult();
        
        if ($entrant && $setCookie) {
            $this->setCookie('entrant', $entrant[0]->get('id'));
        }
        
        return ($entrant);
    }
    
    /**
     * Get Entrant Entity that is saved in the cookies
     */
    public function getLoadedEntrant()
    {
        $entrant = $this->getCookie('entrant');
        
        if ($entrant) {
            $entrant = $this->getEntityManager()->find($this->entity, $entrant);
        }
        
        return $entrant;
    }
    
    
    public function addChance($entrant, $widgetId)
    {
        $data['entrant'] = $entrant;
        $data['widget'] = $this->loadWidget($widgetId);
        
        $model = $this->getChanceModel();
        
        // Already awarded for this widget
        if ($model->load($entrant, $widgetId)) {
            return false;
        }
        
        return $this->getChanceModel()->save($data);
    }
    
    /**
     * Validate Entrant Data
     * 
     * @param *array $data
     * @return boolean
     */
    protected function validate(&$data)
    {
        $allValid = true;
        
        foreach ($data as $key => $value) {
            if (!property_exists($this->entity, $key)) {
                unset($data[$key]);
            }
        }
        
        $emailValidator = new \Zend\Validator\EmailAddress();
        if (!array_key_exists('email', $data) || !$data['email']) {
            Session::error('Please enter your email');
            $allValid = false;
        } elseif (strlen($data['email']) > 500) {
            Session::error('Your email should not be longer than 500 characters');
            $allValid = false;
        } elseif (!$emailValidator->isValid($data['email'])) {
            Session::error('Please enter a valid email address');
            $allValid = false;
        }
        
        return $allValid;
    }
    
    /**
     * Loads a campaign by id
     * 
     * @param int $campaignId
     * @return Campaign\Entity\Campaign
     */
    protected function loadCampaign($campaignId)
    {
        return $this->getEntityManager()->find('Campaign\Entity\Campaign', $campaignId);
    }
    
    /**
     * Loads a widget by id
     * 
     * @param int $widgetId
     * @return Campaign\Entity\CampaignWidget
     */
    protected function loadWidget($widgetId)
    {
        return $this->getEntityManager()->find('Campaign\Entity\CampaignWidget', $widgetId);
    }
    
    /**
     * Loads an entrant by id
     * 
     * @param int $entrantId
     * @return Campaign\Entity\CampaignEntrant
     */
    protected function loadEntrant($entrantId)
    {
        return $this->getEntityManager()->find($this->entity, $entrantId);
    }
    
    /**
     * Return a new instance of ChanceModel
     * @return Campaign\Model\ChanceModel
     */
    protected function getChanceModel()
    {
        $chanceModel = new ChanceModel();
        $chanceModel->setServiceLocator($this->getServiceLocator());
        
        return $chanceModel;
    }
    
    /**
     * Get campaign id for entrant
     *
     * @param int $entrantId
     * @return int
     */
    public function getCampaignIdForEntrant($entrantId)
    {
        $entrant = $this->getEntityManager()->find($this->entity, $entrantId);
        return $entrant->get('campaign')->get('id');
    }
    
    public function entrantsList()
    {
        $userHelper = new UserHelper();
        $userHelper->updateServiceLocator($this->getServiceLocator());
        
        return $this->getEntityManager()->createQueryBuilder()
            ->select('e AS data','SUM(w.earningValue) AS chances', 'COUNT(w) AS widgets', 'SUM(CASE WHEN w.widgetType = 6 THEN 1 ELSE 0 END) AS reference')
            ->from($this->entity, 'e')
            ->innerJoin('Campaign\Entity\Campaign','c','WITH','e.campaign = c.id')
            ->innerJoin('Campaign\Entity\CampaignEntrantChance', 'ec', 'WITH', 'e.id = ec.entrant')
            ->innerJoin('Campaign\Entity\CampaignWidget', 'w', 'WITH', 'ec.widget = w.id')
            ->where('c.user= :user')
            ->groupBy('e.id')
            ->setParameter('user', $userHelper->getLoggedInUserId())
            ->getQuery()
            ->setHint(Query::HINT_INCLUDE_META_COLUMNS, true)
            ->getArrayResult();
    }
    
    public function getEntrantsForCampaign($campaignId)
    {
        $campaign = $this->getEntityManager()->find('Campaign\Entity\Campaign', $campaignId);
        
        $userHelper = new UserHelper();
        $userHelper->updateServiceLocator($this->getServiceLocator());
        
        if ($userHelper->getLoggedInUserId() != $campaign->get('user')->get('id')) {
            Session::error('You are not the owner of that campaign');
            return false;
        }
        
        return $this->getEntityManager()->createQueryBuilder()
            ->select('e AS data','SUM(w.earningValue) AS chances', 'COUNT(w) AS widgets', 'SUM(CASE WHEN w.widgetType = 6 THEN 1 ELSE 0 END) AS reference')
            ->from($this->entity, 'e')
            ->innerJoin('Campaign\Entity\CampaignEntrantChance', 'c', 'WITH', 'e.id = c.entrant')
            ->innerJoin('Campaign\Entity\CampaignWidget', 'w', 'WITH', 'c.widget = w.id')
            ->where('e.campaign= :campaign')
            ->groupBy('e.id')
            ->setParameter('campaign', $campaignId)
            ->getQuery()
            ->setHint(Query::HINT_INCLUDE_META_COLUMNS, true)
            ->getArrayResult();
    }
    
    public function getCsvData($data)
    {
        $entries = array();
        foreach ($data as $entry) {
            $entry['data']['referenceId'] = $entry['data']['reference'];
            unset($entry['data']['reference']);
            
            $newEntry = array_merge($entry['data'], $entry);
            unset ($newEntry['data']);
            
            $entries[] = $newEntry;
        }
        
        return $entries;
    }
    
    public function load($entrantId)
    {
        $result = array();
        $entrant = $this->getEntityManager()->find($this->entity, $entrantId);
        
        if ($entrant) {
            
            $widgets = $this->getEntityManager()->createQueryBuilder()
                ->select('w.id, w.title, w.earningValue, c.earningDate')
                ->from('Campaign\Entity\CampaignEntrantChance', 'c')
                ->innerJoin('Campaign\Entity\CampaignWidget', 'w', 'WITH', 'c.widget = w.id')
                ->where('c.entrant= :entrant')
                ->setParameter('entrant', $entrant)
                ->getQuery()
                ->getResult();
            
            $result['entrant'] = $entrant;
            $result['widgets'] = $widgets;
        }
        
        return $result;
    }
}