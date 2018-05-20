<?php

use Symfony\Component\Filesystem\Filesystem;

$loader = require __DIR__ . '/vendor/autoload.php';

$components = [
  // 'package/name' => 'Group',
  'drupal/core-annotation' => 'Annotation',
  'drupal/core-assertion' => 'Assertion',
  'drupal/core-bridge' => 'Bridge',
  'drupal/core-class-finder' => 'ClassFinder',
  'drupal/core-datetime' => 'Datetime',
  'drupal/core-dependency-injection' => 'DependencyInjection',
  'drupal/core-diff' => 'Diff',
  'drupal/core-discovery' => 'Discovery',
  'drupal/core-event-dispatcher' => 'EventDispatcher',
  'drupal/core-file-cache' => 'FileCache',
  'drupal/core-file-system' => 'FileSystem',
  'drupal/core-gettext' => 'Gettext',
  'drupal/core-graph' => 'Graph',
  'drupal/core-http-foundation' => 'HttpFoundation',
  'drupal/core-php-storage' => 'PhpStorage',
  'drupal/core-plugin' => 'Plugin',
  'drupal/core-proxy-builder' => 'ProxyBuilder',
  'drupal/core-render' => 'Render',
  'drupal/core-serialization' => 'Serialization',
  'drupal/core-transliteration' => 'Transliteration',
  'drupal/core-utility' => 'Utility',
  'drupal/core-uuid' => 'Uuid',
];

$fs = new Filesystem();

$my_dir = __DIR__;

foreach ($components as $package => $group) {
  echo "\n\n\n===---> Building package: $package\n\n";
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
  $fs->remove(['composer.json', 'composer.lock', 'vendor']);
  // Write out a composer.json to the workspace.
  file_put_contents(
    'composer.json', json_encode(
      buildComposerArray($package, $group, 'component'),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    )
  );

  // 'Priming' required because we use wikimedia merge plugin.
  $composer_command = 'composer update --lock --no-progress --no-suggest --prefer-dist';
  echo "\n\nPRIMING -> $composer_command\n\n";
  if ($signal = execute($composer_command)) {
    exit($signal);
  }

  // Composer update and run tests.
  foreach ([' ', '--prefer-lowest'] as $argument) {
    $composer_command = 'composer update --no-progress --no-suggest --prefer-dist ' . $argument;
    $lowest = strtoupper($argument);
    echo "\n\nCOMPOSER $lowest -> $composer_command\n\n";
    if ($signal = execute($composer_command)) {
      exit($signal);
    }

    $phpunit_command = './vendor/bin/phpunit -v core/tests/Drupal/Tests/Component/' . $group;
    echo "\n\nPHPUNIT -> $phpunit_command\n\n";
    if ($signal = execute($phpunit_command)) {
      exit($signal);
    }
  }
  $fs->remove(['component']);
}

echo "\n\nDone.\n\n";

function buildComposerArray($component_package, $group, $path) {
  return [
    'name' => 'mile23/test_package',
    // @todo: Use VCS tags for versioning.
    'version' => '8.5.0',
    'description' => 'Dummy package for the component.',
    'license' => 'GPL-2.0-or-later',
    'minimum-stability' => 'dev',
    'prefer-stable' => TRUE,
    'require' => [
      "ext-date" => "*",
      "ext-dom" => "*",
      "ext-filter" => "*",
      "ext-gd" => "*",
      "ext-hash" => "*",
      "ext-json" => "*",
      "ext-pcre" => "*",
      "ext-PDO" => "*",
      "ext-session" => "*",
      "ext-SimpleXML" => "*",
      "ext-SPL" => "*",
      "ext-tokenizer" => "*",
      "ext-xml" => "*",
      "php" => "^5.5.9|>=7.0.8",
      'wikimedia/composer-merge-plugin' => '^1.4',
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
        "DrupalComponentTester\\" => 'src',
        "Drupal\\Tests\\Component\\$group\\" => "core/tests/Drupal/Tests/Component/$group",
      ],
    ],
    'respositories' => [
      'type' => 'composer',
      'url' => 'https://packages.drupal.org/8',
    ],
    'scripts' => [
      'post-update-cmd' => 'DrupalComponentTester\Composer::upgradePHPUnit',
      'drupal-phpunit-upgrade' => '@composer update phpunit/phpunit --with-dependencies --no-progress',
    ],
  ];
}

function execute($command) {
  $output = [];
  $signal = 255;
  exec($command, $output, $signal);
  echo implode("\n", $output);
  return $signal;
}
