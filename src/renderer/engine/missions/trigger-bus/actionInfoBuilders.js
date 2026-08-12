// Per-trigger builders: rawEvent (game-layer object) → action.info shape.
// Field names match mission_engine_internals.md §6 / Appendix exactly.
// Raw event shapes are defined by TriggerBusInterface — the real game wiring
// (next step) must supply objects matching these extractors.

function avatarXML(avatar, _tag = 'avatar') {
  if (!avatar) return {}
  return {
    $avatar_id:         String(avatar.id          ?? ''),
    $avatar_first_name: String(avatar.firstName    ?? ''),
    $avatar_last_name:  String(avatar.lastName     ?? ''),
    $avatar_full_name:  String(avatar.fullName     ?? ''),
    $is_local:          String(avatar.isLocal      ?? false),
    $is_user:           String(avatar.isUser       ?? false),
    $is_pet:            'false',
    $is_npc:            'false',
  }
}

function itemModelXML(model) {
  return {
    $model_id:          String(model?.id          ?? ''),
    $model_name:        String(model?.name        ?? ''),
    $model_description: String(model?.description ?? ''),
    $model_tags:        String(model?.tags        ?? ''),
  }
}

function itemXML(item) {
  return {
    $item_id:           String(item?.uid             ?? ''),
    $model_id:          String(item?.model?.id        ?? ''),
    $model_name:        String(item?.model?.name      ?? ''),
    $model_description: String(item?.model?.description ?? ''),
    $model_tags:        String(item?.model?.tags      ?? ''),
  }
}

function locationXML(pos, angle = 0) {
  return { $x: pos?.x ?? 0, $y: pos?.y ?? 0, $z: pos?.z ?? 0, $angle: angle }
}

function petXML(pet) {
  return {
    $avatar_id:           String(pet?.id      ?? ''),
    $pet_name:            String(pet?.name    ?? ''),
    $pet_type:            String(pet?.type    ?? ''),
    $pet_owner_avatar_id: String(pet?.ownerId ?? ''),
  }
}

// Raw event shapes (what the game layer or test supplies):
//
//   SWAvatarUseConsumable: { scriptLocation, avatar, modelId, modelName, targetAvatar? }
//   SWAvatarMove:          { avatar, position }
//   SWAvatarAnimate:       { avatar, animName, start }
//   SWAvatarLeaveSpace /
//   SWAvatarEnterSpace /
//   SWAvatarSelect:        { avatar }
//   SWAvatarEmote:         { avatar, emote }
//   SWAvatarSpeech:        { avatar, text, whisper, whisperRecipient? }
//   SWAvatarUseChair:      { avatar, sitting, chair: { uid, modelId, modelName, description, tags, slots, slotsFilled } }
//   SWAvatarToggleItem:    { avatar, item: { uid, modelId, modelName, description, tags }, animation }
//   SWPetInteraction:      { avatarId, pet, interactionType }
//   SWPetCommand:          { commandType, pet }
//   SWPetEnterSpace /
//   SWPetLeaveSpace:       { pet }
//   SWSaveSpace:           { nameChanged, decorChanged, themeChanged, lightingChanged,
//                            decorOptions?, themeOptions?, lightingOptions? }
//   SWPurchaseItem:        { model: { id, name, description, tags }, quantity }
//   SWUpdateMission:       { missionId, subtaskId?, item, value }
//   SWCompleteMission:     { missionId, subtaskId? }
//   SWTryOnItem /
//   SWSelectItem:          { model }
//   SWAddItem:             { model, position, angle }
//   SWMoveItem:            { model, drag, rotate, oldPosition, oldAngle, newPosition, newAngle }
//   SWPressInterfaceButton:{ code }
//   SWPressChangeAvatarOutfit /
//   SWPressChangeAvatarLook: { avatar, isUserAvatar, isPetAvatar, isNpcAvatar }
//   SWTimeElapsed:         { startTime, duration }  (built by TriggerBus timer)
//   SWOwnItem:             { model }
//   SWOwnBundle:           { bundleId }

