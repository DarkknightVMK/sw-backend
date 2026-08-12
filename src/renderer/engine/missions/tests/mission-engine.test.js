import { describe, it, expect, beforeEach } from 'vitest'
import { MissionResult }              from '../core/MissionResult.js'
import { IntermediateMissionResult }  from '../core/IntermediateMissionResult.js'
import { BlockRegistry }              from '../core/BlockRegistry.js'
import { CompositionParser }          from '../core/CompositionParser.js'
import { MissionRunner }              from '../core/MissionRunner.js'
import { alwaysPassBlock }            from '../test-blocks/alwaysPass.js'
import { alwaysFailBlock }            from '../test-blocks/alwaysFail.js'
import { accumulatorBlock }           from '../test-blocks/accumulator.js'
import { accumulatorDictBlock }       from '../test-blocks/accumulatorDict.js'

// ─── shared helpers ──────────────────────────────────────────────────────────

function makeRegistry() {
  const r = new BlockRegistry()
  r.register('test/always-pass',     alwaysPassBlock)
  r.register('test/always-fail',     alwaysFailBlock)
  r.register('test/accumulator',     accumulatorBlock)
  r.register('test/accumulator-dict', accumulatorDictBlock)
  return r
}

function makeEnv() {
  const registry = makeRegistry()
  const parser   = new CompositionParser(registry)
  const runner   = new MissionRunner()
  const completed = []
  runner.onComplete(id => completed.push(id))
  return { registry, parser, runner, completed }
}

// ─── a) MissionResult serialisation ─────────────────────────────────────────

describe('a) MissionResult serialisation', () => {
  it('true serialises to the exact XML string', () => {
    expect(new MissionResult(true).toXMLString()).toBe('<r t="m" c="true"/>')
  })

  it('false serialises to the exact XML string', () => {
    expect(new MissionResult(false).toXMLString()).toBe('<r t="m" c="false"/>')
  })

  it('round-trips via fromXMLString', () => {
    const r1 = MissionResult.fromXMLString('<r t="m" c="true"/>')
    expect(r1.completed).toBe(true)
    const r2 = MissionResult.fromXMLString('<r t="m" c="false"/>')
    expect(r2.completed).toBe(false)
  })

  it('fields are accessible after construction', () => {
    const r = new MissionResult(false, 'reason')
    expect(r.completed).toBe(false)
    expect(r.completionDescription).toBe('reason')
  })

  it('static TYPE is "m"', () => {
    expect(MissionResult.TYPE).toBe('m')
  })
})

// ─── b) IntermediateMissionResult constructor arg slots ──────────────────────

describe('b) IntermediateMissionResult constructor arg slots', () => {
  it('stores all four args in the correct fields', () => {
    const imr = new IntermediateMissionResult(5, { a: 2 }, 1000, 10)
    expect(imr.intermediateStage).toBe(5)
    expect(imr.intermediateData).toEqual({ a: 2 })
    expect(imr.timeElapsedNotificationDuration).toBe(1000)
    expect(imr.stageTotal).toBe(10)
  })

  it('defaults: data=null, duration=0, total=-1', () => {
    const imr = new IntermediateMissionResult(3)
    expect(imr.intermediateStage).toBe(3)
    expect(imr.intermediateData).toBeNull()
    expect(imr.timeElapsedNotificationDuration).toBe(0)
    expect(imr.stageTotal).toBe(-1)
  })

  it('completed is always false (super(false))', () => {
    const imr = new IntermediateMissionResult(1, null, 0, 5)
    expect(imr.completed).toBe(false)
  })

  it('extends MissionResult', () => {
    expect(new IntermediateMissionResult(1) instanceof MissionResult).toBe(true)
  })

  it('static TYPE is "i"', () => {
    expect(IntermediateMissionResult.TYPE).toBe('i')
  })

  it('XML round-trip preserves all fields including dictionary', () => {
    const orig = new IntermediateMissionResult(4, { foo: 2, bar: 3 }, 0, 10)
    const xml  = orig.toXMLString()
    const back = IntermediateMissionResult.fromXMLString(xml)
    expect(back.intermediateStage).toBe(4)
    expect(back.stageTotal).toBe(10)
    expect(back.intermediateData).toEqual({ foo: 2, bar: 3 })
  })
})

// ─── c) JSON ↔ MM XML round-trip ─────────────────────────────────────────────

