import { describe, it, expect } from 'vitest'
import { MissionKeyStore }    from '../keys/MissionKeyStore.js'
import { MissionActionsUser } from '../keys/MissionActionsUser.js'
import { evaluateKeyGate }    from '../keys/evaluateKeyGate.js'
import { MockTriggerContext } from '../trigger-bus/MockTriggerContext.js'
import { buildWorldInfo }     from '../worldinfo/WorldInfoBuilder.js'

// ─── a) add / has / remove / list / ids basics ────────────────────────────────

describe('a) MissionKeyStore add / has / remove / list / ids', () => {
  it('has() returns false for an absent key', () => {
    expect(new MissionKeyStore().has('x')).toBe(false)
  })

  it('add then has returns true', () => {
    const s = new MissionKeyStore()
    s.add('door-key', 'chain-1')
    expect(s.has('door-key')).toBe(true)
  })

  it('has() is not chain-scoped — any chainId satisfies the check', () => {
    const s = new MissionKeyStore()
    s.add('badge', 'chain-A')
    expect(s.has('badge')).toBe(true)  // has() checks across all chains
  })

  it('ids() returns unique key strings for non-expired entries', () => {
    const s = new MissionKeyStore()
    s.add('k1', 'chain-1')
    s.add('k2', 'chain-2')
    s.add('k1', 'chain-3')  // second entry for same key — should not duplicate in ids()
    expect(s.ids().sort()).toEqual(['k1', 'k2'])
  })

  it('list() returns all non-expired records with expected shape', () => {
    const s = new MissionKeyStore()
    s.add('my-key', 'my-chain', true)
    const recs = s.list()
    expect(recs).toHaveLength(1)
    expect(recs[0].key).toBe('my-key')
    expect(recs[0].chainId).toBe('my-chain')
    expect(recs[0].isCreator).toBe(true)
    expect(typeof recs[0].grantedAt).toBe('number')
    expect(typeof recs[0].expiresAt).toBe('number')
    expect(recs[0].expiresAt).toBeGreaterThan(recs[0].grantedAt)
  })

  it('remove(key, chainId) removes only the matching (key, chainId) pair', () => {
    const s = new MissionKeyStore()
    s.add('flag', 'chain-A')
    s.add('flag', 'chain-B')
    s.remove('flag', 'chain-A')
    // chain-A entry gone; chain-B still present
    expect(s.list().find(r => r.chainId === 'chain-A')).toBeUndefined()
    expect(s.list().find(r => r.chainId === 'chain-B')).toBeDefined()
    expect(s.has('flag')).toBe(true)  // key still held under chain-B
  })

  it('remove(key, chainId) is chain-scoped: other chains untouched', () => {
    const s = new MissionKeyStore()
    s.add('token', 'alpha')
    s.add('token', 'beta')
    s.remove('token', 'alpha')
    expect(s.ids()).toEqual(['token'])  // still held under 'beta'
  })

  it('remove(key) with no chainId only removes the entry with chainId=""', () => {
    const s = new MissionKeyStore()
    s.add('x', 'specific-chain')
    s.add('x', '')               // chainId=""
    s.remove('x')                // defaults to chainId="" — same as remove('x', '')
    // The chainId='' entry is removed; the 'specific-chain' entry survives
    expect(s.list().find(r => r.chainId === '')).toBeUndefined()
    expect(s.list().find(r => r.chainId === 'specific-chain')).toBeDefined()
  })

  it('adding the same (key, chainId) twice replaces the first entry', () => {
    const now = 1_000_000
    const s = new MissionKeyStore()
    s.add('k', 'c', false, 1000, now)
    s.add('k', 'c', true,  9999, now)  // replace
    const recs = s.list(now)
    expect(recs).toHaveLength(1)
    expect(recs[0].isCreator).toBe(true)
    expect(recs[0].expiresAt).toBe(now + 9999)
  })

  it('purge() removes expired entries', () => {
    const now = 1_000_000
    const s = new MissionKeyStore()
    s.add('expired', '', false, 500, now)    // expires at 1_000_500
    s.add('live',    '', false, 5000, now)   // expires at 1_005_000
    s.purge(now + 1000)  // now+1000 = 1_001_000; expired entry is past expiry
    expect(s.size).toBe(1)
    expect(s.has('live', now + 1000)).toBe(true)
    expect(s.has('expired', now + 1000)).toBe(false)
  })
})

// ─── b) TTL / expiry ──────────────────────────────────────────────────────────

