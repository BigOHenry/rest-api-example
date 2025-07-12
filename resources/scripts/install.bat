@echo off
cd ..\..
docker network create rest_net
docker compose up -d --build
docker exec -i rest_api php bin/console doctrine:migrations:migrate --no-interaction
docker exec -i rest_api php bin/console lexik:jwt:generate-keypair
echo Application starts on http://localhost:8080