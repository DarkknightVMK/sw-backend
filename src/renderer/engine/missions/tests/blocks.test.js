import { describe, it, expect, beforeEach } from 'vitest'
import { MissionResult }               from '../core/MissionResult.js'
import { IntermediateMissionResult }   from '../core/IntermediateMissionResult.js'
import { BlockRegistry }               from '../core/BlockRegistry.js'
import { CompositionParser }           from '../core/CompositionParser.js'
import { MissionRunner }               from '../core/MissionRunner.js'
import { MissionKeyStore }             from '../keys/MissionKeyStore.js'
import { MissionActionsUser }          from '../keys/MissionActionsUser.js'
import { MockTriggerContext }          from '../trigger-bus/MockTriggerContext.js'
import { TriggerBus }                  from '../trigger-bus/TriggerBus.js'
import { PARAMETER_MISSION_ID, PARAMETER_SUBTASK_ID } from '../blocks/paramSentinels.js'
import { enterSpaceBlock }             from '../blocks/enterSpace.js'
import { specificLocationBlock }       from '../blocks/specificLocation.js'
import { useOnTargetBlock }            from '../blocks/useOnTarget.js'
import { missionUpdateBlock }          from '../blocks/missionUpdate.js'
import { createGiveMissionKeyBlock }   from '../blocks/giveMissionKey.js'
import { missionKeysConstraintBlock }  from '../blocks/missionKeysConstraint.js'
import { registerCoreBlocks }          from '../blocks/registerCoreBlocks.js'

// ─── shared env factories ─────────────────────────────────────────────────────

function makeEnv({ keyStore = null } = {}) {
  const store     = keyStore ?? new MissionKeyStore()
  const actions   = new MissionActionsUser(store)
  const registry  = new BlockRegistry()
  const parser    = new CompositionParser(registry)
  const runner    = new MissionRunner()
  const completed = []
  runner.onComplete(id => completed.push(id))
  registerCoreBlocks(registry, actions)
  return { store, actions, registry, parser, runner, completed }
}

// Minimal worldInfo matching what blocks read.
function makeWorldInfo({
  avatarId = 'av-local',
  spaceId = 'space-1',
  spaceName = 'Test Space',
  missionKeys = [],
} = {}) {
  return {
    avatarInfo:          { id: avatarId, missionKeys },
    currentSpaceId:      spaceId,
    currentSpaceName:    spaceName,
  }
}

function avatarInfo(id) {
  return { $avatar_id: id, $avatar_full_name: '', $is_local: 'true' }
}

// ─── a) enter_space ───────────────────────────────────────────────────────────

describe('a) enter_space', () => {
  it('completes when space name matches the regex param', () => {
    const { parser, runner, completed } = makeEnv()
    const scripts = parser.parseLwJson({
      blocks: [{ type: 'action', ref: 'action/movement/enter_space', params: ['Grand Hall'] }],
    })
    runner.addMission({ id: 'm1', scripts })

    const wi = makeWorldInfo({ spaceName: 'Grand Hall' })
    runner.dispatch('SWAvatarEnterSpace', { avatar: avatarInfo('av-local') }, wi)
    expect(completed).toContain('m1')
  })

  it('completes when space ID matches (and name does not)', () => {
    const { parser, runner, completed } = makeEnv()
    const scripts = parser.parseLwJson({
      blocks: [{ type: 'action', ref: 'action/movement/enter_space', params: ['sp-99'] }],
    })
    runner.addMission({ id: 'm2', scripts })

    const wi = makeWorldInfo({ spaceId: 'sp-99', spaceName: 'Something Else' })
    runner.dispatch('SWAvatarEnterSpace', { avatar: avatarInfo('av-local') }, wi)
    expect(completed).toContain('m2')
  })

  it('does NOT complete when neither name nor ID matches', () => {
    const { parser, runner, completed } = makeEnv()
    const scripts = parser.parseLwJson({
      blocks: [{ type: 'action', ref: 'action/movement/enter_space', params: ['Secret Room'] }],
    })
    runner.addMission({ id: 'm3', scripts })

    const wi = makeWorldInfo({ spaceName: 'Lobby', spaceId: 'lobby-1' })
    runner.dispatch('SWAvatarEnterSpace', { avatar: avatarInfo('av-local') }, wi)
    expect(completed).toHaveLength(0)
  })

  it('empty param matches any space (regex // matches everything)', () => {
    const { parser, runner, completed } = makeEnv()
    const scripts = parser.parseLwJson({
      blocks: [{ type: 'action', ref: 'action/movement/enter_space', params: [''] }],
    })
    runner.addMission({ id: 'm4', scripts })

    const wi = makeWorldInfo({ spaceName: 'Whatever', spaceId: 'x-1' })
    runner.dispatch('SWAvatarEnterSpace', { avatar: avatarInfo('av-local') }, wi)
    expect(completed).toContain('m4')
  })

  it('does NOT complete when avatar is not local (avatar_id mismatch)', () => {
    const { parser, runner, completed } = makeEnv()
    const scripts = parser.parseLwJson({
      blocks: [{ type: 'action', ref: 'action/movement/enter_space', params: ['Hall'] }],
    })
    runner.addMission({ id: 'm5', scripts })

    const wi = makeWorldInfo({ avatarId: 'av-local', spaceName: 'Hall' })
    // Fire with a DIFFERENT avatar id
    runner.dispatch('SWAvatarEnterSpace', { avatar: { $avatar_id: 'av-OTHER' } }, wi)
    expect(completed).toHaveLength(0)
  })

  it('matching is case-insensitive', () => {
    const { parser, runner, completed } = makeEnv()
    const scripts = parser.parseLwJson({
      blocks: [{ type: 'action', ref: 'action/movement/enter_space', params: ['grand hall'] }],
    })
    runner.addMission({ id: 'm6', scripts })

    const wi = makeWorldInfo({ spaceName: 'GRAND HALL' })
    runner.dispatch('SWAvatarEnterSpace', { avatar: avatarInfo('av-local') }, wi)
    expect(completed).toContain('m6')
  })
})

