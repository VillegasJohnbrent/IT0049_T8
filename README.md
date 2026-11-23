# TFA8 Laravel POS

John Brent D. Villegas · TX33 · IT0049

Continues TFA7 with customer and user resource controllers, Breeze authentication and protected account pages, the existing forms and avatar uploads are retained.

## Setup

Use PHP 8.2+, Composer 2, GD, Mbstring, Fileinfo and PDO SQLite. Set upload_max_filesize to at least 2M and post_max_size to 8M in php.ini.

Open CMD in this folder:

```cmd
composer install
copy .env.example .env
if not exist database\database.sqlite type nul > database\database.sqlite
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Open http://127.0.0.1:8000.

Sample login: john@example.com / Password123!

You can also register an account at /register. Passwords are hashed, registration checks unique usernames and emails, login attempts are limited and logout invalidates the session.

The Breeze package is declared in composer.json and installs with Composer, its authentication files are included with the T7 layout and username/full_name fields. Do not run breeze:install over these files because it replaces the customized routes and views. CSS is local, no Node build is needed.

Password reset messages use the log mailer for local testing, find the reset link in storage/logs/laravel.log. Email verification is not required by the activity.

## Existing T7 database

Back up the database and avatar folder first, keep .env and run composer install, php artisan migrate and php artisan storage:link. Do not overwrite the database with the empty file in this ZIP.

Existing T7 users retain their data but have no login credentials, register using a new username then edit those records to assign an email and password. Seeders leave existing users unchanged. Run migrations before opening the app.

## Routes

Home and About remain public. Customers and Users each use one Route::resource() inside an auth middleware group, this covers index, create, store, show, edit, update and destroy. Delete asks for confirmation and the current account cannot delete itself.

## Checks

```cmd
composer test
php artisan route:list --path=customers
php artisan route:list --path=users
```

Each resource should list seven routes, PUT and PATCH share the update route.

1. Log out and open /customers or /users/create, both must redirect to /login.
2. Log in, the requested page should open, check both lists and their Create, View and Edit pages.
3. Add, edit and delete a customer and another user, upload a profile image while editing a user.
4. Log out again, neither directory should be accessible.

The feature tests cover guest redirects, login, registration, throttling, logout, password reset, resource actions and the retained T7 forms and avatars. Run them before submission.
