import { SERVER_ONLY, AVATAR_USES }   from './TRIGGERS.js'
import { ACTION_INFO_BUILDERS }       from './actionInfoBuilders.js'
import { buildWorldInfo }             from '../worldinfo/WorldInfoBuilder.js'

// Bridge between raw game events and MissionRunner.dispatch().
//
// Wiring diagram:
//   game event → TriggerBus.emit(triggerName, rawEvent)
//              → actionInfoBuilders[triggerName](rawEvent)  → action.info
//              → buildWorldInfo(context)                    → worldInfo
//              → runner.dispatch(triggerName, actionInfo, worldInfo)
//
// Special cases (see TRIGGERS.js for full catalog):
//   • SWOwnItem / SWOwnBundle — server-authoritative; emit() silently drops
//     them. Use serverEmit() for the authorised ingress path.
//   • SWTimeElapsed — fired by startTimeElapsedTimer() every 10 s; not a
//     raw game event, so no rawEvent → info builder is called from emit().
//   • SWAvatarUseConsumable — also mirrors to AVATAR_USES item-side trigger
//     with value = $model_name (internals doc §8, dispatchAvatarUseConsumableTrigger).

export class TriggerBus {
  constructor(runner, context) {
    this._runner      = runner
    this._context     = context
    this._timerHandle = null
  }

  // Client-side trigger ingress. Silently drops server-only trigger names.
  emit(triggerName, rawEvent = {}) {
    if (SERVER_ONLY.has(triggerName)) return

    const info      = this._buildInfo(triggerName, rawEvent)
    const worldInfo = buildWorldInfo(this._context)
    this._runner.dispatch(triggerName, info, worldInfo)

    // Mirror: every SWAvatarUseConsumable also fires item-side AVATAR_USES.
    // Source: internals §8 — dispatchAvatarUseConsumableTrigger fires
    // dispatchAvatarTrigger(e, AVATAR_USES, e.item.$model_name).
    if (triggerName === 'SWAvatarUseConsumable') {
      this._runner.dispatch(AVATAR_USES, { value: info.item?.$model_name ?? '' }, worldInfo)
    }
  }

  // Server-authoritative ingress: the only legal path for SWOwnItem / SWOwnBundle.
  // All other trigger names are silently dropped.
  serverEmit(triggerName, rawEvent = {}) {
    if (!SERVER_ONLY.has(triggerName)) return
    const info      = this._buildInfo(triggerName, rawEvent)
    const worldInfo = buildWorldInfo(this._context)
    this._runner.dispatch(triggerName, info, worldInfo)
  }

  // Starts the 10-second SWTimeElapsed timer loop.
  // Source: internals §5d — timeElapsedNotificationTimerHandler.
  // Uses the global setInterval so vi.useFakeTimers() works in tests.
  startTimeElapsedTimer() {
    if (this._timerHandle !== null) return
    this._timerHandle = setInterval(() => {
      const now = Date.now()
      this._runner.dispatch('SWTimeElapsed', { $startTime: now, $endTime: now }, buildWorldInfo(this._context))
    }, 10_000)
  }

  stopTimeElapsedTimer() {
    if (this._timerHandle === null) return
    clearInterval(this._timerHandle)
    this._timerHandle = null
  }

  // Expose worldInfo builder for callers who need a snapshot directly.
  buildWorldInfo(date) { return buildWorldInfo(this._context, date) }

  _buildInfo(triggerName, rawEvent) {
    const builder = ACTION_INFO_BUILDERS[triggerName]
    return builder ? builder(rawEvent) : {}
  }
}
