import { MissionResult } from '../core/MissionResult.js'

export const alwaysFailBlock = {
  type: 'action',
  name: 'Test: Always Fail',
  group: null,
  join: null,
  sort: 0,
  triggers: ['TEST_TRIGGER'],
  evaluate(_action, _worldInfo, _params, _intermediate) {
    return new MissionResult(false, 'always fails')
  },
}
