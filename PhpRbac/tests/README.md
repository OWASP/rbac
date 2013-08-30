#Instructions for running Unit Tests.

##The Setup

* Create the database and tables

    * See the 'mysql.sql' and 'sqlite.sql' files in the 'rbac/PhpRbac/src/PhpRbac/core/sql/' directory

* Navigate to 'rbac/PhpRbac/src/PhpRbac/core/sql/database' and open up 'database.config'. Change the database connection info accordingly (this is for the core tests)

* Navigate to 'rbac/PhpRbac/tests/database' and open up 'database.config'. Change the database connection info accordingly (this is for the new PSR wrapper tests)

* Navigate to 'rbac/PhpRbac/tests' and open up 'phpunit_mysql.xml'. Change the database connection info accordingly. Don't forget to change the database name in the DNS string (this is for the DBUnit connection, fixture and datasets)

##Run The Unit Tests

* You will need to navigate to 'rbac/PhpRbac/tests/' in order to execute the following commands.

###On Linux

* To run the Core tests (for both MySQL and SQLite): ./core_tests.sh
* To run the PSR Wrapper tests for MySQL: ./mysql_tests.sh
* To run the PSR Wrapper tests for SQLite: ./sqlite_tests.sh

###On Windows

* To run the Core tests (for both MySQL and SQLite): core_tests
* To run the PSR Wrapper tests for MySQL: mysql_tests
* To run the PSR Wrapper tests for SQLite: sqlite_tests

**Notes**

* We've created scripts for Windows and Linux/any OS that has sh/bash available. All scripts will execute PHPUnit with the proper xml configuration file. They will also accept additional parameters (i.e. --colors).

* Make sure you alter the 'databse.config' files (see above) for MySQL vs SQLite tests.

* All scripts will pause after tests so they can be run from a GUI directory/file explorer application.

**Thanks to the AuraPHP team for helping us bootstrap our Unit Testing methods**