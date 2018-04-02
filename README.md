# Drupal Symfony Lock

[![CircleCI](https://circleci.com/gh/Lullabot/drupal-symfony-lock.svg?style=svg)](https://circleci.com/gh/Lullabot/drupal-symfony-lock) [![Maintainability](https://api.codeclimate.com/v1/badges/448ece0f1e569fc7d649/maintainability)](https://codeclimate.com/github/Lullabot/drupal-symfony-lock/maintainability) [![Test Coverage](https://api.codeclimate.com/v1/badges/448ece0f1e569fc7d649/test_coverage)](https://codeclimate.com/github/Lullabot/drupal-symfony-lock/test_coverage) [![Packagist](https://img.shields.io/packagist/dt/lullabot/drupal-symfony-lock.svg)](https://packagist.org/packages/lullabot/drupal-symfony-lock)

Do you want to use a PHP library that requires Symfony's Lock Component in your
Drupal site? This library maps Drupal's LockBackendInterface to a Symfony
StoreInterface.

## Usage

Require this library in your Drupal module:

`$ composer require lullabot/drupal-symfony-lock`

Inject lock service from the Drupal container, and use it when constructing
this class:

```php
<?php

$backend = $this->container->get('lock');
$store = new \Lullabot\DrupalSymfonyLock\DrupalStore($backend);
$store->waitAndSave(new \Symfony\Component\Lock\Key('lock-identifier');
```
