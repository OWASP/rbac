#Instructions for running Unit Tests.

##The Setup

* Create the database and tables

    * MySQL
        * Execute the queries located in the 'mysql.sql' file in the 'rbac/PhpRbac/database/' directory
            * Make sure you replace 'PREFIX_' appropriately
    * SQLite
        * The database will be created for you

* Navigate to 'rbac/PhpRbac/tests/database' and open up 'database.config'. Change the database connection info accordingly

* Navigate to 'rbac/PhpRbac/tests' and open up 'phpunit_mysql.xml'. Change the database connection info accordingly. Don't forget to change the database name in the DNS string (this is for the DBUnit connection, fixture and datasets)

##Run The Unit Tests

* You will need to navigate to 'rbac/PhpRbac/tests/' in order to execute the following commands.

###On Linux

* To run the tests for MySQL: ./mysql_tests.sh
* To run the tests for SQLite: ./sqlite_tests.sh

**Note:** Make sure you make 'mysql_tests.sh' and 'sqlite_tests.sh' executable

###On Windows

* To run the tests for MySQL: mysql_tests
* To run the tests for SQLite: sqlite_tests

**Notes**

* We've created scripts for Windows and Linux/any OS that has sh/bash available. All scripts will execute PHPUnit with the proper xml configuration file. They will also accept additional parameters (i.e. --colors).

* Make sure you alter the 'rbac/PhpRbac/tests/database/databse.config' file (see above) for MySQL vs SQLite tests.

* All scripts will pause after tests so they can be run from a GUI directory/file explorer application.

**Thanks to the AuraPHP team for helping us bootstrap our Unit Testing methods**