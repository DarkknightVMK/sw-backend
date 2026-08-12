import { MissionResult } from './MissionResult.js'

export class IntermediateMissionResult extends MissionResult {
  static TYPE = 'i'

  // Constructor arg order (confirmed from MM bundle):
  //   intermediateStage  — current accumulated count
  //   intermediateData   — per-key dictionary or null
  //   timeElapsedNotificationDuration — ms, 0 means no timer notification
  //   stageTotal         — target count, -1 means unlimited/server-side
  constructor(
    intermediateStage,
    intermediateData = null,
    timeElapsedNotificationDuration = 0,
    stageTotal = -1,
  ) {
    super(false) // an intermediate result is always not-yet-complete
    this._intermediateStage = intermediateStage
    this._intermediateData = intermediateData
    this._timeElapsedNotificationDuration = timeElapsedNotificationDuration
    this._stageTotal = stageTotal
  }

  get intermediateStage() { return this._intermediateStage }
  get intermediateData() { return this._intermediateData }
  get timeElapsedNotificationDuration() { return this._timeElapsedNotificationDuration }
  get stageTotal() { return this._stageTotal }

  // Serialises to <r t="i" p="stage" c="total"[><d k="key" v="val"/>...</r>]
  toXMLString() {
    const open = `<r t="${IntermediateMissionResult.TYPE}" p="${this._intermediateStage}" c="${this._stageTotal}"`
    const entries = this._intermediateData ? Object.entries(this._intermediateData) : []
    if (entries.length === 0) return open + '/>'
    const children = entries.map(([k, v]) => `<d k="${k}" v="${v}"/>`).join('')
    return `${open}>${children}</r>`
  }

  static fromXMLString(xmlStr) {
    const pMatch = xmlStr.match(/\bp="([^"]*)"/)
    const cMatch = xmlStr.match(/\bc="([^"]*)"/)
    const stage = pMatch ? Number(pMatch[1]) : 0
    const total = cMatch ? Number(cMatch[1]) : -1
    const data = {}
    const dRe = /k="([^"]*?)"\s+v="([^"]*?)"/g
    let m
    while ((m = dRe.exec(xmlStr)) !== null) data[m[1]] = Number(m[2])
    return new IntermediateMissionResult(stage, Object.keys(data).length ? data : null, 0, total)
  }
}
