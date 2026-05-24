/**
 * @param {string} text
 */
export function normalizeHtmlText(text) {
  const normalized = (text || '')
    .replace(/\r\n/g, '\n')
    .replace(/\n{3,}/g, '\n\n')
    .trim();

  if (normalized.length < 40) {
    throw new Error('HTML sin texto suficiente para parsear');
  }

  const maxChars = 120000;
  if (normalized.length > maxChars) {
    return normalized.slice(0, maxChars) + '\n\n[... texto truncado ...]';
  }

  return normalized;
}
