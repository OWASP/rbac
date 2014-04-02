<?php
namespace Install;

use Gui\Template;
use PhpRbac\Rbac;

class InstallController
{
    public $baseUrl = '';
    public $baseDir = '';

    public function __construct()
    {
        if (isset($_SERVER['HTTPS'])) {
            $this->baseUrl = 'https://' . $_SERVER['SERVER_NAME'];
        } else {
            $this->baseUrl = 'http://' . $_SERVER['SERVER_NAME'];
        }

        $phpSelf = ltrim($_SERVER['PHP_SELF'], '/');

        $phpSelf = explode('/', $phpSelf);

        foreach ($phpSelf as $val) {
            if ($val !== 'index.php') {
                $this->baseUrl .= '/' . $val;
            } else {
                $this->baseUrl .= '/' . $val;
                break;
            }
        }

        $this->baseDir = dirname(dirname(dirname(dirname(__FILE__)))) . '/PhpRbac';
    }

    public function index()
    {
        $content = new Template(dirname(dirname(dirname(__FILE__))) . '/views/index.tpl.php');
        $content->set('baseUrl', $this->baseUrl);

        return $content;
    }

    public function libraryStart()
    {
        $content = new Template(dirname(dirname(dirname(__FILE__))) . '/views/start_library_install.tpl.php');
        $content->set('baseUrl', $this->baseUrl);

        return $content;
    }

    public function testsStart()
    {
        $content = new Template(dirname(dirname(dirname(__FILE__))) . '/views/start_tests_install.tpl.php');
        $content->set('baseUrl', $this->baseUrl);

        return $content;
    }

    public function submitDbInfo()
    {
        /*
        echo '<br /><pre>$_POST: ';
        echo print_r($_POST);
        echo '</pre><br />';
        exit;
        //*/

        $errors = array(
            'pw_match' => 1,
            'dbTablePrefix' => 1
        );

        // Set error flag
        $errorExists = false;

        // Start error string
        $errors = '?errorsExist=1&blankFields=1&error_dbAdapter=' . $_POST['dbAdapter'] . '&';

        // Make sure there are no empty fields
        if (empty($_POST['dbHost'])) {
            // Create error
            $errorExists = true;
            $errors .= 'error_dbHost=1&';
        } else {
            $errors .= 'error_dbHost=' . $_POST['dbHost'] . '&';
        }

        if (empty($_POST['dbName'])) {
            // Create error
            $errorExists = true;
            $errors .= 'error_dbName=1&';
        } else {
            $errors .= 'error_dbName=' . $_POST['dbName'] . '&';
        }

        if (empty($_POST['dbTablePrefix'])) {
            // Create error
            $errorExists = true;
            $errors .= 'error_dbTablePrefix=1&';
        } else {
            $errors .= 'error_dbTablePrefix=' . $_POST['dbTablePrefix'] . '&';
        }

        if (empty($_POST['dbUser'])) {
            // Create error
            $errorExists = true;
            $errors .= 'error_dbUser=1&';
        } else {
            $errors .= 'error_dbUser=' . $_POST['dbUser'] . '&';
        }

        if (empty($_POST['dbPassword'])) {
            // Create error
            $errorExists = true;
            $errors .= 'error_dbPassword=1&';
        } else {
            $errors .= 'error_dbPassword=' . $_POST['dbPassword'] . '&';
        }

        if (empty($_POST['dbPasswordConfirm'])) {
            // Create error
            $errorExists = true;
            $errors .= 'error_dbPasswordConfirm=1&';
        } else {
            $errors .= 'error_dbPasswordConfirm=' . $_POST['dbPasswordConfirm'] . '&';
        }

        $errors = rtrim($errors, '&');

        if ($_POST['submit'] === 'library') {
            if ($errorExists === true) {
                header('Location: ' . $this->baseUrl . '/library/start' . $errors);
                exit;
            }

            // Make sure the passwords match

            if ($_POST['dbPassword'] === $_POST['dbPasswordConfirm']) {
                // Pass

                $this->installLibrary($_POST);

                /*
                header('Location: ' . $this->baseUrl . '/library/process?process=1&dbAdapter=' . $_POST['dbAdapter'] . '&dbHost=' . $_POST['dbHost'] . '&dbName=' . $_POST['dbName'] . '&dbTablePrefix=' . $_POST['dbTablePrefix'] . '&dbUser=' . $_POST['dbUser'] . '&dbPassword=' . $_POST['dbPassword'] . '&dbPasswordConfirm=' . $_POST['dbPasswordConfirm']);
                exit;
                //*/
            } else {
                // Fail
                header('Location: ' . $this->baseUrl . '/library/start?errorsExist=1&error_pw_match=1&error_dbAdapter=' . $_POST['dbAdapter'] . '&error_dbHost=' . $_POST['dbHost'] . '&error_dbName=' . $_POST['dbName'] . '&error_dbTablePrefix=' . $_POST['dbTablePrefix'] . '&error_dbUser=' . $_POST['dbUser']);
                exit;
            }
        } elseif($_POST['submit'] === 'tests') {
            if ($errorExists === true) {
                header('Location: ' . $this->baseUrl . '/tests/start' . $errors);
                exit;
            }

            // Make sure the passwords match

            if ($_POST['dbPassword'] === $_POST['dbPasswordConfirm']) {
                // Pass
                header('Location: ' . $this->baseUrl . '/tests/process?process=1&dbAdapter=' . $_POST['dbAdapter'] . '&dbHost=' . $_POST['dbHost'] . '&dbName=' . $_POST['dbName'] . '&dbTablePrefix=' . $_POST['dbTablePrefix'] . '&dbUser=' . $_POST['dbUser'] . '&dbPassword=' . $_POST['dbPassword'] . '&dbPasswordConfirm=' . $_POST['dbPasswordConfirm']);
                exit;
            } else {
                // Fail
                header('Location: ' . $this->baseUrl . '/tests/start?errorsExist=1&error_pw_match=1&error_dbAdapter=' . $_POST['dbAdapter'] . '&error_dbHost=' . $_POST['dbHost'] . '&error_dbName=' . $_POST['dbName'] . '&error_dbTablePrefix=' . $_POST['dbTablePrefix'] . '&error_dbUser=' . $_POST['dbUser']);
                exit;
            }
        }

        //return true;
    }

