@servers(['easychild' => 'santosh@beta.easychild.de'])

@task('deploy', ['on' => 'easychild'])
    cd kindergarten
    php artisan down

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
@endtask

@task('optimize:clear')
    cd kindergarten
    php artisan optimize:clear

    php artisan config:cache
    php artisan event:cache
    php artisan route:cache
    php artisan view:cache
@endtask

@task('migrate:restart')
    cd kindergarten

    php artisan migrate:fresh --seed --force

    php artisan optimize:clear

    php artisan config:cache
    php artisan event:cache
    php artisan route:cache
    php artisan view:cache

    php artisan schedule-monitor:sync
@endtask

@task('trans:update')
    cd kindergarten
    php artisan translation_sheet:pull
@endtask

@task('monitor')
    cd kindergarten
    php artisan schedule-monitor:list
@endtask
