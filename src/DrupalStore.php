<?php

namespace Lullabot\DrupalSymfonyLock;

use Drupal\Core\Lock\LockBackendInterface;
use Symfony\Component\Lock\BlockingStoreInterface;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\Key;

/**
 * Wraps a Drupal locking backend with a Symfony store implementation.
 */
class DrupalStore implements BlockingStoreInterface {

  /**
   * The Drupal lock backend.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  private $lockBackend;

  /**
   * Constructs a new DrupalStore.
   *
   * @param \Drupal\Core\Lock\LockBackendInterface $lockBackend
   *   The Drupal lock backend to wrap.
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
    $this->lock($key);
  }

  /**
   * {@inheritdoc}
   */
  public function putOffExpiration(Key $key, $ttl) {
    if (!$this->lockBackend->acquire((string) $key, $ttl)) {
      throw new LockConflictedException(sprintf('The lock expiration for %s could not be put off.', $key));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function delete(Key $key) {
    $this->lockBackend->release((string) $key);
  }

  /**
   * {@inheritdoc}
   */
  public function exists(Key $key): bool {
    return !$this->lockBackend->lockMayBeAvailable((string) $key);
  }

  /**
   * Try to acquire a lock.
   *
   * @param \Symfony\Component\Lock\Key $key
   *   The key to lock.
   *
   * @throws \Symfony\Component\Lock\Exception\LockConflictedException
   *   Thrown if a lock could not be acquired.
   */
  private function lock(Key $key) {
    if (!$acquired = $this->lockBackend->acquire((string) $key)) {
      if (!$this->lockBackend->wait((string) $key)) {
        $acquired = $this->lockBackend->acquire((string) $key);
      }
    }

    if (!$acquired) {
      throw new LockConflictedException(sprintf('Unable to acquire a lock for %s.', $key));
    }
  }

}
