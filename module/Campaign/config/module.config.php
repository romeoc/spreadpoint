<?php
return array(
    'doctrine' => array(
        'driver' => array(
            'campaign_entities' => array(
                'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/Campaign/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Campaign\Entity' => 'campaign_entities'
                )
            )
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Campaign\Controller\Campaign' => 'Campaign\Controller\CampaignController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'campaign' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/campaign[/[:action[/[:id]]]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Campaign\Controller\Campaign',
                        'action'     => 'list',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'wildcard' => array(
                        'type' => 'Wildcard',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'campaign' => __DIR__ . '/../view',
        ),
        'strategies' => array(
           'ViewJsonStrategy',
        ),
    ),
    'view_helpers' => array(
        'invokables'=> array(
            'campaignHelper' => 'Campaign\Helper\CampaignHelper'  
        )
    ),
);