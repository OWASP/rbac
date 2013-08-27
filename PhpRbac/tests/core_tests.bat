@ECHO OFF
php "%~dp0phpunit.phar" -c phpunit_core.xml %*
PAUSE