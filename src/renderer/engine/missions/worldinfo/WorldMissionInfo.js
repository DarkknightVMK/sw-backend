// WorldMissionInfo (G class in MM bundle, offset 1347239).
// Snapshot of space + time at the moment a trigger fires.
export class WorldMissionInfo {
  // Day-of-week constants (matches Date.getDay())
  static DAY_SUNDAY    = 0
  static DAY_MONDAY    = 1
  static DAY_TUESDAY   = 2
  static DAY_WEDNESDAY = 3
  static DAY_THURSDAY  = 4
  static DAY_FRIDAY    = 5
  static DAY_SATURDAY  = 6

  // Month constants (matches Date.getMonth())
  static MONTH_JANUARY   = 0
  static MONTH_FEBRUARY  = 1
  static MONTH_MARCH     = 2
  static MONTH_APRIL     = 3
  static MONTH_MAY       = 4
  static MONTH_JUNE      = 5
  static MONTH_JULY      = 6
  static MONTH_AUGUST    = 7
  static MONTH_SEPTEMBER = 8
  static MONTH_OCTOBER   = 9
  static MONTH_NOVEMBER  = 10
  static MONTH_DECEMBER  = 11

  constructor(userId, avatarInfo, currentSpaceId, currentSpaceOwnerId,
              currentSpaceModelId, currentSpaceName,
              currentSpaceAvatars, currentSpacePets,
              time, timeDay, timeMinute, timeHour, timeDate, timeMonth, timeYear) {
    this.userId              = userId
    this.avatarInfo          = avatarInfo
    this.currentSpaceId      = currentSpaceId      ?? ''
    this.currentSpaceOwnerId = currentSpaceOwnerId ?? ''
    this.currentSpaceModelId = currentSpaceModelId ?? ''
    this.currentSpaceName    = currentSpaceName    ?? ''
    this.currentSpaceAvatars = currentSpaceAvatars ?? []
    this.currentSpacePets    = currentSpacePets    ?? []
    this.time                = time
    this.timeDay             = timeDay
    this.timeMinute          = timeMinute
    this.timeHour            = timeHour
    this.timeDate            = timeDate
    this.timeMonth           = timeMonth
    this.timeYear            = timeYear
  }

  get avatarId() { return this.avatarInfo?.id ?? null }

  clone() { return Object.assign(Object.create(WorldMissionInfo.prototype), this) }
}
