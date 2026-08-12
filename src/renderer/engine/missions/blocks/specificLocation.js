import { MissionResult } from '../core/MissionResult.js'

// constraint/location/specific
// Ref: Assets/missions/constraint/location/specific.web.xml (authoritative behaviour source)
//
// Param contract:
//   params[0]  spaceName  string  Case-insensitive regex matched against
//                                 worldInfo.currentSpaceId AND currentSpaceName.
//                                 Empty string matches any space.

export const specificLocationBlock = {
  type:     'constraint',
  name:     'Specific space',
  group:    'location',
  join:     'or',
  sort:     1,
  triggers: [],

  paramSchema: [
    { label: 'Space name (regex)', type: 'string', default: '', hint: 'Matched against space ID and name; empty = any space' },
  ],

  evaluate(_action, worldInfo, params, _imr) {
    if (!params || params.length === 0)
      return new MissionResult(false, 'parameters invalid')
    const expr = new RegExp(String(params[0]), 'i')
    if (String(worldInfo?.currentSpaceId   ?? '').match(expr)) return new MissionResult(true)
    if (String(worldInfo?.currentSpaceName ?? '').match(expr)) return new MissionResult(true)
    return new MissionResult(false, 'invalid space')
  },

  getDescription(params) {
    const name = String(params?.[0] ?? '')
    if (!name) return 'in any space'
    return `in space called "${name}"`
  },
}
