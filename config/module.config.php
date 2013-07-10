<?php
return array(
    'view_manager' => array(
        'template_map' => array(
            'arc/bootstrap/control-group' => __DIR__ . '/../views/bootstrap/control-group.phtml',
            'arc/bootstrap/horizontal-form' => __DIR__ . '/../views/bootstrap/horizontal-form.phtml',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'arcControlGroup' => 'ArcFormViewHelpers\View\Helper\Bootstrap\ArcControlGroup',
            'arcHorizontalForm' => 'ArcFormViewHelpers\View\Helper\Bootstrap\ArcHorizontalForm',
        ),
    ),
);