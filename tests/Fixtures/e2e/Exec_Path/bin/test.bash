#!/usr/bin/env bash

# This is a very complex program that may lauch some other programs including PHP programs

echo -n "Program finished"
php -r 'echo simplexml_load_string("<message> with flying colors!</message>");'