<template>
  <div class="paragraph-theme-images">
    <div class="paragraph-theme-images-item" v-for="(item, index) in items" :key="index">
      <div
        v-if="item?.image?.src"
        class="el-content-gallery-item__media el-ratio"
        :class="`el-ratio--${imageRatio} object-fit--${imageFit}`"
      >
        <img
          :src="item?.image?.src"
          loading="lazy"
          decoding="async"
        />
      </div>
      <div
        v-if="item?.image?.caption"
        class="el-content-gallery-item__caption"
      >
        {{ item.image.caption }}
      </div>
    </div>
  </div>
</template>

<script setup>
  defineProps({
    items: {
      type: Array,
      default: () => [],
    },
    imageRatio: {
      type: String,
      default: '16x9',
    },
    imageFit: {
      type: String,
      default: 'contain',
    },
    collection: {
      type: Object,
      default: () => ({}),
    },
  })
</script>
