import { enterSpaceBlock }            from './enterSpace.js'
import { specificLocationBlock }      from './specificLocation.js'
import { useOnTargetBlock }           from './useOnTarget.js'
import { missionUpdateBlock }         from './missionUpdate.js'
import { createGiveMissionKeyBlock }  from './giveMissionKey.js'
import { missionKeysConstraintBlock } from './missionKeysConstraint.js'
import {
  talkBlock, whisperBlock, whisperToBlock,
  performActionBlock, performActionWithOthersBlock,
} from './avatarActionBlocks.js'
import {
  interactItemBlock, useItemBlock, useOnSelfBlock, useMultiBlock, useWithOthersBlock,
} from './itemActionBlocks.js'
import { customActionBlock } from './customAction.js'
import {
  homeConstraintBlock, peopleCountConstraintBlock, peopleSpecificConstraintBlock,
  timeOfDayConstraintBlock, dayOfWeekConstraintBlock, dateConstraintBlock,
} from './conditionBlocks.js'

// Single entry point for registering all core mission/interaction blocks.
//
// Registration order == dropdown order in the Task editor (the editor lists
// blocks by type in registry insertion order). The 14 "Completed by" action
// types and 8 "Extra Conditions" constraint types below mirror the real
// SmallWorlds authoring menus exactly.
//
// actionsUser (MissionActionsUser instance, optional):
//   Required to register the give-mission-key completion block.
export function registerCoreBlocks(registry, actionsUser = null) {
  // ── "Completed by" — 14 action types (screenshot order) ───────────────────
  registry.register('action/avatar/talk',                    talkBlock)                       // 1 Talk
  registry.register('action/avatar/animate',                 performActionBlock)              // 2 Perform an action
  registry.register('action/avatar/animate_with_others',     performActionWithOthersBlock)    // 3 Perform an action with others
  registry.register('action/item/interact',                  interactItemBlock)               // 4 Interact with an item
  registry.register('action/item/mission_update',            { ...missionUpdateBlock, name: 'Interact with multiple items' }) // 5
  registry.register('action/consumable/use',                 useItemBlock)                    // 6 Use an item
  registry.register('action/consumable/use_on_target',       { ...useOnTargetBlock, name: 'Use an item on someone' })         // 7
  registry.register('action/consumable/use_on_self',         useOnSelfBlock)                  // 8 Use an item on self
  registry.register('action/consumable/use_multi',           useMultiBlock)                   // 9 Use an item multiple times
  registry.register('action/consumable/use_with_others',     useWithOthersBlock)              // 10 Use an item with others
  registry.register('action/avatar/whisper',                 whisperBlock)                    // 11 Whisper
  registry.register('action/avatar/whisper_to',              whisperToBlock)                  // 12 Whisper to someone
  registry.register('action/movement/enter_space',           enterSpaceBlock)                 // 13 Entering a space
  registry.register('action/custom',                         customActionBlock)               // 14 Custom

  // ── "Extra Conditions" — 8 constraint types (screenshot order) ────────────
  registry.register('constraint/location/home',              homeConstraintBlock)             // 1 Home
  registry.register('constraint/location/specific',          specificLocationBlock)           // 2 Specific space
  registry.register('constraint/space/people_count',         peopleCountConstraintBlock)      // 3 Number of people present
  registry.register('constraint/space/people_specific',      peopleSpecificConstraintBlock)   // 4 Specific people present
  registry.register('constraint/time/time_of_day',           timeOfDayConstraintBlock)        // 5 Specific time
  registry.register('constraint/time/day_of_week',           dayOfWeekConstraintBlock)        // 6 Specific day of week
  registry.register('constraint/time/date',                  dateConstraintBlock)             // 7 Specific date
  registry.register('constraint/state/missionkeys',          missionKeysConstraintBlock)      // 8 Mission keys

  // ── "Task Completion" — effect blocks ─────────────────────────────────────
  if (actionsUser) {
    registry.register('completion/missionkey/give', createGiveMissionKeyBlock(actionsUser))
  }
}
