# DSFR KickStart

DSFR Kickstart is an installer profile for Drupal that includes the modules needed for the DSFR library frontend ("Design System FRançais" or "Système de Design de l'État").

**Official documentation:** [French State Design System](https://www.systeme-de-design.gouv.fr/)

**Warning:** this profil is experimental.

## Licence
It is strictly forbidden to use the DSFR outside of the French State's websites (including other public actors, such as territorial administrations).

## Contents

- [DSFR KickStart](#DSFR-KickStart)
  - [Contents](#Contents)
  - [Introduction](#Introduction)
  - [Requirements](#Requirements)
  - [Installation](#Installation)
  - [Installation using Docker](#Installation-using-Docker)
  - [Configuration](#Configuration)
  - [Supported DSFR library version](#Supported-DSFR-library-version)
  - [Licence](#Licence)

## Introduction

This profile follows the development of the [DSFR theme](https://www.drupal.org/project/dsfr) for Drupal developed by [Académie de Grenoble](https://www.ac-grenoble.fr/).

This profile includes many common modules used while working with Drupal and DSFR.
There is also default configuration that will give you a head start on setting up your sites.

* For a full description of this profile, visit the [project page](https://www.drupal.org/project/dsfr_kickstart).
* To submit bug reports, give feature suggestions, or track changes visit the [issue queue](https://www.drupal.org/project/issues/dsfr_kickstart).


## Requirements

This profile has no special requirements outside of Composer requirements.

## Installation

```
git clone https://git.drupalcode.org/project/dsfr_kickstart.git drupal
cd drupal
composer install
cp web/sites/default/default.settings.php web/sites/default/settings.php


chmod ugo+w web/sites/default/files
chmod ugo+w web/sites/default/files/translations
chmod ugo+w web/sites/default/settings.php
```

add to web/sites/default/settings.php the following line
```
$settings['config_sync_directory'] = '../config/sync';
```

Go to the site address and launch the installation from the interface by choosing the DSFR Kickstart profile or use drush via the command:

```
drush si --existing-config

```

## Installation using Docker

Wodby Docker-based Drupal stack https://github.com/wodby/docker4drupal

1.Download and unpack docker4drupal.tar.gz from the latest stable release to your project root

2.Delete compose.override.yml as it's used to deploy vanilla Drupal

3.Ensure NGINX_SERVER_ROOT (or APACHE_DOCUMENT_ROOT) is correct, by default set to /var/www/html/web for composer-based projects where Drupal is in web subdirectory

4.Ensure database access settings in your settings.php corresponds to values in .env file
```
git clone https://github.com/wodby/docker4drupal.git
cd docker4drupal
git clone https://git.drupalcode.org/project/dsfr_kickstart.git drupal
rm compose.override.yml
```
create a new compose.override.yml file with the following content:

```
services:
  php:
    volumes:
    - ./drupal:/var/www/html

  crond:
    volumes:
    - ./drupal:/var/www/html

  nginx:
    volumes:
    - ./drupal:/var/www/html

```
Modifying the .env file to use PHP 8.3 instead of PHP 8.4
```
#PHP_TAG=8.4-dev-4.69.2
PHP_TAG=8.3-dev-4.69.2
```
launch the docker containers and install the Drupal instance from the php container
```
docker compose up -d
```
enter the PHP container of the stack and launch the installation of the DSFR Kickstart distribution
```
docker exec -ti my_drupal11_project_php bash
composer install
cp web/sites/default/default.settings.php web/sites/default/settings.php
chmod ugo+w web/sites/default/files
chmod ugo+w web/sites/default/files/translations
chmod ugo+w web/sites/default/settings.php
```

add to web/sites/default/settings.php the following line
```
$settings['config_sync_directory'] = '../config/sync';
```

Go to the site address http://drupal.docker.localhost:8000/ and launch the installation from the interface by choosing the DSFR Kickstart profile "Installer DSFR Kickstart en utilisant une configuration existante." or use drush via the command:

```
drush si --existing-config

```

## Configuration

## Supported DSFR library version

The supported DSFR library version is indicated in package.json and the dsfr.libraries.yml file.

## Licence

It is strictly forbidden to use the DSFR outside of the French State's websites (including other public actors, such as territorial administrations).

[Scope of DSFR use](https://www.systeme-de-design.gouv.fr/comment-utiliser-le-dsfr/perimetre-d-application-du-dsfr) (available only in French)
