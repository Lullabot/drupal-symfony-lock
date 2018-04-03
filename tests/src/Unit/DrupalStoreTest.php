<?php

namespace Lullabot\DrupalSymfonyLock\Test;

use Drupal\Core\Lock\LockBackendInterface;
use Lullabot\DrupalSymfonyLock\DrupalStore;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\Exception\NotSupportedException;
use Symfony\Component\Lock\Key;

/**
 * @covers \Lullabot\DrupalSymfonyLock\DrupalStore
 */
class DrupalStoreTest extends TestCase {

  /**
   * The mock Drupal lock backend.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $backend;

  /**
   * The store under test.
   *
   * @var \Lullabot\DrupalSymfonyLock\DrupalStore
   */
  protected $store;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->backend = $this->getMockBuilder(LockBackendInterface::class)
      ->getMock();
    $this->store = new DrupalStore($this->backend);
  }

  /**
   * Tests saving a lock.
   */
  public function testSave() {
    $this->backend->expects($this->once())->method('acquire')
      ->with('test-key')
      ->willReturn(TRUE);

    $key = new Key('test-key');
    $this->store->save($key);
  }

  /**
   * Tests a blocking save.
   */
  public function testWaitAndSave() {
    $this->backend->expects($this->once())->method('acquire')
      ->with('test-key', 30)
      ->willReturn(TRUE);

    $key = new Key('test-key');
    $this->store->waitAndSave($key);
  }

  /**
   * Tests retrying and succeeding a lock.
   *
   * @param string $method
   *   The method name to call.
   *
   * @dataProvider retryDataProvider
   */
  public function testRetry($method) {
    $this->backend->expects($this->at(0))->method('acquire')
      ->with('test-key')
      ->willReturn(FALSE);

    $this->backend->expects($this->at(1))->method('wait')
      ->with('test-key')
      ->willReturn(TRUE);

    $this->backend->expects($this->at(2))->method('acquire')
      ->with('test-key')
      ->willReturn(TRUE);

    $key = new Key('test-key');
    $this->store->$method($key);
  }

  /**
   * Methods to test for retries.
   *
   * @dataProvider
   *
   * @return array
   *   An array of methods.
   */
  public function retryDataProvider() {
    return [
      ['save'],
      ['waitAndSave'],
    ];
  }

  /**
   * Tests when the wait() call fails.
   */
  public function testLockWaitFailed() {
    $this->backend->expects($this->at(0))->method('acquire')
      ->with('test-key')
      ->willReturn(FALSE);

    $this->backend->expects($this->at(1))->method('wait')
      ->with('test-key')
      ->willReturn(FALSE);

    $key = new Key('test-key');
    $this->expectException(LockConflictedException::class);
    $this->store->save($key);
  }

  /**
   * Tests when a second acquire() fails.
   */
  public function testLockAcquireFailed() {
    $this->backend->expects($this->at(0))->method('acquire')
      ->with('test-key')
      ->willReturn(FALSE);

    $this->backend->expects($this->at(1))->method('wait')
      ->with('test-key')
      ->willReturn(TRUE);

    $this->backend->expects($this->at(2))->method('acquire')
      ->with('test-key')
      ->willReturn(FALSE);

    $key = new Key('test-key');
    $this->expectException(LockConflictedException::class);
    $this->store->save($key);
  }

  /**
   * Test extending an expiration.
   */
  public function testPutOffExpiration() {
    $this->backend->expects($this->at(0))->method('acquire')
      ->with('test-key', 10)
      ->willReturn(true);
    $this->backend->expects($this->at(1))->method('acquire')
      ->with('test-key', 10)
      ->willReturn(false);

    // Test a successful extend.
    $this->store->putOffExpiration(new Key('test-key'), 10);

    // Test when we are unable to extend the lock.
    $this->expectException(LockConflictedException::class);
    $this->store->putOffExpiration(new Key('test-key'), 10);
  }

  /**
   * Test releasing a lock.
   */
  public function testDelete() {
    $this->backend->expects($this->once())->method('release')
      ->with('test-key')
      ->willReturn(TRUE);

    $key = new Key('test-key');
    $this->store->delete($key);
  }

  /**
   * Tests checking if a lock exists.
   */
  public function testExists() {
    $this->backend->expects($this->once())->method('lockMayBeAvailable')
      ->with('test-key')
      ->willReturn(TRUE);

    $key = new Key('test-key');
    $this->assertFalse($this->store->exists($key));
  }

}
