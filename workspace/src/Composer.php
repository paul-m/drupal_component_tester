<?php

namespace DrupalComponentTester;

use Composer\Installer\PackageEvent;
use Composer\Semver\Constraint\Constraint;
use Composer\Script\Event;

class Composer {

  /**
   * Fires the drupal-phpunit-upgrade script event if necessary.
   *
   * @param \Composer\Script\Event $event
   */
  public static function upgradePHPUnit(Event $event) {
    $repository = $event->getComposer()->getRepositoryManager()->getLocalRepository();
    // This is, essentially, a null constraint. We only care whether the package
    // is present in the vendor directory yet, but findPackage() requires it.
    $constraint = new Constraint('>', '');
    $phpunit_package = $repository->findPackage('phpunit/phpunit', $constraint);
    if (!$phpunit_package) {
      // There is nothing to do. The user is probably installing using the
      // --no-dev flag.
      return;
    }

    // If the PHP version is 7.2 or above and PHPUnit is less than version 6
    // call the drupal-phpunit-upgrade script to upgrade PHPUnit.
    if (version_compare(PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION, '7.2') >= 0 && version_compare($phpunit_package->getVersion(), '6.1') < 0) {
      $event->getComposer()
        ->getEventDispatcher()
        ->dispatchScript('drupal-phpunit-upgrade');
    }
  }
}
