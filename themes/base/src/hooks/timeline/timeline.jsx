import { defineComponent, ref, computed } from 'vue'

export const Timeline = defineComponent({
  name: 'Timeline',

  props: {
    items: {
      type: Array,
      default: () => ([]),
    },
    allTags: {
      type: String,
      default: 'All'
    },
    results: {
      type: String,
      default: '@count results'
    }
  },
  
  setup(props) {
    const dateActive = ref()
    const tagActive = ref()
    const tagMenuHidden = ref(true)

    // 计算日期选项
    const dates = computed(() => {
      const options = [...new Set(props.items?.map(item => item.date))]?.filter(Boolean)
      dateActive.value = options?.[0]
      return options
    })

    // 计算标签选项
    const tags = computed(() => {
      const options = [...new Set(props.items?.reduce((acc, item) => acc.concat(item.tags), []))]?.filter(Boolean)
      options?.unshift(props.allTags)
      tagActive.value = options?.[0]
      return options
    })

    // 选择日期
    const selectDate = (date) => {
      dateActive.value = date
    }

    // 隐藏标签菜单
    const hiddenMenu = () => {
      setTimeout(() => {
        tagMenuHidden.value = true
      }, 300)
    }

    // 计算显示的timeline项目
    const displayTimelines = computed(() => {
      return props.items?.filter(item => {
        if (tagActive.value === props.allTags) {
          return item.date === dateActive.value
        }
        return item.date === dateActive.value && item.tags.includes(tagActive.value)
      })
    })

    // 计算结果消息
    const resultsMsg = computed(
      () => props.results.replace(
        '@count', 
        `<span class="base-theme-timeline__results-num">${displayTimelines.value?.length}</span>`
      )
    )

    return () => {
      const SvgIcon = () => (
        <svg 
          xmlns="http://www.w3.org/2000/svg" 
          xmlns:xlink="http://www.w3.org/1999/xlink" 
          fill="none" 
          version="1.1" 
          width="18" 
          height="18" 
          viewBox="0 0 18 18"
        >
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
      )

      return (
        <div class="base-theme-timeline">
          {/* 工具区域 */}
          <div class="base-theme-timeline__tools">
            {tags.value?.length ? (
              <div class={[
                'base-theme-timeline__dropdown',
                {
                  'base-theme-timeline__dropdown-menu--hidden': tagMenuHidden.value
                }
              ]}>
                <input 
                  type="text" 
                  class="base-theme-timeline__control" 
                  onFocus={() => tagMenuHidden.value = false}
                  onBlur={hiddenMenu}
                />
                {tagActive.value}
                <div class="base-theme-timeline__dropdown-icon">
                  <SvgIcon />
                </div>
                <div class="base-theme-timeline__dropdown-menu">
                  {tags.value.map((tag, index) => (
                    <div 
                      key={index}
                      class={[
                        'base-theme-timeline__dropdown-menu__item',
                        {
                          'base-theme-timeline__dropdown-menu__item--active': tag === tagActive.value
                        }
                      ]}
                      onClick={() => tagActive.value = tag}
                    >
                      {tag}
                    </div>
                  ))}
                </div>
              </div>
            ) : null}
            <div 
              class="base-theme-timeline__results" 
              innerHTML={resultsMsg.value}
            />
          </div>
  
          {/* 日期选择器 */}
          {dates.value?.length ? (
            <div class="base-theme-timeline__dates">
              {dates.value.map((date, index) => (
                <div 
                  key={index}
                  class={[
                    'base-theme-timeline__dates-item',
                    {
                      'base-theme-timeline__dates-item--active': date === dateActive.value
                    }
                  ]}
                  onClick={() => selectDate(date)}
                >
                  {date}
                </div>
              ))}
            </div>
          ) : null}
  
          {/* Timeline 项目列表 */}
          {displayTimelines.value.map((item, index) => (
            <div key={index} class="base-theme-timeline-item">
              {/* 时间显示 */}
              {item?.startTime && item?.endTime ? (
                <div class="base-theme-timeline-item__time">
                  {`${item.startTime}-${item.endTime}`}
                </div>
              ) : item?.hintText ? (
                <div class="base-theme-timeline-item__hint-text">
                  {item.hintText}
                </div>
              ) : null}
  
              {/* 主要内容 */}
              <div class="base-theme-timeline-item__primary">
                <div class="base-theme-timeline-item__tail"></div>
                <div class="base-theme-timeline-item__node"></div>
                
                {item?.title ? (
                  <div class="base-theme-timeline-item__title">
                    {item.title}
                  </div>
                ) : null}
                
                {item?.subTitle ? (
                  <div class="base-theme-timeline-item__subtitle">
                    {item.subTitle}
                  </div>
                ) : null}
                
                {item?.description ? (
                  <div 
                    class="base-theme-timeline-item__description"
                    innerHTML={item.description}
                  />
                ) : null}
                
                {item?.tags?.length ? (
                  <div class="base-theme-timeline-item__tags">
                    {item.tags.map((tag, tagIndex) => (
                      <div key={tagIndex} class="base-theme-timeline-item__tags-item">
                        {tag}
                      </div>
                    ))}
                  </div>
                ) : null}
              </div>
            </div>
          ))}
        </div>
      )
    }
  }
})
