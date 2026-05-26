.PHONY: test lint analyse check

test:
	./vendor/bin/sail artisan test

lint:
	./vendor/bin/sail php ./vendor/bin/pint --test

analyse:
	./vendor/bin/sail php ./vendor/bin/phpstan analyse

check: lint analyse test
