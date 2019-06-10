Kinders
====


#### Informations

Ce repo est le code source de http://kinders.arnapou.net/

Le code est ouvert, vous pouvez l'utiliser comme vous voulez, mais je ne supporterai 
pas les éventuels problèmes que vous auriez.

En bref, il s'agit d'un site de gestion de collection de kinders surprise, collection
que fait mon épouse. Il y a un admin et un front.


#### Miscellaneous

JS Dependencies

    sudo aptitude install nodejs
    
    curl -o- -L https://yarnpkg.com/install.sh | bash
    
    sudo aptitude install nodejs npm
    sudo npm install npm@latest -g

    yarn install
    yarn encore production


Exemple d'installation 

    sudo composer install
    sudo bin/console doctrine:schema:update --force --dump-sql
    sudo /root/.yarn/bin/yarn install
    sudo /root/.yarn/bin/yarn encore production
    sudo chown www-data:www-data -R .
    sudo chown www-data:www-data -R /cache/kinders
