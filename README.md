## About Mini ATM

This project is made using PHP Framework Laravel for author job interview test.
Mini ATM is simple project of how ATM (Auto Teller Machine) works.

## How to Use This on Your Local

There is two options, with & without docker.

### Without Docker

- You need XAMPP or other RDBMS installed on your local
- Create database named "atm"
- Set database in .env file on your project root
- Run command "php artisan migrate" to create table automatically
- Run command "php artisan db:seed" to insert default users & ATM Machines
- Run command "php artisan serve" to start the app
- Access your app on http://localhost:8000
- There are two users from seed with card number 1122334455667788 & 8877665544332211 with the same pin 123456

### With Docker
- You need docker CLI or docker desktop installed and running on your local
- Change "DB_USERNAME" and "DB_PASSWORD" in your .env file if it's still root with blank password
- Run command "docker compose up -d" from your project root
- Run command "docker exec -t mini-atm bash" and then run "php artisan migrate" & "php artisan db:seed"
- Access your app on http://localhost:8000
- There are two users from seed with card number 1122334455667788 & 8877665544332211 with the same pin 123456


Author: Muhammad Zia Abdillah Asya
