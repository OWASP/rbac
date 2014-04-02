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

<h1>PHP-RBAC: Installation Wizard</h1>

<h2>Unit Test Suite Installation</h2>

<h3>Database information</h3>

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
            <label for="dbAdapter">Adapter:</label>
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
            <label for="dbHost">Host Name/IP:</label>
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
            <button type="submit" name="submit" value="tests" class="pure-button pure-button-primary">Submit</button>
        </div>

    </fieldset>

</form>
