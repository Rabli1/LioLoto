# LioLoto

A Laravel 12 game lounge using virtual points. Accounts and game rounds live in SQL; game outcomes and balance changes are handled by the server. The interface is built with Blade and Vite. Daily Liotodle access resets at midnight UTC (the application timezone).

## Reproduce a development installation

Requirements: PHP 8.2 or later with PDO SQLite (or PDO MySQL), mbstring, openssl, tokenizer, XML, ctype, fileinfo and curl; Composer 2; Node.js 24 with npm. Commit both lockfiles and use `composer install` / `npm ci`, rather than updating dependencies during a release.

```powershell
git clone https://github.com/Rabli1/LioLoto.git
cd LioLoto
./scripts/setup.ps1 -Demo
php artisan serve --no-reload
```

On Linux/macOS:

```sh
git clone https://github.com/Rabli1/LioLoto.git
cd LioLoto
sh scripts/setup.sh --demo
php artisan serve
```

Open `http://localhost:8000`. Setup copies `.env.example` only when `.env` does not exist, generates an application key only when absent, installs the locked dependencies, builds assets, and runs forward migrations. It preserves existing accounts and game data on reruns. `-Demo` / `--demo` creates one confirmed, non-admin local account named `demo` with a random password printed to the terminal once. Subsequent runs preserve that password and the account's progress. No production or default seed account is created.

If Composer is installed as a PHAR instead of on PATH, set `COMPOSER_BINARY` to its absolute path. `-SkipInstall` / `--skip-install` and `-SkipBuild` / `--skip-build` are available when dependencies or assets were already prepared.

Development mail uses the log driver; confirmation links appear in `storage/logs/laravel.log`. For normal registration with delivered mail, configure SMTP in `.env`.

If a confirmation link expires or email delivery fails, enter the account credentials on the login page and choose **Renvoyer la confirmation**. Legacy imported accounts with a valid email can use this same flow.

## Database migrations and an intentional rebuild

SQLite is the development default at `database/database.sqlite`. Set an absolute `DB_DATABASE` to choose another file. MySQL and PostgreSQL can also be configured with `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD`; create the database and grant its application user migration permissions before running the script. The container includes SQLite and MySQL drivers; PostgreSQL deployments must add `pdo_pgsql` to their PHP image.

```powershell
./scripts/database.ps1
```

This prints the resolved database target and applies pending migrations. It preserves the original SQL schema upgrade path: French legacy columns are renamed to the fields used by the application, while stored balances and password hashes are retained. Existing duplicate SQL usernames must be resolved before upgrading.

A rebuild deletes **every table** in the selected database. Take a backup first. It requires a separate explicit command, the exact target printed by the script, and `APP_ENV=local` or `testing`:

```powershell
./scripts/database.ps1 -Fresh -ConfirmTarget 'sqlite:C:\full\path\LioLoto\database\database.sqlite' -Demo
```

```sh
sh scripts/database.sh --fresh --confirm=sqlite:/full/path/LioLoto/database/database.sqlite --demo
```

For MySQL the confirmation target is `mysql:HOST:PORT:DATABASE`; PostgreSQL uses `pgsql:HOST:PORT:DATABASE`. Production rebuilds are blocked. Normal setup, deployment and container restarts never use `migrate:fresh`. `-Seed` / `--seed` invokes the safe default seeder, which creates no accounts.

Older versions stored users in `database/json/users.json`. No personal data is bundled or imported automatically. Import a trusted backup explicitly after migrations:

```sh
php scripts/import-users.php /private/backup/users.json --dry-run
php scripts/import-users.php /private/backup/users.json
```

The importer validates the entire export, rejects plaintext passwords and conflicting identities, preserves IDs and password hashes, and imports within a transaction. Reruns skip existing matching identities without overwriting live passwords, balances or privileges. Legacy confirmation and remember tokens are invalidated, and the source file is preserved. Back up the original JSON file and SQL database before migration. Pending legacy accounts with an email can request a new confirmation from the login page using their existing credentials. Accounts without an email require operator verification before confirmation.

