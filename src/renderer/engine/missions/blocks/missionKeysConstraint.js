import { MissionResult }  from '../core/MissionResult.js'
import { evaluateKeyGate } from '../keys/evaluateKeyGate.js'

// constraint/state/missionkeys
// No authoritative XML found in repo — thin wrapper over evaluateKeyGate (§3).
//
// Param contract:
//   params[0]  hasKeys     string  Comma-separated key names; ALL must be present.
//                                  Empty string = no required keys (trivially passes).
//   params[1]  withoutKeys string  Comma-separated key names; NONE may be present.
//                                  Empty string = no forbidden keys (trivially passes).
//
// missionKeys is read from worldInfo.avatarInfo.missionKeys — a pre-filtered string array
// (already expiry-checked by the store's ids() call in WorldInfoBuilder).
// A thin store-like adapter wraps the array so evaluateKeyGate can call .has().

export const missionKeysConstraintBlock = {
  type:     'constraint',
  name:     'Mission keys',
  group:    'state',
  join:     null,
  sort:     0,
  triggers: [],

  paramSchema: [
    { label: 'Required keys', type: 'csv', default: '', hint: 'Comma-separated — ALL must be present' },
    { label: 'Forbidden keys', type: 'csv', default: '', hint: 'Comma-separated — NONE may be present' },
  ],

  evaluate(_action, worldInfo, params, _imr) {
    const missionKeys = worldInfo?.avatarInfo?.missionKeys ?? []
    const keySet      = new Set(missionKeys)
    // Adapter so evaluateKeyGate can call store.has() without knowing it's an array.
    const arrayStore  = { has: key => keySet.has(key) }
    const hasKeys     = String(params?.[0] ?? '')
    const withoutKeys = String(params?.[1] ?? '')
    const passes      = evaluateKeyGate(arrayStore, hasKeys, withoutKeys)
    if (passes) return new MissionResult(true)
    return new MissionResult(false, 'key gate failed')
  },

  getDescription(params) {
    const has     = String(params?.[0] ?? '').trim()
    const without = String(params?.[1] ?? '').trim()
    const parts   = []
    if (has)     parts.push(`has "${has}"`)
    if (without) parts.push(`without "${without}"`)
    return parts.length ? `key gate: ${parts.join(', ')}` : 'any key state'
  },
}
