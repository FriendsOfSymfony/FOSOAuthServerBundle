<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__ . '/../vendor/autoload.php';

// require Propel
if (file_exists($file = __DIR__ . '/../vendor/propel/propel/src/Propel/Generator/Util/QuickBuilder.php')) {
    require_once $file;
}

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');

// Generate Propel base classes on the fly
$builder = new \Propel\Generator\Util\QuickBuilder();
$builder->setSchema(file_get_contents(__DIR__ . '/../Resources/config/propel/schema.xml'));
$builder->build(null, null, null, null, array('tablemap', 'object', 'query'));

