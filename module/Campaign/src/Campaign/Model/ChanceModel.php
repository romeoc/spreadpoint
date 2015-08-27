<?php
/**
 * Entrant Chance Model
 *
 * @module     Campaign
 * @author     SpreadPoint <support@spreadpoint.co>
 * @copyright  Copyright (c) 2015 SpreadPoint (http://www.spreadpoint.co)
 */

namespace Campaign\Model;

use Doctrine\ORM\Query;
use Base\Model\AbstractModel;

class ChanceModel extends AbstractModel
{
    // Initialize Entrant Chance Model
    public function __construct() 
    {
        $this->init('Campaign\Entity\CampaignEntrantChance');
    }
    
    public function load($entrant, $widget)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $chance = $queryBuilder->select('e')
            ->from($this->entity, 'e')
            ->where('e.entrant = :entrant')
            ->andWhere('e.widget = :widget')
            ->setParameter('entrant', $entrant)
            ->setParameter('widget', $widget)
            ->getQuery()
            ->setMaxResults(1)
            ->getResult();
        
        return $chance;
    }
    
    /**
     * Get all widgets the entrant completed
     * @param int | Entrant/Entity/CampaignEntrant $entrant
     */
    public function getCompletedWidgetIds($entrant) 
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $widgetIds = $queryBuilder->select('IDENTITY(e.widget)')
            ->from($this->entity, 'e')
            ->where('e.entrant= :entrant')
            ->setParameter('entrant', $entrant)
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);
        
        $result = array();
        foreach ($widgetIds as $widget) {
            $result[] = $widget[1];
        }
        
        return $result;
    }
    
    public function getLoggedEntrantsChances()
    {
        $chances = 0;
        $entrant = $this->getCookie('entrant');
        
        if ($entrant) {
            $chances = $this->getEntityManager()->createQueryBuilder()
                ->select('sum(w.earningValue)')
                ->from($this->entity, 'c')
                ->innerJoin('Campaign\Entity\CampaignWidget', 'w', 'WITH', 'c.widget = w.id')
                ->where('c.entrant = :entrant')
                ->setParameter('entrant', $entrant)
                ->getQuery()
                ->setHint(Query::HINT_INCLUDE_META_COLUMNS, true)
                ->getResult()[0][1];            
        }
        
        return $chances;
    }
}