<?php

/**
 * Campaign Prize Model
 *
 * @module     Campaign
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Campaign\Model;

use Doctrine\ORM\Query;

use Base\Model\AbstractModel;
use Base\Model\Session;

class PrizeModel extends AbstractModel
{
    // Initialize Campaign Model
    public function __construct() 
    {
        $this->init('Campaign\Entity\CampaignPrize');
    }
    
    /**
     * Save prizes for a campaign
     * 
     * @param Campaign\Entity\Campaign $campaign
     * @param string(JSON) $data
     */
    public function savePrizes($campaign, $data)
    {
        $prizes = json_decode($data, true);
        $this->removeEliminatedPrizes($campaign->get('id'), $prizes);

        $files = $this->getUploadedFiles();
        $allValid = true;
        
        foreach ($prizes as $prize) {
            // Skip empty values
            if (!$prize) {
                continue;
            }
            
            // If an image was uploaded for this field reset the image name
            $fileName = 'prize-' . $prize['referenceId'];
            if (array_key_exists($fileName, $files) && $this->isFileValid($files[$fileName])) {
                $extension = pathinfo($files[$fileName]['name'], PATHINFO_EXTENSION);
                $prize['image'] = $fileName . '.' . $extension;
            }
            
            // We don't need the referenceId, this is only used by the javascript controller
            unset($prize['referenceId']);
            // Add campaign data
            $prize['campaign'] = $campaign;
            
            // Save Prize if all data are valid
            if ($this->validate($prize)) {
                $saveResult = $this->save($prize);
                if (!$saveResult) {
                    Session::error("One of your prizes was not saved properly!");
                    $allValid = false;
                } 
            } else {
                $allValid = false;
            }
        }
        
        return $allValid;
    }
    
    /**
     * Validate prize data before saving
     * 
     * @param array $data
     * @return bool
     */
    protected function validate($data)
    {
        $errorsFound = false;
        
        foreach ($data as $key => $value) {
            if (!property_exists($this->entity, $key)) {
                unset($data[$key]);
            }
        }
        
        if (!array_key_exists('campaign', $data) || get_class($data['campaign']) != 'Campaign\Entity\Campaign') {
            Session::error("An invalid campaign was specified to one of the widgets!");
            $errorsFound = true;
        }
        
        if (!array_key_exists('name', $data) || !$data['name']) {
            Session::error("You didn't provide a <strong>'Name'</strong> for your prize");
            $errorsFound = true;
        } elseif (strlen($data['name']) > 32) {
            Session::error("The <strong>'Name'</strong> of your prize can't be longer than 32 characters");
            $errorsFound = true;
        }
        
        if (!array_key_exists('description', $data) || !$data['description']) {
            Session::error("You didn't provide a <strong>'Description'</strong> for your prize");
            $errorsFound = true;
        } elseif (strlen($data['description']) > 10000) {
            Session::error("The <strong>'Description'</strong> of your prize can't be longer than 10000 characters");
            $errorsFound = true;
        }
        
        if (!array_key_exists('image', $data) || !$data['image']) {
            Session::error("You didn't provide an <strong>'Image'</strong> for your prize");
            $errorsFound = true;
        }
        
        if (!array_key_exists('count', $data) || !$data['count']) {
            Session::error("You didn't provide a <strong>'Count'</strong> for your prize");
            $errorsFound = true;
        } elseif (!is_numeric($data['count'])) {
            Session::error("You provided an invalid <strong>'Count'</strong> for your prize");
            $errorsFound = true;
        } elseif (strlen($data['count']) > 10) {
            Session::error("Your prize <strong>'Count'</strong> is to big");
            $errorsFound = true;
        }
        
        return !$errorsFound;
    }
    
    /**
     * Remove the prizes that were eliminated from the database
     *
     * @param int $campaignId 
     * @param array $data
     */
    protected function removeEliminatedPrizes($campaignId, $data)
    {
        // We fetch all prize ids for our campaign
        $allCampaignPrizes = $this->getEntityManager()
            ->createQuery("SELECT e.id FROM $this->entity e WHERE e.campaign = $campaignId")
            ->getScalarResult();
        
        $prizeIds = array_map('current', $allCampaignPrizes);
        
        // Then we remove from this list all prize ids that we received from the request
        foreach ($data as $prize) {
            if ($prize && array_key_exists('id', $prize)) {
                $prizeIds = array_diff($prizeIds, [$prize['id']]);
            }
        }
    
        // Everything that is left was removed by the user so we delete it
        foreach ($prizeIds as $prizeId) {
            $this->delete($prizeId);
        }
    }
    
    /**
     * Get all prizes associated with a campaign
     * 
     * @param int $campaignId
     * @return array
     */
    public function getAssociatedPrizes($campaignId)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $prizes = $queryBuilder->select('e')
            ->from($this->entity, 'e')
            ->where('e.campaign= :campaign')
            ->setParameter('campaign', $campaignId)
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
        
        return $prizes;
    }
    
    /**
     * Get all prizes associated with a campaign in JSON format
     * 
     * @param int $campaignId
     * @return JSON (string)
     */
    public function getAssociatedPrizesForJavaScript($campaignId)
    {
        $prizes = $this->getAssociatedPrizes($campaignId);
        
        if ($prizes) {
            $count = 1;
            foreach ($prizes as &$prize) {
                $prize['referenceId'] = $count++;
            }
        }
        
        return json_encode($prizes);
    }
}
