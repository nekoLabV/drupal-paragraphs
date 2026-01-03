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

/* core/profiles/demo_umami/themes/umami/templates/content/node.html.twig */
class __TwigTemplate_822850417b32c70843cb5c9769becf18 extends Template
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
        // line 64
        $context["classes"] = ["node", ("node--type-" . \Drupal\Component\Utility\Html::getClass(CoreExtension::getAttribute($this->env, $this->source,         // line 66
($context["node"] ?? null), "bundle", [], "any", false, false, true, 66))), (((($tmp = CoreExtension::getAttribute($this->env, $this->source,         // line 67
($context["node"] ?? null), "isPromoted", [], "method", false, false, true, 67)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("node--promoted") : ("")), (((($tmp = CoreExtension::getAttribute($this->env, $this->source,         // line 68
($context["node"] ?? null), "isSticky", [], "method", false, false, true, 68)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("node--sticky") : ("")), (((($tmp =  !CoreExtension::getAttribute($this->env, $this->source,         // line 69
($context["node"] ?? null), "isPublished", [], "method", false, false, true, 69)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("node--unpublished") : ("")), (((($tmp =         // line 70
($context["view_mode"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (("node--view-mode-" . \Drupal\Component\Utility\Html::getClass(($context["view_mode"] ?? null)))) : (""))];
        // line 73
        $context["created_date"] = $this->env->getFilter('format_date')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, ($context["node"] ?? null), "getCreatedTime", [], "any", false, false, true, 73), "umami_dates");
        // line 74
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("umami/classy.node"), "html", null, true);
        yield "

<article";
        // line 76
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 76), "html", null, true);
        yield ">

  ";
        // line 78
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title_prefix"] ?? null), "html", null, true);
        yield "
  ";
        // line 79
        if ((($tmp = ($context["label"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 80
            yield "    ";
            if ((($tmp = ($context["page"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 81
                yield "      <header class=\"node__header\">
        <h1 class=\"page-title\">
          ";
                // line 83
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["label"] ?? null), "html", null, true);
                yield "
        </h1>
      </header>
    ";
            } else {
                // line 87
                yield "      <h2";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title_attributes"] ?? null), "html", null, true);
                yield ">
        <a href=\"";
                // line 88
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["url"] ?? null), "html", null, true);
                yield "\" rel=\"bookmark\">";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["label"] ?? null), "html", null, true);
                yield "</a>
      </h2>
    ";
            }
            // line 91
            yield "  ";
        }
        // line 92
        yield "  ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title_suffix"] ?? null), "html", null, true);
        yield "

  ";
        // line 94
        if ((($tmp = ($context["display_submitted"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 95
            yield "    <footer class=\"node__meta\">
      ";
            // line 96
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["author_picture"] ?? null), "html", null, true);
            yield "
      <div";
            // line 97
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["author_attributes"] ?? null), "addClass", ["node__submitted"], "method", false, false, true, 97), "html", null, true);
            yield ">
        ";
            // line 98
            yield t("<span class=\"by-author\">by @author_name</span> @created_date", array("@author_name" => ($context["author_name"] ?? null), "@created_date" => ($context["created_date"] ?? null), ));
            // line 99
            yield "        ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["metadata"] ?? null), "html", null, true);
            yield "
      </div>
    </footer>
  ";
        }
        // line 103
        yield "
  <div";
        // line 104
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["content_attributes"] ?? null), "addClass", ["node__content"], "method", false, false, true, 104), "html", null, true);
        yield ">
    ";
        // line 105
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["content"] ?? null), "html", null, true);
        yield "
  </div>

</article>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["node", "view_mode", "attributes", "title_prefix", "label", "page", "title_attributes", "url", "title_suffix", "display_submitted", "author_picture", "author_attributes", "author_name", "metadata", "content_attributes", "content"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "core/profiles/demo_umami/themes/umami/templates/content/node.html.twig";
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
        return array (  135 => 105,  131 => 104,  128 => 103,  120 => 99,  118 => 98,  114 => 97,  110 => 96,  107 => 95,  105 => 94,  99 => 92,  96 => 91,  88 => 88,  83 => 87,  76 => 83,  72 => 81,  69 => 80,  67 => 79,  63 => 78,  58 => 76,  53 => 74,  51 => 73,  49 => 70,  48 => 69,  47 => 68,  46 => 67,  45 => 66,  44 => 64,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "core/profiles/demo_umami/themes/umami/templates/content/node.html.twig", "/Users/jie.zhou/web/Drupal/drupal-paragraphs/core/profiles/demo_umami/themes/umami/templates/content/node.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 64, "if" => 79, "trans" => 98];
        static $filters = ["clean_class" => 66, "format_date" => 73, "escape" => 74];
        static $functions = ["attach_library" => 74];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if', 'trans'],
                ['clean_class', 'format_date', 'escape'],
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
