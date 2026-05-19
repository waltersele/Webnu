# Workspace Cursor — Webnu

## Cómo abrir el proyecto

1. Clona o actualiza: `git clone https://github.com/waltersele/Webnu.git`
2. Abre **`Webnu.code-workspace`** (doble clic o *File → Open Workspace from File*).
3. Ruta local habitual: `c:\webProject\webnu\Webnu`

## Sincronización con GitHub

| Qué se sube | Qué no |
|-------------|--------|
| Código, docs, `Webnu.code-workspace`, este README | `.cursor/conversations/` (historial local) |
| `docs/HISTORIAL-CURSOR-WEBNU.md` (resumen legible) | `.env`, `vendor/`, `node_modules/` |

Tras `git pull`:

```bash
composer install
php artisan migrate
npm install
```

## Contexto para el agente

- **README principal:** [../README.md](../README.md)
- **Últimas features:** [../docs/ONBOARDING-FREEMIUM.md](../docs/ONBOARDING-FREEMIUM.md)
- **Historial desarrollo:** [../docs/HISTORIAL-CURSOR-WEBNU.md](../docs/HISTORIAL-CURSOR-WEBNU.md)

Si el chat no tiene contexto previo, indica por ejemplo:

> Continúa Webnu: onboarding freemium, landing en `/`, límites en `UserPlanService`. Lee `docs/ONBOARDING-FREEMIUM.md`.

## Historial importado (workspace anterior)

| Archivo | Descripción |
|---------|-------------|
| `conversations/` (gitignored) | Transcripts JSONL locales de Cursor |
| `../docs/HISTORIAL-CURSOR-WEBNU.md` | Resumen en Markdown |

**ID conversación original:** `2502c06f-0e81-4866-a184-aacb72fa4ba5`

## Estado del repo (mayo 2026)

- Landing principal: Blade `landing-preview` en `/`
- Registro freemium → onboarding 5 pasos
- Plan Gratis: 1 carta, 5 escaneos IA
- Estudio de plantillas, escaneo Gemini, panel plataforma

Repositorio remoto: **https://github.com/waltersele/Webnu** (rama `main`).
