<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* modules/contrib/mercury_editor/templates/page--mercury-editor.html.twig */
class __TwigTemplate_0a096235d62b011d83016d59f0097892 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield "
";
        // line 43
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("mercury_editor/edit_screen"), "html", null, true);
        yield "
<div id=\"me-toolbar\" class=\"me-toolbar\">
  <div class=\"me-toolbar__branding\">
    <a href=\"https://drupal.org/project/mercury_editor\" target=\"_blank\" class=\"me-toolbar__logo\">";
        // line 46
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Mercury Editor"));
        yield "</a>
  </div>
  <div class=\"me-toolbar__screen-controls screen-actions me-toolbar__group\">
    <a href=\"#\" id=\"me-desktop-toggle-btn\" class=\"me-button--icon me-button--desktop\"><span>";
        // line 49
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Desktop"));
        yield "</span></a>
    <a href=\"#\" id=\"me-mobile-toggle-btn\" class=\"me-button--icon me-button--mobile\"><span>";
        // line 50
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Mobile"));
        yield "</span></a>
  </div>
  <div class=\"me-toolbar__edit-controls main-actions me-toolbar__group\">
    ";
        // line 53
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["mobile_presets"] ?? null), "html", null, true);
        yield "
    <button id=\"me-save-btn\" class=\"me-button--primary\" title=\"";
        // line 54
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Save and continue editing."));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Save"));
        yield "</button>
    <a id=\"me-done-btn\" class=\"me-button--secondary\" href=\"#\" title=\"";
        // line 55
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Exit without saving."));
        yield "\">";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Done"));
        yield "</a>
    <a id=\"me-sidebar-toggle-btn\" class=\"me-button--icon me-button--sidebar-collapse\" href=\"#toggle-sidebar\" role=\"button\" title=\"";
        // line 56
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Hide sidebar"));
        yield "\"><span>";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Hide Sidebar"));
        yield "</span></a>
  </div>
</div>
<div id=\"me-iframe-wrapper\">
  <iframe id=\"me-preview\" width=\"100%\" height=\"100%\" style=\"border:none;\" data-src=\"";
        // line 60
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["preview_url"] ?? null), "html", null, true);
        yield "\">
  </iframe>
</div>
<mercury-dialog id=\"me-edit-screen\" hide-close-button push resizable dock=\"right\">
  ";
        // line 64
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "highlighted", [], "any", false, false, true, 64), "html", null, true);
        yield "
  ";
        // line 65
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->withoutFilter(CoreExtension::getAttribute($this->env, $this->source, ($context["page"] ?? null), "content", [], "any", false, false, true, 65), ($context["local_actions_block"] ?? null)), "html", null, true);
        yield "
</mercury-dialog>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["mobile_presets", "preview_url", "page", "local_actions_block"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/contrib/mercury_editor/templates/page--mercury-editor.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  105 => 65,  101 => 64,  94 => 60,  85 => 56,  79 => 55,  73 => 54,  69 => 53,  63 => 50,  59 => 49,  53 => 46,  47 => 43,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/contrib/mercury_editor/templates/page--mercury-editor.html.twig", "/Users/como/dev/drupal/drupal-paragraphs/modules/contrib/mercury_editor/templates/page--mercury-editor.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = [];
        static $filters = ["escape" => 43, "t" => 46, "without" => 65];
        static $functions = ["attach_library" => 43];

        try {
            $this->sandbox->checkSecurity(
                [],
                ['escape', 't', 'without'],
                ['attach_library'],
                $this->source
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
