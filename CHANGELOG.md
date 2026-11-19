# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses
[Semantic Versioning](https://semver.org/).

## [2.0.0] - 2026-11-19

The package is renamed to `okarabudka/php-mvc-framework-core` and requires PHP 8.5.

### Changed
- Namespace `Akrbdk\PhpMvcCore` is now `Okarabudka\PhpMvcCore`.
- Sources live in `src/` with directories cased like their namespaces (`Db`, `Form`, `Exception`, `Middlewares`).
- All classes declare `strict_types` and native parameter, property and return types.
- `Request` accepts server, query and post arrays (default: the superglobals). `getBody()` returns the raw string
  values instead of `FILTER_SANITIZE_SPECIAL_CHARS`-encoded ones; escape on output.
- Form fields HTML-escape labels, values and errors.
- `Request::redirect()` moved to `Response::redirect()`; `Response` remembers its status code and headers.
- `Model::loadData()` only fills public properties declared by the concrete model, converts scalars to the declared
  type and skips values that cannot be converted.
- `Model::hasError()` returns `bool`, `getFirstError()` returns `''` when there is no error, `getLabel()` takes a
  string.
- `DbModel::findOne()` returns `null` instead of `false` when nothing matches. `save()` returns `bool` and fills the
  primary key property.
- `Session::get()` and `getFlash()` return `null` for missing keys.
- Migrations must implement `Db\Migration` (`up(): void`, `down(): void`). Only `*.php` files in `migrations/` are
  considered. The migrations table is also created on SQLite.
- `NotFoundException` and `ForbiddenException` extend the new `Exception\HttpException`.
- Validation messages for `email` and `max` are reworded ("must be" instead of "might be").

### Added
- `Form::textareaField()`, `Model::stringValue()`, `Model::attributeValue()`, `Database::driver()`.

### Fixed
- Autoloading failed on case-sensitive file systems such as Linux because directory names did not match namespaces.
- `findOne()` with more than one condition built invalid SQL (`AND` without a leading space).
- A session pointing at a deleted user caused a `TypeError` on every request; that user is now a guest.
- Error responses used the exception code as HTTP status: `0` for most exceptions and a string such as `'42S02'` for
  `PDOException`. Now 404/403 for `HttpException`, otherwise a logged 500.
- The `unique` rule called the instance method `tableName()` statically, which is an `Error` since PHP 8.
- `min`/`max` messages passed an `int` to `str_replace()`.
- Rendering the same view or layout twice in one request returned an empty string (`include_once`).
- `TextareaField` lost its value after a validation error.
- Migration log timestamps printed the month instead of the minutes.
- Migration names were concatenated into SQL instead of being bound as parameters.
- `Form::begin()` printed the action and method unescaped.

### Removed
- Root classes `Okarabudka\PhpMvcCore\Database` and `DbModel` (duplicates of the `Db\` classes) and the unused
  `Form\Field`.
- `View::renderContent()`.

### Upgrading from 1.x

1. Replace the requirement: `composer remove akrbdk/php-mvc-framework-core` and
   `composer require okarabudka/php-mvc-framework-core:^2.0`. Make sure the application runs on PHP 8.5.
2. Replace `Akrbdk\PhpMvcCore\` with `Okarabudka\PhpMvcCore\` in all `use` statements and docblocks.
3. If you used the root `Database` or `DbModel`, switch to `Db\Database` and `Db\DbModel`.
4. Add return types to overridden methods, for example `save(): bool`, `rules(): array`, `execute(): void`.
5. Replace `$request->redirect($url)` with `$response->redirect($url)` (actions receive the `Response` as second
   argument, or use `Application::$app->response`).
6. Let migrations implement `Okarabudka\PhpMvcCore\Db\Migration` with `up(): void` and `down(): void`.
7. Compare `findOne()` results with `null` instead of `false`, and `getFlash()`/`Session::get()` results with `null`.
8. Escape request data in your own templates with `htmlspecialchars()`; it is no longer pre-encoded. Users whose
   password contains `&`, `<`, `>`, `"` or `'` were hashed from the encoded value in 1.x and need to reset it.
9. Declare the primary key as a public property on user models (for example `public int $id = 0;`); columns without
   a matching property are no longer assigned as dynamic properties.

## [1.0.6] - 2023-05-18

Last release under the name `akrbdk/php-mvc-framework-core`.
