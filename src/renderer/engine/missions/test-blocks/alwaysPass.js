import { MissionResult } from '../core/MissionResult.js'

export const alwaysPassBlock = {
  type: 'action',
  name: 'Test: Always Pass',
  group: null,
  join: null,
  sort: 0,
  triggers: ['TEST_TRIGGER'],
  evaluate(_action, _worldInfo, _params, _intermediate) {
    return new MissionResult(true)
  },
}
