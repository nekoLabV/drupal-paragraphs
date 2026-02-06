<script setup>
import { computed } from 'vue';

const props = defineProps({
  theme: {
    type: String,
    default: 'white'
  },
  blockAlign: {
    type: String,
    default: 'center' // 'left' | 'center' | 'right'
  },
  colWidth: {
    type: String,
    default: '100'  // '100' | '80' | '60'
  },
  paddingTop: {
    type: String,
    default: 'none'
  },
  paddingBottom: {
    type: String,
    default: 'none'
  },
  marginTop: {
    type: String,
    default: 'none'
  },
  marginBottom: {
    type: String,
    default: 'none'
  },
  backgroundImageSrc: {
    type: String,
    default: ''
  },
  backgroundImageMobileSrc: {
    type: String,
    default: ''
  }
})

const className = computed(() => {
  const rootBlock = 'paragraph-theme-block'
  let resultClassArr = [
    `paragraph-theme-${props.theme}`,
    `${rootBlock}--align-${props.blockAlign}`,
    `${rootBlock}--col-width-${props.colWidth}`,
    `${rootBlock}--padding-top-${props.paddingTop}`,
    `${rootBlock}--padding-bottom-${props.paddingBottom}`,
    `${rootBlock}--margin-top-${props.marginTop}`,
    `${rootBlock}--margin-bottom-${props.marginBottom}`
  ]

  return resultClassArr.join(' ')
})

const useBackgroundImage = computed(() => {
  if (props.theme !== 'light-image' && props.theme !== 'dark-image') {
    return false
  }
  return !!(props.backgroundImageSrc || props.backgroundImageMobileSrc)
})
</script>

<template>
  <section class="paragraph-theme-block" :class="className">
    <div class="paragraph-theme-block__container">
      <slot></slot>
    </div>
    <picture class="paragraph-theme-block__background">
      <template v-if="useBackgroundImage">
        <source
          v-if="backgroundImageMobileSrc"
          media="(max-width: 920px)"
          :srcset="backgroundImageMobileSrc"
        />
        <source
          v-if="backgroundImageSrc"
          media="(min-width: 921px)"
          :srcset="backgroundImageSrc"
        />
        <img
          :src="backgroundImageMobileSrc || backgroundImageSrc"
          loading="lazy"
          decoding="async"
        />
      </template>
    </picture>
  </section>
</template>