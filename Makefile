.PHONY: test lint analyse e2e e2e-headed e2e-report check

test:
	./vendor/bin/sail artisan test

lint:
	./vendor/bin/sail php ./vendor/bin/pint --test

analyse:
	./vendor/bin/sail php ./vendor/bin/phpstan analyse

e2e:
	npm run e2e

e2e-headed:
	npm run e2e:headed

e2e-report:
	npm run e2e:report

check: lint analyse test e2e
