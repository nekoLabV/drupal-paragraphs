import { defineComponent, ref, computed } from 'vue'

const timelines = []
export const initTimeline = (timeline) => {
  timelines = timeline
}

export default defineComponent({
  name: 'ContentTimeline',
  
  setup() {
    const allTags = 'All'
    const results = '@count results'

    const dateActive = ref()
    const tagActive = ref()
    const tagMenuHidden = ref(true)

    // 计算日期选项
    const dates = computed(() => {
      const options = [...new Set(timelines?.map(item => item.date))]?.filter(Boolean)
      dateActive.value = options?.[0]
      return options
    })

    // 计算标签选项
    const tags = computed(() => {
      const options = [...new Set(timelines?.reduce((acc, item) => acc.concat(item.tags), []))]?.filter(Boolean)
      options?.unshift(allTags)
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
      return timelines?.filter(item => {
        if (tagActive.value === allTags) {
          return item.date === dateActive.value
        }
        return item.date === dateActive.value && item.tags.includes(tagActive.value)
      })
    })

    // 计算结果消息
    const resultsMsg = computed(
      () => results.replace(
        '@count', 
        `<span class="el-content-timeline__results-num">${displayTimelines.value?.length}</span>`
      )
    )

    return {
      dateActive,
      dates,
      selectDate,
      tagActive,
      tags,
      tagMenuHidden,
      hiddenMenu,
      displayTimelines,
      resultsMsg
    }
  },
  
  render() {
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
      <div class="el-content-timeline">
        {/* 工具区域 */}
        <div class="el-content-timeline__tools">
          {this.tags?.length ? (
            <div class={[
              'el-content-timeline__dropdown',
              {
                'el-content-timeline__dropdown-menu--hidden': this.tagMenuHidden
              }
            ]}>
              <input 
                type="text" 
                class="el-content-timeline__control" 
                onFocus={() => this.tagMenuHidden = false}
                onBlur={this.hiddenMenu}
              />
              {this.tagActive}
              <div class="el-content-timeline__dropdown-icon">
                <SvgIcon />
              </div>
              <div class="el-content-timeline__dropdown-menu">
                {this.tags.map((tag, index) => (
                  <div 
                    key={index}
                    class={[
                      'el-content-timeline__dropdown-menu__item',
                      {
                        'el-content-timeline__dropdown-menu__item--active': tag === this.tagActive
                      }
                    ]}
                    onClick={() => this.tagActive = tag}
                  >
                    {tag}
                  </div>
                ))}
              </div>
            </div>
          ) : null}
          <div 
            class="el-content-timeline__results" 
            innerHTML={this.resultsMsg}
          />
        </div>

        {/* 日期选择器 */}
        {this.dates?.length ? (
          <div class="el-content-timeline__dates">
            {this.dates.map((date, index) => (
              <div 
                key={index}
                class={[
                  'el-content-timeline__dates-item',
                  {
                    'el-content-timeline__dates-item--active': date === this.dateActive
                  }
                ]}
                onClick={() => this.selectDate(date)}
              >
                {date}
              </div>
            ))}
          </div>
        ) : null}

        {/* Timeline 项目列表 */}
        {this.displayTimelines.map((item, index) => (
          <div key={index} class="el-content-timeline-item">
            {/* 时间显示 */}
            {item?.startTime && item?.endTime ? (
              <div class="el-content-timeline-item__time">
                {`${item.startTime}-${item.endTime}`}
              </div>
            ) : item?.hintText ? (
              <div class="el-content-timeline-item__hint-text">
                {item.hintText}
              </div>
            ) : null}

            {/* 主要内容 */}
            <div class="el-content-timeline-item__primary">
              <div class="el-content-timeline-item__tail"></div>
              <div class="el-content-timeline-item__node"></div>
              
              {item?.title ? (
                <div class="el-content-timeline-item__title">
                  {item.title}
                </div>
              ) : null}
              
              {item?.subTitle ? (
                <div class="el-content-timeline-item__subtitle">
                  {item.subTitle}
                </div>
              ) : null}
              
              {item?.description ? (
                <div 
                  class="el-content-timeline-item__description"
                  innerHTML={item.description}
                />
              ) : null}
              
              {item?.tags?.length ? (
                <div class="el-content-timeline-item__tags">
                  {item.tags.map((tag, tagIndex) => (
                    <div key={tagIndex} class="el-content-timeline-item__tags-item">
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
})