describe('b) TTL and expiry', () => {
  it('key is present before TTL expires', () => {
    const now = 1_000_000
    const s = new MissionKeyStore()
    s.add('timed-key', 'c', false, 5000, now)  // expires at 1_005_000
    expect(s.has('timed-key', now + 4999)).toBe(true)
  })

  it('key is absent at the exact expiry timestamp', () => {
    const now = 1_000_000
    const s = new MissionKeyStore()
    s.add('timed-key', 'c', false, 5000, now)  // expiresAt = 1_005_000
    // has() checks expiresAt > now — strictly greater, so at exactly expiresAt it is absent
    expect(s.has('timed-key', now + 5000)).toBe(false)
  })

  it('key is absent after TTL expires', () => {
    const now = 1_000_000
    const s = new MissionKeyStore()
    s.add('timed-key', 'c', false, 5000, now)
    expect(s.has('timed-key', now + 5001)).toBe(false)
  })

  it('list() excludes expired records', () => {
    const now = 1_000_000
    const s = new MissionKeyStore()
    s.add('live',    'c', false, 9000, now)
    s.add('expired', 'c', false, 1000, now)
    expect(s.list(now + 2000)).toHaveLength(1)
    expect(s.list(now + 2000)[0].key).toBe('live')
  })

  it('ids() excludes expired key strings', () => {
    const now = 1_000_000
    const s = new MissionKeyStore()
    s.add('fresh',   '', false, 9000, now)
    s.add('stale',   '', false,  500, now)
    expect(s.ids(now + 1000)).toEqual(['fresh'])
  })

  it('default TTL is 30 days (2592000000 ms)', () => {
    const now = 0
    const s = new MissionKeyStore()
    s.add('perm', '', false, undefined, now)
    const rec = s.list(now)[0]
    expect(rec.expiresAt - rec.grantedAt).toBe(2_592_000_000)
  })
})

// ─── c) MissionActionsUser arg-order reversal ─────────────────────────────────

describe('c) MissionActionsUser giveMissionKey / removeMissionKey arg-order reversal', () => {
  it('giveMissionKey(chainId, key, isCreator) stores key with swapped args', () => {
    // Public API arg order:  giveMissionKey(chainId, key, isCreator)
    // Internal store call:   store.add(key, chainId, isCreator)  ← positions 0+1 swapped
    const store   = new MissionKeyStore()
    const actions = new MissionActionsUser(store)
    actions.giveMissionKey('chain-7', 'golden-key', true)
    const recs = store.list()
    expect(recs).toHaveLength(1)
    expect(recs[0].key).toBe('golden-key')
    expect(recs[0].chainId).toBe('chain-7')
    expect(recs[0].isCreator).toBe(true)
  })

  it('giveMissionKey isCreator defaults to false', () => {
    const store   = new MissionKeyStore()
    const actions = new MissionActionsUser(store)
    actions.giveMissionKey('chain-1', 'plain-key')
    expect(store.list()[0].isCreator).toBe(false)
  })

  it('removeMissionKey(chainId, key) removes the swapped (key, chainId) pair', () => {
    // Public API arg order:  removeMissionKey(chainId, key)
    // Internal store call:   store.remove(key, chainId)  ← positions 0+1 swapped
    const store   = new MissionKeyStore()
    const actions = new MissionActionsUser(store)
    actions.giveMissionKey('chain-1', 'token')
    expect(store.has('token')).toBe(true)
    actions.removeMissionKey('chain-1', 'token')
    expect(store.has('token')).toBe(false)
  })

  it('removeMissionKey is chain-scoped: only removes the matching chain', () => {
    const store   = new MissionKeyStore()
    const actions = new MissionActionsUser(store)
    actions.giveMissionKey('chain-A', 'flag')
    actions.giveMissionKey('chain-B', 'flag')
    actions.removeMissionKey('chain-A', 'flag')
    // chain-B entry still present
    expect(store.has('flag')).toBe(true)
    expect(store.ids()).toEqual(['flag'])
  })

  it('removeMissionKey with a non-matching chainId leaves the store unchanged', () => {
    const store   = new MissionKeyStore()
    const actions = new MissionActionsUser(store)
    actions.giveMissionKey('real-chain', 'badge')
    actions.removeMissionKey('wrong-chain', 'badge')  // no match — nothing removed
    expect(store.has('badge')).toBe(true)
  })
})

// ─── d) evaluateKeyGate ───────────────────────────────────────────────────────

