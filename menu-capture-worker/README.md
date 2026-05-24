# menu-capture-worker

Worker externo para captación comercial de cartas (scraping + Gemini + Pre-Alta en Webnu).

Documentación operativa: [docs/MENU-CAPTURE-WORKER.md](../docs/MENU-CAPTURE-WORKER.md).

```bash
cp .env.example .env
npm install
npm run capture -- --input examples/urls.csv --dry-run --limit 1
```
