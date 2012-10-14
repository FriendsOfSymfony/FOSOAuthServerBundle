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
if (file_exists($file = __DIR__ . '/../vendor/propel/propel1/generator/lib/util/PropelQuickBuilder.php')) {
    set_include_path(__DIR__ . '/../vendor/phing/phing/classes' . PATH_SEPARATOR . get_include_path());
    require_once $file;
}

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');

// Generate Propel base classes on the fly
if (class_exists('TypehintableBehavior')) {
    $class   = new \ReflectionClass('TypehintableBehavior');
    $builder = new \PropelQuickBuilder();
    $builder->getConfig()->setBuildProperty('behavior.typehintable.class', $class->getFileName());
    $builder->setSchema(file_get_contents(__DIR__ . '/../Resources/config/propel/schema.xml'));
    $builder->setClassTargets(array('tablemap', 'peer', 'object', 'query', 'peerstub'));
    $builder->build();
}
