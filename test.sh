#!/bin/bash

USER="test@test.test"
PASSWORD="pintest42"

ldapsearch -D "uid=${USER}" \
           -w "$PASSWORD" -h localhost -p 3389 \
           -s sub "uid=${USER}"