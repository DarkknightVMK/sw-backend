import { describe, it, expect, beforeEach } from 'vitest'
import { BlockRegistry }      from '../core/BlockRegistry.js'
import { CompositionParser }  from '../core/CompositionParser.js'
import { MissionRunner }      from '../core/MissionRunner.js'
import { MissionKeyStore }    from '../keys/MissionKeyStore.js'
import { MissionActionsUser } from '../keys/MissionActionsUser.js'
import { registerCoreBlocks } from '../blocks/registerCoreBlocks.js'
import { TriggerBus }         from '../trigger-bus/TriggerBus.js'
import { MockTriggerContext } from '../trigger-bus/MockTriggerContext.js'

// Helpers ---------------------------------------------------------------------
function makeEngine(space = { spaceId: 'hall-1', ownerId: 'o1', modelId: 'm1', name: 'Hall' }, spaceAvatars = []) {
  const registry = new BlockRegistry()
  const parser   = new CompositionParser(registry)
  const runner   = new MissionRunner()
  const keyStore = new MissionKeyStore()
  registerCoreBlocks(registry, new MissionActionsUser(keyStore))
  const ctx = new MockTriggerContext({ keyStore, localAvatarId: 'av1', space, spaceAvatars })
  const bus = new TriggerBus(runner, ctx)
  const completed = []
  runner.onComplete(id => completed.push(id))
  const add = (id, blocks) => runner.addMission({ id, scripts: parser.parseLwJson({ blocks }) })
  return { registry, parser, runner, bus, completed, add, ctx }
}
const localAvatar = { id: 'av1', fullName: 'Player One', isLocal: true, isUser: true }

describe('block registration', () => {
  it('registers all 14 action + 8 constraint types (+ give) in order', () => {
    const registry = new BlockRegistry()
    registerCoreBlocks(registry, new MissionActionsUser(new MissionKeyStore()))
    const actions = registry.list().filter(([, d]) => d.type === 'action').map(([, d]) => d.name)
    const constraints = registry.list().filter(([, d]) => d.type === 'constraint').map(([, d]) => d.name)
    expect(actions).toEqual([
      'Talk', 'Perform an action', 'Perform an action with others', 'Interact with an item',
      'Interact with multiple items', 'Use an item', 'Use an item on someone', 'Use an item on self',
      'Use an item multiple times', 'Use an item with others', 'Whisper', 'Whisper to someone',
      'Entering a space', 'Custom',
    ])
    expect(constraints).toEqual([
      'Home', 'Specific space', 'Number of people present', 'Specific people present',
      'Specific time', 'Specific day of week', 'Specific date', 'Mission keys',
    ])
  })
})

describe('avatar action blocks', () => {
  it('Talk completes on matching speech, not on whisper', () => {
    const e = makeEngine()
    e.add('m', [{ type: 'action', ref: 'action/avatar/talk', params: ['hello'] }])
    e.bus.emit('SWAvatarSpeech', { avatar: localAvatar, text: 'well hello there', whisper: false })
    expect(e.completed).toContain('m')
  })
  it('Talk ignores whispers', () => {
    const e = makeEngine()
    e.add('m', [{ type: 'action', ref: 'action/avatar/talk', params: [''] }])
    e.bus.emit('SWAvatarSpeech', { avatar: localAvatar, text: 'psst', whisper: true })
    expect(e.completed).toHaveLength(0)
  })
  it('Whisper to someone matches recipient', () => {
    const e = makeEngine()
    e.add('m', [{ type: 'action', ref: 'action/avatar/whisper_to', params: ['secret', 'Buddy'] }])
    e.bus.emit('SWAvatarSpeech', {
      avatar: localAvatar, text: 'a secret', whisper: true,
      whisperRecipient: { id: 'av2', fullName: 'Buddy Bear' },
    })
    expect(e.completed).toContain('m')
  })
  it('Perform an action with others needs presence', () => {
    const solo = makeEngine('Hall', [])
    solo.add('m', [{ type: 'action', ref: 'action/avatar/animate_with_others', params: ['dance', '1'] }])
    solo.bus.emit('SWAvatarAnimate', { avatar: localAvatar, animName: 'dance', start: true })
    expect(solo.completed).toHaveLength(0) // no others present

    const group = makeEngine({ spaceId: 's', name: 'Hall' }, [{ id: 'x', fullName: 'X' }])
    group.add('m', [{ type: 'action', ref: 'action/avatar/animate_with_others', params: ['dance', '1'] }])
    group.bus.emit('SWAvatarAnimate', { avatar: localAvatar, animName: 'dance', start: true })
    expect(group.completed).toContain('m')
  })
})

describe('item action blocks', () => {
  it('Use an item multiple times accumulates', () => {
    const e = makeEngine()
    e.add('m', [{ type: 'action', ref: 'action/consumable/use_multi', params: ['dryer', '3'] }])
    const use = () => e.bus.emit('SWAvatarUseConsumable', { avatar: localAvatar, modelName: 'dryer', modelId: 'm' })
    use(); use(); expect(e.completed).toHaveLength(0)
    use(); expect(e.completed).toContain('m')
  })
  it('Use an item on self checks target id', () => {
    const e = makeEngine()
    e.add('m', [{ type: 'action', ref: 'action/consumable/use_on_self', params: ['potion'] }])
    e.bus.emit('SWAvatarUseConsumable', { avatar: localAvatar, modelName: 'potion', targetAvatar: { id: 'av1' } })
    expect(e.completed).toContain('m')
  })
})

