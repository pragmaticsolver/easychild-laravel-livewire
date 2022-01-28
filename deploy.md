### Deploy
php artisan down --render="errors::555"

git clean -df
git checkout -- .
git pull

composer install
npm install
npm run prod

php artisan optimize:clear
php artisan livewire:publish --assets

php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache

php artisan schedule-monitor:sync

php artisan up


### Translation update
php artisan translation_sheet:pull
