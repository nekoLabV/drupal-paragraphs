<template>
  <div class="paragraph-theme-timeline">
    <div class="paragraph-theme-timeline__tools">
      <div
        v-if="tags?.length"
        :class="['paragraph-theme-timeline__dropdown', {
        'paragraph-theme-timeline__dropdown-menu--hidden': tagMenuHidden
        }]"
      >
        <input 
          type="text" 
          class="paragraph-theme-timeline__control" 
          @focus="() => {
            tagMenuHidden = false
          }"
          @blur="hiddenMenu">
        {{ tagActive }}
        <div class="paragraph-theme-timeline__dropdown-icon">
          <svg 
            xmlns="http://www.w3.org/2000/svg" 
            xmlns:xlink="http://www.w3.org/1999/xlink" 
            fill="none" 
            version="1.1" 
            width="18" 
            height="18" 
            viewBox="0 0 18 18">
            <defs>
              <clipPath id="master_svg0_745_72570/556_024318">
                <rect x="0" y="0" width="18" height="18" rx="0"/>
              </clipPath>
            </defs>
            <g clip-path="url(#master_svg0_745_72570/556_024318)">
              <g>
                <path d="M9,12L4.5,7.5L13.5,7.5L9,12Z" fill="#666666" fill-opacity="1"/>
              </g>
            </g>
          </svg>
        </div>
        <div class="paragraph-theme-timeline__dropdown-menu">
          <div 
            v-for="(tag, index) in tags" :key="index" 
            :class="['paragraph-theme-timeline__dropdown-menu__item', {
              'paragraph-theme-timeline__dropdown-menu__item--active': tag === tagActive
            }]"
            @click="tagActive = tag">
            {{ tag }}
          </div>
        </div>
      </div>
      <div class="paragraph-theme-timeline__results" v-html="resultsMsg"></div>
    </div>
    <div v-if="dates?.length" class="paragraph-theme-timeline__dates">
      <div 
        :class="['paragraph-theme-timeline__dates-item', {
          'paragraph-theme-timeline__dates-item--active': date === dateActive
        }]" 
        v-for="(date, index) in dates" 
        :key="index"
        @click="selectDate(date)">
        {{ date }}
      </div>
    </div>
    <div class="paragraph-theme-timeline-item" v-for="(item, index) in displayTimelines" :key="index">
      <div
        v-if="item?.startTime && item?.endTime"
        class="paragraph-theme-timeline-item__time">
        {{ `${item.startTime}-${item.endTime}` }}
      </div>
      <div v-else class="paragraph-theme-timeline-item__hint-text">{{ item.hintText }}</div>
      <div class="paragraph-theme-timeline-item__primary">
        <div class="paragraph-theme-timeline-item__tail"></div>
        <div class="paragraph-theme-timeline-item__node"></div>
        <div
          v-if="item?.title"
          class="paragraph-theme-timeline-item__title">
          {{ item.title }}
        </div>
        <div 
          v-if="item?.subTitle" 
          class="paragraph-theme-timeline-item__subtitle">
          {{ item.subTitle }}
        </div>
        <div 
          v-if="item?.description" 
          v-html="item.description"
          class="paragraph-theme-timeline-item__description">
        </div>
        <div 
          v-if="item?.tags?.length" 
          class="paragraph-theme-timeline-item__tags">
          <div class="paragraph-theme-timeline-item__tags-item" v-for="(tag, index) in item.tags" :key="index">
            {{ tag }}
          </div>
        </div>
        <div
          v-if="item?.buttons?.length"
          class="paragraph-theme-links paragraph-theme-links--direction-row paragraph-theme-timeline-item__actions"
          style="justify-content: inherit"
        >
          <CallToActionBtn
            v-for="(btn, index) in item.buttons"
            v-bind="btn"
            :key="index"
            :btn-style="buttonStyle"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import CallToActionBtn from '../common/CallToActionBtn.vue'
  import { ref, computed, watch } from 'vue'

  const props = defineProps({
    items: {
      type: Array,
      default: () => ([]),
    },
    buttonStyle: {
      type: String,
      default: 'outline-primary',
    },
    allTags: {
      type: String,
      default: 'All'
    },
    results: {
      type: String,
      default: '@count results'
    },
  })

  const dateActive = ref()
  const dates = computed(() => {
    const options = [...new Set(props.items?.map(item => item.date))]?.filter(Boolean)
    dateActive.value = options?.[0]
    return options
  })
  const selectDate = (date) => {
    dateActive.value = date
  }

  const tagActive = ref()
  const tags = computed(() => {
    const options = [...new Set(props.items?.reduce((acc, item) => acc.concat(item.tags), []))]?.filter(Boolean)
    options?.unshift(props.allTags)
    tagActive.value = options?.[0]
    return options
  })
  const tagMenuHidden = ref(true)
  const hiddenMenu = () => {
    setTimeout(() => {
      tagMenuHidden.value = true
    }, 300)
  }

  const displayTimelines = computed(() => {
    return props.items?.filter(item => {
      if (tagActive.value === props.allTags) {
        return item.date === dateActive.value
      }
      return item.date === dateActive.value && item.tags.includes(tagActive.value)
    })
  })

  const resultsMsg = computed(
    () => props.results.replace(
      '@count', 
      `<span class="paragraph-theme-timeline__results-num">${displayTimelines.value?.length}</span>`
    )
  )
</script>
