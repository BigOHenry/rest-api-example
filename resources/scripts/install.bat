@echo off
cd ..\..
echo Installing REST API application...

REM Create .env.local file if it does not exist
if not exist .env.local (
    echo Creating .env.local z .env.local.example...
    copy .env.local.example .env.local
    echo .env.local the file was created
) else (
    echo .env.local already exists
)

REM Creating a Docker network if it does not exist
docker network create rest_net 2>nul

REM Starting the application
echo Launching the app...
docker-compose up -d

REM Starting DB migrations
echo Starting DB migrations...
docker exec -i rest_api php bin/console doctrine:migrations:migrate --no-interaction

REM Generating keypair
echo Generating keypair...
docker exec -i rest_api php bin/console lexik:jwt:generate-keypair

echo The app is running on http://localhost:8080