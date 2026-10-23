import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

// Builds the same files at the same paths as Laravel Mix did, without hashes:
// the layouts link to /assets/<version>/js/app.js, css/app.css and
// css/cover.css, which are served from public/assets/build.
export default defineConfig({
    plugins: [vue()],
    // URLs in the CSS (the Font Awesome fonts) point here, as nginx serves
    // public/assets/build directly.
    base: '/assets/build/',
    publicDir: false,
    build: {
        outDir: 'public/assets/build',
        emptyOutDir: true,
        rollupOptions: {
            input: {
                'js/app': 'resources/js/app.js',
                'css/app': 'resources/sass/app.scss',
                'css/cover': 'resources/sass/cover.scss',
            },
            output: {
                entryFileNames: '[name].js',
                chunkFileNames: 'js/[name].js',
                assetFileNames: ({ names }) => (names[0]?.endsWith('.css') ? '[name][extname]' : 'fonts/[name][extname]'),
            },
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                // Bootstrap 4 and Font Awesome 5 use Sass features that are now
                // deprecated, such as @import and slash division.
                quietDeps: true,
                silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'slash-div'],
            },
        },
    },
});
