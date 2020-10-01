install:
	cd ./docker && ./install.sh

start:
	cd ./docker && ./start.sh

stop:
	cd ./docker && ./stop.sh

xdebug-enable:
	cd ./docker && docker-compose exec -T -u 0 php docker-php-ext-enable xdebug
	cd ./docker && docker-compose exec -T -u 0 php bash -c "kill -USR2 1"

xdebug-disable:
	cd ./docker && docker-compose exec -T -u 0 php sed -i '/zend_extension/d' /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
	cd ./docker && docker-compose exec -T -u 0 php bash -c "kill -USR2 1"

shell:
	cd ./docker && docker-compose exec php bash

check:
	cd ./docker/ && docker-compose exec -T php make phpcs
	cd ./docker/ && docker-compose exec -T php make stan

phpcs:
	vendor/bin/phpcs

fixer:
	./vendor/bin/phpcbf

stan:
	vendor/bin/phpstan analyse --memory-limit=4000M
