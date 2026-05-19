# Workspace Cursor — Webnu

## Cómo abrir el proyecto

1. Clona o actualiza: `git clone https://github.com/waltersele/Webnu.git`
2. Abre **`Webnu.code-workspace`** (doble clic o *File → Open Workspace from File*).
3. Ruta local habitual: `c:\webProject\webnu\Webnu`

## Sincronización con GitHub

| Qué se sube | Qué no |
|-------------|--------|
| Código, docs, `Webnu.code-workspace`, este README | `.env`, `vendor/`, `node_modules/` |
| **`.cursor/conversations/*.jsonl`** — historial del chat (contexto) | — |
| `docs/HISTORIAL-CURSOR-WEBNU.md` — resumen legible en Markdown | — |

### Conservar conversaciones (siempre)

```powershell
.\scripts\sync-cursor-conversations.ps1
git add .cursor/conversations/
git commit -m "Actualizar conversaciones Cursor"
git push
```

Detalle: [.cursor/conversations/README.md](conversations/README.md)

Tras `git pull` en otro equipo, el agente puede leer el último `.jsonl` o el resumen en `docs/HISTORIAL-CURSOR-WEBNU.md`.

## Contexto para el agente

- **README principal:** [../README.md](../README.md)
- **Últimas features:** [../docs/ONBOARDING-FREEMIUM.md](../docs/ONBOARDING-FREEMIUM.md)
- **Transcripts:** [conversations/](conversations/)
- **Historial resumido:** [../docs/HISTORIAL-CURSOR-WEBNU.md](../docs/HISTORIAL-CURSOR-WEBNU.md)

Ejemplo de prompt:

> Continúa Webnu según `.cursor/conversations/` (último JSONL) y `docs/ONBOARDING-FREEMIUM.md`.

## IDs de conversación

| ID | Archivo en repo |
|----|-----------------|
| `2502c06f-…` | Resumen en `docs/HISTORIAL-CURSOR-WEBNU.md` (importación anterior) |
| `ddd8802e-…` | `conversations/ddd8802e-2026-05-19.jsonl` (onboarding, landing, freemium) |

## Estado del repo (mayo 2026)

- Landing principal: Blade `landing-preview` en `/`
- Registro freemium → onboarding 5 pasos
- Plan Gratis: 1 carta, 5 escaneos IA
- Estudio de plantillas, escaneo Gemini, panel plataforma

Repositorio remoto: **https://github.com/waltersele/Webnu** (rama `main`).
