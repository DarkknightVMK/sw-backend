// Integration interface consumed by TriggerBus to read live game state.
//
// The real implementation (TODO — next step) wires to SpaceView / engine:
//   - getLocalAvatarId()          → PlayerContext.instance.avatar.avatarId
//   - getLocalAvatarMissionKeys() → avatar.missionKeyIds()
//   - getCurrentSpace()           → SpaceService.currentSpace (spaceId/ownerId/modelId/name)
//   - getSpaceAvatars()           → array of { id, firstName, lastName, fullName, motionType }
//                                   for all avatars currently in the space (excluding local)
//
// For tests use MockTriggerContext (same directory).

export class TriggerBusInterface {
  getLocalAvatarId()          { throw new Error('TriggerBusInterface.getLocalAvatarId() not implemented') }
  getLocalAvatarMissionKeys() { throw new Error('TriggerBusInterface.getLocalAvatarMissionKeys() not implemented') }
  getCurrentSpace()           { throw new Error('TriggerBusInterface.getCurrentSpace() not implemented') }
  // Returns array of { id, firstName?, lastName?, fullName?, motionType? }
  getSpaceAvatars()           { throw new Error('TriggerBusInterface.getSpaceAvatars() not implemented') }
  // Space ids the local user owns — drives the "Home" (own-space) condition.
  // Concrete default so older contexts keep working; override to enable "Home".
  getHomeSpaceIds()           { return [] }
}
