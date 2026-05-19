# Conversaciones Cursor (versionadas en Git)

Esta carpeta guarda **transcripts JSONL** del chat de Cursor para conservar contexto entre máquinas, clones y sesiones del agente.

## Convención de nombres

```text
{uuid-corto}-{tema-o-fecha}.jsonl
```

Ejemplo: `ddd8802e-onboarding-freemium-landing.jsonl`

## Sincronizar antes de commit / push

Desde la raíz del proyecto:

```powershell
.\scripts\sync-cursor-conversations.ps1
git add .cursor/conversations/
git commit -m "Actualizar conversaciones Cursor"
git push
```

El script copia los `.jsonl` desde el almacenamiento local de Cursor (`agent-transcripts` del workspace) a esta carpeta.

## Uso para el agente

Si abres el repo en otro equipo o el chat no recuerda el hilo:

1. Lee el `.jsonl` más reciente de esta carpeta, o
2. Consulta el resumen en [docs/HISTORIAL-CURSOR-WEBNU.md](../docs/HISTORIAL-CURSOR-WEBNU.md)

Puedes pedir al agente: *"Continúa según la última conversación en `.cursor/conversations/`"*.

## Privacidad

No subas claves API, contraseñas ni datos personales de clientes reales en el chat; si aparecen en un transcript, redacta antes de commitear o usa un resumen en `docs/` en lugar del JSONL completo.
