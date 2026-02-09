import { defineCustomElement } from 'vue'
import Block from './paragraphs/Block.vue'
import Buttons from './paragraphs/Buttons.vue'
import Swiper from './paragraphs/Swiper.vue'

const BlockElement = defineCustomElement({ ...Block, shadowRoot: false })
customElements.define('paragraph-content-block', BlockElement)

const ButtonsElement = defineCustomElement({ ...Buttons, shadowRoot: false })
customElements.define('paragraph-buttons', ButtonsElement)

const SwiperElement = defineCustomElement({ ...Swiper, shadowRoot: false })
customElements.define('paragraph-swiper', SwiperElement)