// ─── b) specific location constraint (tested in a 2-block mission) ────────────

describe('b) specific location constraint', () => {
  it('2-block mission completes when action fires AND constraint matches', () => {
    const { parser, runner, completed } = makeEnv()
    const scripts = parser.parseLwJson({
      blocks: [
        { type: 'action',     ref: 'action/movement/enter_space',  params: ['Hall'] },
        { type: 'constraint', ref: 'constraint/location/specific', params: ['Hall'] },
      ],
    })
    runner.addMission({ id: 'gate', scripts })

    const wi = makeWorldInfo({ spaceName: 'Hall' })
    runner.dispatch('SWAvatarEnterSpace', { avatar: avatarInfo('av-local') }, wi)
    expect(completed).toContain('gate')
  })

  it('does NOT complete when constraint space does not match (even though action fires)', () => {
    const { parser, runner, completed } = makeEnv()
    const scripts = parser.parseLwJson({
      blocks: [
        { type: 'action',     ref: 'action/movement/enter_space',  params: [''] },
        { type: 'constraint', ref: 'constraint/location/specific', params: ['VIP Lounge'] },
      ],
    })
    runner.addMission({ id: 'gated', scripts })

    // Action fires for any space (empty param), but constraint requires 'VIP Lounge'
    const wi = makeWorldInfo({ spaceName: 'Lobby', spaceId: 'lobby-1' })
    runner.dispatch('SWAvatarEnterSpace', { avatar: avatarInfo('av-local') }, wi)
    expect(completed).toHaveLength(0)
  })

  it('constraint standalone: passes when in matching space', () => {
    const result = specificLocationBlock.evaluate(
      {}, makeWorldInfo({ spaceName: 'Garden' }), ['Garden'], null,
    )
    expect(result.completed).toBe(true)
  })

  it('constraint standalone: fails when not in matching space', () => {
    const result = specificLocationBlock.evaluate(
      {}, makeWorldInfo({ spaceName: 'Lobby' }), ['Garden'], null,
    )
    expect(result.completed).toBe(false)
  })

  it('constraint matches space ID as fallback when name does not match', () => {
    const result = specificLocationBlock.evaluate(
      {}, makeWorldInfo({ spaceId: 'sp-vip', spaceName: 'Other' }), ['sp-vip'], null,
    )
    expect(result.completed).toBe(true)
  })
})

// ─── c) use_on_target ─────────────────────────────────────────────────────────

