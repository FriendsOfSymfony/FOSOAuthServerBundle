QA_DOCKER_IMAGE=jakzal/phpqa:alpine
QA_DOCKER_COMMAND=docker run -it --rm -v "$(shell pwd):/project" -w /project ${QA_DOCKER_IMAGE}

dist: cs-full phpstan phpunit
ci: phpstan phpunit-coverage
lint: cs-full-check phpstan

phpstan:
	sh -c "${QA_DOCKER_COMMAND} phpstan analyse --configuration phpstan.neon --level 6 ."

cs:
	sh -c "${QA_DOCKER_COMMAND} php-cs-fixer fix -vvv --diff"

cs-full:
	if [ ! -f .php-cs-fixer.php ]; then cp .php-cs-fixer.dist-php .php-cs-fixer.php; fi
	sh -c "${QA_DOCKER_COMMAND} php-cs-fixer fix -vvv --using-cache=no --diff"

cs-full-check:
	if [ ! -f .php-cs-fixer.php ]; then cp .php-cs-fixer.dist-php .php-cs-fixer.php; fi
	sh -c "${QA_DOCKER_COMMAND} php-cs-fixer fix -vvv --using-cache=no --diff --dry-run"

composer-compat:
	composer config "platform.ext-mongo" "1.6.16"
	composer require alcaeus/mongo-php-adapter  --no-update

composer-config-beta:
	composer config "minimum-stability" "beta"

composer-php7:
	# when php 7 and 8 are both installed, to test with php7 - for example:
	# $ make composer-compat
	# $ make composer-php7
	# $ SYMFONY_VERSION=5.3 make composer-install
	# $ php7.4 vendor/bin/phpunit
	composer config platform.php 7.4

composer-install:
	rm -f composer.lock && cp composer.json composer.json~
ifdef SYMFONY_VERSION
	composer require "symfony/symfony:$(SYMFONY_VERSION)" --no-update --no-interaction
endif
	composer update --prefer-dist --no-interaction
	mv composer.json~ composer.json

phpunit:
	vendor/bin/phpunit

# TODO: output to COV
phpunit-coverage:
	phpdbg -qrr vendor/bin/phpunit --coverage-text
