/*
 * ── VMK ── dev stand-in backend for LittleWorlds ──────────────────────────────
 *
 * A zero-dependency Node server that impersonates just enough of the Laravel API
 * (normally on :8000) to let the Electron app log in and render — WITHOUT PHP or
 * Composer on the machine. It is a throwaway dev stub created ONLY for this task;
 * it holds no real data and persists nothing.
 *
 *   Run:   node vmk/server.mjs            (listens on :8000, override with VMK_PORT)
 *   Reach: the renderer's /api/* calls arrive here via the Vite dev proxy
 *          (npm run dev strips /api → /auth/login etc). Direct :8000 calls with
 *          the /api prefix are handled too. CORS is wide open for local dev.
 *
 * Only the auth handshake needs an exact shape; everything else can be an empty
 * JSON object because the renderer guards responses with Array.isArray()/??.
 */

import http from 'node:http';

const PORT = Number(process.env.VMK_PORT) || 8000;
const ASSET_BASE = 'http://127.0.0.1:9001'; // the running asset server (port 9001)

// A single hard-coded dev identity. This is a stub — any email/password "works".
const DEV = {
  id: 1,
  email: 'dev@local',
  firstName: 'Dev',
  lastName: 'Tester',
  primaryGroupId: 1,
  secondaryGroupIds: [],
  goldBalance: 5000000,
  tokenBalance: 1000000,
  citizenLevel: 1,
};

// swsid -> { email } so /user/me can echo whoever "logged in".
const sessions = new Map();

let counter = 1000;
const newId = (p) => `${p}-${Date.now().toString(36)}-${counter++}`;

function sessionObject(email) {
  const SWSID = newId('vmk-sess');
  sessions.set(SWSID, { email });
  return {
    SWSID,
    user_id: DEV.id,
    ip_address: '127.0.0.1',
    expires_at: '2099-01-01 00:00:00',
  };
}

function authPayload(email) {
  const session = sessionObject(email);
  return {
    success: true,
    api: newId('vmk-tok'),
    SWSID: session,               // client reads data.SWSID.SWSID — must be an object
    permissions: { admin: true, moderator: true, staff: true },
    user: { ...DEV, email },
    _vmk: true,
  };
}

function userMe(email) {
  return {
    ...DEV,
    email,
    fullName: `${DEV.firstName} ${DEV.lastName}`,
    // consumed by the game store (all optional):
    webServiceUrl: `http://localhost:${PORT}`,
    contentUrl: ASSET_BASE,
    configUrl: ASSET_BASE,
    avatarImagesPath: `${ASSET_BASE}/avatars`,
    wwwRoot: ASSET_BASE,
    webassetsPath: ASSET_BASE,
    config: {},
    defaultAvatar: {
      id: DEV.id,
      avatar_id: DEV.id,
      firstName: DEV.firstName,
      lastName: DEV.lastName,
      fullName: `${DEV.firstName} ${DEV.lastName}`,
      gender: 'male',
      nameInstance: 1,
      friends: [],
    },
    friends: [],
    _vmk: true,
  };
}

// A real head PNG that exists on the asset server, for the login-screen preview.
const HEAD_URL = `${ASSET_BASE}/Items/base/consumables/head/con_head_32_phantom.png`;

function send(res, status, body) {
  const json = JSON.stringify(body);
  res.writeHead(status, {
    'Content-Type': 'application/json',
    'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Headers': 'Content-Type, Authorization, swsid, Accept',
    'Access-Control-Allow-Methods': 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
  });
  res.end(json);
}

function readBody(req) {
  return new Promise((resolve) => {
    let raw = '';
    req.on('data', (c) => { raw += c; if (raw.length > 1e6) req.destroy(); });
    req.on('end', () => {
      try { resolve(raw ? JSON.parse(raw) : {}); } catch { resolve({}); }
    });
    req.on('error', () => resolve({}));
  });
}

const server = http.createServer(async (req, res) => {
  const method = req.method || 'GET';
  if (method === 'OPTIONS') return send(res, 204, {});

  // Normalize: drop query string and an optional leading /api (the dev proxy
  // strips it, but direct calls keep it — handle both).
  let path = decodeURIComponent((req.url || '/').split('?')[0]);
  path = path.replace(/\/+$/, '') || '/';
  const route = path.replace(/^\/api(?=\/|$)/, '') || '/';

  const body = method === 'POST' || method === 'PUT' || method === 'PATCH'
    ? await readBody(req) : {};
  const swsid = req.headers['swsid'];
  const email =
    (swsid && sessions.get(String(swsid))?.email) || body.email || DEV.email;

  const line = `${method} ${path}`;

  // ── Auth handshake (the only shape-critical endpoints) ──────────────────────
  if (method === 'POST' && route === '/auth/login') {
    console.log(`[vmk] ${line}  → login as ${body.email || DEV.email}`);
    return send(res, 200, authPayload(body.email || DEV.email));
  }
  if (method === 'POST' && route === '/auth/token') {
    console.log(`[vmk] ${line}  → token login`);
    return send(res, 200, authPayload(email));
  }
  if (method === 'POST' && route === '/register') {
    console.log(`[vmk] ${line}  → register ${body?.user?.email || body.email}`);
    return send(res, 200, { success: true, ...authPayload(body?.user?.email || body.email || DEV.email) });
  }
  if (method === 'POST' && route === '/auth/logout') {
    if (swsid) sessions.delete(String(swsid));
    return send(res, 200, { success: true });
  }

  // ── Data the app reads right after login ────────────────────────────────────
  if (method === 'GET' && route === '/user/me') {
    console.log(`[vmk] ${line}  → user/me (${email})`);
    return send(res, 200, userMe(email));
  }
  if (method === 'GET' && route.startsWith('/avatar/head')) {
    return send(res, 200, { url: HEAD_URL });
  }

  // ── Everything else: valid-but-empty JSON so nothing 404s or throws ─────────
  console.log(`[vmk] ${line}  → {} (stub)`);
  return send(res, 200, {});
});

server.listen(PORT, () => {
  console.log('──────────────────────────────────────────────');
  console.log(`  VMK dev backend listening on http://localhost:${PORT}`);
  console.log(`  (stub for LittleWorlds — task-only, no real data)`);
  console.log('  Any email/password logs in. Ctrl-C to stop.');
  console.log('──────────────────────────────────────────────');
});
