<?php

namespace Lullabot\DrupalSymfonyLock;

use Drupal\Core\Lock\LockBackendInterface;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\Exception\NotSupportedException;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\StoreInterface;

class DrupalStore implements StoreInterface {

  /**
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  private $lockBackend;

  public function __construct(LockBackendInterface $lockBackend) {
    $this->lockBackend = $lockBackend;
  }

  /**
   * Stores the resource if it's not locked by someone else.
   *
   * @throws \Symfony\Component\Lock\Exception\LockConflictedException
   */
  public function save(Key $key) {
    $this->lock($key);
  }

  /**
   * Waits until a key becomes free, then stores the resource.
   *
   * If the store does not support this feature it should throw a NotSupportedException.
   *
   * @throws \Symfony\Component\Lock\Exception\LockConflictedException
   * @throws \Symfony\Component\Lock\Exception\NotSupportedException
   */
  public function waitAndSave(Key $key) {
    $this->lock($key, TRUE);
  }

  /**
   * Extends the ttl of a resource.
   *
   * If the store does not support this feature it should throw a NotSupportedException.
   *
   * @param float $ttl amount of second to keep the lock in the store
   *
   * @throws \Symfony\Component\Lock\Exception\LockConflictedException
   * @throws \Symfony\Component\Lock\Exception\NotSupportedException
   */
  public function putOffExpiration(Key $key, $ttl) {
    throw new NotSupportedException('Drupal locks can not have their expiration extended.');
  }

  /**
   * Removes a resource from the storage.
   */
  public function delete(Key $key) {
    $this->lockBackend->release($key);
  }

  /**
   * Returns whether or not the resource exists in the storage.
   *
   * @return bool
   */
  public function exists(Key $key) {
    return !$this->lockBackend->lockMayBeAvailable($key);
  }

  /**
   * @param \Symfony\Component\Lock\Key $key
   * @param bool $blocking
   */
  private function lock(Key $key, bool $blocking = false): bool {
    $duration = ($blocking ? 30 : 0);
    if (!$acquired = $this->lockBackend->acquire($key, $duration)) {
      if ($this->lockBackend->wait($key, $duration)) {
        $acquired = $this->lockBackend->acquire($key, $duration);
      }
    }

    if (!$acquired) {
      throw new LockConflictedException(sprintf('Unable to acquire a lock for %s.', $key));
    }

    return true;
  }

}
