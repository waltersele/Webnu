import fs from 'node:fs';
import path from 'node:path';
import { config } from '../config.js';

export function createLogger() {
  const logsDir = path.join(config.rootDir, 'logs');
  fs.mkdirSync(logsDir, { recursive: true });
  const date = new Date().toISOString().slice(0, 10);
  const logPath = path.join(logsDir, `capture-${date}.jsonl`);

  function write(entry) {
    const line = JSON.stringify({ ...entry, at: new Date().toISOString() }) + '\n';
    fs.appendFileSync(logPath, line, 'utf8');
  }

  return { logPath, write };
}

export function printSummary(results) {
  const ok = results.filter((r) => r.status === 'success').length;
  const failed = results.filter((r) => r.status === 'failed').length;
  const skipped = results.filter((r) => r.status === 'skipped').length;

  console.log('\n--- Resumen ---');
  console.log(`Éxito: ${ok} | Fallo: ${failed} | Omitidos: ${skipped}`);
  for (const row of results) {
    const prefix = row.status === 'success' ? 'OK' : row.status === 'skipped' ? 'SKIP' : 'FAIL';
    console.log(`[${prefix}] ${row.restaurant_name} — ${row.message || row.source_url}`);
    if (row.public_url) {
      console.log(`       ${row.public_url}`);
    }
  }
}
