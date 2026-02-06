import { defineCustomElement } from 'vue'
import Block from './paragraphs/Block.vue'
import Swiper from './paragraphs/Swiper.vue'

const BlockElement = defineCustomElement({ ...Block, shadowRoot: false })
customElements.define('paragraph-block', BlockElement)

const SwiperElement = defineCustomElement({ ...Swiper, shadowRoot: false })
customElements.define('paragraph-swiper', SwiperElement)
