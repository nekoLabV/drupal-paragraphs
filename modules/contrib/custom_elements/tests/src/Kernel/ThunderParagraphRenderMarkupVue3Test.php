<?php

namespace Drupal\Tests\custom_elements\Kernel;

/**
 * Test rendering custom elements using paragraph bundles' in Vue3 style.
 *
 * The CE displays for these tests are defined in config/install files
 * in the custom_elements_thunder module.
 *
 * Many tests are only in the parent class. Test methods referenced here have
 * some difference in relation to the parent (non-Vue3) tests.
 *
 * @group custom_elements
 */
class ThunderParagraphRenderMarkupVue3Test extends ThunderParagraphRenderMarkupTest {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $config = $this->config('custom_elements.settings');
    $config->set('markup_style', 'vue-3');
    $config->set('json_format', 'legacy');
    $config->save();
  }

  /**
   * {@inheritdoc}
   */
  public function testLinkParagraph($vue3_style = FALSE) {
    parent::testLinkParagraph(TRUE);
  }

  // phpcs:disable
  // @todo reinstate when there's a D11 compatible release; see parent.
  /**
   * {@inheritdoc}
   * /
  public function testTwitterParagraph($vue3_style = FALSE) {
    parent::testTwitterParagraph(TRUE);
  }

  /**
   * {@inheritdoc}
   * /
  public function testGalleryParagraph($vue3_style = FALSE) {
    parent::testGalleryParagraph(TRUE);
  }
  */
  // phpcs:enable

}
