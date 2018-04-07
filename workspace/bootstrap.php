<?php
function loader() {
  $autoloader = require __DIR__ . '/vendor/autoload.php';
  $autoloader->addPsr4('Drupal\\Tests\\Component\\', __DIR__ . '/tests/Drupal/Tests/Component/');
  return $autoloader;
}

$test_loader = loader();

// Register the assertion handler. Only the Utility component needs this.
if (class_exists('Drupal\Component\Assertion\Handle')) {
  Drupal\Component\Assertion\Handle::register();
}

return $test_loader;
