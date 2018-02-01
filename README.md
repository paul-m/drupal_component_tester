Drupal Component Tester
=======================

Status
---
[![Build Status](https://travis-ci.org/paul-m/drupal_component_tester.svg?branch=master)](https://travis-ci.org/paul-m/drupal_component_tester)

What?
-----

This is a Travis-CI test process which tries to isolate Drupal's components and test them.

This project does the following:

* Check out Drupal 8.x.x.
* Apply a patch as needed.
* Copy the `core/tests/Drupal/Tests/Components` directory to a working directory.
* Generate a `composer.json` file for the component.
* One by one, copy (patched) components to the working directory and:
	* Use `composer update`
	* Run the tests.
	* Use `composer update --prefer-lowest`
	* Run the tests again.
* Repeat this process in each PHP version supported by that Drupal version.

This will test whether each component's dependencies resolve in Composer, and also whether the version constraints are useful.

The `tester.php` script will die with a fail or error code immediately upon fail or error.

What did we learn?
------------------

* ~~Component dependencies are not properly defined, so tests will fail.~~ This is fixed in: https://www.drupal.org/node/2867960
* Since Drupal components are currently in the `core/composer.json` 'replace' section, we have to make our own project and copy the test files somewhere convenient to that project.
* ~~Component tests inherit from `Drupal\Tests\UnitTestCase`, which has a dependency on `\Drupal`. This automatically makes them not isolated.~~ This is fixed in https://www.drupal.org/project/drupal/issues/2866894
* `TestDiscovery` still has a dependency on simpletest, disallowing the use of test suites, so we can't use core's phpunit.xml config: https://www.drupal.org/node/2863055
