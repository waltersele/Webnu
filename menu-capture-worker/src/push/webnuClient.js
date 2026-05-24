import { config } from '../config.js';

/**
 * @param {{ restaurant_name: string, logo_url?: string, sections: unknown[], source_meta?: Record<string, unknown> }} payload
 */
export async function pushDemoToWebnu(payload) {
  const url = `${config.webnu.baseUrl}/api/v1/demos/create`;
  let lastError = null;

  for (let attempt = 0; attempt <= config.webnu.postRetries; attempt++) {
    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Webnu-Demo-Key': config.webnu.apiKey,
        },
        body: JSON.stringify(payload),
      });

      const bodyText = await response.text();
      let body = null;
      try {
        body = bodyText ? JSON.parse(bodyText) : null;
      } catch {
        body = { raw: bodyText };
      }

      if (!response.ok) {
        const message = body?.message || bodyText || `HTTP ${response.status}`;
        if (attempt < config.webnu.postRetries && response.status >= 500) {
          await sleep(config.webnu.postRetryDelayMs * (attempt + 1));
          continue;
        }
        throw new Error(`Webnu ${response.status}: ${message}`);
      }

      return body;
    } catch (error) {
      lastError = error;
      if (attempt < config.webnu.postRetries) {
        await sleep(config.webnu.postRetryDelayMs * (attempt + 1));
        continue;
      }
    }
  }

  throw lastError || new Error('No se pudo enviar a Webnu');
}

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}
