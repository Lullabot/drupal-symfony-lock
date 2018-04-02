<?php

namespace Lullabot\DrupalSymfonyLock;

use Drupal\Core\Lock\LockBackendInterface;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\Exception\NotSupportedException;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\StoreInterface;

/**
 *
 */
class DrupalStore implements StoreInterface {

  /**
   * The Drupal lock backend.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  private $lockBackend;

  /**
   *
   */
  public function __construct(LockBackendInterface $lockBackend) {
    $this->lockBackend = $lockBackend;
  }

  /**
   * {@inheritdoc}
   */
  public function save(Key $key) {
    $this->lock($key);
  }

  /**
   * {@inheritdoc}
   */
  public function waitAndSave(Key $key) {
    $this->lock($key, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function putOffExpiration(Key $key, $ttl) {
    throw new NotSupportedException('Drupal locks can not have their expiration extended.');
  }

  /**
   * {@inheritdoc}
   */
  public function delete(Key $key) {
    $this->lockBackend->release($key);
  }

  /**
   * {@inheritdoc}
   */
  public function exists(Key $key) {
    return !$this->lockBackend->lockMayBeAvailable($key);
  }

  /**
   * Try to acquire a lock.
   *
   * @param \Symfony\Component\Lock\Key $key
   *   The key to lock.
   * @param bool $blocking
   *   (optional) TRUE if acquiring should wait for a lock, FALSE otherwise.
   *
   * @throws \Symfony\Component\Lock\Exception\LockConflictedException
   *   Thrown if a lock could not be acquired.
   */
  private function lock(Key $key, bool $blocking = FALSE) {
    $duration = ($blocking ? 30 : 0);
    if (!$acquired = $this->lockBackend->acquire($key, $duration)) {
      if ($this->lockBackend->wait($key, $duration)) {
        $acquired = $this->lockBackend->acquire($key, $duration);
      }
    }

    if (!$acquired) {
      throw new LockConflictedException(sprintf('Unable to acquire a lock for %s.', $key));
    }
  }

}
