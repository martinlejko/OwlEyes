#!/bin/bash
set -e

# Create .env file
echo "Creating .env file..."
cat > .env << EOF
# Database Configuration
DB_NAME=owleyes
DB_USER=owleyes_user
DB_PASSWORD=owleyes_password
DB_ROOT_PASSWORD=root_password

# Application Settings
APP_ENV=dev
APP_DEBUG=true
APP_SECRET=3b9a3368f7e61a9fc6f4398967ad3ecd

# Worker Settings
MONITOR_MIN_INTERVAL=5
MONITOR_MAX_INTERVAL=300
EOF

# Create directories for logs and cache
echo "Creating directories for logs and cache..."
mkdir -p backend/var/logs
mkdir -p backend/var/cache/doctrine

# Create public directory for the backend
echo "Creating public directory for the backend..."
mkdir -p backend/public/assets

# Initialize composer and npm if not already done
echo "Initializing backend dependencies..."
if [ ! -f backend/composer.json ]; then
  # Check if composer is installed
  if command -v composer &> /dev/null; then
    echo "Using local composer installation"
    (cd backend && composer init --name=owleyes/backend --description="OwlEyes Monitoring Service Backend" --type=project --require="slim/slim:^4.0" --require="php-di/php-di:^6.0" --require="doctrine/orm:^2.10" --require="monolog/monolog:^2.0" --require="vlucas/phpdotenv:^5.0" --require="webonyx/graphql-php:^14.0" --no-interaction)
    (cd backend && composer require slim/psr7:^1.5 doctrine/migrations:^3.0 tuupola/slim-basic-auth:^3.0)
  else
    echo "Composer not found. Creating basic composer.json file."
    cat > backend/composer.json << EOF
{
    "name": "owleyes/backend",
    "description": "OwlEyes Monitoring Service Backend",
    "type": "project",
    "require": {
        "php": "^8.1",
        "slim/slim": "^4.0",
        "slim/psr7": "^1.5",
        "php-di/php-di": "^6.0",
        "doctrine/orm": "^2.10",
        "doctrine/migrations": "^3.0",
        "monolog/monolog": "^2.0",
        "vlucas/phpdotenv": "^5.0",
        "webonyx/graphql-php": "^14.0",
        "tuupola/slim-basic-auth": "^3.0"
    },
    "autoload": {
        "psr-4": {
            "App\\\\": "src/"
        }
    }
}
EOF
  fi
fi

echo "Initializing frontend dependencies..."
if [ ! -f frontend/package.json ]; then
  # Check if npm is installed
  if command -v npm &> /dev/null; then
    echo "Using local npm installation"
    (cd frontend && npm init -y)
    (cd frontend && npm install react react-dom react-router-dom @mui/material @mui/icons-material @emotion/react @emotion/styled react-query)
    (cd frontend && npm install -D vite @vitejs/plugin-react)
  else
    echo "npm not found. Creating basic package.json file."
    cat > frontend/package.json << EOF
{
  "name": "owleyes-frontend",
  "version": "0.1.0",
  "private": true,
  "dependencies": {
    "react": "^18.2.0",
    "react-dom": "^18.2.0",
    "react-router-dom": "^6.10.0",
    "@mui/material": "^5.11.16",
    "@mui/icons-material": "^5.11.16",
    "@emotion/react": "^11.10.6",
    "@emotion/styled": "^11.10.6",
    "react-query": "^3.39.3"
  },
  "devDependencies": {
    "vite": "^4.2.1",
    "@vitejs/plugin-react": "^3.1.0"
  },
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview"
  }
}
EOF
  fi
fi

# Make the worker script executable
echo "Making worker script executable..."
chmod +x backend/bin/worker.php

echo "Setup completed successfully!"
echo
echo "Note: We have created skeleton composer.json and package.json files."
echo "You can now run either:"
echo "1. docker-compose up --build (recommended for Docker workflow)"
echo "or"
echo "2. Manually install dependencies with:"
echo "   - cd backend && composer install"
echo "   - cd frontend && npm install"
echo
echo "Once the application is running, you will need to:"
echo "1. Run backend migrations: docker-compose exec backend php bin/console doctrine:migrations:migrate"
echo "2. Access the application at http://localhost:3000" 