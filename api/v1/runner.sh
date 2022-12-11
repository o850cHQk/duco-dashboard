#!/bin/bash
echo Duco Worker Starting
while true
do
    curl https://duco.tcmeta.net/api/v1/runner/
    sleep 1
done
