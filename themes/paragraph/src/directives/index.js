export * from './inline-html'

export const lazySrc = {
  mounted(el, binding) {
    if (typeof window === 'undefined') return
    const observer = new IntersectionObserver(
      entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            el.src = binding.value
            observer.unobserve(el)
          }
        })
      },
      {
        rootMargin: '200px 0px', // 提前50px开始加载
      }
    )
    observer.observe(el)
    el._observer = observer
  },
  unmounted(el) {
    if (typeof window === 'undefined') return
    if (el._observer) {
      el._observer.disconnect()
    }
  }
}
