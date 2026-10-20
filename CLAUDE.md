# CLAUDE.md

Guidance for working in this repository.

## Keeping this file current

Update this file in the same change when:

- you change something it describes (versions, commands, file locations, the DynamoDB migration stage), or
- you hit a non-obvious gotcha that would cost the next person time.

Keep entries short, put them in the matching section, and remove anything that's no longer true. Don't add routine changes or anything git history already records.

## What this is

[VATS.IM](https://vats.im/) is a URL shortener for [VATSIM](https://vatsim.net/). It serves short URLs across several domains (e.g. `vats.im`, `vts.im`), supports organisation URL prefixes (`vats.im/<prefix>/<url>`), and has a management "platform" where users and organisations create and manage their URLs.

## Stack

- PHP 8.5, Laravel 13, PHPUnit 12
- Doctrine ORM via `laravel-doctrine/orm`, not Eloquent. Entities live in `app/Entities` and use PHP attribute mapping (`#[ORM\Entity]`), with Gedmo extensions such as soft deletes.
- MariaDB 10.6 in production. Tests run on SQLite, and in CI also on MariaDB.
- AWS SDK for PHP: DynamoDB, and SimpleDB through `aws/aws-sdk-php-v3-bridge`.
- The app keeps the pre-Laravel 11 structure: `App\Http\Kernel`, `App\Console\Kernel`, `App\Exceptions\Handler`, and service providers listed in `config/app.php`. Laravel 13 still supports this, so put middleware, providers and so on there rather than in `bootstrap/app.php`.

## Doctrine gotchas

- **Cache namespaces must be strings.** Production sets `DOCTRINE_CACHE=php_file`, and laravel-doctrine 3 passes each cache's `namespace` straight to Symfony's file cache adapters. A `null` namespace in `config/doctrine.php` crash-loops the php-fpm container on start-up. `tests/Unit/Config/DoctrineCacheTest.php` covers the `array`, `file` and `php_file` drivers. Local runs default to the `array` driver, so they won't show cache problems unless you set the driver.
- **No proxy classes.** On PHP 8.4+ Doctrine uses native lazy objects, so `get_class()` on a lazy-loaded entity returns the real entity class and `doctrine:generate:proxies` does nothing.
- **`findAll()` is reserved.** ORM 3 declares `EntityRepository::findAll(): array`. The app's sorted, paginated listing is `Repository::findAllOrdered($orderBy, $order, $perPage, $page)`.
- **Validation goes through Doctrine.** `exists:`/`unique:` rules name entity classes (e.g. `exists:App\Entities\Domain,id`) and are checked by laravel-doctrine's `PresenceVerifierProvider`. Don't re-add `Illuminate\Validation\ValidationServiceProvider` to `config/app.php`: its deferred registration overrides Doctrine's and makes those rules query SQL tables named after the classes.
- **Build `unique:`/`exists:` rules with conditions from input using `Rule::unique(Entity::class, 'column')->where(...)`**, not string concatenation, so request input can't add commas to the rule. `->where('column', null)` becomes an `IS NULL` check.
- **Don't rely on the database for case-insensitive comparisons.** MariaDB's `utf8mb4_unicode_ci` collation ignores case, but SQLite doesn't, so a `unique:` rule behaves differently in tests and in production. Compare with `LOWER()` in DQL.
- **Short URLs must be unique by full URL, ignoring case.** `App\Rules\AvailableShortUrl` checks the domain URL, prefix and URL together, so a prefixed URL clashes with one from any organization that has the same prefix. It also rejects `Url::RESERVED_PATHS`.
- **Organization prefixes must be unique, ignoring case,** because SimpleDB and DynamoDB key URLs by the lowercased full URL. There's no database index for this (it was dropped in 2019), so validate prefixes with `App\Rules\AvailablePrefix`. It also rejects `Url::RESERVED_PATHS`. Applicants aren't told a prefix is taken (`checkTaken: false`). Instead, the admin list and review pages flag the duplicate with a link to the other organization, and approval rejects it.
- Validate user-supplied links with `url:http,https`. Plain `url` accepts about 250 schemes, including `file:` and `data:`.
- Catch database failures with `Doctrine\DBAL\Exception` (an interface in DBAL 4); `DBALException` no longer exists.
- **Nullable relations need `?->`.** Entities use getters, and `??` doesn't guard a method call on null: `$url->getOrganization()->getName() ?? 'None'` throws when there's no organization. Write `$url->getOrganization()?->getName() ?? 'None'`. Eloquent-era code such as `$url->organization->name ?? 'None'` was safe, so look out for this where views or code were converted in the Doctrine migration.

## Front end

- Vue 2 (full build, with the template compiler), bundled by Laravel Mix from `resources/js/app.js`.
- **Never mount Vue on a wrapper around server-rendered content.** Vue compiles the HTML it mounts on as a template, so `{{ }}` in user-supplied text (an organization name, say) runs as JavaScript. Blade's escaping doesn't stop this. `resources/js/mount-components.js` mounts each registered component tag on its own instead, so register new components in the `components` map in `app.js`. Static attributes on a component tag are passed as plain strings and are safe, but don't put user-supplied text in a component's slot.

## Layout

- `routes/url.php`: the catch-all short URL redirect, `{prefix?}/{short_url?}` → `App\Http\Controllers\UrlController@redirect`.
- `routes/platform.php`: the site and management platform (`platform/...`), restricted to the app's own domains in `RouteServiceProvider`.
- `app/Http/Controllers/Platform`: platform controllers. `Platform/UrlController` handles URL create, update and delete.
- `app/Repositories`: Doctrine repositories, bound in `RepositoryServiceProvider`.
- `app/Services/UrlService.php`: URL lookup and caching for redirects.
- `app/Policies`: authorisation for URLs, domains and organisations.
- `app/Providers/AwsServiceProvider.php`: registers the `SimpleDbClient` and `DynamoDbClient` singletons.
- `database/migrations`: Laravel migrations (schema). `database/factories`: entity factories.
- `docker/`: production nginx and php-fpm config. `docker/test/` holds the local test runner.

## Running tests

PHP doesn't need to be installed locally. Run the suite in Docker:

```bash
docker/test/run-tests.sh
```

Any arguments are passed through to PHPUnit:

```bash
docker/test/run-tests.sh --filter dynamodb --testdox
docker/test/run-tests.sh tests/Feature/Platform/UrlTest.php
```

How the script works:

- It builds `docker/test/Dockerfile` (PHP 8.5 CLI on Alpine, matching CI) as the image `vatsim-url-shortener-test`. Docker's cache makes rebuilds fast.
- It streams the working tree into the container with `tar`, so uncommitted changes are tested. `.git`, `vendor/`, `node_modules/`, `.env` and `database/*.sqlite` are excluded, and nothing is written back to the host.
- Inside the container it copies `.env.ci` to `.env`, creates `database/database.tests.sqlite`, runs `composer install` and `php artisan key:generate`, then runs `vendor/bin/phpunit --configuration phpunit.xml`, which uses SQLite.
- `vendor/` and the Composer cache persist between runs in the named volumes `vatsim-url-shortener-vendor` and `vatsim-url-shortener-composer-cache`. If dependencies get into a bad state, remove the volumes:

  ```bash
  docker volume rm vatsim-url-shortener-vendor vatsim-url-shortener-composer-cache
  ```

JavaScript tests live in `tests/js` and use Node's built-in test runner with jsdom (`npm test`, Node 18+). CI runs them. To run them in Docker:

```bash
tar -cf - package.json package-lock.json resources/js tests/js | docker run --rm -i node:lts-alpine sh -c 'mkdir /app && cd /app && tar -xf - && npm ci --no-audit --no-fund -s && npm test'
```

Don't bind-mount `$TMPDIR` into Docker on macOS: it isn't shared with Docker Desktop, so the container silently sees an empty directory. Stream files in and out with `tar` instead.

`phpunit.mysql.xml` runs the same suite against MySQL/MariaDB using the `DB_*` settings in `.env.ci` (`127.0.0.1:3306`, database `laravel`, user `ci`/`ci`). The script doesn't start a database. The test image includes `pdo_mysql`, though, so you can run this suite locally by hand:

1. Start a MariaDB container on a Docker network, with the database and user from `.env.ci`.
2. Run the test image on that network, set up the same way as `run-tests.sh`.
3. Set `DB_HOST` to the database container's name and run `vendor/bin/phpunit --configuration phpunit.mysql.xml`.

MariaDB 11+ images don't include `mysqladmin`. Use `healthcheck.sh --connect --innodb_initialized` to wait for the database to be ready.

To check that a test fails without a fix, copy the fixed file aside, write the version from `main`, run the test, then copy the fixed file back:

```bash
cp path/to/file "$TMPDIR/file.fixed"
git show main:path/to/file > path/to/file
docker/test/run-tests.sh --filter the_test
cp "$TMPDIR/file.fixed" path/to/file
```

This works whether or not the fix is committed. Don't use `git stash` for this. The repo has many old stashes, and if `git stash push` finds nothing to save (for example, because the fix is already committed), `git stash pop` applies an unrelated old stash instead. Don't restore with `git checkout HEAD -- path/to/file` either, because that discards an uncommitted fix.

### Writing tests

- Tests live in `tests/Unit` and `tests/Feature`. Mark test methods with `#[\PHPUnit\Framework\Attributes\Test]`, since PHPUnit 12 no longer reads `@test` doc comments.
- Create entities with the global helpers `create(Class::class, [...])`, `make(...)` and `entity(Class::class)->states('org', 'prefix')->create(...)`. The factories are in `database/factories`.
- Factory attributes are entity property names (`'totpSecret' => ...`). Snake_case column names (`'totp_secret'`) are silently ignored.
- Use `Tests\Traits\RefreshDatabase` (the project's own trait, not Laravel's) for database tests, and `$this->signIn()` / `$this->signInAdmin()` to authenticate.
- `Tests\TestCase` calls `withoutExceptionHandling()` by default. Call `$this->withExceptionHandling()` when a test expects validation errors or HTTP error responses.
- `tests/CreatesApplication.php` replaces the AWS clients with permissive Mockery mocks, so tests never call AWS. To assert on AWS calls, bind a strict mock in the test. See `mockDynamoDb()` in `tests/Feature/Platform/UrlTest.php`.
- Use `$this->createStub()` for PHPUnit test doubles that only return values. `createMock()` without expectations triggers PHPUnit 12 notices.
- Use Laravel's `$this->assertThrows(fn () => ..., Exception::class, 'message')`. The closure comes first, and the message check is a case-sensitive substring match.
- To assert notifications, call `Notification::fake()` and then `Notification::assertSentTo()` or `assertSentOnDemand()`. The old `expectsNotification()` helper no longer exists.

## Short URL storage: SimpleDB → DynamoDB migration (in progress)

The redirect path is served by a separate AWS Lambda (repo `vatsim-url-shortener-short-url-redirector-lambda`, function `vatsim_url_shortener_short_url_redirector`), which reads a key-value copy of the URLs. This app keeps that copy up to date.

**Stage 1 (current):** URLs are written to both SimpleDB and DynamoDB.

- `Platform\UrlController` writes to SimpleDB and then DynamoDB on every create and update, and deletes from both on delete.
- Both stores use the same item shape:
  - Key: the lowercased full URL (`Str::lower($url->getFullUrl())`), stored as the SimpleDB item name and as the DynamoDB attribute `Url`.
  - Attributes: `RedirectUrl` and `UpdatedAt` (an ISO 8601 Zulu string).
  - URLs at a domain root are stored with a double slash, e.g. `https://vts.im//`. The Lambda relies on this.
- `php artisan simpledb:sync` rewrites every URL to SimpleDB, deletes stale items, then runs `php artisan dynamodb:sync` to do the same for DynamoDB. Use these to backfill or reconcile the stores.
- Config is in `config/services.php`:
  - `services.simpledb`, domain `VatsimUrlShortenerUrls`.
  - `services.dynamodb`, table set by `DYNAMODB_URLS_TABLE` (default `VatsimUrlShortenerUrls`), with optional `DYNAMODB_ENDPOINT`.
  - Both use the standard `AWS_*` credentials.

**Stage 2 (to do):** once the Lambda reads from DynamoDB in production, remove SimpleDB:

- From this repo: the SimpleDB code, `SyncSimpleDb`, `services.simpledb`, and the `aws/aws-sdk-php-v3-bridge` package. Point any scheduled sync at `dynamodb:sync`.
- From Terraform: the SimpleDB domain and policies.

When changing how URLs are keyed or stored, keep this app, both sync commands and the Lambda consistent.

## Infrastructure

Infrastructure is managed in the separate Terraform repo `TheRailroad/terraform`:

- `modules/vatsim-url-shortener` (prod) and `modules/vatsim-url-shortener-dev`: the DynamoDB tables, the SimpleDB domain, the service IAM user `vatsim_url_shortener` and the redirector Lambda.
- The service user's DynamoDB policy covers every table matching `VatsimUrlShortener*`, so new tables with that prefix need no IAM change.
- The Lambda role's permissions are granted per table.

## CI and deployment

- GitHub Actions (`.github/workflows/laravel.yml`) runs on push, on pull requests, weekly, and on manual dispatch.
- The `laravel-tests` job is a matrix over MariaDB 10.6, 10.11 and 11.4. Each leg runs PHPUnit on SQLite, then on that MariaDB version, so SQLite runs once per leg.
- If the tests pass, the `docker` job builds the `nginx` and `php-fpm` targets of the root `Dockerfile` for arm64, natively on `ubuntu-24.04-arm` runners (the tests run on them too). It pushes them to Docker Hub as `nfarrington/vatsim-url-shortener:<branch-or-version>-nginx` and `-php-fpm`. This runs for every branch push, not just `main`.
- Production and local development are arm64, so amd64 images are off by default. Set `BUILD_AMD64: "true"` at the top of the workflow to build them too. They run under QEMU, which is many times slower than native, so the `resources` (assets) and `app` (Composer and artisan) stages use `--platform=$BUILDPLATFORM`: they build once, natively, and the per-platform `php-fpm` stage copies their output. Only the `php-fpm` stage, mainly its PHP extension compile, runs emulated. Anything that differs by architecture, such as PHP extensions, must go in the `php-fpm` stage. The `app` stage lacks `ext-gd` and sets `COMPOSER_IGNORE_PLATFORM_REQ=ext-gd`.
- The layer cache is the GitHub Actions cache (`type=gha`) with `mode=max`, scoped per target. `mode=max` is needed to cache the intermediate stages; `mode=min` keeps only the target's own layers.
- GitHub disables the scheduled workflow after 60 days of repo inactivity. If pushes stop triggering CI, re-enable it in the Actions tab (or with `gh workflow enable`).
- The php-fpm container runs `composer docker-entrypoint` on start: it caches config and routes, clears the app and Doctrine caches, and runs `php artisan migrate-with-lock`. If start-up fails, the container exits and restarts in a loop.
- To check the production image before deploying, build the `php-fpm` target and run it with production-like settings: `APP_ENV=production`, `DOCTRINE_CACHE=php_file`, and a MariaDB 10.6 database. Then send requests through php-fpm with `cgi-fcgi`, which the image includes for its health check.
- Building the image directly from this Dropbox checkout fails, because files here are mode 0600 and the non-root `node` user in the assets stage can't read `package.json`. Build from a copy with normal permissions (`chmod -R go+rX`). CI checkouts aren't affected.
- The local Colima Docker has no `buildx` plugin. To do multi-platform builds without installing it, run BuildKit in a container (`docker run -d --privileged --name bk moby/buildkit`), `docker cp` the context in, and use `docker exec bk buildctl build --frontend dockerfile.v0 --opt platform=linux/amd64,linux/arm64 ...`. Bind mounts from outside your home directory don't reach the Colima VM. To run an image, export it with `--output type=docker,name=...,dest=...` and pipe it into `docker load`.

## Code style

- StyleCI checks pull requests using the `laravel` preset with risky fixers enabled.
- Its configuration lives in the StyleCI web settings, not in a `.styleci.yml` in the repo. StyleCI's `version` setting must be 8.1 or later.
- Match the existing style: 4-space indentation, Laravel conventions, and short-array syntax.
- StyleCI's configuration wants no space after `!` (`!empty($x)`, not `! empty($x)`), unlike the stock Laravel style.
