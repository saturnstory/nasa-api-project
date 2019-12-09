## About Project

Mars Rover Space via Nasa Api Application that provides an interface that takes the pictures taken from the Api and transfers the data to the database.

## Setup
- Clone Git repo
- switch to the project directory and run the "composer install" command. then "composer update" package dependencies will be installed
- Create a database
- Copy the file ".env.example" and rename it to ".env" Enter db connection information
- Run the command "php artisan migrate" to create the table schema.
- php artisan serve
