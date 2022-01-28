# Installation Guide

## Clone Repo

Clone the repo in your webserver root.


## Install Dependencies

Go to project directory and run:

```bash
composer install
npm install
npm run dev  # development
npm run prod #(for production build)
```

If you use Yarn then use following:
```bash
npm install
npm run dev  # development
npm run prod  #(for production build)
```


## Create environment

Copy `.env.example` file content and create `.env` file in root of the directory

I have added my default timezone on example env file.
Edit the `APP_TIMEZONE` with the timezone viable for your location. So the server will keep this date/time as its default.


## Database

You can use any database you like from `mysql, sqlite, postgres and sql server`

Now in your `.env` file created above check for database configuration, and edit them as follows:

Mostly we use mysql or sqlite, so I will write configuration for those, but for other it should be almost the same as mysql.



### MySql

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dbname
DB_USERNAME=dbusername
DB_PASSWORD=bduserpassword
```


### Sqlite

```bash
DB_CONNECTION=sqlite
DB_DATABASE=filename.sqlite
```

So in sqlite case, you can remove all other env config and only have above two. and dont forget to just create a filename.sqlite (you can name that file as you like and use that in above DB_DATABASE) inside `./database/` folder. That folder is in root of the project, so the full path will be like `./database/{dbname}.sqlite`



## Run few commands

Generate a secured key used to encrypt and decrypt things inside the framework.

Run the migration for users table

Run the seed so we have some fake user data

```bash
php artisan key:generate
php artisan migrate
php artisan db:seed
```



## View application

Now go to the browser and load the application. you can use built in php/laravel script to start just a server as well if you have not setup some application like (WAMP/laragon)

```bash
php artisan serve
```

Above command will start a server and give you indication of url it has started the server which you can use to visit the app.



You will be taken to welcome page. that page is publicly visible so you can view that. But if you click on users on the left, you will be taken to login page.

Just use below credential to login.

```
email: santosh.khanal55@gmail.com
password: password
```

You can use the search bar on top navbar to search for user as well. and use the pagination link to navigate to other page.


## Test the app

You can run some test command to run the test for the app. For now I have test to check the users page. More in future. 

```
php artisan test

or

./vendor/bin/phpunit
```

The first command is laravel new feature which gives all the test names and passed ones.

And the other one is default phpunit test command

Both are almost same, but the phpunit will run all tests and give all failed report, laravel test will fail on first one and provide detail.
