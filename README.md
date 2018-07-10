# Drupal Symfony Lock

[![CircleCI](https://circleci.com/gh/Lullabot/drupal-symfony-lock.svg?style=svg)](https://circleci.com/gh/Lullabot/drupal-symfony-lock) [![Maintainability](https://api.codeclimate.com/v1/badges/68a640924d568cf75781/maintainability)](https://codeclimate.com/github/Lullabot/drupal-symfony-lock/maintainability) [![Test Coverage](https://api.codeclimate.com/v1/badges/68a640924d568cf75781/test_coverage)](https://codeclimate.com/github/Lullabot/drupal-symfony-lock/test_coverage)

Do you want to use a PHP library that requires Symfony's
[Lock Component](https://symfony.com/doc/3.4/components/lock.html) in your Drupal
site? This library maps Drupal's
[LockBackendInterface](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Lock%21LockBackendInterface.php/interface/LockBackendInterface/8.5.x) to a
[Symfony StoreInterface](https://api.symfony.com/3.4/Symfony/Component/HttpKernel/HttpCache/StoreInterface.html).

## Usage

Require this library in your Drupal module:

`$ composer require lullabot/drupal-symfony-lock`

Inject lock service from the Drupal container, and use it when constructing
this class:

```php
<?php

$backend = \Drupal::lock();
$store = new \Lullabot\DrupalSymfonyLock\DrupalStore($backend);
$factory = new \Symfony\Component\Lock\Factory($store);
$lock = $factory->createLock('lock-identifier', 10);

// Blocking means this will throw an exception on failure.
$lock->acquire(true);
```
