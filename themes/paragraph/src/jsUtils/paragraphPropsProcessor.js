import { toKebabCase } from '@/jsUtils/string'

export const modulePropsMap = {
  contentBlock: content => ({
    ...content,
    theme: toKebabCase(content?.theme),
    backgroundImageSrc: content?.backgroundImageSrc?.url,
    backgroundImageMobileSrc: content?.backgroundImageMobileSrc?.url
  }),
  button: content => content,
  buttons: content => content,
  swiper: content => ({
    ...content,
    cols: Number(content.cols) || 3,
    rows: Number(content.rows)
  }),
  countdown: content => ({
    end: content,
    text: '天|时|分|秒'
  }),
  image: content => content,
  textWithEmbed: content => content,
  timeline: content => ({
    items: content
  }),
  default: content => content
}