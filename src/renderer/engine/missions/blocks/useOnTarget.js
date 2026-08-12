import { MissionResult } from '../core/MissionResult.js'

// action/consumable/use_on_target
// No authoritative XML found in repo — implemented against spec §2 and internals §6.
//
// Param contract:
//   params[0]  itemNameRegex    string  Case-insensitive regex matched against
//                                       action.info.item.$model_name.
//   params[1]  targetNameRegex  string  Case-insensitive regex matched against
//                                       action.info.target.$avatar_full_name.
//                                       Empty string matches any target (or no-target).

export const useOnTargetBlock = {
  type:     'action',
  name:     'Using an item on a target',
  group:    null,
  join:     null,
  sort:     0,
  triggers: ['SWAvatarUseConsumable'],

  paramSchema: [
    { label: 'Item model name (regex)', type: 'string', default: '', hint: 'Matched against consumable\'s model name' },
    { label: 'Target avatar name (regex)', type: 'string', default: '', hint: 'Empty = any target' },
  ],

  evaluate(action, worldInfo, params, _imr) {
    // Guard: only the local avatar can satisfy this action.
    if (action.info?.avatar?.$avatar_id !== worldInfo?.avatarInfo?.id)
      return new MissionResult(false, 'invalid avatar')
    if (!params || params.length < 2)
      return new MissionResult(false, 'parameters invalid')

    const itemExpr   = new RegExp(String(params[0]), 'i')
    const targetExpr = new RegExp(String(params[1]), 'i')

    if (!String(action.info?.item?.$model_name ?? '').match(itemExpr))
      return new MissionResult(false, 'invalid item')
    if (!String(action.info?.target?.$avatar_full_name ?? '').match(targetExpr))
      return new MissionResult(false, 'invalid target')

    return new MissionResult(true)
  },

  getDescription(params) {
    const item   = String(params?.[0] ?? '') || 'any item'
    const target = String(params?.[1] ?? '') || 'any target'
    return `using "${item}" on "${target}"`
  },
}