export const ACTION_INFO_BUILDERS = {

  SWAvatarUseConsumable(e) {
    return {
      avatar:  avatarXML(e.avatar),
      item:    { $model_id: String(e.modelId ?? ''), $model_name: String(e.modelName ?? '') },
      target:  avatarXML(e.targetAvatar, 'target'),
      script:  { location: String(e.scriptLocation ?? '') },
    }
  },

  SWAvatarMove(e) {
    return { avatar: avatarXML(e.avatar), pos: e.position ?? {} }
  },

  SWAvatarAnimate(e) {
    const start = Boolean(e.start)
    return {
      avatar:    avatarXML(e.avatar),
      animation: { $name: String(e.animName ?? ''), $start: start, $stop: !start },
    }
  },

  SWAvatarLeaveSpace(e) { return { avatar: avatarXML(e.avatar) } },
  SWAvatarEnterSpace(e) { return { avatar: avatarXML(e.avatar) } },
  SWAvatarSelect(e)     { return { avatar: avatarXML(e.avatar) } },

  SWAvatarEmote(e) {
    return { avatar: avatarXML(e.avatar), emote: e.emote ?? {} }
  },

  SWAvatarSpeech(e) {
    return {
      avatar:            avatarXML(e.avatar),
      $text:             String(e.text ?? ''),
      $whisper:          Boolean(e.whisper),
      $whisperRecipient: avatarXML(e.whisperRecipient, 'whisperRecipient'),
    }
  },

  SWAvatarUseChair(e) {
    const sit = Boolean(e.sitting)
    return {
      avatar:  avatarXML(e.avatar),
      action:  { $sit: sit, $stand: !sit },
      chair: {
        $item_id:           String(e.chair?.uid          ?? ''),
        $model_id:          String(e.chair?.modelId      ?? ''),
        $model_name:        String(e.chair?.modelName    ?? ''),
        $model_description: String(e.chair?.description  ?? ''),
        $model_tags:        String(e.chair?.tags         ?? ''),
        $model_slots:       String(e.chair?.slots        ?? ''),
        $slots_filled:      String(e.chair?.slotsFilled  ?? ''),
      },
    }
  },

  SWAvatarToggleItem(e) {
    return {
      avatar:    avatarXML(e.avatar),
      item: {
        $item_id:           String(e.item?.uid          ?? ''),
        $model_id:          String(e.item?.modelId      ?? ''),
        $model_name:        String(e.item?.modelName    ?? ''),
        $model_description: String(e.item?.description  ?? ''),
        $model_tags:        String(e.item?.tags         ?? ''),
      },
      animation: e.animation ?? null,
    }
  },

  SWPetInteraction(e) {
    return {
      avatar:      { avatar_id: String(e.avatarId ?? '') },  // just ID — not full avatarXML (per §6)
      pet:         petXML(e.pet),
      interaction: { type: String(e.interactionType ?? '') },
    }
  },

  SWPetCommand(e) {
    return { command: { type: String(e.commandType ?? '') }, pet: petXML(e.pet) }
  },

  SWPetEnterSpace(e) { return { pet: petXML(e.pet) } },
  SWPetLeaveSpace(e) { return { pet: petXML(e.pet) } },

  SWSaveSpace(e) {
    return {
      decor_options:    e.decorOptions    ?? {},
      theme_options:    e.themeOptions    ?? {},
      lighting_options: e.lightingOptions ?? {},
      changes: {
        $name_changed:     String(Boolean(e.nameChanged)),
        $decor_changed:    String(Boolean(e.decorChanged)),
        $theme_changed:    String(Boolean(e.themeChanged)),
        $lighting_changed: String(Boolean(e.lightingChanged)),
      },
    }
  },

  SWPurchaseItem(e) {
    return {
      item:     itemModelXML(e.model),
      purchase: { $quantity: String(e.quantity ?? 1) },
    }
  },

  SWUpdateMission(e) {
    const mission = { $mission_id: String(e.missionId ?? '') }
    if (e.subtaskId != null) mission.$subtask_id = String(e.subtaskId)
    return {
      mission,
      item:   itemXML(e.item),
      update: { $value: e.value ?? null },
    }
  },

  SWCompleteMission(e) {
    const mission = { $mission_id: String(e.missionId ?? '') }
    if (e.subtaskId != null) mission.$subtask_id = String(e.subtaskId)
    return { mission }
  },

  SWTryOnItem(e)  { return { item: itemModelXML(e.model) } },
  SWSelectItem(e) { return { item: itemModelXML(e.model) } },

  SWAddItem(e) {
    return {
      item:     itemModelXML(e.model),
      location: locationXML(e.position, e.angle),
    }
  },

  SWMoveItem(e) {
    return {
      item:         itemModelXML(e.model),
      old_location: locationXML(e.oldPosition, e.oldAngle),
      new_location: locationXML(e.newPosition, e.newAngle),
      action: {
        $drag:      String(Boolean(e.drag)),
        $rotate:    String(Boolean(e.rotate)),
        $old_angle: e.oldAngle ?? 0,
        $new_angle: e.newAngle ?? 0,
      },
    }
  },

  SWPressInterfaceButton(e) {
    return { button: { $code: String(e.code ?? '') } }
  },

  SWPressChangeAvatarOutfit(e) {
    return {
      avatar:     avatarXML(e.avatar),
      avatarinfo: {
        $is_user_avatar: String(Boolean(e.isUserAvatar)),
        $is_pet_avatar:  String(Boolean(e.isPetAvatar)),
        $is_npc_avatar:  String(Boolean(e.isNpcAvatar)),
      },
    }
  },

  SWPressChangeAvatarLook(e) {
    return {
      avatar:     avatarXML(e.avatar),
      avatarinfo: {
        $is_user_avatar: String(Boolean(e.isUserAvatar)),
        $is_pet_avatar:  String(Boolean(e.isPetAvatar)),
        $is_npc_avatar:  String(Boolean(e.isNpcAvatar)),
      },
    }
  },

  // registerSWGiftWrapTimerComplete is EMPTY in the MM bundle (no-op)
  SWGiftWrapTimerComplete(_e) { return {} },

  // Constructed by TriggerBus timer; rawEvent = { startTime, duration }
  SWTimeElapsed(e) {
    return { $startTime: e.startTime ?? 0, $endTime: e.duration ?? 0 }
  },

  // Server-authoritative. Shape not fully documented; provide minimal info.
  SWOwnItem(e)   { return { item: itemModelXML(e?.model) } },
  SWOwnBundle(e) { return { bundle: { $bundle_id: String(e?.bundleId ?? '') } } },
}
