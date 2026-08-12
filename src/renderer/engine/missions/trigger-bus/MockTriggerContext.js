import { TriggerBusInterface } from './TriggerBusInterface.js'

// Test-only implementation of TriggerBusInterface.
// config: {
//   localAvatarId?:  string
//   keyStore?:       MissionKeyStore  — when provided, getLocalAvatarMissionKeys() delegates to store.ids()
//   missionKeys?:    string[]         — plain array fallback (used when keyStore is absent)
//   space?:          { spaceId, ownerId, modelId, name }
//   spaceAvatars?:   Array<{ id, firstName?, lastName?, fullName?, motionType? }>
// }
export class MockTriggerContext extends TriggerBusInterface {
  constructor(config = {}) {
    super()
    this._localAvatarId = config.localAvatarId ?? 'local-1'
    this._keyStore      = config.keyStore      ?? null
    this._missionKeys   = config.missionKeys   ?? []
    this._space         = config.space ?? {
      spaceId: 'space-1', ownerId: 'owner-1', modelId: 'model-1', name: 'Test Space',
    }
    this._spaceAvatars  = config.spaceAvatars  ?? []
    this._homeSpaceIds  = config.homeSpaceIds  ?? []
  }

  getHomeSpaceIds() { return this._homeSpaceIds }

  getLocalAvatarId() { return this._localAvatarId }

  // If a MissionKeyStore is configured, delegate to store.ids() so tests get real expiry
  // semantics. Otherwise fall back to the plain string array (Step-2 compat path).
  getLocalAvatarMissionKeys() {
    return this._keyStore ? this._keyStore.ids() : this._missionKeys
  }

  getCurrentSpace()  { return this._space }
  getSpaceAvatars()  { return this._spaceAvatars }
}
