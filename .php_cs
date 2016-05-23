<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\CS\Config\Config;
use Symfony\CS\Finder\DefaultFinder;
use Symfony\CS\Fixer\Contrib\HeaderCommentFixer;
use Symfony\CS\FixerInterface;

$finder = DefaultFinder::create()
    ->in(__DIR__)
;

$header = <<<EOF
This file is part of the FOSOAuthServerBundle package.

(c) FriendsOfSymfony <http://friendsofsymfony.github.com/>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

HeaderCommentFixer::setHeader($header);

return Config::create()
    ->level(FixerInterface::SYMFONY_LEVEL)
    ->fixers(array('align_double_arrow', 'header_comment'))
    ->finder($finder)
    ->setUsingCache(true)
;