describe('c) JSON ↔ MM XML round-trip', () => {
  it('round-trips a 2-block mission losslessly', () => {
    const { parser } = makeEnv()

    const lwJson = {
      blocks: [
        { type: 'action',     ref: 'test/always-pass', params: [] },
        { type: 'constraint', ref: 'test/always-fail', params: ['val1', 'val2'] },
      ],
    }

    const xml       = parser.lwJsonToMmXml(lwJson)
    const recovered = parser.mmXmlToLwJson(xml)

    expect(recovered.blocks).toHaveLength(2)

    expect(recovered.blocks[0].type).toBe('action')
    expect(recovered.blocks[0].ref).toBe('test/always-pass')
    expect(recovered.blocks[0].params).toEqual([])

    expect(recovered.blocks[1].type).toBe('constraint')
    expect(recovered.blocks[1].ref).toBe('test/always-fail')
    expect(recovered.blocks[1].params).toEqual(['val1', 'val2'])
  })

  it('round-trips a mission with no params', () => {
    const { parser } = makeEnv()
    const lwJson = { blocks: [{ type: 'completion', ref: 'test/always-pass', params: [] }] }
    const recovered = parser.mmXmlToLwJson(parser.lwJsonToMmXml(lwJson))
    expect(recovered.blocks[0].ref).toBe('test/always-pass')
    expect(recovered.blocks[0].type).toBe('completion')
  })

  it('MM XML → LW JSON strips .xml suffix from location', () => {
    const { parser } = makeEnv()
    const xml = `<scripts>\n  <script type="action" location="test/always-pass.xml"/>\n</scripts>`
    const json = parser.mmXmlToLwJson(xml)
    expect(json.blocks[0].ref).toBe('test/always-pass')
  })

  it('MM XML → LW JSON strips .web.xml suffix', () => {
    const { parser } = makeEnv()
    const xml = `<scripts>\n  <script type="action" location="test/always-pass.web.xml"/>\n</scripts>`
    const json = parser.mmXmlToLwJson(xml)
    expect(json.blocks[0].ref).toBe('test/always-pass')
  })
})

// ─── d) Runner: always-pass / always-fail ────────────────────────────────────

describe('d) Runner: always-pass / always-fail dispatch', () => {
  it('always-pass completes on first dispatch', () => {
    const { parser, runner, completed } = makeEnv()
    const scripts = parser.parseLwJson({ blocks: [{ type: 'action', ref: 'test/always-pass', params: [] }] })
    runner.addMission({ id: 'p1', scripts })
    runner.dispatch('TEST_TRIGGER', {})
    expect(completed).toContain('p1')
  })

  it('always-fail never completes regardless of dispatch count', () => {
    const { parser, runner, completed } = makeEnv()
    const scripts = parser.parseLwJson({ blocks: [{ type: 'action', ref: 'test/always-fail', params: [] }] })
    runner.addMission({ id: 'f1', scripts })
    runner.dispatch('TEST_TRIGGER', {})
    runner.dispatch('TEST_TRIGGER', {})
    runner.dispatch('TEST_TRIGGER', {})
    expect(completed).toHaveLength(0)
  })

  it('wrong trigger name does not fire the mission', () => {
    const { parser, runner, completed } = makeEnv()
    const scripts = parser.parseLwJson({ blocks: [{ type: 'action', ref: 'test/always-pass', params: [] }] })
    runner.addMission({ id: 'p2', scripts })
    runner.dispatch('WRONG_TRIGGER', {})
    expect(completed).toHaveLength(0)
    runner.dispatch('TEST_TRIGGER', {})
    expect(completed).toContain('p2')
  })

  it('mission not listed for a trigger does not receive the dispatch', () => {
    const { parser, runner, completed } = makeEnv()
    const scripts = parser.parseLwJson({ blocks: [{ type: 'action', ref: 'test/always-pass', params: [] }] })
    runner.addMission({ id: 'p3', scripts })
    // A trigger nothing is registered for — no crash, no completion
    runner.dispatch('UNRELATED_TRIGGER', {})
    expect(completed).toHaveLength(0)
  })
})

// ─── e) Accumulator intermediate state tracking ───────────────────────────────

