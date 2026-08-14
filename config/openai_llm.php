<?php

return [
    'api_key' => env('OPEN_AI_API_KEY'),
    'organization' => env('OPEN_AI_ORGANIZATION'),
    'embedding_model' => env('OPEN_AI_EMBEDDING_MODEL', 'text-embedding-3-small'),
    'chat_model' => env('OPEN_AI_CHAT_MODEL', 'gpt-4o-mini'),
    'vector_size' => (int) env('EMBEDDING_VECTOR_SIZE', 1536),
    'api_base' => env('OPEN_AI_API_BASE', 'https://api.openai.com/v1'),
    'timeout' => (int) env('OPEN_AI_TIMEOUT', 60),

    'chat_bot' => [
        'debounce_seconds' => (int) env('CHAT_BOT_DEBOUNCE_SECONDS', 5),
        'context_top_k' => (int) env('CHAT_BOT_CONTEXT_TOP_K', 5),
        'max_history' => (int) env('CHAT_BOT_MAX_HISTORY', 5),
        'system_prompt' => env(
            'CHAT_BOT_SYSTEM_PROMPT',
            "Eres el asistente virtual de \"Los Chavos\", un negocio de uniformes escolares en México.\n"
            . "SIEMPRE responde en español, sin importar el idioma en que el cliente escriba.\n"
            . "Si el cliente escribe en otro idioma, traduce su intención al español al responder.\n"
            . "Usa el CONTEXTO proporcionado (escuelas y uniformes) para responder con datos precisos.\n"
            . "Si no hay contexto suficiente, pregunta amablemente qué necesita (escuela, nivel educativo, tipo de uniforme).\n"
            . "No inventes precios, tallas exactas ni existencias. Si te preguntan, di que un agente humano confirmará.\n"
            . "Sé breve, cordial y profesional. Usa máximo 3-4 oraciones por mensaje."
        ),
        'no_context_message' => env(
            'CHAT_BOT_NO_CONTEXT_MESSAGE',
            "No encontré información específica sobre tu pregunta. ¿Me compartes el nombre de la escuela o el nivel educativo para ayudarte mejor?"
        ),
        'fallback_message' => env(
            'CHAT_BOT_FALLBACK_MESSAGE',
            "Tuve un problema al procesar tu mensaje. Un asesor te contactará en breve. 🙏"
        ),
    ],
];
