<?php
/**
 * Original Class from: Simple Route - http://www.phpclasses.org/package/7405-PHP-Parse-and-build-URIs-from-routing-rules.html
 */
namespace Gui;

class Route
{
    // TRAP REQUESTS ARRAY:
    public static $routes;
    private static $param;

    public static function add($routes)
    {
        self::$routes[] = $routes;
    }

    /*
    public static function getBaseUrl()
    {
        if (isset($_SERVER['HTTPS'])) {
            $baseUrl = 'https://' . $_SERVER['SERVER_NAME'];
        } else {
            $baseUrl = 'http://' . $_SERVER['SERVER_NAME'];
        }

        $phpSelf = ltrim($_SERVER['PHP_SELF'], '/');

        $phpSelf = explode('/', $phpSelf);

        foreach ($phpSelf as $val) {
            if ($val !== 'PhpRbac') {
                $baseUrl .= '/' . $val;
            } else {
                $baseUrl .= '/' . $val;
                break;
            }
        }

        return $baseUrl;
    }
    //*/

    public static function setRoutes($locationDir = null)
    {
        if ($locationDir !== null) {
            require_once $locationDir;
            self::add($routes);

            return true;
        } else {
            return false;
        }

    }

    public static function matchURI($uri = null)
    {
        if (isset($_SERVER['PATH_INFO'])) {
            if ($_SERVER['PATH_INFO'] === '/') {
                $pathInfo = '';
            } else {
                $pathInfo = $_SERVER['PATH_INFO'];
            }
        } else {
            $pathInfo = '';
        }

        $uri = (!$uri) ? $pathInfo : $uri;
        $uri = (!$uri) ? '/' : rtrim($uri,"\/");

        if(!empty(self::$routes)) {
            $count=count(self::$routes);
            for($i=0; $i<$count; ++$i) {
                foreach(self::$routes[$i] as $k => $v) {
                    if (is_array($v) and $k !== 'param') {
                        self::$param = self::$routes[$i]['param'];
                        $v['request'] = preg_replace_callback("/\<(?<key>[0-9a-z_]+)\>/",
                            'Gui\Route::_replacer',
                            str_replace(")",")?", $v['request'])
                        );
                        $rulleTemp = array_merge((array)self::$routes[$i], (array)$v);
                        if(($t = self::_reportRulle($rulleTemp, $uri)))
                            return $t;
                    }
                }
            }

        } else return array();
    }

    private static function _replacer($matches)
    {
        if(isset(self::$param[$matches['key']])) {
            return "(?<".$matches['key'].">".self::$param[$matches['key']].")";
        } else return "(?<".$matches['key'].">"."([^/]+)".")";
    }

    private static function _reportRulle($ini_array, $uri)
    {
        if(is_array($ini_array) and $uri) {
            if(preg_match("#^".$ini_array['request']."$#", $uri, $match)){
                $r = array_merge((array)$ini_array, (array)$match);
                foreach($r as $k => $v)
                    if((int)$k OR $k == 'param' OR $k == 'request')
                        unset($r[$k]);
                return $r;
            }
        }
    }
    /** =================================================================== **/
}