// LW JSON mission format:
//   { "blocks": [ { "type", "ref", "params":[], "subtaskId"? } ] }
//
// MM XML mission format (inside a <scripts> element):
//   <script type="action" location="path/to/block.xml">
//     <param>value1</param>
//   </script>
//
// Both refer to the same concept: a sequence of typed, parameterised block refs.

export class CompositionParser {
  constructor(registry) {
    this._registry = registry
  }

  // Parse LW JSON → array of resolved script entries (ready for MissionRunner)
  parseLwJson(json) {
    return (json.blocks ?? []).map((b, i) => this._resolveBlock(b, i))
  }

  _resolveBlock(blockDef, _index) {
    const def = this._registry.resolve(blockDef.ref)
    const params = blockDef.params ?? []
    const type = blockDef.type ?? def?.type ?? 'constraint'

    return {
      ref:       blockDef.ref,
      type,
      params,
      subtaskId: blockDef.subtaskId ?? null,
      definition: def,

      hasTrigger(triggerName) {
        return (def?.triggers ?? []).includes(triggerName)
      },

      evaluate(action, worldInfo, runtimeParams, intermediateResult) {
        if (!def) return null
        return def.evaluate(action, worldInfo, runtimeParams ?? params, intermediateResult)
      },
    }
  }

  // MM XML string → LW JSON
  mmXmlToLwJson(xmlStr) {
    const blocks = []
    // Attribute content regex allows "/" inside quoted values, e.g. location="path/to/file.xml"
    // Pattern: non-quote/slash/gt chars  |  double-quoted string  |  single-quoted string
    // \b prevents matching "<scripts>" — the wrapper element shares the prefix "<script"
    const scriptRe = /<script\b((?:[^"'/>]|"[^"]*"|'[^']*')*)(?:>([\s\S]*?)<\/script>|\/>)/g
    let m
    while ((m = scriptRe.exec(xmlStr)) !== null) {
      const attrStr = m[1] ?? ''
      const inner   = m[2] ?? ''
      const type     = this._attr(attrStr, 'type') ?? 'constraint'
      const location = this._attr(attrStr, 'location') ?? ''
      const ref      = location.replace(/\.(web\.)?xml$/, '')
      const params   = []
      const paramRe  = /<param[^>]*>([\s\S]*?)<\/param>/g
      let pm
      while ((pm = paramRe.exec(inner)) !== null) {
        params.push(decodeURIComponent(pm[1].trim()))
      }
      blocks.push({ type, ref, params })
    }
    return { blocks }
  }

  // LW JSON → MM XML string
  lwJsonToMmXml(json) {
    const lines = (json.blocks ?? []).map(b => {
      const location = `${b.ref}.xml`
      const type     = b.type ?? 'constraint'
      const paramLines = (b.params ?? []).map(p => `    <param>${encodeURIComponent(p)}</param>`)
      if (paramLines.length > 0) {
        return `  <script type="${type}" location="${location}">\n${paramLines.join('\n')}\n  </script>`
      }
      return `  <script type="${type}" location="${location}"/>`
    })
    return `<scripts>\n${lines.join('\n')}\n</scripts>`
  }

  _attr(attrStr, name) {
    const m = new RegExp(`\\b${name}="([^"]*)"`, 'i').exec(attrStr)
    return m ? m[1] : null
  }
}
