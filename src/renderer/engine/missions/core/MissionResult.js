export class MissionResult {
  static TYPE = 'm'

  constructor(completed, description = null) {
    this._completed = Boolean(completed)
    this._completionDescription = description ?? null
  }

  get completed() { return this._completed }
  get completionDescription() { return this._completionDescription }

  // Serialises to <r t="m" c="true|false"/>
  toXMLString() {
    return `<r t="${MissionResult.TYPE}" c="${this._completed}"/>`
  }

  static fromXMLString(xmlStr) {
    const m = xmlStr.match(/\bc="([^"]*)"/)
    return new MissionResult(m ? m[1] === 'true' : false)
  }
}
