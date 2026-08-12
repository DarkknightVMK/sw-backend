import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest'
import { MissionResult }       from '../core/MissionResult.js'
import { BlockRegistry }       from '../core/BlockRegistry.js'
import { CompositionParser }   from '../core/CompositionParser.js'
import { MissionRunner }       from '../core/MissionRunner.js'
import { TriggerBus }          from '../trigger-bus/TriggerBus.js'
import { MockTriggerContext }  from '../trigger-bus/MockTriggerContext.js'
import { buildWorldInfo }      from '../worldinfo/WorldInfoBuilder.js'
import { AvatarMissionInfo }   from '../worldinfo/AvatarMissionInfo.js'

// ─── shared helpers ───────────────────────────────────────────────────────────

function makeMockRunner() {
  const dispatches = []
  return {
    dispatches,
    dispatch(triggerName, actionInfo, worldInfo) {
      dispatches.push({ triggerName, actionInfo, worldInfo })
    },
  }
}

function makeFullEnv(ctxConfig = {}) {
  const ctx      = new MockTriggerContext(ctxConfig)
  const registry = new BlockRegistry()
  const parser   = new CompositionParser(registry)
  const runner   = new MissionRunner()
  const bus      = new TriggerBus(runner, ctx)
  const completed = []
  runner.onComplete(id => completed.push(id))
  return { ctx, registry, parser, runner, bus, completed }
}

// ─── a) emit() builds correct action.info per trigger ────────────────────────

describe('a) emit() builds correct action.info per trigger', () => {
  it('SWAvatarEnterSpace: avatar fields match raw event', () => {
    const mockRunner = makeMockRunner()
    const bus = new TriggerBus(mockRunner, new MockTriggerContext())
    bus.emit('SWAvatarEnterSpace', {
      avatar: { id: 'av-42', firstName: 'Ada', lastName: 'Lovelace',
                fullName: 'Ada Lovelace', isLocal: true, isUser: true },
    })
    expect(mockRunner.dispatches).toHaveLength(1)
    const info = mockRunner.dispatches[0].actionInfo
    expect(info.avatar.$avatar_id).toBe('av-42')
    expect(info.avatar.$avatar_first_name).toBe('Ada')
    expect(info.avatar.$avatar_last_name).toBe('Lovelace')
    expect(info.avatar.$avatar_full_name).toBe('Ada Lovelace')
    expect(info.avatar.$is_local).toBe('true')
  })

  it('SWAvatarUseConsumable: avatar + item + script location fields', () => {
    const mockRunner = makeMockRunner()
    const bus = new TriggerBus(mockRunner, new MockTriggerContext())
    bus.emit('SWAvatarUseConsumable', {
      avatar:         { id: 'av-1', firstName: 'Ben', lastName: 'P', fullName: 'Ben P', isLocal: true, isUser: true },
      modelId:        'mdl-99',
      modelName:      'sparkle-wand',
      scriptLocation: 'items/wand/action.web.xml',
    })
    // Two dispatches: SWAvatarUseConsumable + AVATAR_USES mirror
    const use = mockRunner.dispatches.find(d => d.triggerName === 'SWAvatarUseConsumable')
    expect(use).toBeDefined()
    expect(use.actionInfo.avatar.$avatar_id).toBe('av-1')
    expect(use.actionInfo.item.$model_id).toBe('mdl-99')
    expect(use.actionInfo.item.$model_name).toBe('sparkle-wand')
    expect(use.actionInfo.script.location).toBe('items/wand/action.web.xml')
  })

  it('SWSaveSpace: changes fields are stringified booleans', () => {
    const mockRunner = makeMockRunner()
    const bus = new TriggerBus(mockRunner, new MockTriggerContext())
    bus.emit('SWSaveSpace', {
      nameChanged: true, decorChanged: false, themeChanged: true, lightingChanged: false,
    })
    const info = mockRunner.dispatches[0].actionInfo
    expect(info.changes.$name_changed).toBe('true')
    expect(info.changes.$decor_changed).toBe('false')
    expect(info.changes.$theme_changed).toBe('true')
    expect(info.changes.$lighting_changed).toBe('false')
  })

  it('SWUpdateMission: mission id + subtask id + update value', () => {
    const mockRunner = makeMockRunner()
    const bus = new TriggerBus(mockRunner, new MockTriggerContext())
    bus.emit('SWUpdateMission', {
      missionId: 'chain-7', subtaskId: 'sub-2',
      item:  { uid: 'item-uid-1', model: { id: 'm1', name: 'cog', description: 'a cog', tags: '' } },
      value: '5',
    })
    const info = mockRunner.dispatches[0].actionInfo
    expect(info.mission.$mission_id).toBe('chain-7')
    expect(info.mission.$subtask_id).toBe('sub-2')
    expect(info.update.$value).toBe('5')
    expect(info.item.$item_id).toBe('item-uid-1')
    expect(info.item.$model_name).toBe('cog')
  })

  it('SWPressInterfaceButton: button code is passed through', () => {
    const mockRunner = makeMockRunner()
    const bus = new TriggerBus(mockRunner, new MockTriggerContext())
    bus.emit('SWPressInterfaceButton', { code: 'open inventory panel' })
    expect(mockRunner.dispatches[0].actionInfo.button.$code).toBe('open inventory panel')
  })
})

