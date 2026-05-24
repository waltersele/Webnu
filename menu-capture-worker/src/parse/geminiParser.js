import { config } from '../config.js';
import { validateMenuJson } from './validateMenu.js';

const MENU_PROMPT = `Eres un asistente que digitaliza cartas de restaurante en español.
Devuelve SOLO un JSON válido con esta estructura: {"sections":[{"name":"Nombre sección","products":[{"name":"Plato","description":"","price_unit":"12,50","price_portion":"","allergens":[]}]}]}
Extrae TODAS las secciones y platos visibles. Precios en formato español (12,50) sin €.
En "allergens" incluye solo alérgenos que aparezcan explícitos en la carta (iconos, leyenda o texto): Gluten, Crustáceos, Huevos, Pescados, Cacahuetes, Soja, Lácteos, Frutos secos, Apio, Mostaza, Sésamo, Sulfitos, Altramuz, Moluscos. Array vacío si no hay información.
No inventes platos ni alérgenos. Si no hay precio claro, price_unit vacío.`;

const responseSchema = {
  type: 'object',
  properties: {
    sections: {
      type: 'array',
      items: {
        type: 'object',
        properties: {
          name: { type: 'string' },
          products: {
            type: 'array',
            items: {
              type: 'object',
              properties: {
                name: { type: 'string' },
                description: { type: 'string' },
                price_unit: { type: 'string' },
                price_portion: { type: 'string' },
                allergens: { type: 'array', items: { type: 'string' } },
              },
              required: ['name'],
            },
          },
        },
        required: ['name', 'products'],
      },
    },
  },
  required: ['sections'],
};

function extractJsonFromText(text) {
  const match = text.match(/\{[\s\S]*"sections"[\s\S]*\}/);
  return match ? match[0] : text;
}

/**
 * @param {string} rawText
 */
export async function parseMenuWithGemini(rawText) {
  const url = `${config.gemini.baseUrl}/models/${config.gemini.model}:generateContent`;
  const userText = `${MENU_PROMPT}\n\n--- TEXTO DE LA CARTA ---\n${rawText}`;

  const payload = {
    contents: [{ parts: [{ text: userText }] }],
    generationConfig: {
      temperature: 0.2,
      responseMimeType: 'application/json',
      responseSchema,
    },
  };

  let lastError = null;
  for (let attempt = 0; attempt < 3; attempt++) {
    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'x-goog-api-key': config.gemini.apiKey,
        },
        body: JSON.stringify(payload),
      });

      if (!response.ok) {
        const body = await response.text();
        if ([429, 500, 502, 503, 504].includes(response.status) && attempt < 2) {
          await sleep(1000 * (attempt + 1));
          continue;
        }
        throw new Error(`Gemini HTTP ${response.status}: ${body.slice(0, 300)}`);
      }

      const data = await response.json();
      const text = extractTextFromResponse(data);
      if (!text) {
        throw new Error('Gemini no devolvió texto');
      }

      const jsonText = extractJsonFromText(text);
      const parsed = JSON.parse(jsonText);
      return validateMenuJson(parsed);
    } catch (error) {
      lastError = error;
      if (attempt < 2 && isRetryable(error)) {
        await sleep(1000 * (attempt + 1));
        continue;
      }
      throw error;
    }
  }

  throw lastError || new Error('Error desconocido en Gemini');
}

function extractTextFromResponse(body) {
  for (const candidate of body.candidates || []) {
    for (const part of candidate.content?.parts || []) {
      if (part.text) {
        return part.text;
      }
    }
  }
  return null;
}

function isRetryable(error) {
  const msg = String(error?.message || error);
  return /HTTP 429|HTTP 5\d\d|fetch failed/i.test(msg);
}

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}
