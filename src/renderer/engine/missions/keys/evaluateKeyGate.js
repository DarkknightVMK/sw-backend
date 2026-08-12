// Pure gate helper: evaluates mission-key preconditions.
// Called by the missionkeys constraint block (next step); lives here so the block is
// a thin wrapper rather than a reimplementation.
//
// store:          MissionKeyStore instance
// hasKeysCsv:     comma-separated key names; ALL must be present and non-expired
// withoutKeysCsv: comma-separated key names; NONE may be present (non-expired)
// now:            injectable timestamp for tests (defaults to Date.now())
//
// Matching is exact-string (not pattern). Empty/whitespace entries are ignored.
// Both lists are checked across ALL chainIds — gate does not scope by chain.
export function evaluateKeyGate(store, hasKeysCsv = '', withoutKeysCsv = '', now = Date.now()) {
  const required  = parseCsv(hasKeysCsv)
  const forbidden = parseCsv(withoutKeysCsv)

  for (const key of required) {
    if (!store.has(key, now)) return false
  }
  for (const key of forbidden) {
    if (store.has(key, now)) return false
  }
  return true
}

function parseCsv(csv) {
  if (!csv || !csv.trim()) return []
  return csv.split(',').map(s => s.trim()).filter(Boolean)
}