// ─── b) SWAvatarUseConsumable also produces an AVATAR_USES dispatch ───────────

describe('b) SWAvatarUseConsumable mirrors to AVATAR_USES', () => {
  it('produces exactly two dispatches: SWAvatarUseConsumable + AVATAR_USES', () => {
    const mockRunner = makeMockRunner()
    const bus = new TriggerBus(mockRunner, new MockTriggerContext())
    bus.emit('SWAvatarUseConsumable', {
      avatar:    { id: 'av-1', firstName: 'X', isLocal: true, isUser: true },
      modelId:   'mdl-7',
      modelName: 'fairy-wings',
    })
    expect(mockRunner.dispatches).toHaveLength(2)
    const names = mockRunner.dispatches.map(d => d.triggerName)
    expect(names).toContain('SWAvatarUseConsumable')
    expect(names).toContain('AVATAR_USES')
  })

  it('AVATAR_USES dispatch carries value = model_name', () => {
    const mockRunner = makeMockRunner()
    const bus = new TriggerBus(mockRunner, new MockTriggerContext())
    bus.emit('SWAvatarUseConsumable', {
      avatar: { id: 'av-1', isLocal: true },
      modelId: 'mdl-7', modelName: 'fairy-wings',
    })
    const avatarUses = mockRunner.dispatches.find(d => d.triggerName === 'AVATAR_USES')
    expect(avatarUses.actionInfo.value).toBe('fairy-wings')
  })

  it('other triggers do NOT produce an AVATAR_USES mirror', () => {
    const mockRunner = makeMockRunner()
    const bus = new TriggerBus(mockRunner, new MockTriggerContext())
    bus.emit('SWAvatarEnterSpace', { avatar: { id: 'av-1' } })
    expect(mockRunner.dispatches).toHaveLength(1)
    expect(mockRunner.dispatches[0].triggerName).toBe('SWAvatarEnterSpace')
  })
})

// ─── c) SWOwnItem / SWOwnBundle are NOT dispatched via client emit() ──────────