describe('c) use_on_target', () => {
  function makeAction(avatarId, modelName, targetFullName) {
    return {
      action: 'SWAvatarUseConsumable',
      info: {
        avatar:  { $avatar_id: avatarId, $avatar_full_name: 'Local User' },
        item:    { $model_name: modelName },
        target:  { $avatar_full_name: targetFullName },
      },
    }
  }

  const wi = makeWorldInfo({ avatarId: 'av-1' })

  it('correct item + correct target → completes', () => {
    const result = useOnTargetBlock.evaluate(
      makeAction('av-1', 'magic-wand', 'Alice Smith'),
      wi,
      ['magic-wand', 'Alice'],
      null,
    )
    expect(result.completed).toBe(true)
  })

  it('wrong item name → fails', () => {
    const result = useOnTargetBlock.evaluate(
      makeAction('av-1', 'wrong-item', 'Alice Smith'),
      wi,
      ['magic-wand', 'Alice'],
      null,
    )
    expect(result.completed).toBe(false)
    expect(result.completionDescription).toMatch(/item/)
  })

  it('wrong target name → fails', () => {
    const result = useOnTargetBlock.evaluate(
      makeAction('av-1', 'magic-wand', 'Bob Jones'),
      wi,
      ['magic-wand', 'Alice'],
      null,
    )
    expect(result.completed).toBe(false)
    expect(result.completionDescription).toMatch(/target/)
  })

  it('non-local avatar (avatar_id mismatch) → fails', () => {
    const result = useOnTargetBlock.evaluate(
      makeAction('av-OTHER', 'magic-wand', 'Alice Smith'),
      wi,       // worldInfo.avatarInfo.id = 'av-1' → mismatch
      ['magic-wand', 'Alice'],
      null,
    )
    expect(result.completed).toBe(false)
    expect(result.completionDescription).toMatch(/avatar/)
  })

  it('item regex is case-insensitive', () => {
    const result = useOnTargetBlock.evaluate(
      makeAction('av-1', 'MAGIC-WAND', 'Alice'),
      wi,
      ['magic-wand', ''],
      null,
    )
    expect(result.completed).toBe(true)
  })

  it('empty target param matches any target', () => {
    const result = useOnTargetBlock.evaluate(
      makeAction('av-1', 'wand', 'Anyone At All'),
      wi,
      ['wand', ''],
      null,
    )
    expect(result.completed).toBe(true)
  })
})

// ─── d) mission_update — through real runner ──────────────────────────────────

