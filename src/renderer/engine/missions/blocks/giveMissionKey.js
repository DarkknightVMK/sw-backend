import { MissionResult } from '../core/MissionResult.js'

// completion/missionkey/give
// No authoritative XML found in repo — implemented against spec §2/§9, internals §3.
//
// Param contract:
//   params[0]  key        string   The mission key string to grant.
//   params[1]  chainId    string   The chain that owns the key (default '').
//   params[2]  isCreator  'true'|''  Whether the avatar is a creator of this key (default false).
//
// Calls MissionActionsUser.giveMissionKey(chainId, key, isCreator) — note arg-order reversal
// (matches MM §3 exactly; the reversal is in MissionActionsUser, not here).
//
// Completion blocks are always called with action=null (runner §5e); the block must not
// branch on action fields. They always return MissionResult(true).
//
// Factory: takes an actionsUser instance so the block can call the give method.
// registerCoreBlocks() supplies the actionsUser during registration.

export function createGiveMissionKeyBlock(actionsUser) {
  return {
    type:     'completion',
    name:     'Give mission key',
    group:    null,
    join:     null,
    sort:     0,
    triggers: [],

    paramSchema: [
      { label: 'Key name', type: 'string', default: '', hint: 'The mission key string to grant' },
      { label: 'Chain ID', type: 'string', default: '', hint: 'Leave empty for default chain' },
      { label: 'Is creator', type: 'string', default: '', hint: 'Enter "true" if this grants creator status' },
    ],

    evaluate(_action, _worldInfo, params, _imr) {
      if (!params || params.length === 0)
        return new MissionResult(false, 'parameters invalid')
      const key       = String(params[0])
      const chainId   = params.length > 1 ? String(params[1]) : ''
      const isCreator = params.length > 2 && String(params[2]) === 'true'
      actionsUser.giveMissionKey(chainId, key, isCreator)
      return new MissionResult(true)
    },

    getDescription(params) {
      const key     = String(params?.[0] ?? '') || '(key)'
      const chainId = String(params?.[1] ?? '') || ''
      return chainId
        ? `giving mission key "${key}" for chain "${chainId}"`
        : `giving mission key "${key}"`
    },
  }
}
