# CV

A single-profile CV application: the resume is public at `/`, everything else is behind a
one-account admin login, and the whole thing exports to PDF.

Symfony 8.1 · PHP 8.4+ · Doctrine ORM 3 · Twig · AssetMapper + Stimulus/Turbo ·
Tailwind CSS v3 (via `symfonycasts/tailwind-bundle`) · Dompdf · MySQL 8

## Requirements

Either Docker (the whole stack is in `compose.yaml`), or a local toolchain:

- PHP 8.4 or newer with `ctype`, `iconv` and `gd`
  (`gd` is required — the photo upload validates images with `detectCorrupted`)
- Composer 2
- MySQL 8

## Setup

Create `.env.local` first — it is gitignored, and this is where every secret lives:

```dotenv
APP_SECRET=<run: php -r "echo bin2hex(random_bytes(16));">
ADMIN_PASSWORD_HASH='<run: php bin/console security:hash-password>'
```

> Keep the **single quotes** around the hash — bcrypt hashes contain `$`, which the
> shell and Dotenv would otherwise interpret.

### With Docker

```bash
docker compose up -d --build
docker compose exec php php bin/console doctrine:migrations:migrate
docker compose exec php php bin/console tailwind:build
```

The first boot is slow: the entrypoint runs `composer install` into the container's
own `vendor/` before php-fpm accepts requests.

| Service | URL | Credentials |
|---|---|---|
| The app (nginx → php-fpm) | http://localhost:8001 | admin / your `ADMIN_PASSWORD_HASH` |
| phpMyAdmin | http://localhost:8081 | `cv` / `cv`, or `root` / `root` |
| MySQL 8 | `127.0.0.1:3307` | same |

Those host ports are set in `compose.override.yaml`, chosen to dodge what commonly
already runs on a dev machine (3306 a local MySQL, 8000 another container project,
8080 a host Apache). Inside the stack the database is plain `database:3306`.

The database credentials live in `compose.yaml`, not in `.env` — nothing about the
connection belongs in a committed app file. `compose.yaml` also hands the `php`
service a `DATABASE_URL` pointing at the `database` container; a real environment
variable outranks every `.env` file, so it wins inside the stack and leaves any
host-side `DATABASE_URL` in `.env.local` alone. Override the credentials for a real
deployment by exporting `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_DATABASE` and
`MYSQL_ROOT_PASSWORD` before `docker compose up`.

> MySQL only applies those variables when it initialises an **empty** data directory.
> If the `cv_symfony_database_data` volume already exists from an earlier run with
> different credentials, either keep using the old ones or drop the volume with
> `docker compose down -v` (**this deletes the CV data**).

Two directories are named volumes rather than part of the bind mount:

- `var/` — a cache built on the host points at host paths and is useless in the
  container. Read the logs with `docker compose exec php cat var/log/dev.log`.
- `vendor/` — speed. Reading the dependency tree across a Windows bind mount costs
  seconds on *every* request (measured: ~5–9 s per page, ~0.5 s from a volume). The
  cost is that the container's `vendor/` is its own copy: after changing
  `composer.json`, run `composer install` on the host **and**
  `docker compose exec php composer install`.

### Without Docker

```bash
composer install
```

Add the connection to `.env.local` as well:

```dotenv
DATABASE_URL="mysql://user:pass@127.0.0.1:3306/cv?serverVersion=8.0.32&charset=utf8mb4"
```

```bash
php bin/console doctrine:migrations:migrate
php bin/console tailwind:build
symfony server:start   # or: php -S localhost:8000 -t public
```

Open `/`, log in at `/login` as **admin**, and create the profile.

## Tests

```bash
php vendor/bin/phpunit
```

The suite runs against SQLite (`var/test.db`, configured in `.env.test`) and builds its
schema from the entity mapping, so **no MySQL is needed** to run tests or to build in CI.

## Deployment notes

- Set `APP_ENV=prod` and `APP_DEBUG=0` in the server's `.env.local`, and put the real
  `APP_SECRET` and `ADMIN_PASSWORD_HASH` there. Nothing secret belongs in a committed
  file. `DATABASE_URL` comes from the environment (`compose.yaml` under Docker).
- The images are tuned for development: `docker/php/php.ini` revalidates opcache on
  every request because the source is bind-mounted. Turn `opcache.validate_timestamps`
  off and bake the source into the image for a real deployment.
- Rotate `ADMIN_PASSWORD_HASH` before going public. The hash for the old default
  password (`changeme`) is in this repository's git history.
- `public/uploads/` holds profile photos at runtime. It must be writable and it must
  survive deploys — it is not in version control.
- Login is rate limited to 5 attempts per 15 minutes per client (`login_throttling`).

## Layout

| Path | What lives there |
|---|---|
| `src/Controller/` | One thin CRUD controller per section, plus `HomeController` (public CV + PDF) |
| `src/Entity/` | `Profile` and its five collections: education, experience, skills, interests, competencies |
| `src/Service/` | `ProfilePhotoStorage` (upload dir, accepted formats, data URIs) and `CvPdfRenderer` |
| `templates/home/pdf.html.twig` | The PDF layout. Its comments explain the Dompdf quirks behind the odd-looking values — read them before changing any spacing. |
| `docker/` | Image and server config for the stack: `php/` (Dockerfile, `php.ini`, entrypoint) and `nginx/default.conf` |
| `docs/` | Code audit |
