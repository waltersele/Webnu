import dotenv from 'dotenv';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const rootDir = path.resolve(__dirname, '..');

dotenv.config({ path: path.join(rootDir, '.env') });

function intEnv(name, fallback) {
  const raw = process.env[name];
  if (raw === undefined || raw === '') {
    return fallback;
  }
  const value = Number.parseInt(raw, 10);
  return Number.isFinite(value) ? value : fallback;
}

export const config = {
  rootDir,
  gemini: {
    apiKey: (process.env.GEMINI_API_KEY || '').trim(),
    model: (process.env.GEMINI_MODEL || 'gemini-2.5-flash-lite').trim(),
    baseUrl: (process.env.GEMINI_BASE_URL || 'https://generativelanguage.googleapis.com/v1beta').replace(/\/$/, ''),
  },
  webnu: {
    baseUrl: (process.env.WEBNU_BASE_URL || 'http://127.0.0.1:8000').replace(/\/$/, ''),
    apiKey: (process.env.WEBNU_DEMO_API_KEY || process.env.PRE_ALTA_INGEST_KEY || '').trim(),
    postRetries: intEnv('WEBNU_POST_RETRIES', 2),
    postRetryDelayMs: intEnv('WEBNU_POST_RETRY_DELAY_MS', 1500),
  },
  fetch: {
    timeoutMs: intEnv('FETCH_TIMEOUT_MS', 30000),
    maxBytes: intEnv('FETCH_MAX_BYTES', 10 * 1024 * 1024),
  },
  concurrency: Math.max(1, intEnv('CONCURRENCY', 1)),
};

export function assertWorkerConfig({ dryRun = false } = {}) {
  if (!config.gemini.apiKey) {
    throw new Error('Falta GEMINI_API_KEY en .env');
  }
  if (!dryRun && !config.webnu.apiKey) {
    throw new Error('Falta WEBNU_DEMO_API_KEY (o PRE_ALTA_INGEST_KEY) en .env');
  }
}
