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

use Campaign\Entity\CampaignPrize;
use Base\Model\AbstractModel;
use Base\Model\Session;
use User\Helper\UserHelper;

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
        $campaignId = $campaign->get('id');
        $this->removeEliminatedPrizes($campaignId, $prizes);

        $files = $this->getUploadedFiles();
        $allValid = true;
        
        $userHelper = new UserHelper();
        $userHelper->updateServiceLocator($this->getServiceLocator());
        
        foreach ($prizes as $prize) {
            // Skip empty values
            if (!$prize) {
                continue;
            }
            
            // Check if a valid file was uploaded
            $fileName = "prize-" . $prize['referenceId'];
            $file = $files[$fileName];
            $hasValidFile = ($files[$fileName]['name'] && $this->isFileValid($file));
            
            // We don't need the referenceId, this is only used by the javascript controller
            unset($prize['referenceId']);
            // Add campaign data
            $prize['campaign'] = $campaign;
            
            // Save Prize if all data are valid
            if ($this->validate($prize, $hasValidFile)) {
                /**
                 * Remove image field if we don't have a valid image so it will not be updated
                 * Note that if this is a new entity and we don't have a valid file then
                 * the validate method above will return false
                 */
                if (!$hasValidFile) {
                    unset($prize['image']);
                }
                // Save prize
                $saveResult = $this->save($prize);
                if (!$saveResult) {
                    Session::error("One of your prizes was not saved properly!");
                    $allValid = false;
                } elseif ( $hasValidFile ) {// Upload prize image
                    // Create file path
                    $prizeId = $saveResult->get('id');
                    $mediaPath = "public/media/$campaignId/";

                    // Generate new file name
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $rename = "prize-$prizeId.$extension";

                    // Upload file
                    $this->uploadFile($fileName, $mediaPath, $rename);
                    // Update image field in the DB
                    $saveResult->set('image', $rename);
                } 
            } else {
                $allValid = false;
            }
        }
        
        // Save changes to the image fields after uploading the files
        $this->getEntityManager()->flush();
        return $allValid;
    }
    
    /**
     * Validate prize data before saving
     * 
     * @param array $data
     * @param bool $hasValidFile
     * @return bool
     */
    protected function validate($data, $hasValidFile)
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
        } elseif (strlen($data['description']) > 255) {
            Session::error("The <strong>'Description'</strong> of your prize can't be longer than 10000 characters");
            $errorsFound = true;
        }
        
        if (!array_key_exists('image', $data) || !$data['image']) {
            Session::error("You didn't provide an <strong>'Image'</strong> for your prize");
            $errorsFound = true;
        } elseif (!array_key_exists('id', $data) && !$hasValidFile) {
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
            $this->updateStatus($prizeId, CampaignPrize::STATUS_DISABLED);
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
            ->andWhere('e.status= :status')
            ->setParameter('campaign', $campaignId)
            ->setParameter('status', CampaignPrize::STATUS_ACTIVE)
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
        
        return $prizes;
    }
    
    /**
     * Update the status for a prize
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
