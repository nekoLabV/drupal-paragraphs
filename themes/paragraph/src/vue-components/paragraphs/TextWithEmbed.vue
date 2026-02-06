<template>
  <div :class="className">
    <div class="paragraph-theme-text-with-embedding__grid">
      <div class="paragraph-theme-text-with-embedding__embed">
        <div
          class="el-ratio"
          v-html="embedHtml"
        ></div>
      </div>
      <div class="paragraph-theme-text-with-embedding__text">
        <Richtext
          simpleTable
          :html="richtext"
        />
        <div
          v-if="buttons?.length"
          class="paragraph-theme-links paragraph-theme-links--direction-row"
          style="justify-content: inherit"
        >
          <CallToActionBtn
            v-for="(btn, index) in buttons"
            :btnStyle="settings?.buttonStyle"
            :key="index"
            v-bind="btn"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { onMounted, computed } from 'vue'
  import { kebabCase } from '@/jsUtils/string'
  import Richtext from './Richtext.vue'
  import CallToActionBtn from '../common/CallToActionBtn.vue'

  const props = defineProps({
    loadJS: String,
    runJS: String,
    embedHtml: String,
    richtext: String,
    buttons: {
      type: Array,
      default: () => [],
    },
    settings: {
      type: Object,
      default: () => ({}),
    },
  })

  // 计算类名
  const className = computed(() => {
    const resultClass = ['paragraph-theme-text-with-embedding']
    for (let [key, val] of Object.entries(props.settings)) {
      if (key === 'buttonStyle') continue
      key = kebabCase(key)
      resultClass.push(`${resultClass[0]}--${key}-${val}`)
    }
    return resultClass.join(' ')
  })

  // JS 加载工具函数
  const requestJS = url => {
    return new Promise((resolve, reject) => {
      let script = document.querySelector(`script[src='${url}']`)
      if (!script) {
        script = document.createElement('script')
        script.type = 'text/javascript'
        script.src = url
        document.body.appendChild(script)
      }

      script.addEventListener('load', () => resolve(script))
      script.addEventListener('error', error => reject(error))
    })
  }

  // 生命周期钩子
  onMounted(async () => {
    try {
      if (props.loadJS) await requestJS(props.loadJS)
      setTimeout(() => {
        if (props.runJS) new Function(props.runJS)()
      }, 300)
    } catch (error) {
      console.error('脚本执行失败:', error)
    }
  })
</script>
