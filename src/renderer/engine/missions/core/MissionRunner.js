import { IntermediateMissionResult }                  from './IntermediateMissionResult.js'
import { PARAMETER_MISSION_ID, PARAMETER_SUBTASK_ID } from '../blocks/paramSentinels.js'

// Headless runner — no game/trigger wiring, no UI, no DOM.
// Entry point: runner.dispatch(triggerName, actionInfo)
//
// Intermediate results are stored as:
//   activeIntermediateResultsMap[missionId]            — non-subtask missions
//   activeIntermediateResultsMap[missionId + subtaskId] — subtask missions
// This key scheme matches MM exactly (§5f / §5g of internals doc).

export class MissionRunner {
  constructor() {
    // missionId → missionState
    this._activeMissions = new Map()

    // triggerName → Set<missionId>
    this._activeTriggerMap = new Map()

    // missionId[+subtaskId] → IntermediateMissionResult
    this._activeIntermediateResultsMap = Object.create(null)

    // callbacks: (missionId, missionState) => void
    this._completionCallbacks = []
  }

  // ─── Public API ────────────────────────────────────────────────────────────

  // missionDef: {
  //   id:       string,
  //   chainId?: string,
  //   active?:  bool (default true),
  //   expired?: bool (default false),
  //   scripts:  ScriptEntry[]   ← output of CompositionParser.parseLwJson()
  // }
  addMission(missionDef) {
    const { id } = missionDef
    if (this._activeMissions.has(id)) return  // idempotent
    const state = this._buildState(missionDef)
    this._activeMissions.set(id, state)

    if (state.chainActive && !state.chainExpired) {
      for (const trigger of state.triggers) {
        if (!this._activeTriggerMap.has(trigger)) this._activeTriggerMap.set(trigger, new Set())
        this._activeTriggerMap.get(trigger).add(id)
      }
    }
  }

  removeMission(missionId) {
    const state = this._activeMissions.get(missionId)
    if (!state) return
    for (const trigger of state.triggers) {
      const set = this._activeTriggerMap.get(trigger)
      if (set) { set.delete(missionId); if (set.size === 0) this._activeTriggerMap.delete(trigger) }
    }
    this._activeMissions.delete(missionId)
    // clear all intermediate state for this mission
    for (const key of Object.keys(this._activeIntermediateResultsMap)) {
      if (key === missionId || key.startsWith(missionId)) {
        delete this._activeIntermediateResultsMap[key]
      }
    }
  }

  // The single dispatch entry point.
  // worldInfo is optional; TriggerBus passes it as the third arg.
  // The legacy _worldInfo-on-actionInfo path is preserved for backward compat.
  dispatch(triggerName, actionInfo = {}, worldInfo = null) {
    const action = { action: triggerName, info: actionInfo }
    const wi     = worldInfo ?? actionInfo._worldInfo ?? {}
    const missionIds = this._activeTriggerMap.get(triggerName)
    if (!missionIds || missionIds.size === 0) return
    for (const missionId of missionIds) {
      const state = this._activeMissions.get(missionId)
      if (!state || state.completed) continue
      this._evaluateMissionScripts(state, action, wi)
    }
  }

  // fn: (missionId: string, state: object) => void
  onComplete(fn) { this._completionCallbacks.push(fn) }

  // Retrieve current intermediate result (null if none / not yet started)
  getIntermediateResult(missionId, subtaskId = null) {
    const key = subtaskId != null ? missionId + subtaskId : missionId
    return this._activeIntermediateResultsMap[key] ?? null
  }

  // ─── State builder ─────────────────────────────────────────────────────────

  _buildState(def) {
    const scripts = def.scripts ?? []

    // First script with type 'action' becomes the action script.
    // Remaining scripts are classified by their type.
    const actionScript       = scripts.find(s => s.type === 'action') ?? null
    const constraintScripts  = scripts.filter(s => s.type === 'constraint')
    const completionScripts  = scripts.filter(s => s.type === 'completion')
    const hintScripts        = scripts.filter(s => s.type === 'hint')

    // Subtask scripts get a tracking object attached (subtask.id / subtask.complete)
    const subtaskScripts = scripts
      .filter(s => s.type === 'subtask')
      .map((s, i) => ({
        ...s,
        subtask: { id: s.subtaskId ?? String(i), complete: false },
      }))

    const hasSubtasks = subtaskScripts.length > 0

    // Collect triggers from action + subtask + hint scripts
    const triggers = new Set()
    if (!hasSubtasks && actionScript) {
      for (const t of actionScript.definition?.triggers ?? []) triggers.add(t)
    }
    for (const s of subtaskScripts) {
      for (const t of s.definition?.triggers ?? []) triggers.add(t)
    }
    for (const s of hintScripts) {
      for (const t of s.definition?.triggers ?? []) triggers.add(t)
    }

    return {
      id:               def.id,
      chainId:          def.chainId ?? def.id,
      chainActive:      def.active !== false,
      chainExpired:     def.expired === true,
      actionScript,
      constraintScripts,
      completionScripts,
      subtaskScripts,
      hintScripts,
      triggers:         [...triggers],
      hasSubtasks,
      completed:        false,
    }
  }

