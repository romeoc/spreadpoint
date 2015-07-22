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
}