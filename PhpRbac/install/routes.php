<?php
$routes = array(
    array(
        'request' => '/',
        'action' => 'index',
    ),

    array(
        'request' => '/hub',
        'action' => 'hub',
    ),

    array(
        'request' => '/success',
        'action' => 'success',
    ),

    array(
        'request' => '/phprbac/start',
        'action' => 'phpRbacStart',
    ),

    array(
        'request' => '/tests/start',
        'action' => 'testsStart',
    ),

    array(
        'request' => '/<lang>/catalog(/<test>)', // key url section
        'action' => 'catalog_review'
    ),

    array(
        'request' => '/news(/page/<pagination>|/<page>)', // key url section
        'action' => 'news',
    ),

    array(
        'request' => '/<lang>/<action>', // key url section
        'action' => 'page' // default action in url
    ),

    //'namespace' => 'Install',

    'controller' => 'Install\\InstallController',

    'param' => array(
        'lang' => '[a-z]{2}', // regex url parametr <lang>
        'action' => '(contact|servise|go|[a-z]{5,25})', // regex url parametr <action>
        'page' => '[a-z0-9_\-]{5,25}',
        'pagination' => '[0-9]{1,2}',
        'year' => '[0-9]{4}'
    )
);
