import fs from 'node:fs';
import path from 'node:path';
import Ajv from 'ajv';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const schemaPath = path.resolve(__dirname, '../../schemas/menu.schema.json');
const schema = JSON.parse(fs.readFileSync(schemaPath, 'utf8'));
const ajv = new Ajv({ allErrors: true, strict: false });
const validate = ajv.compile(schema);

/**
 * @param {unknown} menu
 */
export function validateMenuJson(menu) {
  const valid = validate(menu);
  if (!valid) {
    const details = (validate.errors || [])
      .map((e) => `${e.instancePath || '/'} ${e.message}`)
      .join('; ');
    throw new Error(`JSON de menú inválido: ${details}`);
  }

  const sections = menu.sections;
  let productCount = 0;
  for (const section of sections) {
    productCount += section.products?.length || 0;
  }
  if (productCount === 0) {
    throw new Error('El menú no contiene platos');
  }

  return menu;
}
