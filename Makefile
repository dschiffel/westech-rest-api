setup:
	docker compose build
	docker compose up -d
	sleep 5
	docker compose exec app php public/index.php migrate=1

up:
	docker compose up -d

down:
	docker compose down

migrate:
	docker compose exec app php public/index.php migrate=1

test:
	docker compose exec app ./vendor/bin/phpunit --display-deprecations --display-warnings

shell:
	docker compose exec app sh
