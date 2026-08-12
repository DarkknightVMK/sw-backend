// Runtime parameter sentinels.
// When a block param holds one of these values, MissionRunner._runScript() replaces it
// with the live mission/subtask ID at evaluation time — matching MM's
// UserDefinedMissionScript.PARAMETER_MISSION_ID / PARAMETER_SUBTASK_ID behaviour (§4).
//
// MM stores these as {id:"id"} / {id:"subtaskId"} object refs checked by identity.
// In LW params are strings, so we use reserved string sentinels checked with ===.
export const PARAMETER_MISSION_ID = '__lw_mission_id__'
export const PARAMETER_SUBTASK_ID = '__lw_subtask_id__'
