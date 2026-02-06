<template>
  <div
    class="paragraph-theme-richtext"
    :class="{
      'paragraph-theme-richtext--full-table': fullTable,
      'paragraph-theme-richtext--simple-table': simpleTable,
    }"
  >
    <LongText
      class="change-theme-primary"
      :html="processedHtml"
    />
  </div>
</template>

<script setup>
  import { computed } from 'vue'
  import LongText from '../common/LongText.vue'

  // 定义组件 props
  const props = defineProps({
    html: {
      type: String,
      required: true,
    },
    fullTable: {
      type: Boolean,
      default: false,
    },
    simpleTable: {
      type: Boolean,
      default: false,
    },
  })

  // 处理HTML中的表格
  const processedHtml = computed(() => {
    if (!props.fullTable) return props.html

    // 使用正则表达式包装table元素
    return props.html
      .replace(/<table([^>]*)>/g, '<div class="table-responsive"><table$1>')
      .replace(/<\/table>/g, '</table></div>')
  })
</script>
