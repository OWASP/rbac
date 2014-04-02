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
        'request' => '/library/start',
        'action' => 'libraryStart',
    ),

    array(
        'request' => '/tests/start',
        'action' => 'testsStart',
    ),

    array(
        'request' => '/submit',
        'action' => 'submitDbInfo',
    ),

    /*
    array(
        'request' => '/library/process',
        'action' => 'installLibrary',
    ),

    array(
        'request' => '/tests/process',
        'action' => 'installTests',
    ),
    //*/

    array(
        'request' => '/library/success',
        'action' => 'successLibrary',
    ),

    array(
        'request' => '/tests/success',
        'action' => 'successTests',
    ),

    /*
    array(
        'request' => '/test/stuff(/<errors>)',
        'action' => 'testStuff',
    ),
    //*/

    /*
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
    //*/

    'controller' => 'Install\\InstallController',

    'param' => array(
        //'lang' => '[a-z]{2}', // regex url parametr <lang>
        'action' => '(contact|servise|go|[a-z]{5,25})', // regex url parametr <action>
        //'page' => '[a-z0-9_\-]{5,25}',
        //'pagination' => '[0-9]{1,2}',
        //'year' => '[0-9]{4}'
    )
);
