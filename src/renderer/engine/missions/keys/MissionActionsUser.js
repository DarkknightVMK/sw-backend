// LW equivalent of MM's InternalMissionActionsUser key methods (module 56983).
//
// isCreator semantics (sourced from bundle §3, addMissionKey):
//   - Stored as the `visible` field in MM's key record: {id, visible, missionId, expire}
//   - Does NOT affect TTL — TTL is always the same arg (default 30 days), regardless of isCreator
//   - IS passed to the server: missionService.addMissionKey(..., isCreator, ttlSecs)
//   - No client-side logic in the bundle branches on visible/isCreator after storage
//   - The field name "visible" suggests it controls whether the key shows as a badge/indicator
//     in the game UI (creator = prominently displayed or confers creator status in a chain).
//     The server may enforce additional semantics we cannot observe from the JS client alone.
//
// removeMissionKey scope (sourced from bundle §3, avatar.removeMissionKey):
//   CHAIN-SCOPED. The avatar loops through entries for the key and removes only the one where
//   id==key && missionId==chainId. Multiple entries for the same key name with different
//   chainIds coexist and are removed independently. Calling removeMissionKey(chainId, key)
//   without a chainId would default chainId to "" and only remove the entry with chainId="".

export class MissionActionsUser {
  constructor(store) {
    this._store = store
  }

  // Give a mission key to the avatar.
  //
  // IMPORTANT ARG-ORDER REVERSAL (matches MM bundle exactly):
  //   Public API:   giveMissionKey(chainId, key, isCreator)
  //   Store call:   store.add(key, chainId, isCreator)         ← chainId and key swapped
  // Source: static giveMissionKey(e/*chainId*/,t/*key*/){a.addMissionKey(t,i,e)}
  //         i.e. avatar.addMissionKey(key, isCreator, chainId)
  giveMissionKey(chainId, key, isCreator = false, ttlMs, now) {
    this._store.add(key, chainId, isCreator, ttlMs, now)
  }

  // Remove a mission key from the avatar.
  //
  // IMPORTANT ARG-ORDER REVERSAL (matches MM bundle exactly):
  //   Public API:   removeMissionKey(chainId, key)
  //   Store call:   store.remove(key, chainId)                 ← chainId and key swapped
  // Source: static removeMissionKey(e/*chainId*/,t/*key*/){a.removeMissionKey(t,e)}
  //
  // CHAIN-SCOPED: removes only the (key, chainId) pair. If chainId is '', only the entry
  // with chainId='' is removed. Keys added under other chainIds are unaffected.
  removeMissionKey(chainId, key) {
    this._store.remove(key, chainId)
  }
}
