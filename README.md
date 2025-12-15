BrokerX (Laravel)

This repository contains the BrokerX Laravel application. The sections below explain how to set up a local development environment on Windows (PowerShell) or a Unix-like shell.

## Requirements

- PHP >= 8.0 (check `php -v`)
- Composer (https://getcomposer.org)
- Node.js + npm (or bun/pnpm if preferred)
- A database server (MySQL, MariaDB, PostgreSQL, or SQLite)
- Git

## Quick start (PowerShell)

1. Clone the repository:

```powershell
git clone <repo-url> brokerx
cd brokerx
```

2. Install PHP dependencies with Composer:

```powershell
composer install --prefer-dist --no-interaction
```

3. Copy the environment file. If `.env.example` is present you can use any of the commands below (PowerShell / cross-platform):

PowerShell:
```powershell
Copy-Item .env.example .env -Force
```
Cross-platform (works in composer scripts / bash):
```powershell
php -r "file_exists('.env') || copy('.env.example', '.env');"
```

4. Generate the application key:

```powershell
php artisan key:generate
```

5. Edit `.env` and set your database credentials and other environment variables. Example DB settings you should set:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=brokerx
DB_USERNAME=root
DB_PASSWORD=
```

6. Run database migrations (and seeders if needed):

```powershell
php artisan migrate --seed
```

7. Create the storage symlink so uploaded files are accessible:

```powershell
php artisan storage:link
```

8. Install frontend dependencies and build assets (choose one):

npm (recommended):
```powershell
npm install
npm run dev    # development with hot-reload
# or for production:
npm run build
```

If you use bun:
```powershell
bun install
bun run dev
```

9. Run the local server:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Open http://127.0.0.1:8000 in your browser.

## Running tests

Use the artisan test runner:

```powershell
php artisan test
```

Or use PHPUnit directly (if needed):

```powershell
./vendor/bin/phpunit
```

## Common tasks and scripts

- Clear and cache config/routes/views:

```powershell
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:clear
```

- Re-generate Composer autoload files:

```powershell
composer dump-autoload
```

## Troubleshooting

- Composer memory errors: run `php -d memory_limit=-1 composer install`.
- Missing PHP extensions: ensure `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, and `ctype` are enabled.
- If `php artisan migrate` fails, check your `.env` DB settings and that your DB server is running.
- If storage files are not visible, re-run `php artisan storage:link` and ensure IIS/Apache/Nginx has permission to read `storage/app/public`.

## Notes for maintainers

- This project uses Vite for frontend tooling (see `vite.config.js` and `package.json`).
- There is a `composer` script that creates `.env` from `.env.example` if missing; adjust environment handling for CI if needed.

## What's next

- Add any project-specific setup steps here (OAuth credentials, third-party API keys, background worker configuration, queue drivers, etc.).

---

If you'd like, I can also add a small `SETUP.md` with screenshots or add a PowerShell script to automate the steps above.
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
