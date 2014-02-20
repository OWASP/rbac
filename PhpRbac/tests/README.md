#Instructions for running Unit Tests.

##Front Matter

* The Unit Tests should be run using a database specific for Unit Testing. This way your dev/testing/production data will not be affected.
* To run the Unit Tests using the MySQL adapter you will need to have an existing database with the proper tables and default data prior to running the tests.
* If you are running the Unit Tests for the SQLite adapter the database will be created/overwritten for you automatically.

##The Setup

* Create the database and tables
    * MySQL
        * Execute the queries located in the 'mysql.sql' file in the 'rbac/PhpRbac/database/' directory
            * **WARNING:** Make sure you replace 'PREFIX_' appropriately
    * SQLite
        * The database will be created/overwritten for you automatically when you run the Unit Tests
* Navigate to 'rbac/PhpRbac/tests/database' and open up 'database.config'. Change the database connection info accordingly
* Navigate to 'rbac/PhpRbac/tests' and open up 'phpunit_mysql.xml'. Change the database connection info accordingly. Don't forget to change the database name in the DNS string (this is for the DBUnit connection, fixture and datasets)

##Run The Unit Tests

* You will need to navigate to 'rbac/PhpRbac/tests/' in order to execute the following commands.

###On Linux

**Note:** Make sure you make 'mysql_tests.sh' and 'sqlite_tests.sh' executable

* To run the tests for MySQL: ./mysql_tests.sh
* To run the tests for SQLite: ./sqlite_tests.sh

###On Windows

* To run the tests for MySQL: mysql_tests.bat
* To run the tests for SQLite: sqlite_tests.bat

##Notes

* Make sure you alter the 'rbac/PhpRbac/tests/database/database.config' file (see above) before switching between MySQL and SQLite tests.
* We've created scripts for Windows and Linux (any OS that has sh/bash available). All scripts will:
    * Execute PHPUnit with the proper xml configuration file.
    * Accept additional PHPUnit parameters (i.e. --colors).
    * Pause after tests so they can be run from a GUI directory/file explorer application.
* **Thanks to the AuraPHP team for helping us bootstrap our Unit Testing methods**