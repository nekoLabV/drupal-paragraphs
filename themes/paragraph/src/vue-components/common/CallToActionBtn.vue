<template>
  <a
    v-if="text && href"
    class="btn"
    v-bind="getAProps"
    :class="`btn--${btnStyle} ${size ? `btn--${size}` : ''}`"
    :data-node-type="nodeType"
  >
    <i
      v-if="showIcon && iconPosition === 'before'"
      class="btn__icon"
      :class="customIcon || `btn__icon--${type}`"
    ></i>
    <span class="btn__label">{{ text }}</span>
    <i
      v-if="showIcon && iconPosition === 'after'"
      class="btn__icon"
      :class="customIcon || `btn__icon--${type}`"
    ></i>
  </a>
</template>

<script setup>
  import { computed } from 'vue'

  const props = defineProps({
    type: {
      type: String,
      required: true,
    },
    text: String,
    size: String,
    btnStyle: {
      type: String,
      default: 'outline-primary',
    },
    href: String,
    target: String,
    downloadFileName: {
      type: String,
      default: '',
    },
    nodeType: String,
    iconSettings: {
      type: Object,
      default: () => ({
        custom_icon: '',
        show_icon: true,
        icon_position: 'before',
      }),
    },
  })

  const showIcon = computed(() => {
    return props.iconSettings.show_icon
  })
  const customIcon = computed(() => {
    return props.iconSettings.custom_icon ? 'ri-' + props.iconSettings.custom_icon : ''
  })
  const iconPosition = computed(() => {
    return props.iconSettings.icon_position
  })

  const linkHref = computed(() => {
    let { href } = props
    if (href.startsWith('http')) return href
    if (!href.startsWith('/')) return (href = '/' + href)
    return href
  })

  const typeHandlers = {
    link: () => ({
      href: linkHref.value,
      target: props.target || props.href.startsWith('http') ? '_blank' : '_self',
      'data-node-type': props.nodeType,
    }),
    mail: () => ({
      href: `mailto:${props.href}`,
      target: props.target,
    }),
    phone: () => ({
      href: `tel:${props.href}`,
      target: props.target,
    }),
    sms: () => ({
      href: `sms:${props.href}`,
      target: props.target,
    }),
    download: () => ({
      href: props.href,
      download: props.downloadFileName,
      target: '_blank',
    }),
  }

  const getAProps = computed(() => {
    return typeHandlers[props.type]?.() || {}
  })
</script>