describe('c) Server-only triggers blocked on client emit, allowed on serverEmit', () => {
  it('emit(SWOwnItem) produces no dispatch', () => {
    const mockRunner = makeMockRunner()
    const bus = new TriggerBus(mockRunner, new MockTriggerContext())
    bus.emit('SWOwnItem', { model: { id: 'mdl-1', name: 'hat' } })
    expect(mockRunner.dispatches).toHaveLength(0)
  })

  it('emit(SWOwnBundle) produces no dispatch', () => {
    const mockRunner = makeMockRunner()
    const bus = new TriggerBus(mockRunner, new MockTriggerContext())
    bus.emit('SWOwnBundle', { bundleId: 'bundle-9' })
    expect(mockRunner.dispatches).toHaveLength(0)
  })

  it('serverEmit(SWOwnItem) DOES dispatch with item info', () => {
    const mockRunner = makeMockRunner()
    const bus = new TriggerBus(mockRunner, new MockTriggerContext())
    bus.serverEmit('SWOwnItem', { model: { id: 'mdl-1', name: 'hat', description: '', tags: '' } })
    expect(mockRunner.dispatches).toHaveLength(1)
    expect(mockRunner.dispatches[0].triggerName).toBe('SWOwnItem')
    expect(mockRunner.dispatches[0].actionInfo.item.$model_name).toBe('hat')
  })

  it('serverEmit(SWOwnBundle) DOES dispatch with bundle info', () => {
    const mockRunner = makeMockRunner()
    const bus = new TriggerBus(mockRunner, new MockTriggerContext())
    bus.serverEmit('SWOwnBundle', { bundleId: 'bundle-9' })
    expect(mockRunner.dispatches).toHaveLength(1)
    expect(mockRunner.dispatches[0].actionInfo.bundle.$bundle_id).toBe('bundle-9')
  })

  it('serverEmit silently drops non-server triggers', () => {
    const mockRunner = makeMockRunner()
    const bus = new TriggerBus(mockRunner, new MockTriggerContext())
    bus.serverEmit('SWAvatarEnterSpace', { avatar: { id: 'av-1' } })
    expect(mockRunner.dispatches).toHaveLength(0)
  })
})

// ─── d) SWTimeElapsed fires on 10s timer; timer start/stop ───────────────────

describe('d) SWTimeElapsed timer', () => {
  afterEach(() => { vi.useRealTimers() })

  it('fires SWTimeElapsed after 10s with fake clock', () => {
    vi.useFakeTimers()
    const mockRunner = makeMockRunner()
    const bus = new TriggerBus(mockRunner, new MockTriggerContext())
    bus.startTimeElapsedTimer()

    expect(mockRunner.dispatches).toHaveLength(0)
    vi.advanceTimersByTime(10_000)
    expect(mockRunner.dispatches).toHaveLength(1)
    expect(mockRunner.dispatches[0].triggerName).toBe('SWTimeElapsed')
    bus.stopTimeElapsedTimer()
  })

  it('fires twice after 20s', () => {
    vi.useFakeTimers()
    const mockRunner = makeMockRunner()
    const bus = new TriggerBus(mockRunner, new MockTriggerContext())
    bus.startTimeElapsedTimer()
    vi.advanceTimersByTime(20_000)
    const swte = mockRunner.dispatches.filter(d => d.triggerName === 'SWTimeElapsed')
    expect(swte).toHaveLength(2)
    bus.stopTimeElapsedTimer()
  })

  it('action.info has $startTime and $endTime fields', () => {
    vi.useFakeTimers()
    const mockRunner = makeMockRunner()
    const bus = new TriggerBus(mockRunner, new MockTriggerContext())
    bus.startTimeElapsedTimer()
    vi.advanceTimersByTime(10_000)
    const info = mockRunner.dispatches[0].actionInfo
    expect(info).toHaveProperty('$startTime')
    expect(info).toHaveProperty('$endTime')
    bus.stopTimeElapsedTimer()
  })

  it('stopTimeElapsedTimer prevents further fires', () => {
    vi.useFakeTimers()
    const mockRunner = makeMockRunner()
    const bus = new TriggerBus(mockRunner, new MockTriggerContext())
    bus.startTimeElapsedTimer()
    vi.advanceTimersByTime(10_000)
    expect(mockRunner.dispatches).toHaveLength(1)
    bus.stopTimeElapsedTimer()
    vi.advanceTimersByTime(30_000)  // 3 more intervals — should not fire
    expect(mockRunner.dispatches).toHaveLength(1)
  })

  it('startTimeElapsedTimer is idempotent (calling twice only registers one timer)', () => {
    vi.useFakeTimers()
    const mockRunner = makeMockRunner()
    const bus = new TriggerBus(mockRunner, new MockTriggerContext())
    bus.startTimeElapsedTimer()
    bus.startTimeElapsedTimer()  // second call must be a no-op
    vi.advanceTimersByTime(10_000)
    expect(mockRunner.dispatches.filter(d => d.triggerName === 'SWTimeElapsed')).toHaveLength(1)
    bus.stopTimeElapsedTimer()
  })
})

