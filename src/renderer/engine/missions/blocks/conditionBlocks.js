import { MissionResult }     from '../core/MissionResult.js'
import { WorldMissionInfo }  from '../worldinfo/WorldMissionInfo.js'

// "Extra Conditions" constraint blocks. Constraints have empty triggers and
// evaluate against the worldInfo snapshot (§7). They pass/fail the whole task.

function rx(v) { return new RegExp(String(v ?? ''), 'i') }

// ── #1 Home ──────────────────────────────────────────────────────────────────
// Passes when the current space is the local avatar's home space.
export const homeConstraintBlock = {
  type: 'constraint', name: 'Home', group: 'location', join: 'or', sort: 0, triggers: [],
  paramSchema: [],
  evaluate(_action, worldInfo) {
    const home = String(worldInfo?.avatarInfo?.homeSpaceId ?? '')
    const here = String(worldInfo?.currentSpaceId ?? '')
    if (home && here && home === here) return new MissionResult(true)
    return new MissionResult(false, 'not at home')
  },
  getDescription() { return 'while in your own home space' },
}

// ── #3 Number of people present ──────────────────────────────────────────────
export const peopleCountConstraintBlock = {
  type: 'constraint', name: 'Number of people present', group: 'presence', join: null, sort: 0, triggers: [],
  paramSchema: [
    { label: 'Minimum other avatars', type: 'number', default: '1', hint: 'Others in the space (excludes you)' },
  ],
  evaluate(_action, worldInfo, params) {
    const min = Math.max(0, parseInt(params?.[0], 10) || 0)
    if ((worldInfo?.currentSpaceAvatars ?? []).length >= min) return new MissionResult(true)
    return new MissionResult(false, 'not enough people')
  },
  getDescription(p) { const m = parseInt(p?.[0], 10) || 0; return `with at least ${m} other${m !== 1 ? 's' : ''} present` },
}

// ── #4 Specific people present ───────────────────────────────────────────────
// Comma-separated names; EVERY named person (regex) must be present.
export const peopleSpecificConstraintBlock = {
  type: 'constraint', name: 'Specific people present', group: 'presence', join: null, sort: 1, triggers: [],
  paramSchema: [
    { label: 'Names (comma-separated)', type: 'csv', default: '', hint: 'All listed people must be present' },
  ],
  evaluate(_action, worldInfo, params) {
    const wanted = String(params?.[0] ?? '').split(',').map(s => s.trim()).filter(Boolean)
    if (!wanted.length) return new MissionResult(true)
    const present = (worldInfo?.currentSpaceAvatars ?? []).map(a => String(a.fullName ?? ''))
    const allHere = wanted.every(w => present.some(name => name.match(rx(w))))
    if (allHere) return new MissionResult(true)
    return new MissionResult(false, 'people missing')
  },
  getDescription(p) { const n = String(p?.[0] ?? '').trim(); return n ? `with ${n} present` : 'with specific people present' },
}

// ── #5 Specific time (of day) ────────────────────────────────────────────────
// params: [from "HH:MM", to "HH:MM"]. Passes when local time is within [from,to].
function toMins(hhmm, fallback) {
  const m = String(hhmm ?? '').match(/^(\d{1,2}):?(\d{2})?$/)
  if (!m) return fallback
  return (parseInt(m[1], 10) || 0) * 60 + (parseInt(m[2], 10) || 0)
}
export const timeOfDayConstraintBlock = {
  type: 'constraint', name: 'Specific time', group: 'time', join: null, sort: 0, triggers: [],
  paramSchema: [
    { label: 'From (HH:MM)', type: 'string', default: '00:00', hint: '24-hour' },
    { label: 'To (HH:MM)',   type: 'string', default: '23:59', hint: '24-hour' },
  ],
  evaluate(_action, worldInfo, params) {
    const now  = (worldInfo?.timeHour ?? 0) * 60 + (worldInfo?.timeMinute ?? 0)
    const from = toMins(params?.[0], 0)
    const to   = toMins(params?.[1], 24 * 60)
    const ok   = from <= to ? (now >= from && now <= to) : (now >= from || now <= to) // wraps midnight
    return ok ? new MissionResult(true) : new MissionResult(false, 'outside time window')
  },
  getDescription(p) { return `between ${p?.[0] ?? '00:00'} and ${p?.[1] ?? '23:59'}` },
}

// ── #6 Specific day of week ──────────────────────────────────────────────────
// params[0]: comma-separated day names or numbers (0=Sun … 6=Sat).
const DAY_NAMES = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat']
export const dayOfWeekConstraintBlock = {
  type: 'constraint', name: 'Specific day of week', group: 'time', join: null, sort: 1, triggers: [],
  paramSchema: [
    { label: 'Days (e.g. Mon,Fri)', type: 'csv', default: '', hint: 'Names or 0-6 (0=Sun); empty = any day' },
  ],
  evaluate(_action, worldInfo, params) {
    const raw = String(params?.[0] ?? '').split(',').map(s => s.trim().toLowerCase()).filter(Boolean)
    if (!raw.length) return new MissionResult(true)
    const today = worldInfo?.timeDay ?? new Date().getDay()
    const set = new Set()
    for (const tok of raw) {
      if (/^\d+$/.test(tok)) set.add(parseInt(tok, 10))
      else { const idx = DAY_NAMES.findIndex(d => tok.startsWith(d)); if (idx >= 0) set.add(idx) }
    }
    return set.has(today) ? new MissionResult(true) : new MissionResult(false, 'wrong day')
  },
  getDescription(p) { const d = String(p?.[0] ?? '').trim(); return d ? `on ${d}` : 'on any day' },
}

// ── #7 Specific date ─────────────────────────────────────────────────────────
// params[0]: "MM/DD" or "YYYY-MM-DD". Year optional (any year when omitted).
export const dateConstraintBlock = {
  type: 'constraint', name: 'Specific date', group: 'time', join: null, sort: 2, triggers: [],
  paramSchema: [
    { label: 'Date (MM/DD or YYYY-MM-DD)', type: 'string', default: '', hint: 'Empty = any date' },
  ],
  evaluate(_action, worldInfo, params) {
    const s = String(params?.[0] ?? '').trim()
    if (!s) return new MissionResult(true)
    let y = null, mo, d
    let m = s.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/)
    if (m) { y = +m[1]; mo = +m[2]; d = +m[3] }
    else { m = s.match(/^(\d{1,2})[/](\d{1,2})$/); if (!m) return new MissionResult(false, 'bad date'); mo = +m[1]; d = +m[2] }
    const curMonth = (worldInfo?.timeMonth ?? 0) + 1 // timeMonth is 0-based (§7)
    const curDate  = worldInfo?.timeDate ?? 0
    const curYear  = worldInfo?.timeYear ?? 0
    if (mo === curMonth && d === curDate && (y === null || y === curYear)) return new MissionResult(true)
    return new MissionResult(false, 'wrong date')
  },
  getDescription(p) { const d = String(p?.[0] ?? '').trim(); return d ? `on ${d}` : 'on any date' },
}
