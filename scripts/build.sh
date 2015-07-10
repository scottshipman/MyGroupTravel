#!/bin/bash
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

  [ -d web/assetic ] && rm -rf web/assetic
  [ ! -L web/assetic ] && ln -s `pwd`/../../static/assetic web/assetic

  [ -d web/css ] && rm -rf web/css
  [ ! -L web/css ] && ln -s `pwd`/../../static/css web/css
  
  [ -d web/js ] && rm -rf web/js
  [ ! -L web/js ] && ln -s `pwd`/../../static/js web/js
  
  [ -d web/upload ] && rm -rf web/upload
  [ ! -L web/upload ] && ln -s `pwd`/../../static/upload web/upload
  
  echo " - Cleaning up & copying parameters.yml"
  
  [ -f app/config/parameters.yml ] && rm -rf app/config/parameters.yml
  cp ../../config/parameters.yml app/config/parameters.yml
fi

echo $'\n============================================='
echo "Installing Composer dependencies (this may take awhile)"
echo $'=============================================\n'

/usr/local/bin/composer install --no-progress --no-plugins --prefer-dist -v $composer_params

if [ "$1" != "vm" ]
then
  echo $'\n============================================='
  echo "Build prep complete! Updating 'live' symlink"
  echo "============================================="
  
  ln -sfn `pwd` ../../live
  echo $'\n  - Done!\n'
fi

echo $'\n============================================='
echo "Assetic Dump & Cache Clear"
echo $'=============================================\n'

if [ "$1" != "vm" ]
then
  cd ../../live
fi

php app/console assetic:dump --env=$application_env
php app/console cache:clear --env=$application_env

# @TODO: Database Migrations! (run earlier?)
# echo $'\n============================================='
# echo "Database Migrations"
# echo $'=============================================\n'

# @TODO: Tests! (run earlier?)
# echo $'\n============================================='
# echo "Tests"
# echo $'=============================================\n'

echo $'\nAll Done!\n'