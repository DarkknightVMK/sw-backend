import { TriggerBusInterface } from './TriggerBusInterface.js'
import { useUserStore }        from '@/stores/user.js'

// Concrete TriggerBusInterface that reads live game state.
// getSpaceInfo: () => { spaceId, name, ownerId, modelId }
// keyStore: MissionKeyStore instance (from the missions store)
export class LiveTriggerContext extends TriggerBusInterface {
  // getSpaceAvatars: () => array of { id, firstName?, lastName?, fullName?, motionType? }
  //                  for other avatars in the space (excludes local).
  // getHomeSpaceIds: () => array of space ids the local user owns.
  constructor(keyStore, getSpaceInfo, getSpaceAvatars = () => [], getHomeSpaceIds = () => []) {
    super()
    this._keyStore        = keyStore
    this._getSpaceInfo    = getSpaceInfo
    this._getSpaceAvatars = getSpaceAvatars
    this._getHomeSpaceIds = getHomeSpaceIds
  }

  getLocalAvatarId() {
    const user = useUserStore()
    return String(user.defaultAvatar?.id ?? '')
  }

  getLocalAvatarMissionKeys() {
    return this._keyStore.ids()
  }

  getCurrentSpace() {
    return this._getSpaceInfo()
  }

  getSpaceAvatars() {
    return this._getSpaceAvatars() ?? []
  }

  getHomeSpaceIds() {
    return this._getHomeSpaceIds() ?? []
  }
}
