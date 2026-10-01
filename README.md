# DebtLens

A small PHP technical debt analyzer with a React + TypeScript frontend built with [Vite](https://vite.dev/guide/).

## Run locally

You need PHP 8.5, Composer, Node.js 22.12+ (or 20.19+), and pnpm 11+. Use two terminals from this folder.

### Backend

```sh
cd Backend
composer install
php -S 127.0.0.1:8000 -t public public/index.php
```

### Frontend

```sh
cd frontend
pnpm install
pnpm dev
```

Open **http://127.0.0.1:5173**. The PHP example is ready to analyze. Replace it with your own source, including the `<?php` opening tag, and select **Analyze code**.

The UI shows severity totals and finding cards with rule details, location, measured value, and threshold. Missing input is caught before sending a request. API errors, invalid PHP, and network failures are shown inline. Editing the source clears results so they always match the analyzed code.

## API connection

The browser sends `POST /api/analyze` with `Content-Type: application/json` and `{"code":"<?php ..."}`. Vite forwards that request to **http://127.0.0.1:8000/api/analyze**, so local development needs no CORS changes. Both the dev server and build preview use this proxy. Requests time out after 30 seconds.

For deployment, configure your web server to forward `/api` to the PHP backend; the development proxy is not included in the static build. See [backend documentation](Backend/README.md) for response and error details.

## Build and test

```sh
cd frontend
pnpm build
pnpm preview
```

The build checks TypeScript and writes static files to `frontend/dist`. Preview is available at **http://127.0.0.1:4173** while the backend is running.

```sh
cd Backend
php vendor/bin/pest
```

The backend test suite includes real HTTP API tests. The frontend uses React state, native fetch, and plain CSS, with no UI or state management libraries.
