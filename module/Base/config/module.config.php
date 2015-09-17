<?php
return array(
    'view_manager' => array(
        'template_path_stack' => array(
            'email' => __DIR__ . '/../view',
        ),
    ),
    'view_helpers' => array(
        'invokables'=> array(
            'configHelper' => 'Base\Helper\Config'  
        )
    ),
);