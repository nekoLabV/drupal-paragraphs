/**
 * @file
 * Nuxt preview provider behavior.
 *
 * Integrates with the NuxtPreviewProvider PHP plugin to render
 * custom elements using the Nuxt component-preview module.
 */

((Drupal, drupalSettings, once) => {
  let scriptLoadFailed = false;
  const pendingErrorCallbacks = [];

  Drupal.behaviors.customElementsNuxtPreview = {
    attach(context, settings) {
      if (!settings.customElementsNuxtPreview?.baseUrl) {
        console.error('Nuxt preview baseUrl not configured');
        return;
      }

      // Load the Nuxt app loader script only once globally, so all components
      // share the same nuxt instance.
      once('nuxt-preview-app-loader', 'head').forEach(() => {
        const script = document.createElement('script');
        script.src = `${settings.customElementsNuxtPreview.baseUrl}/nuxt-component-preview/app-loader.js`;

        script.onerror = () => {
          console.error('Failed to load Nuxt preview script from:', script.src);
          scriptLoadFailed = true;
          pendingErrorCallbacks.forEach((callback) => callback());
          pendingErrorCallbacks.length = 0;
        };

        document.head.appendChild(script);
      });

      const waitForNuxtApp = (onSuccess, onError) => {
        if (scriptLoadFailed) {
          onError();
          return;
        }

        if (window.__nuxtComponentPreviewApp) {
          onSuccess(window.__nuxtComponentPreviewApp);
          return;
        }

        pendingErrorCallbacks.push(onError);
        window.addEventListener(
          'nuxt-component-preview:ready',
          (event) => {
            const index = pendingErrorCallbacks.indexOf(onError);
            if (index > -1) {
              pendingErrorCallbacks.splice(index, 1);
            }
            onSuccess(event.detail.nuxtApp);
          },
          { once: true },
        );
      };

      once('nuxt-preview', '.nuxt-preview-container', context).forEach(
        (container) => {
          const showError = () => {
            container.innerHTML = `
            <div style="padding: 1rem; background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 0.25rem; color: #991b1b;">
              <strong>${Drupal.t('Preview Error:')}</strong> ${Drupal.t('Unable to load preview scripts from the Nuxt server.')}
              <br>
              <small>${Drupal.t('Make sure the Nuxt server is running and accessible at @url', { '@url': settings.customElementsNuxtPreview.baseUrl })}</small>
            </div>
          `;
          };

          waitForNuxtApp(async (nuxtApp) => {
            try {
              // Extract slot containers (DOM elements) - only direct children
              const slotContainers = {};
              Array.from(container.children).forEach((child) => {
                if (child.dataset.slot) {
                  slotContainers[child.dataset.slot] = child;
                }
              });

              // Pass DOM elements as slots (not HTML strings)
              await nuxtApp.$previewComponent(
                container.dataset.componentName,
                JSON.parse(container.dataset.componentProps || '{}'),
                slotContainers,
                `#${container.id}`,
              );
            } catch (error) {
              console.error('Component rendering failed:', error);
              showError();
            }
          }, showError);
        },
      );
    },
  };
})(Drupal, drupalSettings, once);
