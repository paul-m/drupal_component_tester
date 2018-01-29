Drupal Component Tester
=======================

Status
---
[![Build Status](https://travis-ci.org/paul-m/drupal_component_tester.svg?branch=master)](https://travis-ci.org/paul-m/drupal_component_tester)

What?
-----

This is a Travis-CI test process which tries to isolate Drupal's components and test them.

This project does the following:

* Check out Drupal 8.5.x.
* Copy the `core/tests/` directory to a working directory.
* Use composer to require components.
* Run `phpunit` for the component tests, using a path to find only Component-based tests.


What did we learn?
------------------

* ~~Component dependencies are not properly defined, so tests will fail.~~ This is fixed in: https://www.drupal.org/node/2867960
* Since Drupal components are currently in the `core/composer.json` 'replace' section, we have to make our own project and copy the test files somewhere convenient to that project.
* ~~Component tests inherit from `Drupal\Tests\UnitTestCase`, which has a dependency on `\Drupal`. This automatically makes them not isolated.~~ This is fixed in https://www.drupal.org/project/drupal/issues/2866894
* `TestDiscovery` still has a dependency on simpletest, disallowing the use of test suites, so we can't use core's phpunit.xml config: https://www.drupal.org/node/2863055
* We have a false positive in that the `composer.json` file for this test requires all components as ``"8.4.*"``. The components themselves require each other in a much looser version constraint, so we should have a test that requires each one independently and runs its tests.

TODO:
-----

* ~~Expand this project's `composer.json` to include all components. Currently many won't even install properly, so we have to wait for https://www.drupal.org/node/2867960 ~~
* ~~Expand the tests to iterate over Drupal core's supported versions of PHP.~~
* ~~Use `composer update --prefer-lowest` to test against the lowest dependencies possible.~~
