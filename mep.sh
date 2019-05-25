#!/bin/bash

cd "$(dirname "$0")"

sudo gits -u
sudo composer install
sudo bin/console doctrine:schema:update --force --dump-sql
sudo /root/.yarn/bin/yarn install
sudo /root/.yarn/bin/yarn encore production
sudo chown www-data:www-data -R .
sudo chown www-data:www-data -R /cache/kinders


