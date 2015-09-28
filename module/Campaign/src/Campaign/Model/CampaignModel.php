<?php

/**
 * Campaign Model
 *
 * @module     Campaign
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Campaign\Model;

use Zend\Session\Container;
use Doctrine\ORM\Query;

use Base\Model\AbstractModel;
use Base\Model\Session;

use Campaign\Model\PrizeModel;
use Campaign\Model\WidgetModel;
use Campaign\Model\EntrantModel;
use Campaign\Entity\Campaign as CampaignEntity;
use User\Helper\UserHelper;

class CampaignModel extends AbstractModel
{
    /**
     * A WidgetModel Instance
     * 
     * @var Campaign\Model\WidgetModel 
     */
    protected $widgetModel;
    
    /**
     * A PrizeModel Instance
     *
     * @var Campaign\Model\PrizeModel 
     */
    protected $prizeModel;
    
    // Initialize Campaign Model
    public function __construct() 
    {
        $this->init('Campaign\Entity\Campaign');
    }
    
    /**
     * Process the data received from the controller and send every data to it's
     * corresponding helper
     * 
     * @param array $data
     * @return int
     */
    public function process($data)
    {       
        // Widgets Data
        $widgetsData = '';
        if (array_key_exists('widgets-serialized', $data) && $data['widgets-serialized']) {
            $widgetsData = $data['widgets-serialized'];
            unset($data['widgets-serialized']);
        } else {
            Session::error("Your users have no ways to participate, please add some widgets");
        }

        // Prizes Data
        $prizeData = '';
        if (array_key_exists('prizes-serialized', $data) && $data['prizes-serialized']) {
            $prizeData = $data['prizes-serialized'];
            unset($data['prizes-serialized']);
        } else {
            Session::error("Your users have nothing to win, please add some prizes");
        }
        
        // Save the campaign data
        $campaign = $this->saveCampaign($data);
        if ($campaign) {
            $campaignId = $campaign->get('id');
            
            // Upload Banner
            $userId = $this->getUserHelper()->getLoggedInUserId();
            $mediaPath = "public/media/$campaignId/";
            
            $rename = false;
            $files = $this->getUploadedFiles();
            $banner = $files['banner'];
            if ($banner['name']) {
                $bannerExtension = pathinfo($banner['name'], PATHINFO_EXTENSION);
                $rename = 'banner.' . $bannerExtension;
            }
            
            $this->uploadFile('banner', $mediaPath, $rename);
            
            // If we managed to save the campaign succesfully then we save the widgets and the prizes
            $widgetsResult = $this->getWidgetModel()->saveWidgets($campaign, $widgetsData);
            $prizeResult = $this->getPrizeModel()->savePrizes($campaign, $prizeData);
            
            if (!$widgetsResult || !$prizeResult) {
                $this->updateStatus($campaignId, CampaignEntity::STATUS_PAUSED);
            } else {
                Session::success('Your campaign was succesfully saved');
            }
        }
        
        $result = ($campaign) ? $campaign->get('id') : $campaign;
        return $result;
    }
    
    /**
     * Save campaign
     * 
     * @param array $data
     * @return Campaign\Entity\Campaign | bool
     */
    public function saveCampaign($data)
    {
        if (!$this->validate($data)) {
            return false;
        }
        
        $data['user'] = $this->getUserHelper()->getLoggedInUser();
        if (!array_key_exists('status', $data)) {
            $data['status'] = CampaignEntity::STATUS_ACTIVE;
        }
        
        $timezone = new \DateTimeZone($data['timezone']);
        $data['startTime'] = new \DateTime($data['startTime'], $timezone);
        $data['endTime'] = new \DateTime($data['endTime'], $timezone);
        
        $data['showEntrants'] = (array_key_exists('showEntrants', $data)) ? 1 : 0;
        $data['sendWelcomeEmail'] = (array_key_exists('sendWelcomeEmail', $data)) ? 1 : 0;
        $data['notifyWinners'] = (array_key_exists('notifyWinners', $data)) ? 1 : 0;
        $data['retainPreviousEntrants'] = (array_key_exists('retainPreviousEntrants', $data)) ? 1 : 0;
        
        // Rename banner
        $files = $this->getUploadedFiles();
        $banner = $files['banner'];
        if ($banner['name']) {
            $bannerExtension = pathinfo($banner['name'], PATHINFO_EXTENSION);
            $data['banner'] = 'banner.' . $bannerExtension;
        }
        
        $result = $this->save($data);
        return $result;
    }
    
    /**
     * Validate Campaign Data
     * 
     * @param *array $data
     * @return boolean
     */
    protected function validate(&$data)
    {
        $errosFound = false;

        foreach ($data as $key => $value) {
            if (!property_exists($this->entity, $key)) {
                unset($data[$key]);
            }
        }
        
        if (!array_key_exists('title', $data) || !$data['title']) {
            Session::error("You didn't provide a <strong>'Title'</strong> for your campaign");
            $errosFound = true;
        } elseif (strlen($data['title']) > 32) {
            Session::error("The maximum <strong>'title'</strong> length is <strong>32</strong>");
            $errosFound = true;
        }
        
        if (!array_key_exists('description', $data) || !$data['description']) {
            Session::error("You didn't provide a <strong>'Description'</strong> for your campaign");
            $errosFound = true;
        } elseif (strlen($data['description']) > 500) {
            Session::error("The maximum <strong>'Description'</strong> length is <strong>500</strong> characters");
            $errosFound = true;
        }
        
        $files = $this->getUploadedFiles();
        $banner = $files['banner'];
        if (!array_key_exists('banner', $data) || !$data['banner']) {
            Session::error("You didn't provide a <strong>'Banner'</strong> for your campaign");
            $errosFound = true;
        } elseif ($banner['name'] && !$this->isFileValid($banner)) {
            $errosFound = true;
        }
        
        if (array_key_exists('titleCss', $data) && $data['titleCss'] && strlen($data['titleCss']) > 255) {
            Session::error("The maximum allowed <strong>'Title Css'</strong> length is <strong>255</strong> characters");
            $errosFound = true;
        }
        
        if (array_key_exists('descriptionCss', $data) && $data['descriptionCss'] && strlen($data['descriptionCss']) > 255) {
            Session::error("The maximum allowed <strong>'Description Css'</strong> length is <strong>255</strong> characters");
            $errosFound = true;
        }
        
        if (!array_key_exists('layout', $data) || !$data['layout']) {
            Session::error("Please choose a layout for your campaign");
            $errosFound = true;
        } elseif ($data['layout'] != 1 && $data['layout'] != 2) {
            Session::error("You specified an invalid layout");
            $errosFound = true;
        }
        
        if (array_key_exists('termsAndConditions', $data) && $data['termsAndConditions'] && strlen($data['termsAndConditions']) > 50000) {
            Session::error("There is a limit of <strong>50000</strong> characters to the <strong>'Terms & Conditions'</strong> field");
            $errosFound = true;
        }
        
        if (array_key_exists('sendWelcomeEmail', $data) && !(array_key_exists('welcomeEmail', $data) && $data['welcomeEmail'])) {
            Session::error("Please provide a <strong>Welcome Email</strong> or uncheck <strong>'Send Welcome Email'</strong>");
            $errosFound = true;
        }
        
        if (array_key_exists('welcomeEmail', $data) && $data['welcomeEmail'] && strlen($data['welcomeEmail']) > 20000) {
            Session::error("There is a limit of <strong>20000</strong> characters to the <strong>'Welcome Email'</strong> field");
            $errosFound = true;
        }
        
        if (array_key_exists('notifyWinners', $data) && !(array_key_exists('winnerEmail', $data) && $data['winnerEmail'])) {
            Session::error("Please provide a <strong>Winner's Email</strong> or uncheck the <strong>'Notify Winners'</strong> field");
            $errosFound = true;
        }
        
        if (array_key_exists('winnerEmail', $data) && $data['notifyWinners'] && strlen($data['winnerEmail']) > 20000) {
            Session::error("There is a limit of <strong>20000</strong> characters to the <strong>'Winner's Email'</strong> field");
            $errosFound = true;
        }
        
        if (!array_key_exists('ageRequirement', $data) || !$data['ageRequirement']) {
            Session::error("You didn't specify an <strong>Age Requirement</strong>");
            $errosFound = true;
        } elseif (!is_numeric ($data['ageRequirement']) || $data['ageRequirement'] < 1 || $data['ageRequirement'] > 5) {
            Session::error("Invalid <strong>Age Requirement</strong>");
            $errosFound = true;
        }
        
        if (!array_key_exists('type', $data) || !$data['type'] 
                || ($data['type'] != CampaignEntity::CAMPAIGN_TYPE_SINGLE 
                && $data['type'] != CampaignEntity::CAMPAIGN_TYPE_CYCLE)) {
            Session::error("Invalid <strong>Competition Type</strong>");
            $errosFound = true;
        }
        
        if (!array_key_exists('startTime', $data) || !$data['startTime']) {
            Session::error("You didn't provide a <strong>'Start Time'</strong> for your campaign");
            $errosFound = true;
        } elseif (!$this->isValidDateTime($data['startTime'])) {
            Session::error("Invalid <strong>'Start Time'</strong>");
            $errosFound = true;
        }
        
        if (!array_key_exists('timezone', $data) || !$data['timezone']) {
            Session::error("You didn't provide a <strong>'Time Zone'</strong> for your campaign");
            $errosFound = true;
        } elseif (!in_array($data['timezone'], timezone_identifiers_list())) {
            Session::error("Invalid <strong>'Time Zone'</strong> specified");
            $errosFound = true;
        }
        
        if (array_key_exists('type', $data) && $data['type'] == CampaignEntity::CAMPAIGN_TYPE_SINGLE) {
            if (!array_key_exists('endTime', $data) || !$data['endTime']) {
                Session::error("You didn't provide an <strong>'End Time'</strong> for your campaign");
                $errosFound = true;
            } elseif (!$this->isValidDateTime($data['endTime'])) {
                Session::error("Invalid <strong>'End Time'</strong>");
                $errosFound = true;
            } elseif (array_key_exists('startTime', $data) && strtotime($data['startTime']) > strtotime($data['endTime'])) {
                Session::error("Your campaign can't end before it started. Please fix your <strong>'Start Time & End Time'</strong>");
                $errosFound = true;
            }
        }
        
        if (array_key_exists('type', $data) && $data['type'] == CampaignEntity::CAMPAIGN_TYPE_CYCLE) { 
            if (!array_key_exists('cycleDuration', $data) || !$data['cycleDuration']) {
                Session::error("You didn't provide a <strong>'Campaign Cycle Duration'</strong> for your campaign");
                $errosFound = true;
            } elseif (!is_numeric($data['cycleDuration'])) {
                Session::error("The <strong>'Campaign Cycle Duration'</strong> must be a numeric value");
                $errosFound = true;
            } elseif ($data['cycleDuration'] <= 0) {
                Session::error("The <strong>'Campaign Cycle Duration'</strong> must be greater than 0");
                $errosFound = true;
            }
            
            if (!array_key_exists('cyclesCount', $data) || !$data['cyclesCount']) {
                Session::error("You didn't provide a <strong>'Campaign Cycle Count'</strong> for your campaign");
                $errosFound = true;
            } elseif (!is_numeric($data['cyclesCount'])) {
                Session::error("The <strong>'Campaign Cycle Count'</strong> must be a numeric value");
                $errosFound = true;
            } elseif ($data['cyclesCount'] <= 0) {
                Session::error("The <strong>'Campaign Cycle Count'</strong> must be greater than 0");
                $errosFound = true;
            }
        }
        
        return !$errosFound;
    }
    
    public function isValidDateTime($datetime, $format = 'Y/m/d H:i') 
    {
        $temp = \DateTime::createFromFormat($format, $datetime);
        return $temp && $temp->format($format) == $datetime;
    }
    
    /**
     * Update the status for a campaign
     * 
     * @param int $entityId
     * @param int $status
     */
    public function updateStatus($entityId, $status) 
    {
        $data = array (
            'id' => $entityId,
            'status' => $status
        );
        
        $this->update($data);
    }
    
    /**
     * Fetch the data for a campaign Id
     * if the campaign id is null check for session data
     * else return the default data
     * 
     * @param int|null $campaignId
     * @return array
     */
    public function fetchData($campaignId, $skipAuthorChecking = false) 
    {
        $data = array();

        if ($campaignId && is_numeric($campaignId)) {
            // Check that the campaign was created by the logged in user
            if ($skipAuthorChecking || $this->checkCampaignAuthor($campaignId)) {
                // If a campaign Id was specified get then fetch the campaigns data
                $campaign = $this->load($campaignId);
                $data = array(
                    'data' => $campaign,
                    'entriesData' => array(
                        'widgetTypes' => $this->getWidgetModel()->getWidgetTypesJson(),
                        'appliedWidgets' => $this->getWidgetModel()->getAppliedWidgetsForJavaScript($campaignId),
                    ),
                    'prizesData' => $this->getPrizeModel()->getAssociatedPrizesForJavaScript($campaignId),
                    'entrantsData' => $this->getEntrantsModel()->getEntrantsForCampaign($campaignId),
                    'winnersData' => $this->getWinnersData($campaign),
                    'social' => $this->getServiceLocator()->get('config')['social']
                );
            } else {
                Session::error('You are trying to view a campaign that is not associated to your account');
            }
        } else {
            /** 
             * If some data was set on the save action 
             * Saving the campaign must fail for the session to have data
             * but the widgets and prizes don't have to succed
             */
            $session = new Container('campaign');
            if ($session->data) {
                $data['data'] = $session->data;
                $session->offsetUnset('data');

                $data['entriesData'] = array(
                    'widgetTypes' => $this->getWidgetModel()->getWidgetTypesJson(),
                    'appliedWidgets' => $data['data']['widgets-serialized'],
                );
                
                $data['prizesData'] = $data['data']['prizes-serialized'];
                $data['social'] = $this->getServiceLocator()->get('config')['social'];
            }
        }

        // If we still have no data then return the default data
        if (!$data) {
            $data = $this->getDefaultData();
        }
        
        return $data;
    }
    
    public function fetchView($campaignId) 
    {
        if ($campaignId && is_numeric($campaignId)) {
            $data = array(
                'data' => $this->load($campaignId),
                'entriesData' => $this->getWidgetModel()->getAppliedWidgetsForEntrant($campaignId),
                'prizesData' => $this->getPrizeModel()->getAssociatedPrizesForJavaScript($campaignId),
                'entrantsCount' => $this->getEntrantsModel()->getEntrantsCount($campaignId),
                'chancesCount' => $this->getChanceModel()->getLoggedEntrantsChances(),
                'social' => $this->getServiceLocator()->get('config')['social']
            );
            
            return $data;
        }
    }
    
    /**
     * Fetch the default campaign form data
     * 
     * @return array
     */
    public function getDefaultData()
    {
        return array(
            'messages' => \Base\Model\Session::getGlobalMessages(),
            'data' => array(),
            'entriesData' => array(
                'widgetTypes' => $this->getWidgetModel()->getWidgetTypesJson(),
                'appliedWidgets' => '[]',
            ),
            'prizesData' => '[]',
            'social' => $this->getServiceLocator()->get('config')['social']
        );
    }
    
    /**
     * Load a campaigns data
     * 
     * @param int $campaignId
     */
    public function load($campaignId)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $campaign = $queryBuilder->select('e')
            ->from($this->entity, 'e')
            ->where('e.id= :id')
            ->setParameter('id', $campaignId)
            ->getQuery()
            ->getSingleResult(Query::HYDRATE_ARRAY);
        
        $campaign['startTime'] = $campaign['startTime']->format('Y/m/d H:i');
        $campaign['endTime'] = $campaign['endTime']->format('Y/m/d H:i');
        
        return $campaign;
    }
    
    /**
     * Check if the campaign was created by the currently logged in user
     * 
     * @param int $campaignId
     * @return bool
     */
    public function checkCampaignAuthor($campaignId)
    {
        $userId = $this->getEntityManager()
            ->find($this->entity, $campaignId)
            ->get('user')
            ->get('id');
        
        $loggedInUserId = $this->getUserHelper()->getLoggedInUserId();
        
        return ($userId == $loggedInUserId);
    }
    
    /**
     * Returns a WidgetModel instance
     * 
     * @return Campaign\Model\WidgetModel
     */
    protected function getWidgetModel() 
    {
        if (!$this->widgetModel) {
            $this->widgetModel = new WidgetModel();
            $this->widgetModel->setServiceLocator($this->getServiceLocator());
        }
        
        return $this->widgetModel;
    }
    
    /**
     * Returns a WidgetModel instance
     * 
     * @return Campaign\Model\PrizeModel
     */
    protected function getPrizeModel() 
    {
        if (!$this->prizeModel) {
            $this->prizeModel = new PrizeModel();
            $this->prizeModel->setServiceLocator($this->getServiceLocator());
        }
        
        return $this->prizeModel;
    }
    
    /**
     * Returns an Entrant instance
     * @return Campaign\Model\EntrantModel
     */
    protected function getEntrantsModel()
    {
        $entrantModel = new EntrantModel();
        $entrantModel->setServiceLocator($this->getServiceLocator());
        
        return $entrantModel;
    }
    
    protected function getChanceModel()
    {
        $chanceModel = new ChanceModel();
        $chanceModel->setServiceLocator($this->getServiceLocator());
        
        return $chanceModel;
    }
    
    /**
     * Return a UserHelper instance
     * 
     * @return \User\Helper\UserHelper
     */
    protected function getUserHelper()
    {
        $helper = new UserHelper();
        $helper->updateServiceLocator($this->getServiceLocator());
        
        return $helper;
    }
    
    /**
     * Return all campaigns data for listing
     * @return array
     */
    public function getCampaignsList()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('e.id, e.title, e.banner, e.status')
            ->from($this->entity, 'e')
            ->where('e.user= :user')
            ->setParameter('user', $this->getUserHelper()->getLoggedInUserId())
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
    }
    
    public function getWinnersData2($campaign)
    {
        $now = new \DateTime('', new \DateTimeZone($campaign['timezone']));
        $startTime = new \DateTime($campaign['startTime'], new \DateTimeZone($campaign['timezone']));
        
        $endTime = false;
        $count = 1;

        if ($campaign['type'] == CampaignEntity::CAMPAIGN_TYPE_SINGLE) {
            $endTime = new \DateTime($campaign['endTime'], new \DateTimeZone($campaign['timezone']));
        } else {

            $endTime = clone $startTime;
            $endTime->modify("+{$campaign['cycleDuration']} days");

            while (($count < $campaign['cyclesCount'] || $campaign['cyclesCount'] == 0) && $endTime < $now) {
                $endTime->modify("+{$campaign['cycleDuration']} days");
                $count++;
            }
        }
        
        $data = array ('cycle' => $count, 'endTime' => $endTime, 'complete' => false);
        
        if ($endTime < $now) {
            $data['complete'] = true;
            
            $model = new WinnerModel();
            $model->setServiceLocator($this->getServiceLocator());
            
            $data['winners'] = $model->getWinnersForCampaign($campaign['id']);
        }

        return $data;
    }
    
    public function getWinnersData($campaign)
    {
        $data = array();
        $model = new WinnerModel();
        $model->setServiceLocator($this->getServiceLocator());
        
        $now = new \DateTime('', new \DateTimeZone($campaign['timezone']));
        $startTime = new \DateTime($campaign['startTime'], new \DateTimeZone($campaign['timezone']));
        
        $endTime = false;
        $count = 1;
        
        if ($campaign['type'] == CampaignEntity::CAMPAIGN_TYPE_SINGLE) {
            $endTime = new \DateTime($campaign['endTime'], new \DateTimeZone($campaign['timezone']));
            
            $row = array('endTime' => $endTime, 'complete' => false);
            if ($endTime < $now) {
                $row['complete'] = true;
                $row['winners'] = $model->getWinnersForCampaign($campaign['id']);
            }
            
            $data[$count] = $row;
            
        } else {
            $endTime = clone $startTime;
            $endTime->modify("+{$campaign['cycleDuration']} days");

            while (($count < $campaign['cyclesCount'] || $campaign['cyclesCount'] == 0) && $endTime < $now) {
                
                $row = array('endTime' => clone $endTime, 'complete' => false);
                if ($endTime < $now) {
                    $row['complete'] = true;
                    $row['winners'] = $model->getWinnersForCampaign($campaign['id'], $count);
                }

                $data[$count] = $row;
                
                $endTime->modify("+{$campaign['cycleDuration']} days");
                $count++;
            }
            
            $row = array('endTime' => $endTime, 'complete' => false);
            if ($endTime < $now) {
                $row['complete'] = true;
                $row['winners'] = $model->getWinnersForCampaign($campaign['id'], $count);
            }
            
            $data[$count] = $row;
        }
        
        return $data;
    }
    
    public function isCampaignComplete($campaign)
    {
        if ($campaign->get('type') === CampaignEntity::CAMPAIGN_TYPE_SINGLE) {
            return true;
        }
        
        $dateParts = array();
        $cycle = 0;
        
        $duration = $campaign->get('cycleDuration');
        $totalCycles = $campaign->get('cyclesCount');
        
        $startDate = $campaign->get('startTime');
        $startDate->setTimeZone(new \DateTimeZone($campaign->get('timezone')));
        
        if ($totalCycles == 0) {
            return false;
        }

        $query = 'SELECT ';
        while ($cycle < $totalCycles) {
            $cycle++;
            
            $cycleCondition = 'EXISTS( '
                . 'SELECT id FROM campaign_winner '
                . 'WHERE cycle= ":cycle" '
                . 'LIMIT 1'
                . ') AND ';
            
            $cycleCondition = str_replace(':cycle', $cycle, $cycleCondition);
            $query .= $cycleCondition;
        }
        
        $query = substr($query, 0, -4);
        $query .= 'AS finished';
        
        $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return (bool) $result['finished'];
    }
    
    public function validateEntrantCookie($campaignId)
    {
        $entrant = $this->getEntrantsModel()->getLoadedEntrant();
        
        if ($entrant && $entrant->get('campaign')->get('id') != $campaignId) {
            $this->clearCookie('entrant');
            return false;
        }
        
        return true;
    }
    
    public function extractAndAddFonts($css) 
    {
        $fonts = array();
        $matches = array();
        
        preg_match_all('/\s*([-\w]+)\s*:?\s*(.*?)\s*;/m', $css, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            if (strtolower($match[1]) === 'font-family') {
                $values = str_replace(array('\'', '\"'), '', $match[2]);
                $ruleFonts = array();
                
                foreach (explode(',', $values) as $value) {
                    $value = trim($value);
                    $value = str_replace(' ', '+', $value);
                    $ruleFonts[] = $value;
                }
                
                $fonts = array_merge($ruleFonts, $fonts);
            }
        }
        
        $fonts = array_unique($fonts);
        $fonts = implode('|', $fonts);
        
        $url = "https://fonts.googleapis.com/css?family={$fonts}";
        
        $this->getServiceLocator()
            ->get('viewhelpermanager')
            ->get('headLink')
            ->appendStylesheet($url);
    }
}
