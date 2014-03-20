<?php
namespace Install;

use Install\Template;

class InstallController
{
    public function index()
    {
        $content = new Template(dirname(dirname(dirname(__FILE__))) . '/views/index.tpl.php');

        return $content;
    }

    public function phpRbacStart()
    {
        $content = new Template(dirname(dirname(dirname(__FILE__))) . '/views/start_phprbac_install.tpl.php');

        return $content;
    }

    public function testsStart()
    {
        $content = new Template(dirname(dirname(dirname(__FILE__))) . '/views/start_tests_install.tpl.php');

        return $content;
    }

    public function hub()
    {
        /*
        echo '<br /><pre>$_POST: ';
        echo print_r($_POST);
        echo '</pre><br />';
        //exit;
        //*/

        if ($_POST['component'] === 'core') {
            header('Location: phprbac/start');
            exit;
        } elseif ($_POST['component'] === 'tests') {
            header('Location: tests/start');
            exit;
        } else {
            throw \Exception('This is not a valid choice');
        }
    }

}