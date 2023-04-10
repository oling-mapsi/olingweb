<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* base.html.twig */
class __TwigTemplate_b1ae54cde74048b6e096a234bf4bc9e9 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
            'title' => [$this, 'block_title'],
            'stylesheets' => [$this, 'block_stylesheets'],
            'javascripts' => [$this, 'block_javascripts'],
            'body' => [$this, 'block_body'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "base.html.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "base.html.twig"));

        // line 1
        echo "<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1, shrink-to-fit=no\">
        <title>";
        // line 6
        $this->displayBlock('title', $context, $blocks);
        echo "</title>
        <link rel=\"shortcut icon\" href=\"/favicon.ico\">
        <link href=\"https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap\" rel=\"stylesheet\">
        ";
        // line 10
        echo "        ";
        $this->displayBlock('stylesheets', $context, $blocks);
        // line 13
        echo "
        ";
        // line 14
        $this->displayBlock('javascripts', $context, $blocks);
        // line 17
        echo "    </head>
    <body>
        <!-- ========== HEADER ========== -->
        <header id=\"header\" class=\"navbar navbar-expand-lg navbar-end navbar-light navbar-absolute-top navbar-show-hide\"
                data-hs-header-options='{
                    \"fixMoment\": 0,
                    \"fixEffect\": \"slide\"
                }'>
            <div class=\"container\">
            <nav class=\"js-mega-menu navbar-nav-wrap\">
                <!-- Default Logo -->
                <a class=\"navbar-brand\" href=";
        // line 28
        echo $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("index");
        echo " aria-label=\"Oling\">
                    <img class=\"navbar-brand-logo\" src=\"";
        // line 29
        echo twig_escape_filter($this->env, $this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("./img/logo/logoling.png"), "html", null, true);
        echo "\" alt=\"Logo\">
                </a>
                <!-- End Default Logo -->

                <!-- Toggler -->
                <button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#navbarNavDropdown\" aria-controls=\"navbarNavDropdown\" aria-expanded=\"false\" aria-label=\"Toggle navigation\">
                <span class=\"navbar-toggler-default\">
                    <i class=\"bi-list\"></i>
                </span>
                <span class=\"navbar-toggler-toggled\">
                    <i class=\"bi-x\"></i>
                </span>
                </button>
                <!-- End Toggler -->
            
                <!-- Collapse -->
                <div class=\"collapse navbar-collapse\" id=\"navbarNavDropdown\">
                <div class=\"navbar-absolute-top-scroller\">
                    <ul class=\"navbar-nav nav-pills\">
                    ";
        // line 48
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["practices"]) || array_key_exists("practices", $context) ? $context["practices"] : (function () { throw new RuntimeError('Variable "practices" does not exist.', 48, $this->source); })()));
        foreach ($context['_seq'] as $context["_key"] => $context["practice"]) {
            // line 49
            echo "            
                    <!-- Landings -->
                    <li class=\"hs-has-mega-menu nav-item\"
                        data-hs-mega-menu-item-options='{
                            \"desktop\": {
                            \"maxWidth\": \"40rem\"
                            }
                        }'>
                        <a id=\"landingsMegaMenu\" class=\"hs-mega-menu-invoker nav-link dropdown-toggle ";
            // line 57
            if ((twig_get_attribute($this->env, $this->source, $context["practice"], "slug", [], "any", false, false, false, 57) == (isset($context["pract"]) || array_key_exists("pract", $context) ? $context["pract"] : (function () { throw new RuntimeError('Variable "pract" does not exist.', 57, $this->source); })()))) {
                echo "active";
            }
            echo "\" aria-current=\"page\" href=\"#\" role=\"button\" aria-expanded=\"false\">";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["practice"], "DesignationShort", [], "any", false, false, false, 57), "html", null, true);
            echo "</a>

                        <!-- Mega Menu -->
                        <div class=\"hs-mega-menu dropdown-menu\" aria-labelledby=\"landingsMegaMenu\" style=\"min-width: 25rem;\">
                        
                        
                        <!-- Main Content -->

                        <div class=\"row\">
                            <div class=\"col-lg d-none d-lg-block\">
                            <div class=\"d-flex align-items-start flex-column bg-light rounded-3 h-100 p-4\">
                                <span class=\"fs-3 fw-bold d-block\">";
            // line 68
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["practice"], "designation", [], "any", false, false, false, 68), "html", null, true);
            echo "</span>
                                <img class=\"img-fluid rounded-2 mb-2\" src=\"";
            // line 69
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["practice"], "image1", [], "any", false, false, false, 69), "html", null, true);
            echo "\" alt=\"Image Description\">
                                    <div>
                                        <p>";
            // line 71
            echo twig_get_attribute($this->env, $this->source, $context["practice"], "description", [], "any", false, false, false, 71);
            echo "</p>
                                    </div>
                                <div class=\"mt-auto\">
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=";
            // line 74
            echo twig_escape_filter($this->env, $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("practice", ["slug" => twig_get_attribute($this->env, $this->source, $context["practice"], "slug", [], "any", false, false, false, 74), "id" => twig_get_attribute($this->env, $this->source, $context["practice"], "id", [], "any", false, false, false, 74)]), "html", null, true);
            echo ">Découvrir</a></p>
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=\"#\">Nos références</a></p>
                                </div>
                            </div>
                            </div>

                            <div class=\"col-sm\">
                                <div class=\"navbar-dropdown-menu-inner\">
                                    ";
            // line 82
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, $context["practice"], "services", [], "any", false, false, false, 82));
            foreach ($context['_seq'] as $context["_key"] => $context["service"]) {
                // line 83
                echo "                                    <a class=\"dropdown-item \" href=";
                echo twig_escape_filter($this->env, $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("service", ["practice" => twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, $context["service"], "practice", [], "any", false, false, false, 83), "slug", [], "any", false, false, false, 83), "id" => twig_get_attribute($this->env, $this->source, $context["service"], "id", [], "any", false, false, false, 83), "slug" => twig_get_attribute($this->env, $this->source, $context["service"], "slug", [], "any", false, false, false, 83)]), "html", null, true);
                echo "> ";
                echo twig_get_attribute($this->env, $this->source, $context["service"], "designation", [], "any", false, false, false, 83);
                echo "</a>
                                    ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['service'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 85
            echo "                                </div>
                            </div>
                        </div>
                        <!-- End Main Content -->
                        </div>
                        <!-- End Mega Menu -->
                    </li>
                    <!-- End Landings -->

                    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['practice'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 95
        echo "


                   

                    <!-- Log in -->
                    <li class=\"nav-item ms-lg-auto\">
                    
                        
                    </li>
                    <!-- End Log in -->

                    <!-- Sign up -->
                    <li class=\"nav-item\">
                        <a class=\"btn btn-primary d-none d-lg-inline-block\" href=";
        // line 109
        echo $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("contact");
        echo ">Contactez-nous</a>
                    </li>
                    <!-- End Sign up -->
                    </ul>
                </div>
                </div>
                <!-- End Collapse -->
            </nav>
            </div>
        </header>

        <!-- ========== END HEADER ========== -->
        ";
        // line 121
        $this->displayBlock('body', $context, $blocks);
        // line 122
        echo "

        <!-- ========== FOOTER ========== -->
        <footer class=\"bg-dark\">
            <div class=\"container\">
            <div class=\"row align-items-center pt-8 pb-4\">
                <div class=\"col-md mb-5 mb-md-0\">
                <h2 class=\"fw-medium text-white-70 mb-0\"><span class=\"fw-bold text-white\">OLING</span><br> Expertise et innovation<br> au service de votre réussite.</h2>
                </div>
                <!-- End Col -->
            
                <div class=\"col-md-auto\">
                    <div class=\"d-grid d-sm-flex gap-3\">
                        <a class=\"btn btn-primary\" href=\"#\">Découvrir</a>
                        <a class=\"btn btn-ghost-light btn-pointer\" href=";
        // line 136
        echo $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("contact");
        echo ">Contactez nous</a>
                    </div>
                </div>
                <!-- End Col -->
            </div>
            <!-- End Row -->

            <div class=\"border-bottom border-white-10\">
                <div class=\"row py-9\">
                <div class=\"col-4 col-sm-4 col-lg mb-7 mb-lg-0\">
                    <span class=\"text-cap text-white\">Practices</span>

                    <!-- List -->
                    <ul class=\"list-unstyled list-py-1 mb-0\">
                    ";
        // line 150
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["practices"]) || array_key_exists("practices", $context) ? $context["practices"] : (function () { throw new RuntimeError('Variable "practices" does not exist.', 150, $this->source); })()));
        foreach ($context['_seq'] as $context["_key"] => $context["practice"]) {
            // line 151
            echo "                    <li><a class=\"link link-light link-light-75\" href=\"";
            echo twig_escape_filter($this->env, $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("practice", ["slug" => twig_get_attribute($this->env, $this->source, $context["practice"], "slug", [], "any", false, false, false, 151), "id" => twig_get_attribute($this->env, $this->source, $context["practice"], "id", [], "any", false, false, false, 151)]), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["practice"], "designation", [], "any", false, false, false, 151), "html", null, true);
            echo "</a></li>
                    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['practice'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 153
        echo "                    </ul>
                    <!-- End List -->
                </div>
                <!-- End Col -->

                <div class=\"col-4 col-sm-4 col-lg mb-7 mb-lg-0\">
                    <span class=\"text-cap text-white\">La société</span>

                    <!-- List -->
                    <ul class=\"list-unstyled list-py-1 mb-0\">
                    <li><a class=\"link link-light link-light-75\" href=\"#\">A propos de OLING <i class=\"bi-box-arrow-up-right ms-1\"></i></a></li>
                    <li><a class=\"link link-light link-light-75\" href=\"#\">L'Equipe </a> <span class=\"fs-6 fw-bold text-primary\">&mdash; Nous recrutons</span></li>
                    <li><a class=\"link link-light link-light-75\" href=\"#\">Notre démarche RSE</a></li>
                    <li><a class=\"link link-light link-light-75\" href=\"#\">MAPSI <i class=\"bi-box-arrow-up-right ms-1\"></i></a></li>
                    </ul>
                    <!-- End List -->
                </div>
                <!-- End Col -->

                <div class=\"col-4 col-sm-4 col-lg mb-7 mb-lg-0\">
                   
                </div>
                <!-- End Col -->

                

                <div class=\"col-4 col-sm-4 col-lg\">
                    <span class=\"text-cap text-white\">Suivez-nous</span>

                    <!-- List -->
                    <ul class=\"list-unstyled list-py-2 mb-0\">
                    
                    <li><a class=\"link link-light link-light-75\" href=\"#\">
                        <div class=\"d-flex\">
                        <div class=\"flex-shrink-0\">
                            <i class=\"bi-linkedin\"></i>
                        </div>

                        <div class=\"flex-grow-1 ms-2\">
                            <span>Linkedin</span>
                        </div>
                        </div>
                    </a></li>
                    
                    <li><a class=\"link link-light link-light-75\" href=\"#\">
                        <div class=\"d-flex\">
                        <div class=\"flex-shrink-0\">
                            <i class=\"bi-twitter\"></i>
                        </div>

                        <div class=\"flex-grow-1 ms-2\">
                            <span>Twitter</span>
                        </div>
                        </div>
                    </a></li>
                    </ul>
                    <!-- End List -->
                </div>
                <!-- End Col -->
                </div>
                <!-- End Row -->
            </div>

            <div class=\"row align-items-md-center py-6\">
                <div class=\"col-md mb-3 mb-md-0\">
                <!-- List -->
                <ul class=\"list-inline list-px-2 mb-0\">
                    <li class=\"list-inline-item\"><a class=\"link link-light link-light-75\" href=\"#\">Politique générale de protection des données</a></li>
                    <li class=\"list-inline-item\"><a class=\"link link-light link-light-75\" href=";
        // line 221
        echo $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("discloser");
        echo ">Mentions légales</a></li>
                    <li class=\"list-inline-item\">

                    </li>
                </ul>
                <!-- End List -->
                </div>
                <!-- End Col -->

                <div class=\"col-md-auto\">
                <p class=\"fs-5 text-white-70 mb-0\">© OLING Management et Technologie 2012 - 2023</p>
                </div>
                <!-- End Col -->
            </div>
            <!-- End Row -->
            </div>
        </footer>
        <!-- ========== END FOOTER ========== -->


    </body>
</html>
";
        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

    }

    // line 6
    public function block_title($context, array $blocks = [])
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "title"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "title"));

        echo "Welcome!";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

    }

    // line 10
    public function block_stylesheets($context, array $blocks = [])
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "stylesheets"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "stylesheets"));

        // line 11
        echo "            ";
        echo $this->extensions['Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension']->renderWebpackLinkTags("app");
        echo "
        ";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

    }

    // line 14
    public function block_javascripts($context, array $blocks = [])
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "javascripts"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "javascripts"));

        // line 15
        echo "            ";
        echo $this->extensions['Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension']->renderWebpackScriptTags("app");
        echo "
        ";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

    }

    // line 121
    public function block_body($context, array $blocks = [])
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "body"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "body"));

        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

    }

    public function getTemplateName()
    {
        return "base.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  444 => 121,  431 => 15,  421 => 14,  408 => 11,  398 => 10,  379 => 6,  346 => 221,  276 => 153,  265 => 151,  261 => 150,  244 => 136,  228 => 122,  226 => 121,  211 => 109,  195 => 95,  180 => 85,  169 => 83,  165 => 82,  154 => 74,  148 => 71,  143 => 69,  139 => 68,  121 => 57,  111 => 49,  107 => 48,  85 => 29,  81 => 28,  68 => 17,  66 => 14,  63 => 13,  60 => 10,  54 => 6,  47 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1, shrink-to-fit=no\">
        <title>{% block title %}Welcome!{% endblock %}</title>
        <link rel=\"shortcut icon\" href=\"/favicon.ico\">
        <link href=\"https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap\" rel=\"stylesheet\">
        {# Run `composer require symfony/webpack-encore-bundle` to start using Symfony UX #}
        {% block stylesheets %}
            {{ encore_entry_link_tags('app') }}
        {% endblock %}

        {% block javascripts %}
            {{ encore_entry_script_tags('app') }}
        {% endblock %}
    </head>
    <body>
        <!-- ========== HEADER ========== -->
        <header id=\"header\" class=\"navbar navbar-expand-lg navbar-end navbar-light navbar-absolute-top navbar-show-hide\"
                data-hs-header-options='{
                    \"fixMoment\": 0,
                    \"fixEffect\": \"slide\"
                }'>
            <div class=\"container\">
            <nav class=\"js-mega-menu navbar-nav-wrap\">
                <!-- Default Logo -->
                <a class=\"navbar-brand\" href={{ path('index')}} aria-label=\"Oling\">
                    <img class=\"navbar-brand-logo\" src=\"{{ asset('./img/logo/logoling.png') }}\" alt=\"Logo\">
                </a>
                <!-- End Default Logo -->

                <!-- Toggler -->
                <button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#navbarNavDropdown\" aria-controls=\"navbarNavDropdown\" aria-expanded=\"false\" aria-label=\"Toggle navigation\">
                <span class=\"navbar-toggler-default\">
                    <i class=\"bi-list\"></i>
                </span>
                <span class=\"navbar-toggler-toggled\">
                    <i class=\"bi-x\"></i>
                </span>
                </button>
                <!-- End Toggler -->
            
                <!-- Collapse -->
                <div class=\"collapse navbar-collapse\" id=\"navbarNavDropdown\">
                <div class=\"navbar-absolute-top-scroller\">
                    <ul class=\"navbar-nav nav-pills\">
                    {% for practice in practices %}
            
                    <!-- Landings -->
                    <li class=\"hs-has-mega-menu nav-item\"
                        data-hs-mega-menu-item-options='{
                            \"desktop\": {
                            \"maxWidth\": \"40rem\"
                            }
                        }'>
                        <a id=\"landingsMegaMenu\" class=\"hs-mega-menu-invoker nav-link dropdown-toggle {% if practice.slug == pract %}active{% endif %}\" aria-current=\"page\" href=\"#\" role=\"button\" aria-expanded=\"false\">{{ practice.DesignationShort }}</a>

                        <!-- Mega Menu -->
                        <div class=\"hs-mega-menu dropdown-menu\" aria-labelledby=\"landingsMegaMenu\" style=\"min-width: 25rem;\">
                        
                        
                        <!-- Main Content -->

                        <div class=\"row\">
                            <div class=\"col-lg d-none d-lg-block\">
                            <div class=\"d-flex align-items-start flex-column bg-light rounded-3 h-100 p-4\">
                                <span class=\"fs-3 fw-bold d-block\">{{ practice.designation }}</span>
                                <img class=\"img-fluid rounded-2 mb-2\" src=\"{{ practice.image1 }}\" alt=\"Image Description\">
                                    <div>
                                        <p>{{ practice.description  | raw  }}</p>
                                    </div>
                                <div class=\"mt-auto\">
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href={{path('practice',{'slug' : practice.slug, 'id' : practice.id} ) }}>Découvrir</a></p>
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=\"#\">Nos références</a></p>
                                </div>
                            </div>
                            </div>

                            <div class=\"col-sm\">
                                <div class=\"navbar-dropdown-menu-inner\">
                                    {% for service in practice.services %}
                                    <a class=\"dropdown-item \" href={{path('service',{'practice' : service.practice.slug, 'id' : service.id, 'slug' : service.slug} ) }}> {{ service.designation  | raw  }}</a>
                                    {% endfor %}
                                </div>
                            </div>
                        </div>
                        <!-- End Main Content -->
                        </div>
                        <!-- End Mega Menu -->
                    </li>
                    <!-- End Landings -->

                    {% endfor %}



                   

                    <!-- Log in -->
                    <li class=\"nav-item ms-lg-auto\">
                    
                        
                    </li>
                    <!-- End Log in -->

                    <!-- Sign up -->
                    <li class=\"nav-item\">
                        <a class=\"btn btn-primary d-none d-lg-inline-block\" href={{ path('contact')}}>Contactez-nous</a>
                    </li>
                    <!-- End Sign up -->
                    </ul>
                </div>
                </div>
                <!-- End Collapse -->
            </nav>
            </div>
        </header>

        <!-- ========== END HEADER ========== -->
        {% block body %}{% endblock %}


        <!-- ========== FOOTER ========== -->
        <footer class=\"bg-dark\">
            <div class=\"container\">
            <div class=\"row align-items-center pt-8 pb-4\">
                <div class=\"col-md mb-5 mb-md-0\">
                <h2 class=\"fw-medium text-white-70 mb-0\"><span class=\"fw-bold text-white\">OLING</span><br> Expertise et innovation<br> au service de votre réussite.</h2>
                </div>
                <!-- End Col -->
            
                <div class=\"col-md-auto\">
                    <div class=\"d-grid d-sm-flex gap-3\">
                        <a class=\"btn btn-primary\" href=\"#\">Découvrir</a>
                        <a class=\"btn btn-ghost-light btn-pointer\" href={{ path('contact')}}>Contactez nous</a>
                    </div>
                </div>
                <!-- End Col -->
            </div>
            <!-- End Row -->

            <div class=\"border-bottom border-white-10\">
                <div class=\"row py-9\">
                <div class=\"col-4 col-sm-4 col-lg mb-7 mb-lg-0\">
                    <span class=\"text-cap text-white\">Practices</span>

                    <!-- List -->
                    <ul class=\"list-unstyled list-py-1 mb-0\">
                    {% for practice in practices %}
                    <li><a class=\"link link-light link-light-75\" href=\"{{path('practice',{'slug' : practice.slug, 'id' : practice.id} ) }}\">{{ practice.designation }}</a></li>
                    {% endfor %}
                    </ul>
                    <!-- End List -->
                </div>
                <!-- End Col -->

                <div class=\"col-4 col-sm-4 col-lg mb-7 mb-lg-0\">
                    <span class=\"text-cap text-white\">La société</span>

                    <!-- List -->
                    <ul class=\"list-unstyled list-py-1 mb-0\">
                    <li><a class=\"link link-light link-light-75\" href=\"#\">A propos de OLING <i class=\"bi-box-arrow-up-right ms-1\"></i></a></li>
                    <li><a class=\"link link-light link-light-75\" href=\"#\">L'Equipe </a> <span class=\"fs-6 fw-bold text-primary\">&mdash; Nous recrutons</span></li>
                    <li><a class=\"link link-light link-light-75\" href=\"#\">Notre démarche RSE</a></li>
                    <li><a class=\"link link-light link-light-75\" href=\"#\">MAPSI <i class=\"bi-box-arrow-up-right ms-1\"></i></a></li>
                    </ul>
                    <!-- End List -->
                </div>
                <!-- End Col -->

                <div class=\"col-4 col-sm-4 col-lg mb-7 mb-lg-0\">
                   
                </div>
                <!-- End Col -->

                

                <div class=\"col-4 col-sm-4 col-lg\">
                    <span class=\"text-cap text-white\">Suivez-nous</span>

                    <!-- List -->
                    <ul class=\"list-unstyled list-py-2 mb-0\">
                    
                    <li><a class=\"link link-light link-light-75\" href=\"#\">
                        <div class=\"d-flex\">
                        <div class=\"flex-shrink-0\">
                            <i class=\"bi-linkedin\"></i>
                        </div>

                        <div class=\"flex-grow-1 ms-2\">
                            <span>Linkedin</span>
                        </div>
                        </div>
                    </a></li>
                    
                    <li><a class=\"link link-light link-light-75\" href=\"#\">
                        <div class=\"d-flex\">
                        <div class=\"flex-shrink-0\">
                            <i class=\"bi-twitter\"></i>
                        </div>

                        <div class=\"flex-grow-1 ms-2\">
                            <span>Twitter</span>
                        </div>
                        </div>
                    </a></li>
                    </ul>
                    <!-- End List -->
                </div>
                <!-- End Col -->
                </div>
                <!-- End Row -->
            </div>

            <div class=\"row align-items-md-center py-6\">
                <div class=\"col-md mb-3 mb-md-0\">
                <!-- List -->
                <ul class=\"list-inline list-px-2 mb-0\">
                    <li class=\"list-inline-item\"><a class=\"link link-light link-light-75\" href=\"#\">Politique générale de protection des données</a></li>
                    <li class=\"list-inline-item\"><a class=\"link link-light link-light-75\" href={{ path('discloser')}}>Mentions légales</a></li>
                    <li class=\"list-inline-item\">

                    </li>
                </ul>
                <!-- End List -->
                </div>
                <!-- End Col -->

                <div class=\"col-md-auto\">
                <p class=\"fs-5 text-white-70 mb-0\">© OLING Management et Technologie 2012 - 2023</p>
                </div>
                <!-- End Col -->
            </div>
            <!-- End Row -->
            </div>
        </footer>
        <!-- ========== END FOOTER ========== -->


    </body>
</html>
", "base.html.twig", "/Users/florestanrouet/myweb/olingweb/templates/base.html.twig");
    }
}
