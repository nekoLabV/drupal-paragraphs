import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
  base: './',
  resolve: {
    alias: {
      
    }
  },
  build: {
    outDir: 'dist/js',
    rollupOptions: {
      input: {
        countdown: resolve(__dirname, 'hooks/countdown/countdown.js'),
        // countdown2: resolve(__dirname, 'hooks/countdown/countdown.js')
      },
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: '[name]-[hash].js',
        assetFileNames: 'assets/[name].[ext]'
      }
    },
    sourcemap: true
  }
})
