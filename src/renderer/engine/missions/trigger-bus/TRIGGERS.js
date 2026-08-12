// Complete catalog of all 29 SW* trigger names.
// Sources: module 94805 (InternalMissionActionsAvatarSpace),
//          module 9871  (pet triggers),
//          module 56983 (InternalMissionActionsUser).
// Caveats sourced from mission_engine_internals.md §6.
//
// source values:
//   'client' — emitted via TriggerBus.emit()
//   'server' — server-authoritative; client must NOT fabricate these
//   'timer'  — fired by TriggerBus's internal 10s interval, not a raw game event
//
// Item-side mirror: SWAvatarUseConsumable also dispatches AVATAR_USES (see TriggerBus)

export const TRIGGERS = {
  // ── Avatar / Space (module 94805) ─────────────────────────────────────────
  SWAvatarUseConsumable:     { source: 'client' },
  SWAvatarMove:              { source: 'client' },
  SWAvatarAnimate:           { source: 'client' },
  SWAvatarLeaveSpace:        { source: 'client' },
  SWAvatarEnterSpace:        { source: 'client' },
  SWAvatarSelect:            { source: 'client' },
  SWAvatarEmote:             { source: 'client' },
  SWAvatarSpeech:            { source: 'client' },
  SWAvatarUseChair:          { source: 'client' },
  SWAvatarToggleItem:        { source: 'client' },

  // ── Pet (module 9871) ─────────────────────────────────────────────────────
  SWPetInteraction:          { source: 'client' },
  SWPetCommand:              { source: 'client' },
  SWPetEnterSpace:           { source: 'client' },
  SWPetLeaveSpace:           { source: 'client' },

  // ── User / Item (module 56983) ────────────────────────────────────────────
  SWSaveSpace:               { source: 'client' },
  SWPurchaseItem:            { source: 'client' },
  SWUpdateMission:           { source: 'client' },
  SWCompleteMission:         { source: 'client' },
  SWTryOnItem:               { source: 'client' },
  SWSelectItem:              { source: 'client' },
  SWAddItem:                 { source: 'client' },
  SWMoveItem:                { source: 'client' },
  SWPressInterfaceButton:    { source: 'client' },
  SWPressChangeAvatarOutfit: { source: 'client' },
  SWPressChangeAvatarLook:   { source: 'client' },
  // registerSWGiftWrapTimerComplete is EMPTY in the MM bundle (no-op handler)
  SWGiftWrapTimerComplete:   { source: 'client' },

  // ── Timer-internal ────────────────────────────────────────────────────────
  // Fired by a 10s internal timer loop; not driven by a raw game event.
  SWTimeElapsed:             { source: 'timer' },

  // ── Server-authoritative ─────────────────────────────────────────────────
  // Per §6: "explicitly skipped in performInitialTriggerChecks — no
  // registerSW* call in the JS client". Must NOT be fabricated by the client.
  SWOwnItem:                 { source: 'server' },
  SWOwnBundle:               { source: 'server' },
}

export const SERVER_ONLY  = new Set(['SWOwnItem', 'SWOwnBundle'])
export const TIMER_DRIVEN = new Set(['SWTimeElapsed'])

// Item-side mirror trigger name (not a SW* mission trigger; dispatched in
// parallel with SWAvatarUseConsumable — see internals §8).
export const AVATAR_USES = 'AVATAR_USES'
