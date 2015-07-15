# The Toolkit
_A Symfony 2 Thing_

## Getting Started with local development

1. The Toolkit is intended to run as a site under the Precip VM, so start by getting that:
[clwdev/precip](https://github.com/clwdev/precip)

2. Set up your config.rb file in the precip root similar to:
```ruby
drupal_sites = {
  "toolkit" => {
      "host" => "toolkit.vm",
      "path" => "Toolkit/web",
      "drupal" => "false",
      "git_url" => "git@github.com:TUI-SAS-Web-Development/Toolkit.git",
      "git_dir" => "Toolkit",
      "commands" => {
        "toolkit-composer" => {
          "path" => "/srv/www/Toolkit",
          "cmd" => "sudo composer",
        },
        "toolkit-console" => {
          "path" => "/srv/www/Toolkit",
          "cmd" => "sudo php app/console",
        }
      }
    }
  }
```

3. Boot the VM with `vagrant up`. If it's already built, `vagrant reload --provision`.
```
$ cd [your workspace]/precip
$ vagrant up
```

4. SSH into the VM and install Symfony
```
$ vagrant ssh default
$ cd /srv/www/Toolkit
$ sudo composer install
```


If you have the config.rb with bin command settings,
instead of SSH into vagrant box and running commands as 'sudo php app/console',
you can do stuff like below from your host machine precip directory:

you may need to run the two commands below first:
```
$ vagrant plugin install vagrant-exec
$ vagrant exec --binstubs
```


```
~/www/precip (master)$ bin/toolkit-console cache:clear --env=dev

~/www/precip (master)$ bin/toolkit-composer require [package-provider/package-name]
~/www/precip (master)$ bin/toolkit-composer install
```

5. Init the app
```
$ sudo php app/console doctrine:schema:create  #subsequent use is doctrine:schema:update
$ sudo php app/console doctrine:fixtures:load
```

Note: this will likely get replaced by running a migration 0 (doctrine:migrations:migrate)

6. Load the assets and clear cache
```
$ sudo app/console cache:clear    # possible options are --env=prod --no-debug
$ sudo php app/console assetic:dump    # option are --env=prod --no-debug
```

7. You can also run the Symfony shell and run commands in that shell
```
$ sudo php app/console --shell
Symfony > cache:clear --env=prod
```

(We're working on getting Precip to build handy aliases for us so we don't have to ssh into the box to do all this. Soon.)

## Symfony env variable stuff you should know

By default, when you point your browser to http://toolkit.vm, you will be running the prod environment.
This means you are serving cached content and non-refreshed assets (css/js/images/content repository etc).

While developing, add 'app_dev.php' to your path to view the dev env of the application. http://toolkit.vm/app_dev.php
It treats it like a base_url.

## Dev Resources
* This also seems like a good tutorial to start with: [Basic CMS](http://symfony.com/doc/master/cmf/tutorial/introduction.html)
* Information about [creating menus](http://symfony.com/doc/current/bundles/KnpMenuBundle/index.html) using KnpMenuBundle
* Information for Dev Ops [putting Env vars outside of project](http://symfony.com/doc/current/cookbook/configuration/external_parameters.html) (like db conn credds)
* Something to run to fix PSR-x issues: [PHP-cs-fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)
* how to [Handle forms](http://symfony.com/doc/current/best_practices/forms.html)
* custom User event [HTML emails](https://github.com/TUI-SAS-Web-Development/Toolkit/tree/Sym2-core-only)
* adding [invitations to registration](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/adding_invitation_registration.md) (require token to register)
* form API [reference field types](http://symfony.com/doc/current/reference/forms/types.html)
* Ice Age - Dont miss the [migrations](http://symfony.com/doc/current/bundles/DoctrineMigrationsBundle/index.html)
* Doctrine query builder [API](http://doctrine-orm.readthedocs.org/en/latest/reference/query-builder.html)

## Source Control and Developer Best Practices
* If you install or update a bundle, or do anything that changes schema, like add a field, change a field mapping etc,
then you'll need to generate a Doctrine Migration so others can run Migrations when they pull latest source code.
```
\\ to generate a migration file - you'll need to add and commit to source control
$ bin/toolkit-console doctrine:migrations:diff

\\ to run all migrations still pending
$ bin/toolkit-console doctrine:migrations:migrate

\\ to check migrations status
$ bin/toolkit-console doctrine:migrations:status
```