#!/usr/bin/env bash

php phpunit.phar -c phpunit_core.xml "$@"
read -p "Press [Enter] key to continue..."