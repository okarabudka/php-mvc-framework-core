# PHP MVC Framework Core

[![CI](https://github.com/okarabudka/php-mvc-framework-core/actions/workflows/ci.yml/badge.svg)](https://github.com/okarabudka/php-mvc-framework-core/actions/workflows/ci.yml)

A small MVC framework core written from scratch: router, request and response, models with validation, views with
layouts, form helpers, migrations, sessions and middleware. It is a Composer library with no runtime dependencies
besides PDO. [php-mvc-framework](https://github.com/okarabudka/php-mvc-framework) is a complete application built
on it.

## Why it exists

Frameworks such as Laravel and Symfony hide a lot of machinery behind a few lines of configuration. This project
builds that machinery by hand to show how it fits together: how a front controller turns a URL into a controller
call, how a model validates input and writes itself to the database, how a template ends up inside a layout, and
where middleware and sessions hook in.

It started in 2023 as part of the demo application and was later extracted into this library. Version 2 brings it
to PHP 8.5 with strict types, PHPStan at the strictest level and a test suite, and fixes the bugs the first version
had, among them autoloading that only worked on case-insensitive file systems.

## What you get

**A complete request cycle in a few hundred lines.** `Application` wires the services together, `Router` maps
`GET`/`POST` paths to views, controller actions or closures, and errors become a rendered error page with the right
status code: 404 and 403 for the framework's HTTP exceptions, a logged 500 for everything else.

**Models with validation.** Rules `required`, `email`, `min`, `max`, `match` and `unique`, labels and per-field error
messages. `loadData()` fills only the model's declared public properties and converts values to their types, so
request data cannot cause a `TypeError`.

**Active-record persistence.** `DbModel::save()` and `findOne()` on PDO with bound parameters, and a migration
runner that records applied migrations. MySQL and SQLite are supported.

**Views and forms that escape output.** Templates are plain PHP, rendered inside a layout chosen per controller.
Form fields print Bootstrap markup with the value and the first error, HTML-escaped.

**Sessions, flash messages and middleware.** Flash messages live for exactly one following request.
`AuthMiddleware` limits controller actions to logged-in users.

**Quality checks.** PHPUnit tests for every component on in-memory SQLite (95 % line coverage), PHPStan at level
max including the tests, PHP CS Fixer (PER-CS) and a secret scan run in GitHub Actions on PHP 8.5.

## How to use it

```bash
composer require okarabudka/php-mvc-framework-core
```

A minimal application needs a front controller, a layout and a view:

```php
<?php // public/index.php

declare(strict_types=1);

use App\Models\User;
use Okarabudka\PhpMvcCore\Application;

require __DIR__ . '/../vendor/autoload.php';

$app = new Application(dirname(__DIR__), [
    'userClass' => User::class, // extends Okarabudka\PhpMvcCore\UserModel
    'db' => ['dsn' => 'sqlite:' . dirname(__DIR__) . '/app.sqlite'],
]);

$app->router->get('/', 'home');                                    // views/home.php
$app->router->get('/hello', fn () => 'Hello');                     // a closure
$app->router->post('/contacts', [ContactController::class, 'store']); // a controller action

$app->run();
```

```php
<!-- views/layouts/main.php -->
<html><head><title><?= htmlspecialchars($this->title) ?></title></head>
<body>{{content}}</body></html>
```

```php
<!-- views/home.php -->
<?php $this->title = 'Home'; ?>
<h1>Welcome</h1>
```

Errors are rendered with `views/_error.php`, which receives the `$exception`.

Controllers extend `Controller`, return the rendered view and can register middleware:

```php
final class ProfileController extends Controller
{
    public function __construct()
    {
        $this->registerMiddleware(new AuthMiddleware(['show']));
    }

    public function show(Request $request, Response $response): string
    {
        return $this->render('profile', ['user' => Application::$app->user]);
    }
}
```

Migrations are classes `Migrations\<file name>` in `migrations/` that implement `Db\Migration`. Apply them with
`$app->db->applyMigrations()`, for example from a small `migrations.php` script.

### Upgrading from 1.x

Version 2 is published under a new name and namespace. See [CHANGELOG.md](CHANGELOG.md#upgrading-from-1x) for the
steps.

### Development

You need Docker and make; PHP runs in a container.

| Target          | What it does                                      |
|-----------------|---------------------------------------------------|
| `make install`  | Build the PHP 8.5 image and install dependencies  |
| `make test`     | Run the PHPUnit tests                             |
| `make coverage` | Run the tests with a coverage report              |
| `make stan`     | Run PHPStan (level max, sources and tests)        |
| `make cs`       | Check code style                                  |
| `make fix`      | Fix code style                                    |
| `make check`    | Code style, PHPStan and tests                     |
| `make help`     | List all targets                                  |

## Architecture

```
Application ── Request, Response, Session, View, Router, Database, current user
     │
     └─ run() ─▶ Router::resolve()
                    ├─ 'view'                 ─▶ View::renderView()
                    ├─ [Controller, 'action'] ─▶ middlewares ─▶ action(Request, Response)
                    └─ callable               ─▶ callable(Request, Response)
                 exception ─▶ status code + views/_error.php
```

| Path              | Contents                                                                      |
|-------------------|-------------------------------------------------------------------------------|
| `src/`            | `Application`, `Router`, `Request`, `Response`, `Controller`, `Model`, `UserModel`, `View`, `Session` |
| `src/Db/`         | `Database` (PDO and migration runner), `DbModel` (active record), `Migration` |
| `src/Form/`       | `Form`, `InputField`, `TextareaField`                                         |
| `src/Middlewares/`| `BaseMiddleware`, `AuthMiddleware`                                            |
| `src/Exception/`  | `HttpException`, `NotFoundException`, `ForbiddenException`                    |
| `tests/`          | PHPUnit tests with fixture views and migrations                              |

Services are reachable through the static `Application::$app`, as in Yii 2. It keeps the code short and is the main
trade-off of the design: tests create a fresh `Application` for each case.

## Versioning

Semantic versioning. Released tags are never moved or rewritten. 1.x was published as `akrbdk/php-mvc-framework-core`
with the namespace `Akrbdk\PhpMvcCore`; those versions stay installable but receive no changes.

## Status

Maintained.

## License

MIT, see [LICENSE](LICENSE).
