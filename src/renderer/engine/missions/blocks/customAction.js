import { MissionResult } from '../core/MissionResult.js'
import { TRIGGERS }      from '../trigger-bus/TRIGGERS.js'

// ── #14 Custom ───────────────────────────────────────────────────────────────
// Passthrough action: completes when the named trigger fires. Subscribes to
// every known SW* trigger so any of them can drive it; params pick which one
// (and an optional substring that must appear anywhere in the serialized info).
//
//   params[0]  triggerName  e.g. "SWAvatarEmote"  (must match action.action)
//   params[1]  contains     optional — substring that must appear in JSON(info)
export const customActionBlock = {
  type: 'action', name: 'Custom', group: null, join: null, sort: 0,
  triggers: Object.keys(TRIGGERS),
  paramSchema: [
    { label: 'Trigger name', type: 'string', default: 'SWAvatarEmote', hint: 'e.g. SWAvatarEmote, SWPurchaseItem' },
    { label: 'Info contains (optional)', type: 'string', default: '', hint: 'Substring that must appear in the event' },
  ],
  evaluate(action, _worldInfo, params) {
    const want = String(params?.[0] ?? '')
    if (!want || action.action !== want) return new MissionResult(false, 'trigger mismatch')
    const needle = String(params?.[1] ?? '')
    if (needle) {
      let hay = ''
      try { hay = JSON.stringify(action.info ?? {}) } catch { hay = '' }
      if (!hay.toLowerCase().includes(needle.toLowerCase()))
        return new MissionResult(false, 'info mismatch')
    }
    return new MissionResult(true)
  },
  getDescription(p) {
    const t = String(p?.[0] ?? '') || 'a custom event'
    const n = String(p?.[1] ?? '')
    return n ? `on ${t} containing "${n}"` : `on ${t}`
  },
}
