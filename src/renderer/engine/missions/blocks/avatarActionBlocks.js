import { MissionResult }             from '../core/MissionResult.js'
import { IntermediateMissionResult } from '../core/IntermediateMissionResult.js'

// Avatar-driven "Completed by" action blocks (speech / animation / emote).
// Maps the real SmallWorlds task-completion types to engine triggers per
// mission_engine_internals.md §6.
//
// All guard on the LOCAL avatar: action.info.avatar.$avatar_id must equal
// worldInfo.avatarInfo.id (same guard used by enter_space / use_on_target).

function isLocal(action, worldInfo) {
  return action?.info?.avatar?.$avatar_id === worldInfo?.avatarInfo?.id
}
function rx(v) { return new RegExp(String(v ?? ''), 'i') }

// ── #1 Talk ──────────────────────────────────────────────────────────────────
// SWAvatarSpeech with $whisper falsy. param[0] = spoken-text regex (empty = any).
export const talkBlock = {
  type: 'action', name: 'Talk', group: null, join: null, sort: 0,
  triggers: ['SWAvatarSpeech'],
  paramSchema: [
    { label: 'Text contains (regex)', type: 'string', default: '', hint: 'Empty = any speech' },
  ],
  evaluate(action, worldInfo, params) {
    if (!isLocal(action, worldInfo))          return new MissionResult(false, 'invalid avatar')
    if (action.info?.$whisper)                return new MissionResult(false, 'was a whisper')
    if (!String(action.info?.$text ?? '').match(rx(params?.[0]))) return new MissionResult(false, 'text mismatch')
    return new MissionResult(true)
  },
  getDescription(p) { const t = String(p?.[0] ?? ''); return t ? `saying "${t}"` : 'saying anything' },
}

// ── #11 Whisper ──────────────────────────────────────────────────────────────
export const whisperBlock = {
  type: 'action', name: 'Whisper', group: null, join: null, sort: 0,
  triggers: ['SWAvatarSpeech'],
  paramSchema: [
    { label: 'Text contains (regex)', type: 'string', default: '', hint: 'Empty = any whisper' },
  ],
  evaluate(action, worldInfo, params) {
    if (!isLocal(action, worldInfo))          return new MissionResult(false, 'invalid avatar')
    if (!action.info?.$whisper)               return new MissionResult(false, 'not a whisper')
    if (!String(action.info?.$text ?? '').match(rx(params?.[0]))) return new MissionResult(false, 'text mismatch')
    return new MissionResult(true)
  },
  getDescription(p) { const t = String(p?.[0] ?? ''); return t ? `whispering "${t}"` : 'whispering anything' },
}

// ── #12 Whisper to someone ───────────────────────────────────────────────────
// param[1] = recipient full-name regex, matched on $whisperRecipient.$avatar_full_name.
export const whisperToBlock = {
  type: 'action', name: 'Whisper to someone', group: null, join: null, sort: 0,
  triggers: ['SWAvatarSpeech'],
  paramSchema: [
    { label: 'Text contains (regex)', type: 'string', default: '', hint: 'Empty = any whisper' },
    { label: 'Recipient name (regex)', type: 'string', default: '', hint: 'Empty = anyone' },
  ],
  evaluate(action, worldInfo, params) {
    if (!isLocal(action, worldInfo))          return new MissionResult(false, 'invalid avatar')
    if (!action.info?.$whisper)               return new MissionResult(false, 'not a whisper')
    if (!String(action.info?.$text ?? '').match(rx(params?.[0]))) return new MissionResult(false, 'text mismatch')
    const recipient = action.info?.$whisperRecipient?.$avatar_full_name ?? ''
    if (!String(recipient).match(rx(params?.[1]))) return new MissionResult(false, 'recipient mismatch')
    return new MissionResult(true)
  },
  getDescription(p) {
    const t = String(p?.[0] ?? ''), r = String(p?.[1] ?? '') || 'someone'
    return t ? `whispering "${t}" to ${r}` : `whispering to ${r}`
  },
}

// ── #2 Perform an action ─────────────────────────────────────────────────────
// SWAvatarAnimate / SWAvatarEmote. param[0] = animation/emote name regex.
function animName(info) {
  return String(info?.animation?.$name ?? info?.emote?.$name ?? info?.emote ?? '')
}
export const performActionBlock = {
  type: 'action', name: 'Perform an action', group: null, join: null, sort: 0,
  triggers: ['SWAvatarAnimate', 'SWAvatarEmote'],
  paramSchema: [
    { label: 'Action name (regex)', type: 'string', default: '', hint: 'Empty = any animation/emote' },
  ],
  evaluate(action, worldInfo, params) {
    if (!isLocal(action, worldInfo)) return new MissionResult(false, 'invalid avatar')
    if (!animName(action.info).match(rx(params?.[0]))) return new MissionResult(false, 'action mismatch')
    return new MissionResult(true)
  },
  getDescription(p) { const n = String(p?.[0] ?? ''); return n ? `performing "${n}"` : 'performing any action' },
}

// ── #3 Perform an action with others ─────────────────────────────────────────
// Same as #2 plus a minimum count of OTHER avatars present in the space.
export const performActionWithOthersBlock = {
  type: 'action', name: 'Perform an action with others', group: null, join: null, sort: 0,
  triggers: ['SWAvatarAnimate', 'SWAvatarEmote'],
  paramSchema: [
    { label: 'Action name (regex)', type: 'string', default: '', hint: 'Empty = any animation/emote' },
    { label: 'Min. others present', type: 'number', default: '1', hint: 'Other avatars in the space' },
  ],
  evaluate(action, worldInfo, params) {
    if (!isLocal(action, worldInfo)) return new MissionResult(false, 'invalid avatar')
    if (!animName(action.info).match(rx(params?.[0]))) return new MissionResult(false, 'action mismatch')
    const min = Math.max(1, parseInt(params?.[1], 10) || 1)
    const others = (worldInfo?.currentSpaceAvatars ?? []).length
    if (others < min) return new MissionResult(false, 'not enough others')
    return new MissionResult(true)
  },
  getDescription(p) {
    const n = String(p?.[0] ?? '') || 'any action', m = parseInt(p?.[1], 10) || 1
    return `performing ${n} with ${m}+ other${m !== 1 ? 's' : ''} present`
  },
}
