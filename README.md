BrokerX (Laravel)

This repository contains the BrokerX Laravel application. The sections below explain how to set up a local development environment on Windows (PowerShell) or a Unix-like shell.

## Requirements

- PHP >= 8.0 (check `php -v`)
- Composer (https://getcomposer.org)
- Node.js + npm (or bun/pnpm if preferred)
- A database server (MySQL, MariaDB, PostgreSQL, or SQLite)
- Git

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
