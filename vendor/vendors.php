#!/usr/bin/env php
<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

set_time_limit(0);

if (isset($argv[1])) {
    $_SERVER['SYMFONY_VERSION'] = $argv[1];
}

$vendorDir = __DIR__;
$deps = array(
    array('symfony', 'http://github.com/symfony/symfony.git', isset($_SERVER['SYMFONY_VERSION']) ? $_SERVER['SYMFONY_VERSION'] : 'origin/master'),
    array('doctrine-common', 'http://github.com/doctrine/common.git', 'origin/master'),
    array('doctrine-dbal', 'http://github.com/doctrine/dbal.git', 'origin/master'),
    array('doctrine', 'http://github.com/doctrine/doctrine2.git', 'origin/master'),
    array('doctrine-mongodb-odm', 'http://github.com/doctrine/mongodb-odm.git', 'origin/master'),
    array('doctrine-mongodb', 'http://github.com/doctrine/mongodb.git', 'origin/master'),
    array('oauth2-php', 'https://github.com/arnaud-lb/oauth2-php', 'origin/master'),
);

foreach ($deps as $dep) {
    list($name, $url, $rev) = $dep;

    echo "> Installing/Updating $name\n";

    $installDir = $vendorDir.'/'.$name;
    if (!is_dir($installDir)) {
        system(sprintf('git clone -q %s %s', escapeshellarg($url), escapeshellarg($installDir)));
    }

    system(sprintf('cd %s && git fetch -q origin && git reset --hard %s', escapeshellarg($installDir), escapeshellarg($rev)));
}
