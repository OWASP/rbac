<?php
include 'autoload.php';
use Install\Route;
use Install\Template;

Route::setRoutes();

$match = Route::matchURI();

$controller = new $match['controller'];
$action = $match['action'];

$content = $controller->$action();

$tpl = new Template();

$tpl->set("content", $content);

// Render active theme template (which in turn loads all other templates assigned to it)
echo $tpl->fetch('install/views/theme.tpl.php');

/*
echo '<br /><pre>$match: ';
echo print_r($match);
echo '</pre><br />';
//exit;
//*/

/*
echo '<br /><pre>Route::$routes: ';
echo print_r(Route::$routes);
echo '</pre><br />';
//exit;
//*/
