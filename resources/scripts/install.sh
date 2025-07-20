#!/bin/bash

cd ../..

echo "Installing REST API application..."

# Create .env.local file if it does not exist
if [ ! -f .env.local ]; then
    echo "Creating .env.local from .env.local.example..."
    cp .env.local.example .env.local
    echo ".env.local file was created"
else
    echo ".env.local already exists"
fi

# Creating a Docker network if it does not exist
docker network create rest_net 2>/dev/null

# Starting the application
echo "Launching the app..."
docker-compose up -d

# Starting DB migrations
echo "Starting DB migrations..."
docker exec -i rest_api php bin/console doctrine:migrations:migrate --no-interaction

# Generating keypair
echo "Generating keypair..."
docker exec -i rest_api php bin/console lexik:jwt:generate-keypair

echo "The app is running on http://localhost:8080"