describe('d) mission_update accumulation (real runner)', () => {
  function makeUpdateAction(missionId, itemId, value, subtaskId = null) {
    const mission = { $mission_id: missionId }
    if (subtaskId !== null) mission.$subtask_id = String(subtaskId)
    return {
      action: 'SWUpdateMission',
      info: {
        mission,
        item:   { $item_id: itemId },
        update: { $value: String(value) },
      },
    }
  }

  function makeCompleteAction(missionId) {
    return {
      action: 'SWCompleteMission',
      info:   { mission: { $mission_id: missionId } },
    }
  }

  it('accumulates per-item-id values and completes at total', () => {
    const { parser, runner, completed } = makeEnv()
    // Params use sentinels; runner substitutes them with live mission ID / subtask ID
    const scripts = parser.parseLwJson({
      blocks: [{
        type: 'action',
        ref:  'action/item/mission_update',
        params: [PARAMETER_MISSION_ID, '5'],
      }],
    })
    runner.addMission({ id: 'upd-1', scripts })

    const wi = makeWorldInfo()
    runner.dispatch('SWUpdateMission', makeUpdateAction('upd-1', 'item-A', 2).info, wi)
    let ir = runner.getIntermediateResult('upd-1')
    expect(ir.intermediateStage).toBe(2)
    expect(ir.intermediateData).toEqual({ 'item-A': 2 })

    runner.dispatch('SWUpdateMission', makeUpdateAction('upd-1', 'item-B', 3).info, wi)
    // sum = 5 = total → completes
    expect(completed).toContain('upd-1')
  })

  it('later update for same item-id overwrites, not increments', () => {
    const { parser, runner, completed } = makeEnv()
    const scripts = parser.parseLwJson({
      blocks: [{
        type: 'action', ref: 'action/item/mission_update',
        params: [PARAMETER_MISSION_ID, '5'],
      }],
    })
    runner.addMission({ id: 'upd-ow', scripts })

    const wi = makeWorldInfo()
    runner.dispatch('SWUpdateMission', makeUpdateAction('upd-ow', 'item-A', 4).info, wi)
    expect(runner.getIntermediateResult('upd-ow').intermediateStage).toBe(4)

    // Overwrite: item-A = 1, not 4+1
    runner.dispatch('SWUpdateMission', makeUpdateAction('upd-ow', 'item-A', 1).info, wi)
    expect(runner.getIntermediateResult('upd-ow').intermediateStage).toBe(1)
    expect(completed).toHaveLength(0)  // 1 < 5

    runner.dispatch('SWUpdateMission', makeUpdateAction('upd-ow', 'item-B', 4).info, wi)
    expect(completed).toContain('upd-ow')  // 1+4 = 5 = total
  })

  it('SWCompleteMission short-circuits without accumulation', () => {
    const { parser, runner, completed } = makeEnv()
    const scripts = parser.parseLwJson({
      blocks: [{
        type: 'action', ref: 'action/item/mission_update',
        params: [PARAMETER_MISSION_ID, '100'],
      }],
    })
    runner.addMission({ id: 'upd-sc', scripts })

    const wi = makeWorldInfo()
    runner.dispatch('SWCompleteMission', makeCompleteAction('upd-sc').info, wi)
    expect(completed).toContain('upd-sc')
    expect(runner.getIntermediateResult('upd-sc')).toBeNull()
  })

  it('wrong missionId in action.info → no effect', () => {
    const { parser, runner, completed } = makeEnv()
    const scripts = parser.parseLwJson({
      blocks: [{
        type: 'action', ref: 'action/item/mission_update',
        params: [PARAMETER_MISSION_ID, '3'],
      }],
    })
    runner.addMission({ id: 'upd-id', scripts })

    const wi = makeWorldInfo()
    runner.dispatch('SWUpdateMission', makeUpdateAction('WRONG-MISSION', 'item-A', 3).info, wi)
    expect(completed).toHaveLength(0)
    expect(runner.getIntermediateResult('upd-id')).toBeNull()
  })

  it('count is clamped to total (never exceeds it in the intermediateStage)', () => {
    const { parser, runner, completed } = makeEnv()
    const scripts = parser.parseLwJson({
      blocks: [{
        type: 'action', ref: 'action/item/mission_update',
        params: [PARAMETER_MISSION_ID, '3'],
      }],
    })
    runner.addMission({ id: 'upd-clamp', scripts })

    const wi = makeWorldInfo()
    runner.dispatch('SWUpdateMission', makeUpdateAction('upd-clamp', 'item-A', 10).info, wi)
    // 10 > total(3) → clamped and completed
    expect(completed).toContain('upd-clamp')
  })

  it('subtask ID keying: action with matching subtaskId only fires, mismatch is ignored', () => {
    const { parser, runner, completed } = makeEnv()
    const scripts = parser.parseLwJson({
      blocks: [{
        type:      'subtask',
        ref:       'action/item/mission_update',
        params:    [PARAMETER_MISSION_ID, '2', PARAMETER_SUBTASK_ID],
        subtaskId: 'sub-X',
      }],
    })
    runner.addMission({ id: 'upd-sub', scripts })

    const wi = makeWorldInfo()
    // Wrong subtask_id in action → block should return false, no accumulation
    runner.dispatch('SWUpdateMission', makeUpdateAction('upd-sub', 'item-1', 1, 'sub-WRONG').info, wi)
    expect(runner.getIntermediateResult('upd-sub', 'sub-X')).toBeNull()

    // Correct subtask_id
    runner.dispatch('SWUpdateMission', makeUpdateAction('upd-sub', 'item-1', 1, 'sub-X').info, wi)
    expect(runner.getIntermediateResult('upd-sub', 'sub-X')?.intermediateStage).toBe(1)

    runner.dispatch('SWUpdateMission', makeUpdateAction('upd-sub', 'item-2', 1, 'sub-X').info, wi)
    expect(completed).toContain('upd-sub')
  })
})

// ─── e) give↔gate cross-mission key loop ──────────────────────────────────────

