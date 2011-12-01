<?php

chdir(dirname(__DIR__));

if (!is_dir(__DIR__ . '/bundles/Alb')) {
    if (!mkdir(__DIR__ . '/bundles/Alb', 0777, true)) {
        fprintf(STDERR, "failed to mkdir %s\n", __DIR__ . '/bundles/Alb');
        exit(1);
    }
}
if (!file_exists(__DIR__ . '/bundles/Alb/OAuth2ServerBundle')) {
    if (!symlink('../../..', __DIR__ . '/bundles/Alb/OAuth2ServerBundle')) {
        fprintf(STDERR, "failed to symlink %s -> %s\n", __DIR__ . '/bundles/Alb/OAuth2ServerBundle', '../../..');
        exit(1);
    }
}

copy(__DIR__ . '/../Tests/autoload.php.vendor', __DIR__ . '/../Tests/autoload.php');

echo "Fetching vendors\n";

passthru('git submodule init');
passthru('git submodule sync');
passthru('git submodule update');

$symfonyVersion = getenv('SYMFONY_VERSION') ?: 'origin/master';
$doctrineVersion = getenv('DOCTRINE_VERSION') ?: 'origin/master';


printf("Checking out symfony version %s\n", $symfonyVersion);

$cmd = sprintf('cd vendor/symfony && git checkout %s', escapeshellarg($symfonyVersion));

echo $cmd, "\n";
passthru($cmd);


printf("Checking out doctrine version %s\n", $doctrineVersion);

$cmd = sprintf('cd vendor/doctrine && git checkout %s', escapeshellarg($doctrineVersion));

echo $cmd, "\n";
passthru($cmd);

$cmd = sprintf('cd vendor/doctrine-common && git checkout %s', escapeshellarg($doctrineVersion));

echo $cmd, "\n";
passthru($cmd);

$cmd = sprintf('cd vendor/doctrine-dbal && git checkout %s', escapeshellarg($doctrineVersion));

echo $cmd, "\n";
passthru($cmd);

