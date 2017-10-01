<?php
function loader() {
  $autoloader = require __DIR__ . '/vendor/autoload.php';
  $autoloader->addPsr4('Drupal\\Tests\\Component\\', __DIR__ . '/tests/Drupal/Tests/Component/');
  return $autoloader;
}
return loader();
