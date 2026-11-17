# PHP MVC Framework Core

A minimal, self-written PHP MVC framework core distributed as a Composer package.

## Context

Learning project built in May 2023. The framework code was first written inside the [php-mvc-framework](https://github.com/okarabudka/php-mvc-framework) application and then extracted into this reusable library, which is published on Packagist as `akrbdk/php-mvc-framework-core`.

Not tested in production. Use it at your own risk.

## Features

- `Application` container with a static instance, request lifecycle and event listeners (`beforeRequest`, `afterRequest`)
- `Router` with GET and POST routes and controller/action callbacks
- `Request` and `Response` wrappers
- `Controller` base class with layouts and middleware registration
- `AuthMiddleware` that restricts actions to logged-in users
- `Model` with rule-based validation: `required`, `email`, `min`, `max`, `match`, `unique`
- `DbModel` / `UserModel` with `save()` and `findOne()` on top of PDO
- `Database` with a simple migration runner that tracks applied migrations
- `Session` with flash messages
- `View` that renders templates inside layouts
- Form helpers (`Form`, `InputField`, `TextareaField`) that output Bootstrap-style markup with validation errors
- `NotFoundException` and `ForbiddenException`

## Tech stack

- PHP (no runtime dependencies in `composer.json`; the demo application targets PHP 8.1)
- PDO
- Composer, PSR-4 autoloading

## Architecture

- Classic MVC with a front controller: the application creates `Application`, registers routes and calls `run()`.
- Global access to services through `Application::$app` (router, request, response, session, view, db, current user).
- Active-record style models: a model defines `tableName()`, `attributes()` and `primaryKey()`, `DbModel` builds the SQL.
- Sub-namespaces: `db/` (database and models), `form/` (form rendering), `middlewares/`, `exception/`.
- Namespace: `Akrbdk\PhpMvcCore`.

## Getting started

```bash
composer require akrbdk/php-mvc-framework-core
```

See [php-mvc-framework](https://github.com/okarabudka/php-mvc-framework) for a complete application that uses this package.

## Testing

There are no automated tests.

## Status

Finished learning project (2023), not maintained.

## License

MIT, see [LICENSE](LICENSE).
