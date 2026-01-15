import { defineConfig } from 'vite'
import { resolve } from 'path'
import vueJsx from '@vitejs/plugin-vue-jsx'

export default defineConfig({
  base: './',
  plugins: [vueJsx()],
  resolve: {
    alias: {
      '@scss': resolve(__dirname, './assets/scss'),
    }
  },
  build: {
    outDir: 'dist/js',
    lib: {
      entry: resolve(__dirname, './hooks/index.js'),
      name: 'BaseThemeHooks', // 全局变量名
      formats: ['iife'],
      fileName: 'hooks'    // 输出文件名
    },
    rollupOptions: {
      external: ['vue'],
      output: {
        globals: {
          vue: 'Vue'
        }
      }
    },
    sourcemap: true
  }
})
