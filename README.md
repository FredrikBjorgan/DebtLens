# DebtLens

A small PHP 8.5 static analyzer with a JSON API. Submitted code is parsed, never executed.

## Run locally

```sh
composer install
php -S 127.0.0.1:8000 -t public public/index.php
```

Run these commands from this directory. The front controller routes requests; the public document root keeps source and vendor files outside the web root.

## Analyze code

```sh
curl -X POST http://127.0.0.1:8000/api/analyze \
  -H 'Content-Type: application/json' \
  -d '{"code":"<?php function example($a, $b, $c, $d, $e, $f) {}"}'
```

The request must be a JSON object with a non-empty string `code` containing PHP source, including its `<?php` opening tag.

Successful responses have HTTP status 200 and contain `summary` (`total`, `high`, `medium`, `low`) and a `findings` array. Each finding includes `rule`, `severity`, `message`, `className`, `methodName`, `line`, `actualValue`, and `threshold`. Metadata is preserved from the existing rules: class findings include `className`; method/function findings currently have a null `className`.

All four rules run: too many parameters (>5), cyclomatic complexity (>10), long methods/functions (>40 lines), and large classes (>300 lines). Line counts include the full declaration span, comments, and blank lines.

Errors are JSON objects in the form `{"error":{"message":"..."}}`:

- 400: malformed JSON, missing code, empty code, or code that is not a string.
- 422: invalid PHP syntax.
- 404: unknown endpoint.
- 405: unsupported method, with an `Allow: POST` header.
- 500: unexpected internal error; details are written to the server log.

## Tests

```sh
php vendor/bin/pest
```

API tests start a temporary local PHP server on an available port, send real HTTP requests, and stop the server afterward. They use PHP's standard process and stream functions, with no additional dependencies.
