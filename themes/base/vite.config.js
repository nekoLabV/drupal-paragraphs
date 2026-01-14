import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
  base: './',
  resolve: {
    alias: {
      '@scss': resolve(__dirname, './assets/scss'),
    }
  },
  build: {
    outDir: 'dist/js',
    rollupOptions: {
      external: ['vue'],
      input: {
        countdown: resolve(__dirname, './hooks/countdown/countdown.js'),
        timeline: resolve(__dirname, './hooks/timeline')
      },
      output: {
        // format: 'iife',
        globals: {
          vue: 'Vue'
        },
        entryFileNames: '[name].js',
        chunkFileNames: '[name]-[hash].js',
        assetFileNames: 'assets/[name].[ext]'
      }
    },
    sourcemap: true
  }
})
