// MissionMissionInfo (V class in MM bundle, offset 1346857).
// Snapshot of the mission's own metadata at evaluation time.
export class MissionMissionInfo {
  constructor(id, title, chainId, creatorId) {
    this.id        = id
    this.title     = title     ?? ''
    this.chainId   = chainId   ?? id
    this.creatorId = creatorId ?? null
  }

  clone() { return Object.assign(Object.create(MissionMissionInfo.prototype), this) }
}
