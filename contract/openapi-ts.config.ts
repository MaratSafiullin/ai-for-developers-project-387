import { fileURLToPath } from 'node:url';
import { defineConfig } from '@hey-api/openapi-ts';

export default defineConfig({
    input: fileURLToPath(new URL('openapi.yaml', import.meta.url)),
    output: {
        path: fileURLToPath(new URL('../resources/js/api', import.meta.url)),
    },
    plugins: ['@hey-api/client-fetch', '@tanstack/react-query'],
});
