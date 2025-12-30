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

/* modules/contrib/mercury_editor/templates/layout-paragraphs-builder-component-menu--mercury-editor.html.twig */
class __TwigTemplate_6584d4547c54307cc02b2900e81904df extends Template
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
\t<h4 class=\"visually-hidden\">";
        // line 4
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Add Item"));
        yield "</h4>
\t";
        // line 5
        if ((($context["count"] ?? null) > 0)) {
            // line 6
            yield "\t\t<div class=\"lpb-component-list__search\">
\t\t\t<input class=\"lpb-component-list-search-input\" type=\"text\" placeholder=\"Filter items...\"/>
\t\t</div>
\t\t<div class=\"lpb-component-list__group\">
\t\t\t";
            // line 10
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["groups"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["group"]) {
                // line 11
                yield "\t\t\t\t";
                if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), CoreExtension::getAttribute($this->env, $this->source, $context["group"], "items", [], "any", false, false, true, 11)) > 0)) {
                    // line 12
                    yield "\t\t\t\t<div class=\"lpb-component-list__group--content\">
\t\t\t\t\t<h3 class=\"lpb-component-list__group-label\">";
                    // line 13
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["group"], "label", [], "any", false, false, true, 13), "html", null, true);
                    yield "</h3>
\t\t\t\t\t";
                    // line 14
                    $context['_parent'] = $context;
                    $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, $context["group"], "items", [], "any", false, false, true, 14));
                    foreach ($context['_seq'] as $context["_key"] => $context["type"]) {
                        // line 15
                        yield "\t\t\t\t\t\t";
                        $context["link_attributes"] = CoreExtension::getAttribute($this->env, $this->source, $context["type"], "link_attributes", [], "any", false, false, true, 15);
                        // line 16
                        yield "\t\t\t\t\t\t<div class=\"lpb-component-list__item type-";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["type"], "id", [], "any", false, false, true, 16), "html", null, true);
                        yield "\">
\t\t\t\t\t\t\t";
                        // line 17
                        if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["type"], "image", [], "any", false, false, true, 17)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                            // line 18
                            yield "\t\t\t\t\t\t\t<style>
\t\t\t\t\t\t\t\t.lpb-component-list__item.type-";
                            // line 19
                            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["type"], "id", [], "any", false, false, true, 19), "html", null, true);
                            yield " a::before {
\t\t\t\t\t\t\t\t\tbackground: url(";
                            // line 20
                            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["type"], "image", [], "any", false, false, true, 20), "html", null, true);
                            yield ");
\t\t\t\t\t\t\t\t\tbackground-size: cover;
\t\t\t\t\t\t\t\t}
\t\t\t\t\t\t\t</style>
\t\t\t\t\t\t\t";
                        }
                        // line 25
                        yield "\t\t\t\t\t\t\t<a";
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["type"], "link_attributes", [], "any", false, false, true, 25), "setAttribute", ["href", CoreExtension::getAttribute($this->env, $this->source, $context["type"], "url", [], "any", false, false, true, 25)], "method", false, false, true, 25), "html", null, true);
                        yield ">
\t\t\t\t\t\t\t\t";
                        // line 26
                        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, $context["type"], "label", [], "any", false, false, true, 26), "html", null, true);
                        yield "
\t\t\t\t\t\t\t</a>
\t\t\t\t\t\t</div>
\t\t\t\t\t";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_key'], $context['type'], $context['_parent']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 30
                    yield "\t\t\t\t</div>
\t\t\t\t";
                }
                // line 32
                yield "\t\t\t";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['group'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 33
            yield "\t\t</div>
\t";
        } else {
            // line 35
            yield "\t\t<div class=\"lpb-component-list__empty-message\">
\t\t\t";
            // line 36
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["empty_message"] ?? null), "html", null, true);
            yield "
\t\t</div>
\t";
        }
        // line 39
        yield "</div>

";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["types", "status_messages", "attributes", "count", "groups", "empty_message"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/contrib/mercury_editor/templates/layout-paragraphs-builder-component-menu--mercury-editor.html.twig";
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
        return array (  147 => 39,  141 => 36,  138 => 35,  134 => 33,  128 => 32,  124 => 30,  114 => 26,  109 => 25,  101 => 20,  97 => 19,  94 => 18,  92 => 17,  87 => 16,  84 => 15,  80 => 14,  76 => 13,  73 => 12,  70 => 11,  66 => 10,  60 => 6,  58 => 5,  54 => 4,  50 => 3,  46 => 2,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/contrib/mercury_editor/templates/layout-paragraphs-builder-component-menu--mercury-editor.html.twig", "/Users/como/dev/drupal/drupal-paragraphs/modules/contrib/mercury_editor/templates/layout-paragraphs-builder-component-menu--mercury-editor.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 1, "if" => 5, "for" => 10];
        static $filters = ["merge" => 1, "escape" => 2, "t" => 4, "length" => 11];
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
