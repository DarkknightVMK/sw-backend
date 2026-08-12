// Per-avatar mission key store.
//
// Key record shape (mirrors MM bundle §3, UserAvatar.addMissionKey):
//   { key, chainId, grantedAt, expiresAt, isCreator }
//
// MM stores it as:  { id: key, visible: isCreator, missionId: chainId, expire: expiresAt }
// We use clearer field names; the mapping is 1-to-1.
//
// Lookup map: `${key}::${chainId}` → record.
// Multiple entries with the same key but different chainIds coexist — consistent with MM's
// per-key Array<record> where each element has a distinct missionId (chainId).
//
// Expiry: lazy — checked on every read; no sweep timer required. Call purge() to compact.

const DEFAULT_TTL_MS = 2_592_000_000  // 30 days (MM default: 2592e6 ms)

export class MissionKeyStore {
  constructor() {
    this._entries = new Map()  // `${key}::${chainId}` → record
  }

  // Add a key.
  // If (key, chainId) already exists, it is replaced (MM removes-then-adds on hasMissionKey).
  // ttlMs: default 30 days. now: injectable for tests (defaults to Date.now()).
  add(key, chainId = '', isCreator = false, ttlMs = DEFAULT_TTL_MS, now = Date.now()) {
    const mapKey    = `${key}::${chainId}`
    const grantedAt = now
    const expiresAt = grantedAt + ttlMs
    this._entries.set(mapKey, { key, chainId, grantedAt, expiresAt, isCreator })
  }

  // Remove a specific (key, chainId) pair.
  // CHAIN-SCOPED: matches MM exactly. Per bundle §3, removeMissionKey(key, chainId) only
  // removes the entry where id==key && missionId==chainId. Omitting chainId defaults to ''.
  // To remove a key added with a specific chainId you MUST pass that chainId.
  remove(key, chainId = '') {
    this._entries.delete(`${key}::${chainId}`)
  }

  // Returns true if the key exists with ANY chainId and has not expired.
  // Used by evaluateKeyGate — mirrors avatar.missionKeyIds() which returns all non-expired keys.
  has(key, now = Date.now()) {
    for (const rec of this._entries.values()) {
      if (rec.key === key && rec.expiresAt > now) return true
    }
    return false
  }

  // All non-expired records.
  list(now = Date.now()) {
    return [...this._entries.values()].filter(r => r.expiresAt > now)
  }

  // Unique non-expired key strings — matches avatar.missionKeyIds() shape.
  // Used by TriggerBusInterface.getLocalAvatarMissionKeys().
  ids(now = Date.now()) {
    const seen = new Set()
    const out  = []
    for (const rec of this._entries.values()) {
      if (rec.expiresAt > now && !seen.has(rec.key)) {
        seen.add(rec.key)
        out.push(rec.key)
      }
    }
    return out
  }

  // Remove all expired entries. Lazy expiry is fine for normal use; call this to compact.
  purge(now = Date.now()) {
    for (const [mapKey, rec] of this._entries) {
      if (rec.expiresAt <= now) this._entries.delete(mapKey)
    }
    return this
  }

  get size() { return this._entries.size }
}
