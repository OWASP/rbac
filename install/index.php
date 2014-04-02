<?php
include 'autoload.php';

use Gui\Route;
use Gui\Template;

Route::setRoutes(__DIR__ . '/routes.php');
$match = Route::matchURI();

/*
echo '<br />$_SERVER["PHP_SELF"] is: ' . $_SERVER['PHP_SELF'] . '<br />';
exit;
//*/

/*
echo '<br />$match["controller"] is: ' . $match['controller'] . '<br />';
//exit;
//*/

/*
echo '<br /><pre>$match: ';
echo print_r($match);
echo '</pre><br />';
exit;
//*/

/*
 echo '<br /><pre>Route::$routes: ';
echo print_r(Route::$routes);
echo '</pre><br />';
//exit;
//*/

$controller = new $match['controller'];
$action = $match['action'];

try {
    $content = $controller->$action();
} catch (Exception $e) {
    $content = $e->getMessage();
}

$tpl = new Template();

$tpl->set("content", $content);

// Render active theme template (which in turn loads all other templates passed to it using the 'set' method)
echo $tpl->fetch('views/theme.tpl.php');
