import { defineConfig } from 'vite'
import { resolve } from 'path'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'

export default defineConfig({
  base: './',
  plugins: [vue(), vueJsx()],
  resolve: {
    alias: {
      '@': resolve(__dirname, './src'),
      '@scss': resolve(__dirname, './src/assets/scss'),
      '@node': resolve(__dirname, './node_modules')
    }
  },
  build: {
    outDir: 'dist/js',
    lib: {
      entry: resolve(__dirname, './src/hooks/index.js'),
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
