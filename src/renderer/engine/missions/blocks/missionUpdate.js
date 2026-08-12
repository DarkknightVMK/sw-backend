import { MissionResult }              from '../core/MissionResult.js'
import { IntermediateMissionResult }  from '../core/IntermediateMissionResult.js'
import { PARAMETER_MISSION_ID, PARAMETER_SUBTASK_ID } from './paramSentinels.js'

// action/item/mission_update
// Ref: Assets/Items/base/other/mission_update.xml (Flash/AS3 — authoritative logic source)
//
// Param contract (sentinels are substituted by MissionRunner before evaluate() is called):
//   params[0]  missionId   PARAMETER_MISSION_ID sentinel  → live mission ID at runtime.
//   params[1]  total       number (as string)             → target count to complete.
//   params[2]  subtaskId   PARAMETER_SUBTASK_ID sentinel  → live subtask ID, or null
//                          (optional — omit to skip subtask-id filtering).
//
// Behaviour:
//   On SWUpdateMission: accumulate action.info.item.$item_id → int(action.info.update.$value)
//     in the per-item dictionary (overwrites previous value for the same item_id).
//     Sum all values; clamp to total. Return IntermediateMissionResult until sum >= total.
//   On SWCompleteMission: immediately return MissionResult(true) (short-circuit).
//   missionId mismatch → MissionResult(false). subtaskId mismatch → MissionResult(false).

export const missionUpdateBlock = {
  type:     'action',
  name:     'Interacting with many items to update a task',
  group:    null,
  join:     null,
  sort:     0,
  triggers: ['SWUpdateMission', 'SWCompleteMission'],

  paramSchema: [
    { type: 'hidden', sentinel: '__lw_mission_id__' },
    { label: 'Target count', type: 'number', default: '3', hint: 'Total item interactions needed to complete' },
    { type: 'hidden', sentinel: '__lw_subtask_id__' },
  ],

  // Export sentinels so editors / tests can reference them without re-importing.
  PARAMETER_MISSION_ID,
  PARAMETER_SUBTASK_ID,

  evaluate(action, _worldInfo, params, intermediateResult) {
    if (!params || params.length < 2)
      return new MissionResult(false, 'parameters invalid')

    const missionId = String(params[0])
    const total     = Math.max(1, parseInt(params[1], 10) || 1)
    const subtaskId = params.length > 2 && params[2] != null ? String(params[2]) : null

    // Mission ID must match what the update was addressed to.
    if (String(action.info?.mission?.$mission_id ?? '') !== missionId)
      return new MissionResult(false, 'invalid mission')

    // Subtask ID check — only when params[2] was resolved to a real subtask ID.
    if (subtaskId !== null) {
      const infoSubtaskId = String(action.info?.mission?.$subtask_id ?? '')
      if (infoSubtaskId !== subtaskId)
        return new MissionResult(false, 'invalid subtask')
    }

    // SWCompleteMission immediately completes regardless of current count.
    if (action.action === 'SWCompleteMission')
      return new MissionResult(true)

    // SWUpdateMission: store value by item_id (overwrites, not increments — per ref XML).
    const dict   = intermediateResult instanceof IntermediateMissionResult
      ? { ...(intermediateResult.intermediateData ?? {}) }
      : {}
    const itemId = String(action.info?.item?.$item_id ?? '')
    const value  = parseInt(String(action.info?.update?.$value ?? '0'), 10) || 0
    dict[itemId] = value

    let count = 0
    for (const v of Object.values(dict)) count += Math.floor(Number(v))
    if (count > total) count = total
    if (count >= total) return new MissionResult(true)

    return new IntermediateMissionResult(count, dict, 0, total)
  },

  getDescription(params) {
    const total = parseInt(params?.[1], 10) || 0
    return `interacting with items that trigger "Task Update" reactions to the sum of ${total}`
  },
}
