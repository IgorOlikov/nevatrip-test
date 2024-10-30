up: docker-up
install: composer-install
tables: create-tables
clients: seed-client-merchandise
text: seed-orders-text
run: import


docker-up:
	docker compose up -d

composer-install:
	docker compose run --rm cli composer install

create-tables:
	docker compose exec -i postgresql psql -d app -U app -f create-tables.sql

seed-client-merchandise:
	docker compose run --rm cli php database-fake-seeder.php

import:
	docker compose run --rm cli php import-script.php orders.txt

seed-orders-text:
	docker compose run --rm cli php orders-text-file-fake-seeder.php

docker-down:
	docker compose down --remove-orphans