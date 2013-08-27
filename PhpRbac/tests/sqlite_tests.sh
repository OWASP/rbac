#!/usr/bin/env bash

php phpunit.phar -c phpunit_sqlite.xml "$@"
read -p "Press [Enter] key to continue..."