describe('e) give↔gate cross-mission key loop', () => {
  it('completing mission 1 gives key K; mission 2 gated on K completes on next dispatch', () => {
    const store   = new MissionKeyStore()
    const { parser, runner, completed } = makeEnv({ keyStore: store })
    const ctx     = new MockTriggerContext({
      keyStore:      store,
      localAvatarId: 'av-local',
      space:         { spaceId: 'hall-1', ownerId: 'o-1', modelId: 'm-1', name: 'Hall' },
    })
    const bus     = new TriggerBus(runner, ctx)

    // Mission 1: enter_space action → gives key 'door-open' on completion
    const scripts1 = parser.parseLwJson({
      blocks: [
        { type: 'action',     ref: 'action/movement/enter_space',    params: ['Hall'] },
        { type: 'completion', ref: 'completion/missionkey/give',     params: ['door-open', 'chain-1'] },
      ],
    })
    runner.addMission({ id: 'mission-1', scripts: scripts1 })

    // Mission 2: enter_space action, gated on having key 'door-open'
    const scripts2 = parser.parseLwJson({
      blocks: [
        { type: 'action',     ref: 'action/movement/enter_space',    params: ['Hall'] },
        { type: 'constraint', ref: 'constraint/state/missionkeys',   params: ['door-open', ''] },
      ],
    })
    runner.addMission({ id: 'mission-2', scripts: scripts2 })

    const rawEnterHall = {
      avatar: { id: 'av-local', firstName: 'X', lastName: 'Y', fullName: 'X Y',
                isLocal: true, isUser: true },
    }

    // First dispatch: mission-1 completes → gives key. Mission-2 evaluated same dispatch
    // but worldInfo snapshot doesn't yet reflect the newly-given key → mission-2 does NOT complete.
    bus.emit('SWAvatarEnterSpace', rawEnterHall)
    expect(completed).toContain('mission-1')
    expect(completed).not.toContain('mission-2')

    // Key is now in the store
    expect(store.has('door-open')).toBe(true)

    // Second dispatch: worldInfo rebuilt → avatarInfo.missionKeys now contains 'door-open'
    // → constraint passes → mission-2 completes
    bus.emit('SWAvatarEnterSpace', rawEnterHall)
    expect(completed).toContain('mission-2')
  })

  it('mission gated on key does NOT complete before the key is given', () => {
    const store   = new MissionKeyStore()
    const { parser, runner, completed } = makeEnv({ keyStore: store })
    const ctx     = new MockTriggerContext({ keyStore: store, localAvatarId: 'av-local' })
    const bus     = new TriggerBus(runner, ctx)

    const scripts = parser.parseLwJson({
      blocks: [
        { type: 'action',     ref: 'action/movement/enter_space',  params: [''] },
        { type: 'constraint', ref: 'constraint/state/missionkeys', params: ['locked-key', ''] },
      ],
    })
    runner.addMission({ id: 'locked-m', scripts })

    const raw = {
      avatar: { id: 'av-local', firstName: 'A', isLocal: true, isUser: true },
    }
    bus.emit('SWAvatarEnterSpace', raw)
    expect(completed).toHaveLength(0)

    // Give the key externally, then dispatch again
    store.add('locked-key', 'chain-1')
    bus.emit('SWAvatarEnterSpace', raw)
    expect(completed).toContain('locked-m')
  })

  it('missionkeys constraint with withoutKeys rejects when forbidden key is present', () => {
    const store = new MissionKeyStore()
    store.add('forbidden-key', '')

    const result = missionKeysConstraintBlock.evaluate(
      {}, makeWorldInfo({ missionKeys: ['forbidden-key'] }),
      ['', 'forbidden-key'],  // hasKeys='', withoutKeys='forbidden-key'
      null,
    )
    expect(result.completed).toBe(false)
  })

  it('missionkeys constraint passes when required key present and no forbidden key', () => {
    const result = missionKeysConstraintBlock.evaluate(
      {}, makeWorldInfo({ missionKeys: ['good-key'] }),
      ['good-key', ''],
      null,
    )
    expect(result.completed).toBe(true)
  })
})

// ─── f) getDescription ────────────────────────────────────────────────────────

describe('f) getDescription', () => {
  it('enter_space: named space', () => {
    expect(enterSpaceBlock.getDescription(['Grand Hall'])).toBe('entering a space named "Grand Hall"')
  })

  it('enter_space: empty param → any space', () => {
    expect(enterSpaceBlock.getDescription([''])).toBe('entering any space')
  })

  it('specific location: named space', () => {
    expect(specificLocationBlock.getDescription(['Garden'])).toBe('in space called "Garden"')
  })

  it('specific location: empty → any space', () => {
    expect(specificLocationBlock.getDescription([''])).toBe('in any space')
  })

  it('use_on_target: item and target named', () => {
    expect(useOnTargetBlock.getDescription(['wand', 'Alice'])).toBe('using "wand" on "Alice"')
  })

  it('use_on_target: empty params → any defaults', () => {
    expect(useOnTargetBlock.getDescription(['', ''])).toBe('using "any item" on "any target"')
  })

  it('mission_update: shows total from params[1]', () => {
    expect(missionUpdateBlock.getDescription([PARAMETER_MISSION_ID, '7'])).toMatch(/7/)
  })

  it('give mission key: shows key and chain', () => {
    const actions = new MissionActionsUser(new MissionKeyStore())
    const block   = createGiveMissionKeyBlock(actions)
    expect(block.getDescription(['vip-key', 'chain-9'])).toContain('vip-key')
    expect(block.getDescription(['vip-key', 'chain-9'])).toContain('chain-9')
  })

  it('mission keys constraint: shows has + without', () => {
    expect(missionKeysConstraintBlock.getDescription(['key-a', 'key-b'])).toMatch(/key-a/)
    expect(missionKeysConstraintBlock.getDescription(['key-a', 'key-b'])).toMatch(/key-b/)
  })

  it('mission keys constraint: empty → any key state', () => {
    expect(missionKeysConstraintBlock.getDescription(['', ''])).toBe('any key state')
  })
})
