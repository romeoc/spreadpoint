<?php
/**
 * Campaign Winner Model
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

use User\Helper\UserHelper;
use Campaign\Entity\Campaign;
use Campaign\Entity\CampaignWinner;

class WinnerModel extends AbstractModel
{
    // Initialize Winner Model
    public function __construct() 
    {
        $this->init('Campaign\Entity\CampaignWinner');
    }
    
    public function saveWinners($winnersJson) 
    {
        if (!$winnersJson) {
            Session::error('Invalid data provided');
            return false;
        }
        
        $winners = json_decode($winnersJson);

        $campaignId = key((array)$winners);
        $campaign = $this->getEntityManager()->find('Campaign\Entity\Campaign', $campaignId);
        
        if (!$campaign || $campaign->get('status') != Campaign::STATUS_ACTIVE) {
            Session::error("Your campaign is not active");
            return false;
        }
        
        $helper = new UserHelper();
        $helper->updateServiceLocator($this->getServiceLocator());
        $userId = $helper->getLoggedInUserId();
        
        $peopleToNotify = array();
        
        foreach ($winners as $prizeId => $entrants) {
            $prize = $this->getEntityManager()->find('Campaign\Entity\CampaignPrize', $prizeId);
            
            if (!$prize) {
                Session::error("Invalid prize id: {$prizeId}");
                continue;
            }
            
            if ($prize->get('campaign')->get('user')->get('id') != $userId) {
                Session::error("You cannot set the winner for the prize with id: {$prizeId}");
                continue;
            }
            
            foreach ($entrants as $entrantId) {
                $entrant = $this->getEntityManager()->find('Campaign\Entity\CampaignEntrant', $entrantId);
                
                if (!$entrant) {
                    Session::error("Invalid entrant id: {$entrantId}");
                    continue;
                }
                
                $winner = new CampaignWinner();
                $winner->set('prize', $prize);
                $winner->set('entrant', $entrant);

                $this->getEntityManager()->persist($winner);
                $peopleToNotify[] = array(
                    'email' => $entrant->get('email'),
                    'name'  => $entrant->get('email'),
                    'prize' => $prize->get('name')
                );
            }
        }
        
        try {
            $this->getEntityManager()->flush();
            Session::success('All winners data was saved');
            
            $campaign->set('status', Campaign::STATUS_FINISHED);
            $this->getEntityManager()->flush();
            
            $this->notifyWinners($peopleToNotify, $campaign);
            
            return $campaignId;
        } catch (Exception $ex) {
            Session::error('An error occured while saving your winners. Error Message: ' . $ex->getMessage());
            return false;
        }
    }
    
    public function notifyWinners($peopleToNotify, $campaign)
    {
        $body = $campaign->get('winnerEmail');
        
        foreach ($peopleToNotify as $winner) {
            $title = $campaign->get('title');
            $subject = "You have won a prize in the '{$title}' competition!";

            Mail::send($body, $subject, Mail::EMAIL, Mail::NAME, $winner['email'], $winner['name']);
        }
        
        Session::success('All winners have been notified');
    }
    
    public function getWinnersForCampaign($campaignId)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        
        $prizes = $this->getEntityManager()->createQueryBuilder()
            ->select('p.id')
            ->from('Campaign\Entity\CampaignPrize', 'p')
            ->where('p.campaign= :campaign')
            ->getDQL();
        
        $winners = $this->getEntityManager()->createQueryBuilder()
            ->select('IDENTITY(w.prize) AS prize','e.email')
            ->from($this->entity, 'w')
            ->innerJoin('Campaign\Entity\CampaignEntrant', 'e', 'WITH', 'w.entrant = e.id')
            ->where($queryBuilder->expr()->in('w.prize', $prizes))
            ->setParameter('campaign', $campaignId)
            ->orderBy('w.prize')
            ->getQuery()
            ->setHint(Query::HINT_INCLUDE_META_COLUMNS, true)
            ->getArrayResult();
        
        return $winners;
    }
}