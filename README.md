# Drupal Symfony Lock

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