    private function installLibrary($configVars)
    {
        //*
        echo '<br /><pre>$configVars: ';
        echo print_r($configVars);
        echo '</pre><br />';
        //exit;
        //*/

        // Create database.config

        if ($configVars['dbAdapter'] == 'MySQL') {
            $dbAdapterString = '$adapter="pdo_mysql";';
            $dbNameString = '$dbname="' . $configVars['dbName'] . '";';
        } elseif ($configVars['dbAdapter'] == 'MySQLi') {
            $dbAdapterString = '$adapter="mysqli";';
            $dbNameString = '$dbname="' . $configVars['dbName'] . '";';
        } else {
            $dbAdapterString = '$adapter="pdo_sqlite";';
            $dbNameString = '$dbname=__DIR__."/' . $configVars['dbName'] . '.sqlite3";';
        }

        $data =	 '<?php

' . $dbAdapterString . '
$host="' . $configVars['dbHost'] . '";

' . $dbNameString . '
$tablePrefix = "' . $configVars['dbTablePrefix'] . '";

$user="' . $configVars['dbUser'] . '";
$pass="' . $configVars['dbPassword'] . '";

';

        $dbConnFile = $this->baseDir . '/database/database.config';

        file_put_contents($dbConnFile, $data);

        $currentOS = strtoupper(substr(PHP_OS, 0, 3));

        if ($currentOS != 'WIN') {
            chmod($dbConnFile, 0644);
        }

        /*
        // Instantiate PhpRbac\Rbac object
        require_once 'autoload.php';
        //*/

        $rbac = new Rbac();

        // execute '$rbac->reset(true);'
        $res = $rbac->reset(true);

        //*
        echo '<br /><pre>$res: ';
        echo var_dump($res);
        echo '</pre><br />';
        exit;
        //*/

        if ($configVars['submit'] == 'library') {
            // Send to Success Message
            header('Location: ' . $this->baseUrl . '/library/success');
            exit;
        } elseif ($configVars['submit'] == 'library') {
            // Send to Success Message
            header('Location: ' . $this->baseUrl . '/tests/success');
            exit;
        }

        return false;
    }

    private function installTests()
    {
        return true;
    }

    public function successLibrary()
    {
        return 'You successfully installed the Library';
    }

    public function successTests()
    {
        return 'You successfully installed the Unit Tests';
    }

    public function testStuff()
    {

        //*
        echo '<br />I am here: ' . __FILE__ . ': ' . __LINE__ . '<br />';
        //exit;
        //*/

        exit;
        //return true;
    }

    public function hub()
    {
        /*
        echo '<br /><pre>$_POST: ';
        echo print_r($_POST);
        echo '</pre><br />';
        exit;
        //*/

        if ($_POST['component'] === 'library') {
            header('Location: ' . $this->baseUrl . '/library/start');
            exit;
        } elseif ($_POST['component'] === 'tests') {
            header('Location: ' . $this->baseUrl . '/tests/start');
            exit;
        } else {
            throw new \Exception('Error 404: Page not found!');
        }
    }

}