describe('e) Accumulator: intermediate state accumulation', () => {
  describe('simple count mode', () => {
    it('completes on exactly the Nth dispatch (not N-1, not N+1)', () => {
      const { parser, runner, completed } = makeEnv()
      const scripts = parser.parseLwJson({ blocks: [{ type: 'action', ref: 'test/accumulator', params: ['3'] }] })
      runner.addMission({ id: 'acc', scripts })

      runner.dispatch('TEST_TRIGGER', {})
      expect(completed).toHaveLength(0)   // 1st: stage=1, not done

      runner.dispatch('TEST_TRIGGER', {})
      expect(completed).toHaveLength(0)   // 2nd: stage=2, not done

      runner.dispatch('TEST_TRIGGER', {})
      expect(completed).toContain('acc')  // 3rd: stage=3 >= 3, done
      expect(completed).toHaveLength(1)
    })

    it('intermediate stage counts up on each dispatch', () => {
      const { parser, runner } = makeEnv()
      const scripts = parser.parseLwJson({ blocks: [{ type: 'action', ref: 'test/accumulator', params: ['5'] }] })
      runner.addMission({ id: 'acc-count', scripts })

      runner.dispatch('TEST_TRIGGER', {})
      expect(runner.getIntermediateResult('acc-count').intermediateStage).toBe(1)

      runner.dispatch('TEST_TRIGGER', {})
      expect(runner.getIntermediateResult('acc-count').intermediateStage).toBe(2)

      runner.dispatch('TEST_TRIGGER', {})
      expect(runner.getIntermediateResult('acc-count').intermediateStage).toBe(3)
    })

    it('does not complete on N-1 dispatches', () => {
      const { parser, runner, completed } = makeEnv()
      const scripts = parser.parseLwJson({ blocks: [{ type: 'action', ref: 'test/accumulator', params: ['4'] }] })
      runner.addMission({ id: 'acc-n-1', scripts })

      for (let i = 0; i < 3; i++) runner.dispatch('TEST_TRIGGER', {})
      expect(completed).toHaveLength(0)   // 3 dispatches, target=4 → not done

      runner.dispatch('TEST_TRIGGER', {})
      expect(completed).toContain('acc-n-1')
    })

    it('does not fire completion again on N+1th dispatch (clamped by state.completed)', () => {
      const { parser, runner, completed } = makeEnv()
      const scripts = parser.parseLwJson({ blocks: [{ type: 'action', ref: 'test/accumulator', params: ['3'] }] })
      runner.addMission({ id: 'acc-clamp', scripts })

      runner.dispatch('TEST_TRIGGER', {})
      runner.dispatch('TEST_TRIGGER', {})
      runner.dispatch('TEST_TRIGGER', {})   // completes
      runner.dispatch('TEST_TRIGGER', {})   // extra — must not re-fire
      runner.dispatch('TEST_TRIGGER', {})

      expect(completed).toHaveLength(1)
      expect(completed[0]).toBe('acc-clamp')
    })

    it('stageTotal on stored IntermediateMissionResult matches params', () => {
      const { parser, runner } = makeEnv()
      const scripts = parser.parseLwJson({ blocks: [{ type: 'action', ref: 'test/accumulator', params: ['7'] }] })
      runner.addMission({ id: 'acc-total', scripts })

      runner.dispatch('TEST_TRIGGER', {})
      const ir = runner.getIntermediateResult('acc-total')
      expect(ir.stageTotal).toBe(7)
    })
  })

  describe('dictionary mode', () => {
    it('sums per-key values and the dictionary accumulates correctly', () => {
      const { parser, runner } = makeEnv()
      const scripts = parser.parseLwJson({ blocks: [{ type: 'action', ref: 'test/accumulator-dict', params: ['4'] }] })
      runner.addMission({ id: 'dict', scripts })

      runner.dispatch('TEST_TRIGGER', { key: 'a' })
      let ir = runner.getIntermediateResult('dict')
      expect(ir.intermediateStage).toBe(1)
      expect(ir.intermediateData).toEqual({ a: 1 })

      runner.dispatch('TEST_TRIGGER', { key: 'b' })
      ir = runner.getIntermediateResult('dict')
      expect(ir.intermediateStage).toBe(2)
      expect(ir.intermediateData).toEqual({ a: 1, b: 1 })

      runner.dispatch('TEST_TRIGGER', { key: 'a' })
      ir = runner.getIntermediateResult('dict')
      expect(ir.intermediateStage).toBe(3)
      expect(ir.intermediateData).toEqual({ a: 2, b: 1 })
    })

    it('completes when dictionary total reaches target', () => {
      const { parser, runner, completed } = makeEnv()
      const scripts = parser.parseLwJson({ blocks: [{ type: 'action', ref: 'test/accumulator-dict', params: ['4'] }] })
      runner.addMission({ id: 'dict-done', scripts })

      runner.dispatch('TEST_TRIGGER', { key: 'a' })
      runner.dispatch('TEST_TRIGGER', { key: 'b' })
      runner.dispatch('TEST_TRIGGER', { key: 'a' })
      runner.dispatch('TEST_TRIGGER', { key: 'c' })   // total=4 → done
      expect(completed).toContain('dict-done')
    })
  })

  describe('subtask keying: missionId+subtaskId', () => {
    it('intermediate state is keyed per-subtask, not shared', () => {
      const { parser, runner } = makeEnv()

      const scripts = parser.parseLwJson({
        blocks: [
          { type: 'subtask', ref: 'test/accumulator', params: ['3'], subtaskId: 'sub-A' },
          { type: 'subtask', ref: 'test/accumulator', params: ['3'], subtaskId: 'sub-B' },
        ],
      })
      runner.addMission({ id: 'sub-mission', scripts })

      runner.dispatch('TEST_TRIGGER', {})
      // sub-A gets stage=1; sub-B not evaluated yet (MM returns early on intermediate)
      const irA = runner.getIntermediateResult('sub-mission', 'sub-A')
      const irB = runner.getIntermediateResult('sub-mission', 'sub-B')
      expect(irA.intermediateStage).toBe(1)
      expect(irB).toBeNull()
    })

    it('each subtask accumulates independently toward its own target', () => {
      const { parser, runner, completed } = makeEnv()

      const scripts = parser.parseLwJson({
        blocks: [
          { type: 'subtask', ref: 'test/accumulator', params: ['2'], subtaskId: 'sub-A' },
          { type: 'subtask', ref: 'test/accumulator', params: ['2'], subtaskId: 'sub-B' },
        ],
      })
      runner.addMission({ id: 'sub-m2', scripts })

      // Dispatch 1: sub-A → stage=1 → intermediate
      runner.dispatch('TEST_TRIGGER', {})
      expect(runner.getIntermediateResult('sub-m2', 'sub-A').intermediateStage).toBe(1)
      expect(runner.getIntermediateResult('sub-m2', 'sub-B')).toBeNull()
      expect(completed).toHaveLength(0)

      // Dispatch 2: sub-A → stage=2 → complete; continues to sub-B → stage=1 → intermediate
      runner.dispatch('TEST_TRIGGER', {})
      expect(runner.getIntermediateResult('sub-m2', 'sub-A')).toBeNull()  // cleared on complete
      expect(runner.getIntermediateResult('sub-m2', 'sub-B').intermediateStage).toBe(1)
      expect(completed).toHaveLength(0)  // mission not done yet (sub-B incomplete)

      // Dispatch 3: sub-A already done; sub-B → stage=2 → complete → mission complete
      runner.dispatch('TEST_TRIGGER', {})
      expect(completed).toContain('sub-m2')
    })

    it('getIntermediateResult(id, subtaskId) reads the correct keyed slot', () => {
      const { parser, runner } = makeEnv()
      const scripts = parser.parseLwJson({
        blocks: [
          { type: 'subtask', ref: 'test/accumulator', params: ['5'], subtaskId: 'X' },
        ],
      })
      runner.addMission({ id: 'key-test', scripts })

      runner.dispatch('TEST_TRIGGER', {})
      runner.dispatch('TEST_TRIGGER', {})

      // keyed by missionId + subtaskId
      const ir = runner.getIntermediateResult('key-test', 'X')
      expect(ir.intermediateStage).toBe(2)

      // wrong subtask ID → null
      expect(runner.getIntermediateResult('key-test', 'Y')).toBeNull()
      // no subtask ID → also null (different key)
      expect(runner.getIntermediateResult('key-test')).toBeNull()
    })
  })
})

