<?php

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'pricing' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/pricing',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'pricing',
                    ),
                ),
            ),
            'contact' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/contact',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'contact',
                    ),
                ),
            ),
            'terms-of-service' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/terms-of-service',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'termsOfService',
                    ),
                ),
            ),
            'privacy-policy' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/privacy-policy',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'privacyPolicy',
                    ),
                ),
            ),
            'contact' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/contact',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'contact',
                    ),
                ),
            ),
            'support' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/support',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'support',
                    ),
                ),
            ),
            'contact/sendContactEmail' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/contact/sendContactEmail',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'sendContactEmail',
                    ),
                ),
            ),
            'support/sendSupportEmail' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/support/sendSupportEmail',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'sendSupportEmail',
                    ),
                ),
            )
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController'
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'layout/empty'            => __DIR__ . '/../view/layout/empty.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
