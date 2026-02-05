export const modulePropsMap = {
  swiper: content => ({
    ...content,
    cols: content.cols || 3
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