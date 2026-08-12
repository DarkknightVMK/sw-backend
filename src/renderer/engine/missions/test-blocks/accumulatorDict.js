import { MissionResult } from '../core/MissionResult.js'
import { IntermediateMissionResult } from '../core/IntermediateMissionResult.js'

// Dictionary-mode accumulator.
//   params[0]: target total (default 3)
//   action.info.key: the key to increment in the per-key dictionary
//
// Each dispatch increments intermediateData[key] by 1.
// Total = sum of all dictionary values (floor of each).
// Returns MissionResult(true) when total reaches target.
export const accumulatorDictBlock = {
  type: 'action',
  name: 'Test: Accumulator (dictionary mode)',
  group: null,
  join: null,
  sort: 0,
  triggers: ['TEST_TRIGGER'],
  evaluate(action, _worldInfo, params, intermediateResult) {
    const target  = Number(params?.[0]) || 3
    const key     = String(action.info?.key ?? 'default')
    const prevData = intermediateResult instanceof IntermediateMissionResult
      ? { ...(intermediateResult.intermediateData ?? {}) }
      : {}
    prevData[key] = (prevData[key] ?? 0) + 1
    const total = Object.values(prevData).reduce((sum, v) => sum + Math.floor(v), 0)
    if (total >= target) return new MissionResult(true)
    return new IntermediateMissionResult(total, prevData, 0, target)
  },
}
