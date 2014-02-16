<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>PHP-RBAC Installation</title>

        <link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.4.1/pure-min.css">

    </head>

    <body>

        <div class="pure-g-r">
            <div class="pure-u-1-3"></div>

            <div class="pure-u-1-3">

<?php if ((!isset($_POST['submit'])) && (!isset($_GET['process'])) && (!isset($_GET['success']))): ?>

<?php

if (isset($_GET['errorsExist'])) {

    if (isset($_GET['blankFields'])) {
        $fieldsRequired = true;
    }

    if (isset($_GET['error_pw_match'])) {
        $error_pw_match = true;
    }

    if (isset($_GET['error_dbAdapter'])) {
        if ($_GET['error_dbAdapter'] == 1) {
            $dbAdapter = '';
        } else {
            $dbAdapter = $_GET['error_dbAdapter'];
        }
    }

    if (isset($_GET['error_dbHost'])) {
        if ($_GET['error_dbHost'] == 1) {
            $dbHost = '';
        } else {
            $dbHost = $_GET['error_dbHost'];
        }
    }

    if (isset($_GET['error_dbName'])) {
        if ($_GET['error_dbName'] == 1) {
            $dbName = '';
        } else {
            $dbName = $_GET['error_dbName'];
        }
    }

    if (isset($_GET['error_dbTablePrefix'])) {
        if ($_GET['error_dbTablePrefix'] == 1) {
            $dbTablePrefix = '';
        } else {
            $dbTablePrefix = $_GET['error_dbTablePrefix'];
        }
    }

    if (isset($_GET['error_dbUser'])) {
        if ($_GET['error_dbUser'] == 1) {
            $dbUser = '';
        } else {
            $dbUser = $_GET['error_dbUser'];
        }
    }

} else {
    $dbAdapter = 'MySQL';
    $dbHost = '';
    $dbName = '';
    $dbTablePrefix = '';
    $dbUser = '';
}

?>

<h1>PHP-RBAC Quick Install</h1>

<h2>Database information</h2>

<?php if (isset($fieldsRequired)): ?>

<p>
    <strong style="color: red">All Fields Are Required</strong>
</p>

<?php elseif (isset($_GET['error_pw_match'])) :?>

<p>
    <strong style="color: red">Passwords Do Not Match</strong>
</p>

<?php endif; ?>

<form class="pure-form pure-form-aligned" name="dbInfo" action="" method="POST">

    <fieldset>

        <div class="pure-control-group">
            <label for="dbAdapter">Database Adapter:</label>
            <select id="dbAdapter" name="dbAdapter">

                    <?php if ($dbAdapter == 'MySQL'): ?>
                        <option selected="selected">MySQL</option>
                    <?php else: ?>
                        <option>MySQL</option>
                    <?php endif; ?>

                    <?php if ($dbAdapter == 'MySQLi'): ?>
                        <option selected="selected">MySQLi</option>
                    <?php else: ?>
                        <option>MySQLi</option>
                    <?php endif; ?>

                    <?php if ($dbAdapter == 'SQLite'): ?>
                        <option selected="selected">SQLite</option>
                    <?php else: ?>
                        <option>SQLite</option>
                    <?php endif; ?>

                </select>
        </div>

        <div class="pure-control-group">
            <label for="dbHost">Database Host Name/IP:</label>
            <input id="dbHost" type="text" name="dbHost" value="<?php echo $dbHost; ?>" required />
        </div>

        <div class="pure-control-group">
            <label for="dbName">Database Name:</label>
            <input id="dbName" type="text" name="dbName" value="<?php echo $dbName; ?>" required />
        </div>

        <div class="pure-control-group">
            <label for="dbTablePrefix">Table Prefix:</label>
            <input id="dbTablePrefix" type="text" name="dbTablePrefix" value="<?php echo ($dbTablePrefix == '') ? 'phprbac_' : $dbTablePrefix;  ?>" required />
        </div>

        <div class="pure-control-group">
            <label for="dbUser">Database User:</label>
            <input id="dbUser" type="text" name="dbUser" value="<?php echo $dbUser; ?>" required />
        </div>

        <div class="pure-control-group">
            <label for="dbPassword">Password:</label>
            <input id="dbPassword" type="password" name="dbPassword" value="" required />
        </div>

        <div class="pure-control-group">
            <label for="dbPasswordConfirm">Confirm Password:</label>
            <input id="dbPasswordConfirm" type="password" name="dbPasswordConfirm" value="" required />
        </div>

        <div class="pure-controls">
            <button type="submit" name="submit" class="pure-button pure-button-primary">Submit</button>
        </div>

    </fieldset>

