QA_DOCKER_IMAGE=jakzal/phpqa:alpine
QA_DOCKER_COMMAND=docker run -it --rm -v "$(shell pwd):/project" --user "$(shell id -u):$(shell id -g)" -w /project ${QA_DOCKER_IMAGE}

dist: cs-full phpstan phpunit
ci: cs-full-check phpstan phpunit-coverage
lint: cs-full-check phpstan

phpstan:
	sh -c "${QA_DOCKER_COMMAND} phpstan analyse --configuration phpstan.neon --level 5 ."

cs:
	sh -c "${QA_DOCKER_COMMAND} php-cs-fixer fix -vvv --diff"

cs-full:
	sh -c "${QA_DOCKER_COMMAND} php-cs-fixer fix -vvv --using-cache=false --diff"

cs-full-check:
	sh -c "${QA_DOCKER_COMMAND} php-cs-fixer fix -vvv --using-cache=false --diff --dry-run"

composer-compat:
	composer config "platform.ext-mongo" "1.6.16"
	composer require alcaeus/mongo-php-adapter  --no-update

composer-config-beta:
	composer config "minimum-stability" "beta"

composer-install:
	rm -f composer.lock && cp composer.json composer.json~
	[[ -v SYMFONY_VERSION ]] && composer require "symfony/symfony:${SYMFONY_VERSION}" --no-update || true
	composer update --prefer-dist --no-interaction
	mv composer.json~ composer.json

phpunit:
	vendor/bin/phpunit

phpunit-coverage: clean
	phpdbg -qrr vendor/bin/phpunit --coverage-text  --coverage-clover=build/clover.xml --coverage-xml=build/coverage-xml --log-junit=build/phpunit.junit.xml

infection: phpunit-coverage
	sh -c "${QA_DOCKER_COMMAND} phpdbg -qrr /usr/local/bin/infection"

clean:
	rm -rf build/*