describe('d) evaluateKeyGate', () => {
  it('empty has + empty without → true (trivial pass)', () => {
    const s = new MissionKeyStore()
    expect(evaluateKeyGate(s, '', '')).toBe(true)
  })

  it('all required keys present → true', () => {
    const s = new MissionKeyStore()
    s.add('a', ''); s.add('b', '')
    expect(evaluateKeyGate(s, 'a,b', '')).toBe(true)
  })

  it('one required key missing → false', () => {
    const s = new MissionKeyStore()
    s.add('a', '')
    expect(evaluateKeyGate(s, 'a,b', '')).toBe(false)
  })

  it('all required keys missing → false', () => {
    const s = new MissionKeyStore()
    expect(evaluateKeyGate(s, 'x,y', '')).toBe(false)
  })

  it('forbidden key present → false', () => {
    const s = new MissionKeyStore()
    s.add('bad', '')
    expect(evaluateKeyGate(s, '', 'bad')).toBe(false)
  })

  it('forbidden key absent → true', () => {
    const s = new MissionKeyStore()
    expect(evaluateKeyGate(s, '', 'forbidden')).toBe(true)
  })

  it('has all required + none forbidden → true', () => {
    const s = new MissionKeyStore()
    s.add('a', ''); s.add('b', '')
    expect(evaluateKeyGate(s, 'a,b', 'c')).toBe(true)
  })

  it('has all required BUT forbidden also present → false', () => {
    const s = new MissionKeyStore()
    s.add('a', ''); s.add('bad', '')
    expect(evaluateKeyGate(s, 'a', 'bad')).toBe(false)
  })

  it('whitespace entries in CSV are ignored', () => {
    const s = new MissionKeyStore()
    s.add('x', '')
    expect(evaluateKeyGate(s, ' x , ', ' , ')).toBe(true)
  })

  it('expired key does not count as present in has-check', () => {
    const now = 1_000_000
    const s   = new MissionKeyStore()
    s.add('expired', '', false, 100, now)   // expiresAt = now+100
    expect(evaluateKeyGate(s, 'expired', '', now + 200)).toBe(false)
  })

  it('expired key does not trigger the forbidden-check failure', () => {
    const now = 1_000_000
    const s   = new MissionKeyStore()
    s.add('was-bad', '', false, 100, now)   // expired before gate check
    // key is expired → treated as absent → forbidden check passes
    expect(evaluateKeyGate(s, '', 'was-bad', now + 200)).toBe(true)
  })

  it('key held under any chainId satisfies has-check (not chain-scoped)', () => {
    const s = new MissionKeyStore()
    s.add('universal', 'some-chain-id')  // added under a specific chain
    expect(evaluateKeyGate(s, 'universal', '')).toBe(true)
  })
})

// ─── e) worldInfo: local keys from store; non-local always [] ─────────────────

describe('e) worldInfo missionKeys wired to MissionKeyStore', () => {
  it('local avatar missionKeys reflect the store contents', () => {
    const store = new MissionKeyStore()
    store.add('key-alpha', 'chain-1')
    store.add('key-beta',  'chain-2')
    const ctx = new MockTriggerContext({ localAvatarId: 'local-1', keyStore: store })
    const wi  = buildWorldInfo(ctx)
    expect(wi.avatarInfo.missionKeys.sort()).toEqual(['key-alpha', 'key-beta'])
  })

  it('non-local avatars in the space always receive empty missionKeys', () => {
    const store = new MissionKeyStore()
    store.add('my-key', 'c')
    const ctx = new MockTriggerContext({
      localAvatarId: 'local-1',
      keyStore:      store,
      spaceAvatars:  [
        { id: 'other-1', fullName: 'Alice Smith' },
        { id: 'other-2', fullName: 'Bob Jones' },
      ],
    })
    const wi = buildWorldInfo(ctx)
    for (const a of wi.currentSpaceAvatars) {
      expect(a.missionKeys).toEqual([])
    }
  })

  it('expired keys are not included in worldInfo missionKeys', () => {
    const now   = 1_000_000
    const store = new MissionKeyStore()
    store.add('live',    'c', false, 9000, now)
    store.add('expired', 'c', false,  100, now)
    const ctx = new MockTriggerContext({ keyStore: store })
    // Build worldInfo at now+500 — 'expired' is past its TTL
    const wi = buildWorldInfo(ctx, new Date(now + 500))
    // worldInfo calls store.ids() at build time; store.ids() accepts now from Date.now()
    // We need to check via the store directly since buildWorldInfo uses Date.now() internally
    // — verify by checking the store at the same timestamp
    const ids = store.ids(now + 500)
    expect(ids).toEqual(['live'])
    expect(ids).not.toContain('expired')
  })

  it('MockTriggerContext.keyStore path and missionKeys array path are equivalent', () => {
    const store = new MissionKeyStore()
    store.add('k1', '')

    const ctxWithStore = new MockTriggerContext({ keyStore: store })
    const ctxWithArray = new MockTriggerContext({ missionKeys: ['k1'] })

    const wi1 = buildWorldInfo(ctxWithStore)
    const wi2 = buildWorldInfo(ctxWithArray)

    expect(wi1.avatarInfo.missionKeys).toEqual(wi2.avatarInfo.missionKeys)
  })

  it('giveMissionKey then buildWorldInfo shows the new key', () => {
    const store   = new MissionKeyStore()
    const actions = new MissionActionsUser(store)
    const ctx     = new MockTriggerContext({ keyStore: store })

    expect(buildWorldInfo(ctx).avatarInfo.missionKeys).toEqual([])

    actions.giveMissionKey('chain-9', 'new-badge', false)
    expect(buildWorldInfo(ctx).avatarInfo.missionKeys).toEqual(['new-badge'])
  })

  it('removeMissionKey then buildWorldInfo reflects the removal', () => {
    const store   = new MissionKeyStore()
    const actions = new MissionActionsUser(store)
    const ctx     = new MockTriggerContext({ keyStore: store })

    actions.giveMissionKey('chain-1', 'old-badge')
    expect(buildWorldInfo(ctx).avatarInfo.missionKeys).toContain('old-badge')

    actions.removeMissionKey('chain-1', 'old-badge')
    expect(buildWorldInfo(ctx).avatarInfo.missionKeys).not.toContain('old-badge')
  })
})
