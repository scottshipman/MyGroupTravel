#!/bin/bash
set -e

if [ "$1" = "" ]
then
  echo "The Toolkit Build Script"
  echo "Usage:    $0 <env>"
  echo "Example:  $0 dev"
  exit 1
fi

case "$1" in
  vm)
    application_env="dev"
    composer_params=""
    ;;
  dev)
    application_env="dev"
    composer_params=""
    ;;
  uat)
    application_env="prod"
    composer_params="--optimize-autoloader"
    ;;
  prod)
    application_env="prod"
    composer_params="--no-dev --optimize-autoloader"
    ;;
esac

if [ "$1" != "vm" ]
then
  echo $'\n============================================='
  echo "Pre-Flight: assets & parameters.yml"
  echo $'=============================================\n'  
  
  echo " - Cleaning up & symlinking static asset directories"

  [ -d web/static ] && rm -rf web/static
  [ ! -L web/static ] && ln -s `pwd`/../../static web/static
  
  echo " - Cleaning up & copying parameters.yml"
  
  [ -f app/config/parameters.yml ] && rm -rf app/config/parameters.yml
  cp ../../config/parameters.yml app/config/parameters.yml
fi

echo $'\n============================================='
echo "Installing Composer dependencies (this may take awhile)"
echo $'=============================================\n'

/usr/local/bin/composer install --no-progress --no-plugins --prefer-dist -v $composer_params

if [ "$1" = "prod" ]
then
  echo $'\n============================================='
  echo "Removing app_dev.php and config.php from /web"
  echo "============================================="
  
  [ -f web/app_dev.php ] && rm -rf web/app_dev.php
  [ -f web/config.php ] && rm -rf web/config.php
  echo $'\n  - Done!'
fi

if [ "$1" != "vm" ]
then
  echo $'\n============================================='
  echo "Build prep complete! Updating 'live' symlink"
  echo "============================================="
  
  ln -sfn `pwd` ../../live
  echo $'\n  - Done!'
fi

echo $'\n============================================='
echo "Database Migrations"
echo $'=============================================\n'

php app/console doctrine:migrations:migrate -n --env=$application_env

echo $'\n============================================='
echo "Assetic Dump & Cache Clear"
echo $'=============================================\n'

if [ "$1" != "vm" ]
then
  cd ../../live
fi

php app/console assetic:dump --env=$application_env
echo ""
php app/console cache:clear --env=$application_env
echo ""

# @TODO: Asset Refresh from Prod (once we have prod)
# if [ "$1" = "dev" -o "$1" = "uat" ]
#   echo $'\n============================================='
#   echo "rsync assets from Prod"
#   echo $'=============================================\n'
# 
#   rsync -ah --stats tuisas-servers@www1-prod.tuistudent.com:/srv/www/uke-ed-tk-prod/web/upload web/upload
# 
#   echo $'\n  - Done!\n'
# fi

# @TODO: Tests! (run earlier?)
# echo $'\n============================================='
# echo "Tests"
# echo $'=============================================\n'

if [ "$1" != "vm" ]
then
  echo $'\n============================================='
  echo "Cleaning up old builds"
  echo "============================================="

  (cd .. && find releases -maxdepth 1 -mindepth 1 -type d | sort | head -n -5 | xargs sudo rm -rf)

  echo $'\n  - Done!'
fi

echo $'\nAll Done!\n'