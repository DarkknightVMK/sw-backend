import { MissionResult }             from '../core/MissionResult.js'
import { IntermediateMissionResult } from '../core/IntermediateMissionResult.js'
import { PARAMETER_MISSION_ID, PARAMETER_SUBTASK_ID } from './paramSentinels.js'

// Item / consumable "Completed by" action blocks.
// (action/item/mission_update covers "Interact with multiple items" — see missionUpdate.js.)

function isLocal(action, worldInfo) {
  return action?.info?.avatar?.$avatar_id === worldInfo?.avatarInfo?.id
}
function rx(v) { return new RegExp(String(v ?? ''), 'i') }

// ── #4 Interact with an item ─────────────────────────────────────────────────
// Completes on the FIRST SWUpdateMission addressed to this mission whose item
// model name matches. params: [hidden mission id, item regex, hidden subtask id].
export const interactItemBlock = {
  type: 'action', name: 'Interact with an item', group: null, join: null, sort: 0,
  triggers: ['SWUpdateMission', 'SWCompleteMission'],
  paramSchema: [
    { type: 'hidden', sentinel: PARAMETER_MISSION_ID },
    { label: 'Item model name (regex)', type: 'string', default: '', hint: 'Empty = any item' },
    { type: 'hidden', sentinel: PARAMETER_SUBTASK_ID },
  ],
  PARAMETER_MISSION_ID, PARAMETER_SUBTASK_ID,
  evaluate(action, _worldInfo, params) {
    const missionId = String(params?.[0] ?? '')
    if (String(action.info?.mission?.$mission_id ?? '') !== missionId)
      return new MissionResult(false, 'invalid mission')
    const subtaskId = params?.length > 2 && params[2] != null ? String(params[2]) : null
    if (subtaskId !== null && String(action.info?.mission?.$subtask_id ?? '') !== subtaskId)
      return new MissionResult(false, 'invalid subtask')
    if (action.action === 'SWCompleteMission') return new MissionResult(true)
    if (!String(action.info?.item?.$model_name ?? '').match(rx(params?.[1])))
      return new MissionResult(false, 'item mismatch')
    return new MissionResult(true)
  },
  getDescription(p) { const i = String(p?.[1] ?? ''); return i ? `interacting with "${i}"` : 'interacting with an item' },
}

// ── #6 Use an item ───────────────────────────────────────────────────────────
// SWAvatarUseConsumable, item model-name only (no target).
export const useItemBlock = {
  type: 'action', name: 'Use an item', group: null, join: null, sort: 0,
  triggers: ['SWAvatarUseConsumable'],
  paramSchema: [
    { label: 'Item model name (regex)', type: 'string', default: '', hint: 'Empty = any item' },
  ],
  evaluate(action, worldInfo, params) {
    if (!isLocal(action, worldInfo)) return new MissionResult(false, 'invalid avatar')
    if (!String(action.info?.item?.$model_name ?? '').match(rx(params?.[0])))
      return new MissionResult(false, 'item mismatch')
    return new MissionResult(true)
  },
  getDescription(p) { const i = String(p?.[0] ?? ''); return i ? `using "${i}"` : 'using any item' },
}

// ── #8 Use an item on self ───────────────────────────────────────────────────
export const useOnSelfBlock = {
  type: 'action', name: 'Use an item on self', group: null, join: null, sort: 0,
  triggers: ['SWAvatarUseConsumable'],
  paramSchema: [
    { label: 'Item model name (regex)', type: 'string', default: '', hint: 'Empty = any item' },
  ],
  evaluate(action, worldInfo, params) {
    if (!isLocal(action, worldInfo)) return new MissionResult(false, 'invalid avatar')
    if (!String(action.info?.item?.$model_name ?? '').match(rx(params?.[0])))
      return new MissionResult(false, 'item mismatch')
    if (String(action.info?.target?.$avatar_id ?? '') !== String(worldInfo?.avatarInfo?.id ?? ''))
      return new MissionResult(false, 'not used on self')
    return new MissionResult(true)
  },
  getDescription(p) { const i = String(p?.[0] ?? '') || 'an item'; return `using ${i} on yourself` },
}

// ── #10 Use an item with others ──────────────────────────────────────────────
export const useWithOthersBlock = {
  type: 'action', name: 'Use an item with others', group: null, join: null, sort: 0,
  triggers: ['SWAvatarUseConsumable'],
  paramSchema: [
    { label: 'Item model name (regex)', type: 'string', default: '', hint: 'Empty = any item' },
    { label: 'Min. others present', type: 'number', default: '1', hint: 'Other avatars in the space' },
  ],
  evaluate(action, worldInfo, params) {
    if (!isLocal(action, worldInfo)) return new MissionResult(false, 'invalid avatar')
    if (!String(action.info?.item?.$model_name ?? '').match(rx(params?.[0])))
      return new MissionResult(false, 'item mismatch')
    const min = Math.max(1, parseInt(params?.[1], 10) || 1)
    if ((worldInfo?.currentSpaceAvatars ?? []).length < min)
      return new MissionResult(false, 'not enough others')
    return new MissionResult(true)
  },
  getDescription(p) {
    const i = String(p?.[0] ?? '') || 'an item', m = parseInt(p?.[1], 10) || 1
    return `using ${i} with ${m}+ other${m !== 1 ? 's' : ''} present`
  },
}

// ── #9 Use an item multiple times ────────────────────────────────────────────
// Accumulates matching uses via IntermediateMissionResult (stageTotal = param[1]).
export const useMultiBlock = {
  type: 'action', name: 'Use an item multiple times', group: null, join: null, sort: 0,
  triggers: ['SWAvatarUseConsumable'],
  paramSchema: [
    { label: 'Item model name (regex)', type: 'string', default: '', hint: 'Empty = any item' },
    { label: 'Times to use', type: 'number', default: '3', hint: 'Total matching uses to complete' },
  ],
  evaluate(action, worldInfo, params, imr) {
    if (!isLocal(action, worldInfo)) return new MissionResult(false, 'invalid avatar')
    if (!String(action.info?.item?.$model_name ?? '').match(rx(params?.[0])))
      return new MissionResult(false, 'item mismatch')
    const total = Math.max(1, parseInt(params?.[1], 10) || 1)
    const prev  = imr instanceof IntermediateMissionResult ? (imr.intermediateStage || 0) : 0
    const count = prev + 1
    if (count >= total) return new MissionResult(true)
    return new IntermediateMissionResult(count, null, 0, total)
  },
  getDescription(p) {
    const i = String(p?.[0] ?? '') || 'an item', n = parseInt(p?.[1], 10) || 0
    return `using ${i} ${n} times`
  },
}
