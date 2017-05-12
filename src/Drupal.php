<?php

/**
 * Stubbed version of the Drupal service discovery class.
 *
 * We need this class in order to satisfy \Drupal\Tests\UnitTestCase.
 */
class Drupal {

  /**
   * Sets a new global container.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   A new container instance to replace the current.
   */
  public static function setContainer(ContainerInterface $container) {
  }

  /**
   * Unsets the global container.
   */
  public static function unsetContainer() {
  }

}
