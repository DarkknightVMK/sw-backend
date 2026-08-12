// AvatarMissionInfo (z class in MM bundle, offset 1343303).
// missionKeys is populated ONLY for the local user avatar —
// non-local avatars always receive an empty array.
export class AvatarMissionInfo {
  static MOTION_TYPE_WALKING  = 'walking'
  static MOTION_TYPE_STANDING = 'standing'
  static MOTION_TYPE_SITTING  = 'sitting'
  static PET_TYPE_CAT = 'cat'
  static PET_TYPE_DOG = 'dog'

  constructor(id, firstName, lastName, fullName, homeSpaceId, motionType, emote,
              actions, missionKeys, xpLevels, sittingChair, interactingWidget,
              petId, petType, petName) {
    this.id                = id
    this.firstName         = firstName         ?? ''
    this.lastName          = lastName          ?? ''
    this.fullName          = fullName          ?? ''
    this.homeSpaceId       = homeSpaceId       ?? ''
    this.motionType        = motionType        ?? AvatarMissionInfo.MOTION_TYPE_STANDING
    this.emote             = emote             ?? null
    this.actions           = actions           ?? []
    this.missionKeys       = missionKeys       ?? []
    this.xpLevels          = xpLevels          ?? []
    this.sittingChair      = sittingChair      ?? null
    this.interactingWidget = interactingWidget ?? null
    this.petId             = petId             ?? null
    this.petType           = petType           ?? null
    this.petName           = petName           ?? null
  }

  clone() { return Object.assign(Object.create(AvatarMissionInfo.prototype), this) }
}
