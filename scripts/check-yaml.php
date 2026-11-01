<?php

require __DIR__.'/../vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

$paths = array_merge(glob(__DIR__.'/../.github/workflows/*.yml'), glob(__DIR__.'/../.github/*.yml'), glob(__DIR__.'/../.github/ISSUE_TEMPLATE/*.yml'), glob(__DIR__.'/../docker-compose*.yml'));
foreach ($paths as $path) {
    Yaml::parseFile($path);
    echo basename($path).": OK\n";
}
