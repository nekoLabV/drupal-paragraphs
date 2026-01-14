import { ref, computed } from '@vue/reactivity'

export const initTimeline = (timelines) => {
  console.log('initTimeline', timelines)

  const dateActive = ref()
  const dates = computed(() => {
    const options = [...new Set(timelines?.map(item => item.date))]?.filter(Boolean)
    dateActive.value = options?.[0]
    return options
  })
  console.log('dates', dates)
  const selectDate = (date) => {
    dateActive.value = date
  }

  const tagActive = ref()
  const tags = computed(() => {
    const options = [...new Set(timelines?.reduce((acc, item) => acc.concat(item.tags), []))]?.filter(Boolean)
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
}