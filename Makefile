build: docker-build
up: docker-up
install: composer-install
tables: create-tables


docker-build:
	docker compose build

docker-up:
	docker compose up -d

composer-install:
	docker compose run --rm cli composer install

create-tables:
	cat app/database/create_orders_table.sql | docker compose exec -T mysql mysql -u app -p"password" -D app

docker-down:
	docker compose down --remove-orphans