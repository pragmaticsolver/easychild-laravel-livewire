# Installation

After cloning this repo. have `.env` file according to the `.env.example`

# Starting docker
`docker-compose up -d`

# Stopping docker
`docker-compose down`

# Starting docker with re build
`docker-compose up -d --build`

# Composer package installation
`docker-compose exec -u laravel php composer install`


# Laravel artisan command
`docker-compose exec -u laravel php php artisan {command name}`


# npm installation and run dev/prod
`docker-compose run --rm npm {command}`

# All the commands to start the project
```bash
docker-compose up -d
docker-compose exec -u laravel php composer install
docker-compose run --rm npm install
docker-compose run --rm npm run dev
docker-compose exec -u laravel php php artisan migrate:fresh --seed
docker-compose exec -u laravel php php artisan storage:link
docker-compose exec -u laravel php php artisan livewire:publish --assets
docker-compose exec -u laravel php php artisan optimize:clear
```