// ─── e) worldInfo: local avatar has missionKeys; non-local has empty ──────────

describe('e) worldInfo missionKeys asymmetry', () => {
  it('local avatar gets the configured missionKeys', () => {
    const ctx = new MockTriggerContext({
      localAvatarId: 'local-1',
      missionKeys:   ['key-alpha', 'key-beta'],
    })
    const wi = buildWorldInfo(ctx)
    expect(wi.avatarInfo.id).toBe('local-1')
    expect(wi.avatarInfo.missionKeys).toEqual(['key-alpha', 'key-beta'])
  })

  it('non-local avatars in the space always get empty missionKeys', () => {
    const ctx = new MockTriggerContext({
      localAvatarId: 'local-1',
      missionKeys:   ['key-alpha'],
      spaceAvatars:  [
        { id: 'other-1', firstName: 'Other', lastName: 'Person', fullName: 'Other Person' },
        { id: 'other-2', firstName: 'Guest', lastName: 'User',   fullName: 'Guest User' },
      ],
    })
    const wi = buildWorldInfo(ctx)
    expect(wi.currentSpaceAvatars).toHaveLength(2)
    for (const a of wi.currentSpaceAvatars) {
      expect(a.missionKeys).toEqual([])
    }
  })

  it('worldInfo time fields match the supplied date', () => {
    const ctx  = new MockTriggerContext()
    const date = new Date(2026, 0, 15, 14, 30, 0)  // Jan 15 2026, 14:30
    const wi   = buildWorldInfo(ctx, date)
    expect(wi.timeYear).toBe(2026)
    expect(wi.timeMonth).toBe(0)    // January = 0
    expect(wi.timeDate).toBe(15)
    expect(wi.timeHour).toBe(14)
    expect(wi.timeMinute).toBe(30)
  })

  it('worldInfo.avatarId is a shortcut for avatarInfo.id', () => {
    const ctx = new MockTriggerContext({ localAvatarId: 'local-42' })
    const wi  = buildWorldInfo(ctx)
    expect(wi.avatarId).toBe('local-42')
  })

  it('space fields match the context', () => {
    const ctx = new MockTriggerContext({
      space: { spaceId: 'sp-9', ownerId: 'owner-9', modelId: 'mdl-9', name: 'Grand Hall' },
    })
    const wi = buildWorldInfo(ctx)
    expect(wi.currentSpaceId).toBe('sp-9')
    expect(wi.currentSpaceOwnerId).toBe('owner-9')
    expect(wi.currentSpaceModelId).toBe('mdl-9')
    expect(wi.currentSpaceName).toBe('Grand Hall')
  })

  it('worldInfo built during emit() carries the same missionKeys', () => {
    const mockRunner = makeMockRunner()
    const ctx = new MockTriggerContext({
      localAvatarId: 'local-77',
      missionKeys:   ['my-key'],
    })
    const bus = new TriggerBus(mockRunner, ctx)
    bus.emit('SWAvatarEnterSpace', { avatar: { id: 'local-77' } })
    const wi = mockRunner.dispatches[0].worldInfo
    expect(wi.avatarInfo.missionKeys).toEqual(['my-key'])
    expect(wi.currentSpaceAvatars.every(a => a.missionKeys.length === 0)).toBe(true)
  })
})

// ─── f) End-to-end: bus → builder → runner → MissionResult ───────────────────

