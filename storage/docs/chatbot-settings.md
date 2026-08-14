# Configuración del Chatbot (Settings)

Estos son los registros que el admin debe crear en la tabla `settings` para que el bot tenga información de horarios, feriados y datos del negocio.

> **¿Cómo crearlos?** Desde el menú admin → **Configuración** → crear nuevo registro con `name` y `value` (el `value` debe ser JSON válido).

---

## 1. `business_hours` — Horario semanal

Define el horario de apertura de cada día de la semana.

**Formato** (un objeto con claves por día):

```json
{
  "monday":    { "open": "09:00", "close": "18:00" },
  "tuesday":   { "open": "09:00", "close": "18:00" },
  "wednesday": { "open": "09:00", "close": "18:00" },
  "thursday":  { "open": "09:00", "close": "18:00" },
  "friday":    { "open": "09:00", "close": "18:00" },
  "saturday":  { "open": "09:00", "close": "14:00" },
  "sunday":    { "closed": true }
}
```

**Claves válidas**: `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`, `sunday`.

**Por día**:
- `open` (string `HH:MM`): hora de apertura
- `close` (string `HH:MM`): hora de cierre
- `closed` (bool): si es `true`, ese día está cerrado (no requiere `open`/`close`)

Si **no defines un día**, el bot no tendrá info de horario para ese día.

---

## 2. `business_holidays` — Días feriados / especiales

Lista de fechas en las que el negocio está cerrado o tiene horario especial.

**Formato** (array de objetos):

```json
[
  { "date": "2026-12-25", "reason": "Navidad", "closed": true },
  { "date": "2027-01-01", "reason": "Año Nuevo", "closed": true },
  { "date": "2026-05-01", "reason": "Día del Trabajo", "closed": true },
  { "date": "2026-09-16", "reason": "Día de la Independencia", "closed": true },
  { "date": "2026-11-02", "reason": "Día de Muertos", "closed": true },
  { "date": "2026-12-12", "reason": "Día de la Virgen", "closed": true }
]
```

**Campos**:
- `date` (string `YYYY-MM-DD`): fecha del feriado
- `reason` (string): motivo (aparecerá en la respuesta del bot)
- `closed` (bool): si el negocio está cerrado. Si es `false`, puedes agregar `open`/`close` para horario especial (todavía no soportado, ponlo siempre en `true` por ahora)

---

## 3. `business_info` — Datos del negocio

Información general que el bot puede incluir en sus respuestas.

**Formato** (objeto):

```json
{
  "business_name": "Uniformes Los Chavos",
  "address": "Av. Principal #123, Centro, Tierra Blanca",
  "phone": "+52 274 123 4567",
  "email": "contacto@loschavos.com"
}
```

**Campos** (todos opcionales):
- `business_name`
- `address`
- `phone`
- `email`

---

## Cómo los usa el bot

El bot construye automáticamente un bloque de contexto (en español) como:

```
Fecha y hora actual: jueves 14 de agosto de 2026, 13:20 (America/Mexico_city).
Horario de hoy (jueves): 09:00 - 18:00.
Próximos días feriados: 16 de septiembre (Día de la Independencia), 02 de noviembre (Día de Muertos).
Datos del negocio: Nombre: Uniformes Los Chavos | Teléfono: +52 274 123 4567.
```

Este bloque se inyecta en el prompt del LLM junto con el contexto de Qdrant (escuelas, uniformes, servicios).

---

## Validación

- Los valores en la tabla `settings` son strings. Asegúrate de que el JSON sea válido (sin comas trailing, comillas dobles, etc).
- El sistema tiene un caché de 1 hora. Al actualizar un setting, el cache se limpia automáticamente (vía `SettingObserver`).
- Si un setting no existe, esa sección simplemente no aparece en el contexto del bot.

---

## Ejemplo de prueba

Para probar rápidamente, ve a **Admin → Configuración** y crea:

1. `name: business_hours`, `label: Horarios del negocio`, `value:` (pega el JSON del punto 1)
2. `name: business_holidays`, `label: Días feriados`, `value:` (pega el JSON del punto 2)
3. `name: business_info`, `label: Datos del negocio`, `value:` (pega el JSON del punto 3)

Manda un WhatsApp preguntando "¿abren los domingos?" o "¿cuándo cierran?" y revisa `storage/logs/bot-*.log` para verificar que el contexto se inyecta correctamente.
