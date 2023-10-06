import {defineConfig} from 'vite';
import preact from '@preact/preset-vite';
import mkcert from 'vite-plugin-mkcert';

// https://vitejs.dev/config/
export default defineConfig({
    // server: {https: true},
    plugins: [
        preact(),
        // mkcert({
        //     hosts: [
        //         'webauthn.local'
        //     ]
        // }),
    ],
});
