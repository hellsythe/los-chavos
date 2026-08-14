<?php

return [
    'host' => env('QDRANT_HOST', 'https://qdrant.sdkconsultoria.com'),
    'api_key' => env('QDRANT_API_KEY'),
    'collections' => [
        'schools' => env('QDRANT_COLLECTION_SCHOOLS', 'schools'),
        'uniforms' => env('QDRANT_COLLECTION_UNIFORMS', 'uniforms'),
    ],
    'vector_size' => (int) env('EMBEDDING_VECTOR_SIZE', 1536),
    'distance' => \Qdrant\Models\Request\VectorParams::DISTANCE_COSINE,
];
