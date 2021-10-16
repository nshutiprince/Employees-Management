## environment file
>after cloning the repo run 
>composer update

to create a .env file run
>touch .env

>You can find environment variables in `.env.example` file. 
Make sure you have all required environment variables in your .env file before you start Running this project locally.
>cp .env.example  .env

->make email configuration in .env file
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=******@gmail.com
MAIL_PASSWORD=*******
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=*********@gmail.com
MAIL_FROM_NAME=ADMINISTRATOR

->make database configuration in .env file
DB_CONNECTION=mysql
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

## Database migration and seeding
### Running migration
You have to execute this command: 

->php artisan migrate 

This command will create all empty tables defined in migrations folder in the database

## Swagger
### Running 
You have to execute this command: 

->php artisan l5-swagger:generate

This command will create api documentation accessible at

->http://127.0.0.1:8000/api/documentation

after running the project

## Running the project
* php artisan serve


# c4-barefoot-backend
>after cloning the repo run 
>composer update

to create a .env file run
>touch .env

>You can find environment variables in `.env.example` file. 
Make sure you have all required environment variables in your .env file before you start Running this project locally.
>cp .env.example  .env

->make email configuration in .env file
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=******@gmail.com
MAIL_PASSWORD=*******
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=*********@gmail.com
MAIL_FROM_NAME=ADMINISTRATOR

->make database configuration in .env file
DB_CONNECTION=mysql
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

## Database migration and seeding
### Running migration
You have to execute this command: 

->php artisan migrate 

This command will create all empty tables defined in migrations folder in the database

## Swagger
### Running 
You have to execute this command: 

->php artisan l5-swagger:generate

This command will create api documentation accessible at

->http://127.0.0.1:8000/api/documentation

after running the project

## Running the project
* php artisan serve

