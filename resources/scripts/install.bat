@echo off
cd ..\..
docker network create rest_net
docker compose up -d --build
docker exec -i rest_api php bin/console doctrine:migrations:migrate --no-interaction
echo Application starts on http://localhost:8080