# sw-backend — dummy server

A **throwaway, zero-dependency Node stand-in** for the real Laravel `sw-backend`
API. It exists so the LittleWorlds Electron client can log in and render locally
**without installing PHP/Composer or running the real backend**.

> This branch (`dummy-server`) intentionally contains **only** the dummy server —
> none of the Laravel application. It is not a replacement for the real backend;
> it fakes just enough responses for client-side testing.

## Run

```bash
npm start          # node server.mjs  → listens on :8000
# or, auto-reload on edit:
npm run dev        # node --watch server.mjs
```

Override the port with `VMK_PORT` (default `8000`).

## How the client reaches it

The renderer's `/api/*` calls reach this server via the Vite dev proxy
(`npm run dev` in the client rewrites `/api` → `:8000`). CORS is wide open, so
direct calls work too.

## What it fakes

- **Auth handshake** (the only shape-critical part): `POST /auth/login`,
  `/auth/token`, `/register` return `{ success, api, SWSID: { SWSID, … },
  permissions, user }`. **Any email/password logs in.**
- `GET /user/me` — echoes the session's identity plus avatar/balances and the
  game content URLs (pointed at the `:9001` asset server).
- `GET /avatar/head[/…]` — returns a real image URL on the asset server.
- **Everything else** — returns a valid but empty `{}`, which is enough for the
  client's `Array.isArray()`/`??`-guarded polls (friends, gifts, profile) not to
  error. Features backed by real server data will simply be empty.

Holds no real data and persists nothing.