describe('constraint blocks gate the task', () => {
  it('Home passes only in the home space', () => {
    const e = makeEngine({ spaceId: 'home-1', name: 'Hall' })
    e.ctx._space = { spaceId: 'home-1', name: 'Hall' }
    // Force home space to match current via a custom worldInfo build
    const wi = e.bus.buildWorldInfo()
    wi.avatarInfo.homeSpaceId = 'home-1'
    const def = e.registry.resolve('constraint/location/home')
    expect(def.evaluate(null, wi, []).completed).toBe(true)
    wi.avatarInfo.homeSpaceId = 'somewhere-else'
    expect(def.evaluate(null, wi, []).completed).toBe(false)
  })
  it('Specific day of week / time / date evaluate against worldInfo', () => {
    const registry = new BlockRegistry()
    registerCoreBlocks(registry, new MissionActionsUser(new MissionKeyStore()))
    const wi = { timeDay: 5, timeHour: 14, timeMinute: 30, timeMonth: 6, timeDate: 4, timeYear: 2026 } // Fri 14:30, Jul 4 2026

    const dow = registry.resolve('constraint/time/day_of_week')
    expect(dow.evaluate(null, wi, ['Fri']).completed).toBe(true)
    expect(dow.evaluate(null, wi, ['Mon,Tue']).completed).toBe(false)
    expect(dow.evaluate(null, wi, ['']).completed).toBe(true) // empty = any day

    const tod = registry.resolve('constraint/time/time_of_day')
    expect(tod.evaluate(null, wi, ['09:00', '17:00']).completed).toBe(true)
    expect(tod.evaluate(null, wi, ['15:00', '17:00']).completed).toBe(false)

    const date = registry.resolve('constraint/time/date')
    expect(date.evaluate(null, wi, ['07/04']).completed).toBe(true)
    expect(date.evaluate(null, wi, ['2026-07-04']).completed).toBe(true)
    expect(date.evaluate(null, wi, ['12/25']).completed).toBe(false)

    const count = registry.resolve('constraint/space/people_count')
    expect(count.evaluate(null, { currentSpaceAvatars: [{}, {}] }, ['2']).completed).toBe(true)
    expect(count.evaluate(null, { currentSpaceAvatars: [] }, ['1']).completed).toBe(false)
  })

  it('Custom passthrough completes on the named trigger', () => {
    const e = makeEngine()
    e.add('m', [{ type: 'action', ref: 'action/custom', params: ['SWAvatarEmote', ''] }])
    e.bus.emit('SWAvatarEmote', { avatar: localAvatar, emote: { $name: 'wave' } })
    expect(e.completed).toContain('m')
  })
})

describe('composition shape the editor emits (hints + subtasks)', () => {
  it('a two-subtask mission completes only when both subtasks are done', () => {
    const e = makeEngine()
    // Shape buildComposition() produces: type:'subtask' blocks each with a subtaskId.
    e.add('m', [
      { type: 'subtask', ref: 'action/avatar/talk', params: ['red'],  subtaskId: 's1' },
      { type: 'subtask', ref: 'action/avatar/talk', params: ['blue'], subtaskId: 's2' },
    ])
    e.bus.emit('SWAvatarSpeech', { avatar: localAvatar, text: 'the red door', whisper: false })
    expect(e.completed).toHaveLength(0) // only s1 done
    e.bus.emit('SWAvatarSpeech', { avatar: localAvatar, text: 'a blue sky', whisper: false })
    expect(e.completed).toContain('m')  // both subtasks done → mission completes
  })

  it('a hint block parses and does not block completion', () => {
    const e = makeEngine()
    e.add('m', [
      { type: 'action', ref: 'action/movement/enter_space', params: ['Hall'] },
      { type: 'hint',   ref: 'action/avatar/talk',          params: ['help'] },
    ])
    e.bus.emit('SWAvatarEnterSpace', { avatar: localAvatar })
    expect(e.completed).toContain('m')
  })
})

describe('live context feeds Home + presence conditions', () => {
  function reg() {
    const registry = new BlockRegistry()
    registerCoreBlocks(registry, new MissionActionsUser(new MissionKeyStore()))
    return registry
  }
  it('Home passes only when the current space is one the user owns', () => {
    const registry = reg()
    const owned = new MockTriggerContext({ localAvatarId: 'av1', space: { spaceId: 's-own', name: 'Home' }, homeSpaceIds: ['s-own', 's-two'] })
    const wiOwn = new TriggerBus(new MissionRunner(), owned).buildWorldInfo()
    expect(registry.resolve('constraint/location/home').evaluate(null, wiOwn, []).completed).toBe(true)

    const visiting = new MockTriggerContext({ localAvatarId: 'av1', space: { spaceId: 'elsewhere' }, homeSpaceIds: ['s-own'] })
    const wiAway = new TriggerBus(new MissionRunner(), visiting).buildWorldInfo()
    expect(registry.resolve('constraint/location/home').evaluate(null, wiAway, []).completed).toBe(false)
  })
  it('people-present conditions read live space avatars', () => {
    const registry = reg()
    const ctx = new MockTriggerContext({
      localAvatarId: 'av1', space: { spaceId: 's' },
      spaceAvatars: [{ id: 'x', fullName: 'Xavier X' }, { id: 'y', fullName: 'Yara Y' }],
    })
    const wi = new TriggerBus(new MissionRunner(), ctx).buildWorldInfo()
    expect(registry.resolve('constraint/space/people_count').evaluate(null, wi, ['2']).completed).toBe(true)
    expect(registry.resolve('constraint/space/people_count').evaluate(null, wi, ['3']).completed).toBe(false)
    expect(registry.resolve('constraint/space/people_specific').evaluate(null, wi, ['Yara']).completed).toBe(true)
    expect(registry.resolve('constraint/space/people_specific').evaluate(null, wi, ['Zed']).completed).toBe(false)
  })
})
