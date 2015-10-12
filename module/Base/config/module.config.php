<?php
return array(
    'router' => array(
        'routes' => array(
            'survey/send' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/survey/send',
                    'defaults' => array(
                        'controller' => 'Base\Controller\Survey',
                        'action'     => 'send',
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Base\Controller\Survey' => 'Base\Controller\SurveyController'
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'email' => __DIR__ . '/../view',
        ),
    ),
    'view_helpers' => array(
        'invokables'=> array(
            'configHelper' => 'Base\Helper\Config',
            'baseHelper' => 'Base\Helper\Base',
        )
    ),
);