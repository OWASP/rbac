<h1>PHP-RBAC: Installation Wizard</h1>

<h2>Choose a component to install.</h2>

<form class="pure-form pure-form-aligned" name="start_installation" action="<?php echo $baseUrl; ?>/hub" method="POST">

    <fieldset>

        <div class="pure-control-group">
            <label for="component">Components:</label>
            <select id="component" name="component">

                    <?php if ($dbAdapter == 'Core'): ?>
                        <option value="library" selected="selected">PHP-RBAC - Library</option>
                    <?php else: ?>
                        <option value="library">PHP-RBAC - Library</option>
                    <?php endif; ?>

                    <?php if ($dbAdapter == 'MySQLi'): ?>
                        <option value="tests" selected="selected">PHP-RBAC - Unit Tests</option>
                    <?php else: ?>
                        <option value="tests">PHP-RBAC - Unit Tests</option>
                    <?php endif; ?>

                </select>
        </div>

        <div class="pure-controls">
            <button type="submit" name="submit" class="pure-button pure-button-primary">Submit</button>
        </div>

    </fieldset>

</form>
