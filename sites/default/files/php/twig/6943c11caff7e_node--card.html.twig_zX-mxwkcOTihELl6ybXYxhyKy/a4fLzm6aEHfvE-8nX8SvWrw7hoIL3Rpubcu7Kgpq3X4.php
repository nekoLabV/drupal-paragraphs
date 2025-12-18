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

/* core/profiles/demo_umami/themes/umami/templates/content/node--card.html.twig */
class __TwigTemplate_2d3c33a803b78e881ebc9e867ea49b0a extends Template
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
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("umami/classy.node"), "html", null, true);
        yield "

";
        // line 75
        $context["read_more"] = ('' === $tmp = \Twig\Extension\CoreExtension::captureOutput((function () use (&$context, $macros, $blocks) {
            // line 76
            yield t("View @node.type.entity.label", array("@node.type.entity.label" => CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["node"] ?? null), "type", [], "any", false, false, true, 76), "entity", [], "any", false, false, true, 76), "label", [], "method", false, false, true, 76), ));
            yield from [];
        })())) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 78
        yield "
";
        // line 79
        yield from $this->load("core/profiles/demo_umami/themes/umami/templates/content/node--card.html.twig", 79, 322231335)->unwrap()->yield(CoreExtension::toArray(["attributes" => CoreExtension::getAttribute($this->env, $this->source,         // line 80
($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 80), "content" =>         // line 81
($context["content"] ?? null), "content_attributes" =>         // line 82
($context["content_attributes"] ?? null), "label" =>         // line 83
($context["label"] ?? null), "title_attributes" =>         // line 84
($context["title_attributes"] ?? null), "title_prefix" =>         // line 85
($context["title_prefix"] ?? null), "title_suffix" =>         // line 86
($context["title_suffix"] ?? null), "read_more" =>         // line 87
($context["read_more"] ?? null), "url" =>         // line 88
($context["url"] ?? null)]));
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["node", "view_mode", "attributes", "content", "content_attributes", "label", "title_attributes", "title_prefix", "title_suffix", "url"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "core/profiles/demo_umami/themes/umami/templates/content/node--card.html.twig";
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
        return array (  74 => 88,  73 => 87,  72 => 86,  71 => 85,  70 => 84,  69 => 83,  68 => 82,  67 => 81,  66 => 80,  65 => 79,  62 => 78,  58 => 76,  56 => 75,  51 => 73,  49 => 70,  48 => 69,  47 => 68,  46 => 67,  45 => 66,  44 => 64,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "core/profiles/demo_umami/themes/umami/templates/content/node--card.html.twig", "/Users/como/dev/drupal/drupal-paragraphs/core/profiles/demo_umami/themes/umami/templates/content/node--card.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 64, "trans" => 76, "embed" => 79];
        static $filters = ["clean_class" => 66, "escape" => 73];
        static $functions = ["attach_library" => 73];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'trans', 'embed'],
                ['clean_class', 'escape'],
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


/* core/profiles/demo_umami/themes/umami/templates/content/node--card.html.twig */
class __TwigTemplate_2d3c33a803b78e881ebc9e867ea49b0a___322231335 extends Template
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

        $this->blocks = [
            'content' => [$this, 'block_content'],
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 79
        return "umami:card";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $this->parent = $this->load("umami:card", 79);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["title_attributes", "label", "content", "title_prefix", "title_suffix", "content_attributes", "read_more", "url"]);    }

    // line 90
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_content(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 91
        yield "    ";
        yield from $this->load("core/profiles/demo_umami/themes/umami/templates/content/node--card.html.twig", 91, 1842761274)->unwrap()->yield(CoreExtension::toArray(["attributes" => CoreExtension::getAttribute($this->env, $this->source,         // line 92
($context["title_attributes"] ?? null), "addClass", ["umami-card__title"], "method", false, false, true, 92), "label" => (((($tmp =         // line 93
($context["label"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? (($context["label"] ?? null)) : (CoreExtension::getAttribute($this->env, $this->source, ($context["content"] ?? null), "field_title", [], "any", false, false, true, 93))), "title_prefix" =>         // line 94
($context["title_prefix"] ?? null), "title_suffix" =>         // line 95
($context["title_suffix"] ?? null)]));
        // line 104
        yield "    <div";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["content_attributes"] ?? null), "addClass", ["umami-card__content"], "method", false, false, true, 104), "html", null, true);
        yield ">
      ";
        // line 105
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["content"] ?? null), "html", null, true);
        yield "
    </div>
    ";
        // line 107
        yield from $this->load("core/profiles/demo_umami/themes/umami/templates/content/node--card.html.twig", 107, 2013896469)->unwrap()->yield(CoreExtension::toArray(["attributes" => $this->extensions['Drupal\Core\Template\TwigExtension']->createAttribute(["class" => ["umami-card__read-more"]]), "read_more" =>         // line 109
($context["read_more"] ?? null), "label" =>         // line 110
($context["label"] ?? null), "url" =>         // line 111
($context["url"] ?? null)]));
        // line 120
        yield "  ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "core/profiles/demo_umami/themes/umami/templates/content/node--card.html.twig";
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
        return array (  205 => 120,  203 => 111,  202 => 110,  201 => 109,  200 => 107,  195 => 105,  190 => 104,  188 => 95,  187 => 94,  186 => 93,  185 => 92,  183 => 91,  176 => 90,  164 => 79,  74 => 88,  73 => 87,  72 => 86,  71 => 85,  70 => 84,  69 => 83,  68 => 82,  67 => 81,  66 => 80,  65 => 79,  62 => 78,  58 => 76,  56 => 75,  51 => 73,  49 => 70,  48 => 69,  47 => 68,  46 => 67,  45 => 66,  44 => 64,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "core/profiles/demo_umami/themes/umami/templates/content/node--card.html.twig", "/Users/como/dev/drupal/drupal-paragraphs/core/profiles/demo_umami/themes/umami/templates/content/node--card.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["extends" => 79, "embed" => 91];
        static $filters = ["escape" => 104];
        static $functions = ["create_attribute" => 108];

        try {
            $this->sandbox->checkSecurity(
                ['extends', 'embed'],
                ['escape'],
                ['create_attribute'],
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


/* core/profiles/demo_umami/themes/umami/templates/content/node--card.html.twig */
class __TwigTemplate_2d3c33a803b78e881ebc9e867ea49b0a___1842761274 extends Template
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

        $this->blocks = [
            'title_prefix' => [$this, 'block_title_prefix'],
            'title_suffix' => [$this, 'block_title_suffix'],
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 91
        return "umami:title";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $this->parent = $this->load("umami:title", 91);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["title_prefix", "title_suffix"]);    }

    // line 97
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_title_prefix(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 98
        yield "        ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title_prefix"] ?? null), "html", null, true);
        yield "
      ";
        yield from [];
    }

    // line 100
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_title_suffix(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 101
        yield "        ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title_suffix"] ?? null), "html", null, true);
        yield "
      ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "core/profiles/demo_umami/themes/umami/templates/content/node--card.html.twig";
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
        return array (  329 => 101,  322 => 100,  314 => 98,  307 => 97,  295 => 91,  205 => 120,  203 => 111,  202 => 110,  201 => 109,  200 => 107,  195 => 105,  190 => 104,  188 => 95,  187 => 94,  186 => 93,  185 => 92,  183 => 91,  176 => 90,  164 => 79,  74 => 88,  73 => 87,  72 => 86,  71 => 85,  70 => 84,  69 => 83,  68 => 82,  67 => 81,  66 => 80,  65 => 79,  62 => 78,  58 => 76,  56 => 75,  51 => 73,  49 => 70,  48 => 69,  47 => 68,  46 => 67,  45 => 66,  44 => 64,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "core/profiles/demo_umami/themes/umami/templates/content/node--card.html.twig", "/Users/como/dev/drupal/drupal-paragraphs/core/profiles/demo_umami/themes/umami/templates/content/node--card.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["extends" => 91];
        static $filters = ["escape" => 98];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['extends'],
                ['escape'],
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


/* core/profiles/demo_umami/themes/umami/templates/content/node--card.html.twig */
class __TwigTemplate_2d3c33a803b78e881ebc9e867ea49b0a___2013896469 extends Template
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

        $this->blocks = [
            'a11y' => [$this, 'block_a11y'],
            'text' => [$this, 'block_text'],
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 107
        return "umami:read-more";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $this->parent = $this->load("umami:read-more", 107);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["label", "read_more"]);    }

    // line 113
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_a11y(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 114
        yield "        ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["label"] ?? null), "html", null, true);
        yield "
      ";
        yield from [];
    }

    // line 116
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_text(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 117
        yield "        ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["read_more"] ?? null), "html", null, true);
        yield "
      ";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "core/profiles/demo_umami/themes/umami/templates/content/node--card.html.twig";
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
        return array (  456 => 117,  449 => 116,  441 => 114,  434 => 113,  422 => 107,  329 => 101,  322 => 100,  314 => 98,  307 => 97,  295 => 91,  205 => 120,  203 => 111,  202 => 110,  201 => 109,  200 => 107,  195 => 105,  190 => 104,  188 => 95,  187 => 94,  186 => 93,  185 => 92,  183 => 91,  176 => 90,  164 => 79,  74 => 88,  73 => 87,  72 => 86,  71 => 85,  70 => 84,  69 => 83,  68 => 82,  67 => 81,  66 => 80,  65 => 79,  62 => 78,  58 => 76,  56 => 75,  51 => 73,  49 => 70,  48 => 69,  47 => 68,  46 => 67,  45 => 66,  44 => 64,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "core/profiles/demo_umami/themes/umami/templates/content/node--card.html.twig", "/Users/como/dev/drupal/drupal-paragraphs/core/profiles/demo_umami/themes/umami/templates/content/node--card.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["extends" => 107];
        static $filters = ["escape" => 114];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['extends'],
                ['escape'],
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
