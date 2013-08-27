@ECHO OFF
php "%~dp0phpunit.phar" -c phpunit_mysql.xml %*
PAUSE