describe('f) End-to-end: emit through bus triggers mission completion', () => {
  // Inline test block that responds to SWAvatarEnterSpace
  const enterSpaceBlock = {
    type:     'action',
    name:     'Test: complete on enter space',
    group:    null,
    join:     null,
    sort:     0,
    triggers: ['SWAvatarEnterSpace'],
    evaluate(_action, _worldInfo, _params, _intermediate) {
      return new MissionResult(true)
    },
  }

  // Inline test block that checks action.info.item.$model_name
  const consumableBlock = {
    type:     'action',
    name:     'Test: complete when model_name = target-item',
    group:    null,
    join:     null,
    sort:     0,
    triggers: ['SWAvatarUseConsumable'],
    evaluate(action, _worldInfo, _params, _intermediate) {
      const name = action.info?.item?.$model_name
      return new MissionResult(name === 'target-item')
    },
  }

  it('bus.emit(SWAvatarEnterSpace) completes a registered mission', () => {
    const { registry, parser, bus, completed } = makeFullEnv()
    registry.register('test/enter-space', enterSpaceBlock)
    const scripts = parser.parseLwJson({
      blocks: [{ type: 'action', ref: 'test/enter-space', params: [] }],
    })
    bus['_runner'].addMission({ id: 'enter-mission', scripts })

    bus.emit('SWAvatarEnterSpace', {
      avatar: { id: 'av-1', firstName: 'Test', lastName: 'User',
                fullName: 'Test User', isLocal: true, isUser: true },
    })

    expect(completed).toContain('enter-mission')
  })

  it('emit of wrong trigger does not complete the mission', () => {
    const { registry, parser, bus, completed } = makeFullEnv()
    registry.register('test/enter-space', enterSpaceBlock)
    const scripts = parser.parseLwJson({
      blocks: [{ type: 'action', ref: 'test/enter-space', params: [] }],
    })
    bus['_runner'].addMission({ id: 'enter-mission-2', scripts })

    bus.emit('SWAvatarLeaveSpace', { avatar: { id: 'av-1' } })
    expect(completed).toHaveLength(0)

    bus.emit('SWAvatarEnterSpace', { avatar: { id: 'av-1' } })
    expect(completed).toContain('enter-mission-2')
  })

  it('action.info from the builder is correctly received by the block evaluate()', () => {
    const { registry, parser, bus, completed } = makeFullEnv()
    registry.register('test/consumable', consumableBlock)
    const scripts = parser.parseLwJson({
      blocks: [{ type: 'action', ref: 'test/consumable', params: [] }],
    })
    bus['_runner'].addMission({ id: 'consumable-mission', scripts })

    // Wrong item — should not complete
    bus.emit('SWAvatarUseConsumable', {
      avatar: { id: 'av-1', isLocal: true }, modelId: 'mdl-1', modelName: 'wrong-item',
    })
    expect(completed).toHaveLength(0)

    // Correct item — should complete
    bus.emit('SWAvatarUseConsumable', {
      avatar: { id: 'av-1', isLocal: true }, modelId: 'mdl-2', modelName: 'target-item',
    })
    expect(completed).toContain('consumable-mission')
  })

  it('worldInfo passed through bus is available to the block evaluate()', () => {
    let capturedWorldInfo = null
    const captureBlock = {
      type: 'action', group: null, join: null, sort: 0,
      triggers: ['SWAvatarEnterSpace'],
      evaluate(_action, worldInfo, _params, _intermediate) {
        capturedWorldInfo = worldInfo
        return new MissionResult(true)
      },
    }
    const { registry, parser, bus } = makeFullEnv({
      localAvatarId: 'local-xyz',
      missionKeys:   ['capture-key'],
    })
    registry.register('test/capture', captureBlock)
    const scripts = parser.parseLwJson({
      blocks: [{ type: 'action', ref: 'test/capture', params: [] }],
    })
    bus['_runner'].addMission({ id: 'capture-mission', scripts })
    bus.emit('SWAvatarEnterSpace', { avatar: { id: 'av-1' } })

    expect(capturedWorldInfo).not.toBeNull()
    expect(capturedWorldInfo.avatarInfo.id).toBe('local-xyz')
    expect(capturedWorldInfo.avatarInfo.missionKeys).toEqual(['capture-key'])
  })
})
