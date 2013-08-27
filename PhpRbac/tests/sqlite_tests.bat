@ECHO OFF
php "%~dp0phpunit.phar" -c phpunit_sqlite.xml %*
PAUSE