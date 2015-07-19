<?php

/**
 * Entrant Model
 *
 * @module     Campaign
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */


namespace Campaign\Model;

use Zend\Http\Header\SetCookie;

use Base\Model\AbstractModel;
use Base\Model\Session;
use Campaign\Model\ChanceModel;

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
        if ($this->validate($entrantData) && !$this->isRegistered($campaignId, $entrantData['email'], true)) {
            $entrant = $this->save($entrantData);
        }
        
        if ($entrant) {
            $this->setEntrantCookie($entrant);
            $this->addChance($entrant, $widgetId);
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
            $this->setEntrantCookie($entrant[0]);
        }
        
        return ($entrant);
    }
    
    /**
     * Set a cookie for the entrant
     * @param Campaign\Entity\Campaign\Entrant $entrant
     */
    public function setEntrantCookie($entrant)
    {
        $id = $entrant->get('id');
        $expires = time() + 365 * 60 * 60 * 24;
        $cookie = new SetCookie('entrant', $id, $expires, '/');
        $this->getServiceLocator()->get('response')->getHeaders()->addHeader($cookie);
    }
    
    
    public function addChance($entrant, $widgetId)
    {
        $data['entrant'] = $entrant;
        $data['widget'] = $this->loadWidget($widgetId);
        
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
     * Return a new instance of ChanceModel
     * @return Campaign\Model\ChanceModel
     */
    protected function getChanceModel()
    {
        $chanceModel = new ChanceModel();
        $chanceModel->setServiceLocator($this->getServiceLocator());
        
        return $chanceModel;
    }
}