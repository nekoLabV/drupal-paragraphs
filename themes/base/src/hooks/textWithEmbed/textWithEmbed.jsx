import { defineComponent, computed, onMounted } from 'vue'

export const TextWithEmbed = defineComponent({
  name: 'TextWithEmbed',
  
  props: {
    loadJS: String,
    runJS: String,
    embedHtml: String,
    richtext: String,
    buttons: {
      type: Array,
      default: () => []
    },
    settings: {
      type: Object,
      default: () => ({})
    }
  },
  
  setup(props) {
    // 计算类名
    const className = computed(() => {
      const resultClass = ['base-theme-text-with-embedding']
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
        if (props.runJS) new Function(props.runJS)()
      } catch (error) {
        console.error('脚本执行失败:', error)
      }
    })

    // 返回渲染函数
    return () => (
      <div class={className.value}>
        <div class="base-theme-text-with-embedding__grid">
          {/* 嵌入内容区域 */}
          <div class="base-theme-text-with-embedding__embed">
            <div 
              class="el-ratio" 
              innerHTML={props.embedHtml}
            />
          </div>
          
          {/* 文本内容区域 */}
          <div class="base-theme-text-with-embedding__text">
            <Richtext 
              simpleTable 
              html={props.richtext} 
            />
            
            {/* 按钮区域 */}
            {props.buttons?.length > 0 && (
              <div 
                class="base-theme-links base-theme-links--direction-row"
                style={{ justifyContent: 'inherit' }}
              >
                {props.buttons.map((btn, index) => (
                  <CallToActionBtn
                    key={index}
                    btnStyle={props.settings?.buttonStyle}
                    {...btn}
                  />
                ))}
              </div>
            )}
          </div>
        </div>
      </div>
    )
  }
})