## Deploy with Docker Compose

Use one replica with the persistent SQLite volume, or configure an external MySQL database for a shared database. Docker Engine and Compose v2 are required. The container serves only `public/` through Apache; put it behind an HTTPS reverse proxy.

1. Copy `.env.production.example` to `.env.production`.
2. Set your HTTPS `APP_URL`, working SMTP settings, `TRUSTED_PROXIES`, and a secret application key. `TRUSTED_PROXIES` is a comma-separated list of the explicit IP addresses/CIDR networks of your HTTPS reverse proxy as seen by Apache. Configure the proxy to forward `X-Forwarded-Proto: https` and the client address. Leave this setting empty only when the PHP host handles TLS directly. Generate the key once with `php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"` and preserve it across releases.
3. Build and launch the release:

```sh
docker compose --env-file .env.production build --pull
docker compose --env-file .env.production up -d
docker compose --env-file .env.production ps
```

The entrypoint validates production settings, applies forward migrations, caches Laravel configuration/routes/views, and exits on failure. The host binding defaults to `127.0.0.1:8080`; configure your HTTPS reverse proxy to that address. `/up` is the container liveness endpoint. Check startup failures with `docker compose --env-file .env.production logs app`.

The named `application_storage` volume holds the SQLite database, sessions and application files across rebuilt images. Preserve it and take regular database backups; `docker compose down -v` deletes it. Do not run multiple containers against a shared SQLite file. For an external MySQL database, configure its credentials in `.env.production` and back it up using the database provider's tooling.

Composer and npm dependencies are fixed by their committed lockfiles. Base image tags receive upstream security fixes. For an exact reproduction of container bases, set `PHP_IMAGE`, `NODE_IMAGE` and `COMPOSER_IMAGE` in the deployment environment to approved immutable registry digests, and set a `RELEASE_TAG` for the resulting application image. Record the source commit and resulting application image digest for each release; deploying that image digest reproduces the built code and assets.

## Deploy on an existing PHP host

Check out the desired source commit on the server, supply a production `.env` or environment variables using `.env.production.example` as a reference, and configure the web root as `public/`. Make `storage/` and `bootstrap/cache/` writable by the web worker. Configure HTTPS and SMTP before deployment.

```powershell
./scripts/deploy.ps1
```

```sh
sh scripts/deploy.sh
```

The script installs production Composer dependencies from the lockfile, builds the frontend with `npm ci`, enables maintenance mode, migrates the existing database, caches Laravel artifacts, and reopens the app. A failure after maintenance begins leaves maintenance enabled for inspection. Preserve `APP_KEY`, environment secrets, the database and storage across releases. After restoring the prior code and fixing a failed deployment, run `php artisan up` only when the application is ready. Apply a code rollback only if it is compatible with the current schema; restoring a database backup is a separate operator decision.

The scripts do not provision a cloud account, public DNS, TLS certificates or an external SQL server. They prepare a reproducible application release for the host you choose.

## Bootstrap an administrator

Register and confirm an account through the normal site flow, then have a trusted server operator promote that specific account. No deployment script grants admin privileges automatically.

```sh
php artisan tinker --execute="App\Models\User::where('name', 'your_username')->firstOrFail()->forceFill(['admin' => true])->save();"
```

For Compose, prefix the PHP command with `docker compose --env-file .env.production exec app`. Replace `your_username` with the exact account name and keep server console access limited to trusted operators.

## Verify a release

```sh
composer validate --strict
composer audit
npm audit
php artisan test
npm run build
```

The database lifecycle tests use disposable SQLite files and verify a repeat setup, preserved legacy SQL data, explicit JSON import, confirmation guards, and production reset refusal. Feature tests cover account and game security. Never point test commands at a production database.
