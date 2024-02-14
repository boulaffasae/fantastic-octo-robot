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
git clone https://git.drupalcode.org/project/dsfr_kickstart.git my_website
cd my_webite
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

## Configuration

## Supported DSFR library version

The supported DSFR library version is indicated in package.json and the dsfr.libraries.yml file.

## Licence

It is strictly forbidden to use the DSFR outside of the French State's websites (including other public actors, such as territorial administrations).

[Scope of DSFR use](https://www.systeme-de-design.gouv.fr/comment-utiliser-le-dsfr/perimetre-d-application-du-dsfr) (available only in French)
