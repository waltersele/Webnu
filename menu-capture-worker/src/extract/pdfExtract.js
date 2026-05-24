import pdfParse from 'pdf-parse';

/**
 * @param {Buffer} buffer
 */
export async function extractTextFromPdf(buffer) {
  const data = await pdfParse(buffer);
  const text = (data.text || '').trim();
  if (text.length < 40) {
    throw new Error('PDF sin texto extraíble suficiente');
  }
  return text;
}