// ─── f) No cross-contamination between concurrent missions ────────────────────

describe('f) Two concurrent missions do not contaminate each other', () => {
  it('two accumulator missions with the same trigger track state separately', () => {
    const { parser, runner, completed } = makeEnv()

    const sA = parser.parseLwJson({ blocks: [{ type: 'action', ref: 'test/accumulator', params: ['3'] }] })
    const sB = parser.parseLwJson({ blocks: [{ type: 'action', ref: 'test/accumulator', params: ['3'] }] })
    runner.addMission({ id: 'mission-A', scripts: sA })
    runner.addMission({ id: 'mission-B', scripts: sB })

    runner.dispatch('TEST_TRIGGER', {})
    expect(runner.getIntermediateResult('mission-A').intermediateStage).toBe(1)
    expect(runner.getIntermediateResult('mission-B').intermediateStage).toBe(1)
    expect(completed).toHaveLength(0)

    runner.dispatch('TEST_TRIGGER', {})
    expect(runner.getIntermediateResult('mission-A').intermediateStage).toBe(2)
    expect(runner.getIntermediateResult('mission-B').intermediateStage).toBe(2)
    expect(completed).toHaveLength(0)

    runner.dispatch('TEST_TRIGGER', {})
    expect(completed).toContain('mission-A')
    expect(completed).toContain('mission-B')
    expect(completed).toHaveLength(2)
  })

  it('completing one mission does not advance or complete the other', () => {
    const { parser, runner, completed } = makeEnv()

    const sPass = parser.parseLwJson({ blocks: [{ type: 'action', ref: 'test/always-pass', params: [] }] })
    const sAcc  = parser.parseLwJson({ blocks: [{ type: 'action', ref: 'test/accumulator', params: ['3'] }] })
    runner.addMission({ id: 'quick',  scripts: sPass })
    runner.addMission({ id: 'slow',   scripts: sAcc })

    runner.dispatch('TEST_TRIGGER', {})   // quick completes; slow → stage 1
    expect(completed).toContain('quick')
    expect(completed).not.toContain('slow')
    expect(runner.getIntermediateResult('slow').intermediateStage).toBe(1)

    runner.dispatch('TEST_TRIGGER', {})   // slow → stage 2
    runner.dispatch('TEST_TRIGGER', {})   // slow → stage 3 → complete
    expect(completed).toContain('slow')
    expect(completed).toHaveLength(2)
  })

  it('removeMission clears only that mission\'s intermediate state', () => {
    const { parser, runner } = makeEnv()

    const sA = parser.parseLwJson({ blocks: [{ type: 'action', ref: 'test/accumulator', params: ['5'] }] })
    const sB = parser.parseLwJson({ blocks: [{ type: 'action', ref: 'test/accumulator', params: ['5'] }] })
    runner.addMission({ id: 'rem-A', scripts: sA })
    runner.addMission({ id: 'rem-B', scripts: sB })

    runner.dispatch('TEST_TRIGGER', {})
    runner.dispatch('TEST_TRIGGER', {})
    expect(runner.getIntermediateResult('rem-A').intermediateStage).toBe(2)
    expect(runner.getIntermediateResult('rem-B').intermediateStage).toBe(2)

    runner.removeMission('rem-A')
    expect(runner.getIntermediateResult('rem-A')).toBeNull()    // cleared
    expect(runner.getIntermediateResult('rem-B').intermediateStage).toBe(2) // untouched

    // rem-A no longer receives dispatches
    runner.dispatch('TEST_TRIGGER', {})
    expect(runner.getIntermediateResult('rem-A')).toBeNull()
    expect(runner.getIntermediateResult('rem-B').intermediateStage).toBe(3)
  })

  it('two dict-mode missions with the same trigger accumulate dictionaries independently', () => {
    const { parser, runner, completed } = makeEnv()

    const s1 = parser.parseLwJson({ blocks: [{ type: 'action', ref: 'test/accumulator-dict', params: ['3'] }] })
    const s2 = parser.parseLwJson({ blocks: [{ type: 'action', ref: 'test/accumulator-dict', params: ['3'] }] })
    runner.addMission({ id: 'd1', scripts: s1 })
    runner.addMission({ id: 'd2', scripts: s2 })

    runner.dispatch('TEST_TRIGGER', { key: 'x' })
    runner.dispatch('TEST_TRIGGER', { key: 'y' })

    const ir1 = runner.getIntermediateResult('d1')
    const ir2 = runner.getIntermediateResult('d2')

    // Both missions independently see the same two events
    expect(ir1.intermediateData).toEqual({ x: 1, y: 1 })
    expect(ir2.intermediateData).toEqual({ x: 1, y: 1 })
    expect(ir1).not.toBe(ir2)  // distinct objects — not shared references
  })
})
