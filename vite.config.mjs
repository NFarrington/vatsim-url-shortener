import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

// Builds the same files at the same paths as Laravel Mix did, without hashes:
// the layouts link to /assets/<version>/js/app.js, css/app.css and
// css/cover.css. The deploy uploads public/assets/build to /assets/<version>/
// in S3, which CloudFront serves.
export default defineConfig({
    plugins: [vue()],
    publicDir: false,
    experimental: {
        // Make URLs in the CSS (the Font Awesome fonts) relative to the CSS
        // files in css/, so they resolve under /assets/<version>/ too.
        renderBuiltUrl(filename, { hostType }) {
            if (hostType === 'css') {
                return `../${filename}`;
            }
        },
    },
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
                // Bootstrap 5 and Font Awesome 5 use Sass features that are now
                // deprecated, such as @import and global functions. quietDeps
                // hides the warnings from inside them. They still need loading
                // with @import, so that's silenced in our own files too.
                quietDeps: true,
                silenceDeprecations: ['import'],
            },
        },
    },
});
