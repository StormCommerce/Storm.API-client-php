<?php
use Storm\StormClient;
use Storm\Util\Str;

define('STORM_ROOT', dirname(__FILE__));
require_once "vendor/autoload.php";
if (count($argv) != 4) {
    echo "Usage: php expose.php [certifcation path] [password] [redis] \n";
    die();
}

$certPath = $argv[1];
if (!file_exists($certPath)) {
    $certPath = Str::trailingSlashIt(getcwd()) . $certPath;
    if (!file_exists($certPath)) {
        echo "Cannot find certifcation \n";
        die;
    }
}
$certPath = realpath($certPath);
$certPassword = $argv[2];
$redis = $argv[3];

$configuration = [
    'certification_path' => $certPath,
    'certification_password' => $certPassword,
    'redis_path' => $redis,
];

StormClient::self($configuration)->expose()->generateClasses();