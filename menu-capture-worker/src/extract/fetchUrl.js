import { chromium } from 'playwright';
import { config } from '../config.js';

function isPdfUrl(url, contentType) {
  const lower = url.toLowerCase();
  if (lower.endsWith('.pdf') || lower.includes('.pdf?')) {
    return true;
  }
  return (contentType || '').toLowerCase().includes('application/pdf');
}

/**
 * @returns {Promise<{ kind: 'pdf' | 'html', buffer?: Buffer, text?: string, finalUrl: string, contentType: string }>}
 */
export async function fetchSource(url) {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    userAgent:
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
  });
  const page = await context.newPage();
  page.setDefaultTimeout(config.fetch.timeoutMs);

  try {
    const response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: config.fetch.timeoutMs });
    if (!response) {
      throw new Error('Sin respuesta HTTP');
    }

    const finalUrl = page.url();
    const contentType = response.headers()['content-type'] || '';

    if (isPdfUrl(finalUrl, contentType)) {
      const buffer = await response.body();
      if (buffer.length > config.fetch.maxBytes) {
        throw new Error(`PDF demasiado grande (${buffer.length} bytes)`);
      }
      return { kind: 'pdf', buffer, finalUrl, contentType };
    }

    const bodyText = await page.evaluate(() => {
      const clone = document.body?.cloneNode(true);
      if (!clone) {
        return '';
      }
      clone.querySelectorAll('script, style, noscript, svg').forEach((el) => el.remove());
      return (clone.innerText || clone.textContent || '').replace(/\s+\n/g, '\n').trim();
    });

    if (bodyText.length < 80) {
      const pdfLink = await page.evaluate(() => {
        const anchors = Array.from(document.querySelectorAll('a[href]'));
        const pdf = anchors.find((a) => /\.pdf(\?|$)/i.test(a.getAttribute('href') || ''));
        return pdf ? new URL(pdf.href, window.location.href).href : null;
      });
      if (pdfLink) {
        const pdfResponse = await page.goto(pdfLink, {
          waitUntil: 'domcontentloaded',
          timeout: config.fetch.timeoutMs,
        });
        if (pdfResponse) {
          const buffer = await pdfResponse.body();
          if (buffer.length > config.fetch.maxBytes) {
            throw new Error(`PDF demasiado grande (${buffer.length} bytes)`);
          }
          return { kind: 'pdf', buffer, finalUrl: pdfLink, contentType: 'application/pdf' };
        }
      }
    }

    return { kind: 'html', text: bodyText, finalUrl, contentType };
  } finally {
    await context.close();
    await browser.close();
  }
}
