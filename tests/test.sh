#!/usr/bin/env bash

WORKER_DIR=$(cd $(dirname $0); pwd)

echo "Start the service ..."
php ${WORKER_DIR}/server/http.php && php ${WORKER_DIR}/server/process.php

echo ""

echo "Beginning of the test ..."
${WORKER_DIR}/../vendor/bin/phpunit -c phpunit.xml
echo "End of the test ..."

echo ""

echo "Stop the service ..."
php ${WORKER_DIR}/server/http.php force && php ${WORKER_DIR}/server/process.php force

echo ""

echo "All over ..."