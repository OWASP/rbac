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

    public function libraryStart()
    {
        $content = new Template(dirname(dirname(dirname(__FILE__))) . '/views/start_library_install.tpl.php');

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

        if ($_POST['component'] === 'library') {
            header('Location: library/start');
            exit;
        } elseif ($_POST['component'] === 'tests') {
            header('Location: tests/start');
            exit;
        } else {
            throw new \Exception('Error 404: Page not found!');
        }
    }

}