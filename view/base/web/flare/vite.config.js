import { dirname, resolve } from 'node:path'
import { defineConfig } from 'vite'

export default defineConfig({
  build: {
    minify: true,
    lib: {
      entry: resolve(import.meta.dirname, 'main.js'),
      name: 'flare',
      fileName: () => 'flareapp.js',
      formats: ['iife'],
    },
  },
  plugins: [{
    name: 'remove-amd',
    enforce: 'pre',
    transform(code, id) {
      return code.replace(
        /typeof define === ["']function["'] && define\.amd/g,
        'false'
      );
    }
  }]
})
