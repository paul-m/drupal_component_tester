<?php

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

$loader = require __DIR__ . '/vendor/autoload.php';

/**
 * Tests are extracted from core to ./component/tests/ by travis.
 * Apply a patch from ./patches/ if it exists.
 * Extract each (patched) component to ./component/
 * Add our phpunit.xml.
 * cd into ./components/
 * composer update --prefer-lowest
 * Run tests --group Componentname.
 * composer update
 * Run tests --group Componentname.
 * Repeat for each component.
 */

$components = [
  // 'package/name' => 'Group',
//  'drupal/core-annotation' => 'Annotation',
//  'drupal/core-assertion' => 'Assertion',
// FAILING  'drupal/core-bridge' => 'Bridge',
//  'drupal/core-class-finder' => 'ClassFinder',
  'drupal/core-datetime' => 'Datetime',
  'drupal/core-dependency-injection' => 'DependencyInjection',
];

$fs = new Filesystem();

$my_dir = __DIR__;

foreach ($components as $package => $group) {
  echo "\n===---> Building package: $package\n\n";
  $path = 'core/lib/Drupal/Component/' . $group;

  chdir($my_dir);

  // Move the component to the workspace.
  $fs->mirror(
    __DIR__ . '/drupal/' . $path,
    __DIR__ . '/workspace/component'
  );
  // Get to work.
  chdir($my_dir . '/workspace');
  // Clean up.
  $fs->remove(['composer.json', 'composer.lock']);
  // Write out a composer.json to the workspace.
  file_put_contents(
    'composer.json', json_encode(
      buildComposerArray($package, $group, 'component'),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    )
  );

  // Ensure there are no leftover vendor artifacts.
  $fs->remove(['vendor', 'composer.lock']);

  $composer_command = 'composer update --lock --no-progress --no-suggest';
  echo "\n\nPRIMING -> $composer_command\n\n";
  echo shell_exec($composer_command);

  // Composer update and run tests.
  foreach (['--prefer-lowest', ' '] as $argument) {
    $composer_command = 'composer update --lock --no-progress --no-suggest ' . $argument;
    echo "\n\nCOMPOSER -> $composer_command\n\n";
    echo shell_exec($composer_command);

    $phpunit_command = './vendor/bin/phpunit core/tests/Drupal/Tests/Component/' . $group;
    echo "\n\nPHPUNIT -> $phpunit_command\n\n";
    echo shell_exec($phpunit_command);
  }
  $fs->remove(['component']);
}

echo "\n\nDone.\n\n";

function buildComposerArray($component_package, $group, $path) {
  return [
    'name' => 'mile23/test_package',
    'description' => 'Dummy package for the component.',
    'license' => 'GPL-2.0+',
    'minimum-stability' => 'dev',
    'prefer-stable' => TRUE,
    'require' => [
      'phpunit/phpunit' => '^4.8.35 || ^6.1',
      'wikimedia/composer-merge-plugin' => '@stable',
    ],
    'replace' => [
      $component_package => 'self.version',
    ],
    'extra' => [
      'merge-plugin' => [
        'require' => [
          "$path/composer.json",
        ],
        'ignore-duplicates' => FALSE,
        'merge-dev' => TRUE,
        'merge-extra' => FALSE,
        'merge-scripts' => FALSE,
        'recurse' => FALSE,
        'replace' => FALSE,
      ],
    ],
    'autoload-dev' => [
      'psr-4' => [
        "Drupal\\Tests\\Component\\$group\\" => "core/tests/Drupal/Tests/Component/$group",
      ],
    ],
    'respositories' => [
      'type' => 'composer',
      'url' => 'https://packages.drupal.org/8',
    ],
  ];
}
