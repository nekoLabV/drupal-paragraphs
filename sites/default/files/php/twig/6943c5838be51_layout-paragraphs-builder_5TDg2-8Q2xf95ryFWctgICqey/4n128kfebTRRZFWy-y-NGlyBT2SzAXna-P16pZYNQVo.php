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

/* modules/contrib/layout_paragraphs/templates/layout-paragraphs-builder-component-menu.html.twig */
class __TwigTemplate_9686185fda75165ed113a2d2f3fcad2a extends Template
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
        $context["all_types"] = Twig\Extension\CoreExtension::merge(CoreExtension::getAttribute($this->env, $this->source, ($context["types"] ?? null), "layout", [], "any", false, false, true, 1), CoreExtension::getAttribute($this->env, $this->source, ($context["types"] ?? null), "content", [], "any", false, false, true, 1));
        // line 2
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["status_messages"] ?? null), "html", null, true);
        yield "
<div";
        // line 3
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["attributes"] ?? null), "html", null, true);
        yield ">
  <h4 class=\"visually-hidden\">";
        // line 4
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Add Item"));
        yield "</h4>
  ";
        // line 5
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["all_types"] ?? null)) > 1)) {
            // line 6
            yield "  <div class=\"lpb-component-list__search\">
    <input class=\"lpb-component-list-search-input\" type=\"text\" placeholder=\"";
            // line 7
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Filter items..."));
            yield "\" />
  </div>
  <div class=\"lpb-component-list__group\">
    ";
            // line 10
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["types"] ?? null), "layout", [], "any", false, false, true, 10)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 11
                yield "    <div class=\"lpb-component-list__group--layout\">
    ";
            }
            // line 13
            yield "    ";
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["types"] ?? null), "layout", [], "any", false, false, true, 13));
            foreach ($context['_seq'] as $context["_key"] => $context["type"]) {
                // line 14
                yield "      <div class=\"lpb-component-list__item type-";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["type"], "id", [], "any", false, false, true, 14), "html", null, true);
                yield " is-layout\">
        <a";
                // line 15
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["type"], "link_attributes", [], "any", false, false, true, 15), "setAttribute", ["href", CoreExtension::getAttribute($this->env, $this->source, $context["type"], "url", [], "any", false, false, true, 15)], "method", false, false, true, 15), "html", null, true);
                yield ">";
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["type"], "image", [], "any", false, false, true, 15)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    yield "<img src=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["type"], "image", [], "any", false, false, true, 15), "html", null, true);
                    yield "\" alt =\"\" />";
                }
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["type"], "label", [], "any", false, false, true, 15), "html", null, true);
                yield "</a>
      </div>
    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['type'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 18
            yield "    ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["types"] ?? null), "layout", [], "any", false, false, true, 18)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 19
                yield "    </div>
    ";
            }
            // line 21
            yield "    ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["types"] ?? null), "content", [], "any", false, false, true, 21)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 22
                yield "    <div class=\"lpb-component-list__group--content\">
    ";
            }
            // line 24
            yield "    ";
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["types"] ?? null), "content", [], "any", false, false, true, 24));
            foreach ($context['_seq'] as $context["_key"] => $context["type"]) {
                // line 25
                yield "      <div class=\"lpb-component-list__item type-";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["type"], "id", [], "any", false, false, true, 25), "html", null, true);
                yield "\">
        <a";
                // line 26
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["type"], "link_attributes", [], "any", false, false, true, 26), "setAttribute", ["href", CoreExtension::getAttribute($this->env, $this->source, $context["type"], "url", [], "any", false, false, true, 26)], "method", false, false, true, 26), "html", null, true);
                yield ">";
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["type"], "image", [], "any", false, false, true, 26)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    yield "<img src=\"";
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["type"], "image", [], "any", false, false, true, 26), "html", null, true);
                    yield "\" alt =\"\" />";
                }
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["type"], "label", [], "any", false, false, true, 26), "html", null, true);
                yield "</a>
      </div>
    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['type'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 29
            yield "    ";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, ($context["types"] ?? null), "content", [], "any", false, false, true, 29)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 30
                yield "    </div>
    ";
            }
            // line 32
            yield "  </div>
  ";
        } else {
            // line 34
            yield "  <div class=\"lpb-component-list__empty-message\">
  ";
            // line 35
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["empty_message"] ?? null), "html", null, true);
            yield "
  </div>
  ";
        }
        // line 38
        yield "</div>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["types", "status_messages", "attributes", "empty_message"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/contrib/layout_paragraphs/templates/layout-paragraphs-builder-component-menu.html.twig";
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
        return array (  161 => 38,  155 => 35,  152 => 34,  148 => 32,  144 => 30,  141 => 29,  125 => 26,  120 => 25,  115 => 24,  111 => 22,  108 => 21,  104 => 19,  101 => 18,  85 => 15,  80 => 14,  75 => 13,  71 => 11,  69 => 10,  63 => 7,  60 => 6,  58 => 5,  54 => 4,  50 => 3,  46 => 2,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/contrib/layout_paragraphs/templates/layout-paragraphs-builder-component-menu.html.twig", "/Users/como/dev/drupal/drupal-paragraphs/modules/contrib/layout_paragraphs/templates/layout-paragraphs-builder-component-menu.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 1, "if" => 5, "for" => 13];
        static $filters = ["merge" => 1, "escape" => 2, "t" => 4, "length" => 5];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if', 'for'],
                ['merge', 'escape', 't', 'length'],
                [],
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