</form>

<?php elseif(isset($_POST['submit'])): ?>

    <?php

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

    if ($errorExists === true) {
        header('Location: install.php' . $errors);
        exit;
    }

    // Make sure the passwords match

    if ($_POST['dbPassword'] === $_POST['dbPasswordConfirm']) {
        // Pass
        header('Location: install.php?process=1&dbAdapter=' . $_POST['dbAdapter'] . '&dbHost=' . $_POST['dbHost'] . '&dbName=' . $_POST['dbName'] . '&dbTablePrefix=' . $_POST['dbTablePrefix'] . '&dbUser=' . $_POST['dbUser'] . '&dbPassword=' . $_POST['dbPassword'] . '&dbPasswordConfirm=' . $_POST['dbPasswordConfirm']);
        exit;
    } else {
        // Fail
        header('Location: install.php?errorsExist=1&error_pw_match=1&error_dbAdapter=' . $_POST['dbAdapter'] . '&error_dbHost=' . $_POST['dbHost'] . '&error_dbName=' . $_POST['dbName'] . '&error_dbTablePrefix=' . $_POST['dbTablePrefix'] . '&error_dbUser=' . $_POST['dbUser']);
        exit;
    }

    ?>

<?php elseif(isset($_GET['process'])): ?>

    <?php

    // Create database.config

    if ($_GET['dbAdapter'] == 'MySQL') {
        $dbAdapterString = '$adapter="pdo_mysql";';
        $dbNameString = '$dbname="' . $_GET['dbName'] . '";';
    } elseif ($_GET['dbAdapter'] == 'MySQLi') {
        $dbAdapterString = '$adapter="mysqli";';
        $dbNameString = '$dbname="' . $_GET['dbName'] . '";';
    } else {
        $dbAdapterString = '$adapter="pdo_sqlite";';
        $dbNameString = '$dbname=__DIR__."/' . $_GET['dbName'] . '.sqlite3";';
    }

    $data =	 '<?php

' . $dbAdapterString . '
$host="' . $_GET['dbHost'] . '";

' . $dbNameString . '
$tablePrefix = "' . $_GET['dbTablePrefix'] . '";

$user="' . $_GET['dbUser'] . '";
$pass="' . $_GET['dbPassword'] . '";

';

    $dbConnFile = 'database' . DIRECTORY_SEPARATOR . 'database.config';

    file_put_contents($dbConnFile, $data);

    $currentOS = strtoupper(substr(PHP_OS, 0, 3));

    if ($currentOS != 'WIN') {
        chmod($dbConnFile, 0644);
    }

    // Instantiate PhpRbac\Rbac object
    require_once 'autoload.php';

    $rbac = new PhpRbac\Rbac();

    // execute '$rbac->reset(true);'
    $rbac->reset(true);

    // Send to Success Message
    header('Location: install.php?success=1');
    exit;

    ?>

<?php elseif(isset($_GET['success'])): ?>

    <h1>Installation Successful!</h1>

    <h2>Congratulations: You are now ready to use PHP-RBAC!</h2>

    <p style="color: red">
        <strong>Warning - Please remove 'install.php' for security reasons!</strong>
    </p>

<?php endif; ?>

            </div>

            <div class="pure-u-1-3"></div>

        </div>

    </body>
</html>