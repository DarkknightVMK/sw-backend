// Block definition shape:
//   { type, name, group, join, sort, triggers: string[], evaluate(action, worldInfo, params, intermediateResult) }
//
// evaluate() must return MissionResult | IntermediateMissionResult | null

export class BlockRegistry {
  constructor() {
    this._blocks = new Map()
  }

  register(ref, definition) {
    if (!ref || typeof ref !== 'string') throw new Error('Block ref must be a non-empty string')
    if (typeof definition.evaluate !== 'function') throw new Error(`Block "${ref}" must have evaluate()`)
    if (!Array.isArray(definition.triggers)) throw new Error(`Block "${ref}" must have a triggers array`)
    this._blocks.set(ref, {
      type:           definition.type           ?? 'action',
      name:           definition.name           ?? ref,
      group:          definition.group          ?? null,
      join:           definition.join           ?? null,
      sort:           definition.sort           ?? 0,
      triggers:       [...definition.triggers],
      evaluate:       definition.evaluate,
      getDescription: definition.getDescription ?? null,
      paramSchema:    definition.paramSchema    ?? [],
    })
  }

  resolve(ref) {
    return this._blocks.get(ref) ?? null
  }

  get size() { return this._blocks.size }

  // Returns [[ref, definition], ...] for introspection
  list() { return [...this._blocks.entries()] }
}
