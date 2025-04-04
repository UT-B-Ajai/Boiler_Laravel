# Laravel API Authentication with Sanctum

This project is a Laravel-based API with authentication using Laravel Sanctum.

## 🚀 Installation

### 1️⃣ Clone the Repository

git clone https://github.com/UT-B-Ajai/Boiler_Laravel.git  [https://github.com/your-repo/projectname.git]

cd Boiler_Laravel [project name]

2️⃣ Install Dependencies

composer install

3️⃣ Configure the Environment
Copy the .env.example file to .env and set up your database connection.

cp .env.example .env

Edit the .env file:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=root
DB_PASSWORD=your_password

4️⃣ Generate Application Key

php artisan key:generate

5️⃣ Install Sanctum
composer require laravel/sanctum

Then publish the Sanctum configuration:

php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

6️⃣Run Migrations
php artisan migrate

