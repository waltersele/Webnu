import fs from 'node:fs';
import path from 'node:path';
import { config } from '../config.js';
import { fetchSource } from '../extract/fetchUrl.js';
import { extractTextFromPdf } from '../extract/pdfExtract.js';
import { normalizeHtmlText } from '../extract/htmlExtract.js';
import { parseMenuWithGemini } from '../parse/geminiParser.js';
import { pushDemoToWebnu } from '../push/webnuClient.js';

/**
 * @param {{ restaurant_name: string, source_url: string, logo_url?: string, row: number, batchId: string, dryRun: boolean }} input
 */
export async function processOne(input) {
  const { restaurant_name, source_url, logo_url, row, batchId, dryRun } = input;

  if (!restaurant_name?.trim()) {
    throw new Error('restaurant_name vacío');
  }
  if (!source_url?.trim()) {
    throw new Error('source_url vacío');
  }

  const fetched = await fetchSource(source_url.trim());
  let rawText;
  if (fetched.kind === 'pdf') {
    rawText = await extractTextFromPdf(fetched.buffer);
  } else {
    rawText = normalizeHtmlText(fetched.text);
  }

  const menu = await parseMenuWithGemini(rawText);

  const payload = {
    restaurant_name: restaurant_name.trim(),
    sections: menu.sections,
    source_meta: {
      capture_worker: 'menu-capture-worker',
      source_url: source_url.trim(),
      final_url: fetched.finalUrl,
      batch_id: batchId,
      row,
    },
  };

  if (logo_url?.trim()) {
    payload.logo_url = logo_url.trim();
  }

  if (dryRun) {
    saveArtifact(row, restaurant_name, { payload, rawTextPreview: rawText.slice(0, 500) });
    return {
      status: 'skipped',
      restaurant_name,
      source_url,
      message: 'dry-run: no POST a Webnu',
      payload,
    };
  }

  const webnuResponse = await pushDemoToWebnu(payload);

  return {
    status: 'success',
    restaurant_name,
    source_url,
    message: 'demo creada',
    public_url: webnuResponse?.public_url,
    claim_url: webnuResponse?.claim_url,
    id: webnuResponse?.id,
  };
}

function saveArtifact(row, name, data) {
  const safeName = String(name).replace(/[^\w.-]+/g, '_').slice(0, 40);
  const dir = path.join(config.rootDir, 'artifacts', `row-${row}-${safeName}`);
  fs.mkdirSync(dir, { recursive: true });
  fs.writeFileSync(path.join(dir, 'result.json'), JSON.stringify(data, null, 2), 'utf8');
}
