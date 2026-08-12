import { WorldMissionInfo }  from './WorldMissionInfo.js'
import { AvatarMissionInfo } from './AvatarMissionInfo.js'

// Build a WorldMissionInfo snapshot from a TriggerBusInterface context.
// CRITICAL: missionKeys is only populated for the local avatar.
// Per internals doc §7: "only populated for the local user avatar
// (calls avatar.missionKeyIds())". Non-local avatars get [].
export function buildWorldInfo(context, date = new Date()) {
  const space       = context.getCurrentSpace()
  const localId     = context.getLocalAvatarId()
  const missionKeys = context.getLocalAvatarMissionKeys()
  const others      = context.getSpaceAvatars()

  // "Home" = the current space is one the local user owns.
  const homeIds        = (context.getHomeSpaceIds?.() ?? []).map(String)
  const currentSpaceId = String(space?.spaceId ?? '')
  const homeSpaceId    = homeIds.includes(currentSpaceId) ? currentSpaceId : ''

  const localAvatar = new AvatarMissionInfo(
    localId, '', '', '', homeSpaceId,
    AvatarMissionInfo.MOTION_TYPE_STANDING,
    null, [], missionKeys, [], null, null, null, null, null,
  )

  const spaceAvatars = others.map(a => new AvatarMissionInfo(
    a.id, a.firstName ?? '', a.lastName ?? '', a.fullName ?? '', '',
    a.motionType ?? AvatarMissionInfo.MOTION_TYPE_STANDING,
    null, [], [], [], null, null, null, null, null,  // missionKeys = [] for non-local
  ))

  return new WorldMissionInfo(
    localId, localAvatar,
    space?.spaceId  ?? '', space?.ownerId  ?? '',
    space?.modelId  ?? '', space?.name     ?? '',
    spaceAvatars, [],
    date.getTime(), date.getDay(), date.getMinutes(),
    date.getHours(), date.getDate(), date.getMonth(), date.getFullYear(),
  )
}
