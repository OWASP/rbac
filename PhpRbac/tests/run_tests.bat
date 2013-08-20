@ECHO OFF
php "%~dp0phpunit.phar" -c phpunit_core.xml %*
PAUSE
php "%~dp0phpunit.phar" -c phpunit_mysql.xml %*
PAUSE