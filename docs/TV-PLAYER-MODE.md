# Modo reproductor TV (HDMI / Cast)

Para locales que **no usan TVPik** en la Smart TV, o como respaldo, Webnu ofrece un **modo reproductor**: una URL en pantalla completa que puedes enviar a la TV por **HDMI** o **compartir pantalla (Cast / duplicar)**.

## Idea clave

| Dónde | Qué hace |
|-------|----------|
| **TV** | Solo muestra el reproductor (`?player=1`). No hace falta tocar la TV al cambiar precios. |
| **Móvil / PC (tú)** | Sigues en **Mi carta** o **TV / TVPik** editando la carta. |

La TV consulta cada ~30 s `GET /tv/{slug}/sync.json`. Si la carta cambió en Webnu, **recarga sola**.

## Cómo emitir

### 1. HDMI (recomendado en bares / fast food)

1. Admin → **TV / TVPik** → **Abrir reproductor** (elige carta y plantilla).
2. Conecta PC o tablet a la TV por HDMI.
3. En el dispositivo: **F11** o pantalla completa en el navegador.
4. En la TV elige la entrada HDMI correcta.

### 2. Cast / duplicar (Chromecast, Fire TV, etc.)

1. Abre el **reproductor** en Chrome (PC o Android).
2. **Cast → Transmitir pestaña** (no “escritorio” entero si quieres seguir usando el móvil en Webnu).
3. En el móvil entra a **Mi carta** y edita; la pestaña del reproductor se actualizará al detectar cambios.

En iPhone/iPad: **Duplicar pantalla** (AirPlay) con el reproductor abierto en Safari; el control sigue siendo desde otro dispositivo con tu sesión Webnu.

### 3. TVPik (plan Ilimitado)

Si la TV tiene la app TVPik, **Publicar** sigue siendo el camino principal. El modo reproductor es compatible: misma URL `/tv/{slug}/{layout}` con `player=1` opcional.

## URLs

| Uso | URL |
|-----|-----|
| Reproductor | `/tv/{slug}/{layout}?player=1` |
| Sincronización | `/tv/{slug}/sync.json` |
| Vista previa (con marca) | `/tv/{slug}/{layout}?preview=1` |

Desde el panel: botón **Reproductor** o **Copiar enlace TV** en TV / TVPik.

## Configuración

```env
TVPIK_PLAYER_POLL_SECONDS=30
```

## Limitaciones actuales

- **Cast nativo** desde el panel Webnu (un botón “Emitir”) depende del navegador del usuario; no sustituye a TVPik en TVs sin navegador.
- Si cambias **plantilla TV** (menú → vídeos), genera un **nuevo enlace reproductor** o vuelve a abrir **Reproductor** con la plantilla nueva.
- La sincronización detecta cambios de **carta** (platos, precios, especial del día), no cambios de plantilla en la misma URL.

## Roadmap (screenshare + control)

- Token por pantalla para reabrir el reproductor sin sesión admin.
- Panel **Control remoto** en el móvil (siguiente plato, forzar recarga) vía WebSocket o TVPik API.
- Integración **Presentation API / Cast SDK** opcional en Chrome para un solo clic “Emitir”.
