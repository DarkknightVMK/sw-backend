import { MissionResult } from '../core/MissionResult.js'
import { IntermediateMissionResult } from '../core/IntermediateMissionResult.js'

// Simple count accumulator.
//   params[0]: target total (default 3)
//
// Each dispatch increments intermediateStage by 1.
// Returns MissionResult(true) when stage reaches target.
// Returns IntermediateMissionResult(stage, null, 0, target) otherwise.
export const accumulatorBlock = {
  type: 'action',
  name: 'Test: Accumulator',
  group: null,
  join: null,
  sort: 0,
  triggers: ['TEST_TRIGGER'],
  evaluate(_action, _worldInfo, params, intermediateResult) {
    const target = Number(params?.[0]) || 3
    const prev   = intermediateResult instanceof IntermediateMissionResult
      ? intermediateResult.intermediateStage
      : 0
    const stage  = prev + 1
    if (stage >= target) return new MissionResult(true)
    return new IntermediateMissionResult(stage, null, 0, target)
  },
}
