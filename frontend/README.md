# DebtLens frontend

React + TypeScript with Vite and plain CSS.

Requires Node.js 22.12+ (or 20.19+) and pnpm 11+.

```sh
pnpm install
pnpm dev
```

Open **http://127.0.0.1:5173**. Start the PHP backend in a separate terminal:

```sh
cd ../Backend
composer install
php -S 127.0.0.1:8000 -t public public/index.php
```

Vite proxies the browser's `POST /api/analyze` to `http://127.0.0.1:8000/api/analyze`. No backend CORS changes are needed. The default example produces high and medium findings.

```sh
pnpm build
pnpm preview
```

`build` runs TypeScript checks and creates `dist/`. `preview` opens the build at **http://127.0.0.1:4173**, also with the API proxy. Keep the backend running. Deployed static files need a web-server proxy for `/api`.

Runtime dependencies: React and React DOM. Development dependencies: Vite, TypeScript, and React type definitions. The lockfile records exact versions. The pnpm configuration allows esbuild's required installation script and stores the dependency cache locally.