  // ─── Evaluation pipeline ───────────────────────────────────────────────────

  _evaluateMissionScripts(state, action, worldInfo) {
    if (state.hasSubtasks) {
      this._evaluateSubtaskScripts(state, action, worldInfo)
      return
    }
    // Non-subtask: single action script path
    if (!state.actionScript?.hasTrigger(action.action)) return

    const prev   = this._activeIntermediateResultsMap[state.id] ?? null
    const result = this._runScript(state.actionScript, state, action, worldInfo, prev)
    if (result === null) return

    if (result instanceof IntermediateMissionResult) {
      // All constraints must still pass before we bank the intermediate progress
      if (!this._allConstraintsPass(state, action, worldInfo)) return
      this._activeIntermediateResultsMap[state.id] = result
    } else if (result.completed) {
      if (!this._allConstraintsPass(state, action, worldInfo)) return
      this._completeMission(state)
    }
  }

  _evaluateSubtaskScripts(state, action, worldInfo) {
    let completedCount = state.subtaskScripts.filter(s => s.subtask.complete).length

    for (const script of state.subtaskScripts) {
      if (script.subtask.complete) continue
      if (!script.hasTrigger(action.action)) continue

      // Constraints are mission-level (applied to each subtask evaluation)
      if (!this._allConstraintsPass(state, action, worldInfo)) continue

      const key    = state.id + script.subtask.id
      const prev   = this._activeIntermediateResultsMap[key] ?? null
      const result = this._runScript(script, state, action, worldInfo, prev)
      if (result === null) continue

      if (result instanceof IntermediateMissionResult) {
        // Bank progress for this subtask; stop processing further subtasks this dispatch
        this._activeIntermediateResultsMap[key] = result
        return
      }

      if (result.completed) {
        script.subtask.complete = true
        completedCount++
        delete this._activeIntermediateResultsMap[key]
        // Continue the loop — later subtasks may also complete on this same trigger
      }
    }

    if (completedCount === state.subtaskScripts.length) {
      this._completeMission(state)
    }
  }

  // Returns true only if every constraint group passes.
  // Groups default to AND; group join='or' means any member of the group passes.
  _allConstraintsPass(state, action, worldInfo) {
    if (state.constraintScripts.length === 0) return true

    const groups = Object.create(null)
    for (const script of state.constraintScripts) {
      const g    = script.group ?? '__and__'
      const join = script.join  ?? 'and'
      if (!groups[g]) groups[g] = { join, results: [] }
      const r = this._runScript(script, state, action, worldInfo, null)
      if (r === null) return false     // runtime error → fail safe
      groups[g].results.push(r.completed)
    }

    for (const { join, results } of Object.values(groups)) {
      const pass = join === 'or' ? results.some(Boolean) : results.every(Boolean)
      if (!pass) return false
    }
    return true
  }

  _runScript(script, state, action, worldInfo, intermediateResult) {
    // Substitute runtime sentinels before handing params to the block.
    // PARAMETER_MISSION_ID → live mission ID; PARAMETER_SUBTASK_ID → live subtask ID (or null).
    const subtaskId = script.subtask?.id ?? null
    const params = (script.params ?? []).map(p => {
      if (p === PARAMETER_MISSION_ID) return state.id
      if (p === PARAMETER_SUBTASK_ID) return subtaskId
      return p
    })
    try {
      return script.evaluate(action, worldInfo, params, intermediateResult)
    } catch (e) {
      console.error(`Mission script error [${state.id}:${script.ref}]: ${e.message}`)
      return null
    }
  }

  _completeMission(state) {
    if (state.completed) return
    state.completed = true
    this._executeCompletionActions(state)
    for (const fn of this._completionCallbacks) fn(state.id, state)
  }

  _executeCompletionActions(state) {
    for (const script of state.completionScripts) {
      const params = (script.params ?? []).map(p => {
        if (p === PARAMETER_MISSION_ID) return state.id
        if (p === PARAMETER_SUBTASK_ID) return null
        return p
      })
      try {
        script.evaluate({ action: null, info: {} }, {}, params, null)
      } catch (e) {
        console.error(`Completion script error [${state.id}:${script.ref}]: ${e.message}`)
      }
    }
  }
}
