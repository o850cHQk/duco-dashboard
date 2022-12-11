#!/bin/bash
echo Duco Worker Starting
while true
do
    php ./runner/runner.php
    sleep 1
done