import { MissionResult } from '../core/MissionResult.js'

// action/movement/enter_space
// Ref: Assets/missions/action/movement/enter_space.web.xml (authoritative behaviour source)
//
// Param contract:
//   params[0]  spaceName  string  Case-insensitive regex matched against
//                                 worldInfo.currentSpaceName AND currentSpaceId.
//                                 Empty string matches any space.

export const enterSpaceBlock = {
  type:     'action',
  name:     'Entering a space',
  group:    null,
  join:     null,
  sort:     0,
  triggers: ['SWAvatarEnterSpace'],

  paramSchema: [
    { label: 'Space name (regex)', type: 'string', default: '', hint: 'Empty = any space; matched against space name and ID' },
  ],

  evaluate(action, worldInfo, params, _imr) {
    if (action.info?.avatar?.$avatar_id !== worldInfo?.avatarInfo?.id)
      return new MissionResult(false, 'invalid avatar')
    if (!params || params.length !== 1)
      return new MissionResult(false, 'parameters invalid')
    const expr = new RegExp(String(params[0]), 'i')
    if (String(worldInfo.currentSpaceName ?? '').match(expr)) return new MissionResult(true)
    if (String(worldInfo.currentSpaceId   ?? '').match(expr)) return new MissionResult(true)
    return new MissionResult(false, 'invalid space')
  },

  getDescription(params) {
    const name = String(params?.[0] ?? '')
    if (!name) return 'entering any space'
    return `entering a space named "${name}"`
  },
}
