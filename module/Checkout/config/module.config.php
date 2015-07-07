<?php
return array(
    'doctrine' => array(
        'driver' => array(
            'checkout_entities' => array(
                'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/Checkout/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Checkout\Entity' => 'checkout_entities'
                )
            )
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Checkout\Controller\Cart' => 'Checkout\Controller\CartController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'checkout' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/checkout[/[:controller[/[:action[/]]]]]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Checkout\Controller\Cart',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'checkout' => __DIR__ . '/../view',
        ),
    ),
);