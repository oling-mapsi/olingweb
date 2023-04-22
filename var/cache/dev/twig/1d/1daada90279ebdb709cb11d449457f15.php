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
        <meta name='robots' content='index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' />
        <title>";
        // line 7
        $this->displayBlock('title', $context, $blocks);
        echo "</title>
        <link rel=\"shortcut icon\" href=\"/favicon.ico\">
        <meta name=\"description\" content=\"OLING Management est une société basée en France et en Guadeloupe. Créée en 2012 afin d&#039;offrir, des prestations digitales expertes...\" />
\t    <link rel=\"canonical\" href=\"https://oling.fr/\" />
        ";
        // line 11
        $this->displayBlock('stylesheets', $context, $blocks);
        // line 14
        echo "
        ";
        // line 15
        $this->displayBlock('javascripts', $context, $blocks);
        // line 18
        echo "    </head>
    <body>
        <!-- ========== HEADER ========== -->
        <header id=\"header\" class=\"navbar navbar-expand-lg navbar-center navbar-light navbar-absolute-top navbar-show-hide\"
                data-hs-header-options='{
                    \"fixMoment\": 0,
                    \"fixEffect\": \"slide\"
                }'>
            <div class=\"container\">
                <nav class=\"js-mega-menu navbar-nav-wrap\">
                    <div class=\"navbar-brand-wrapper\">
                    <!-- Default Logo -->
                    <a class=\"navbar-brand\" href=";
        // line 30
        echo $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("index");
        echo " aria-label=\"Oling\">
                        <img class=\"navbar-brand-logo\" src=\"";
        // line 31
        echo twig_escape_filter($this->env, $this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("./img/logo/logoling.png"), "html", null, true);
        echo "\" alt=\"Logo\">
                    </a>
                    <!-- End Default Logo -->
                    </div>

                    <!-- Toggler -->
                    <button type=\"button\" class=\"navbar-toggler ms-auto\" data-bs-toggle=\"collapse\" data-bs-target=\"#navbarNavMenuLeftAligned\" aria-label=\"Toggle navigation\" aria-expanded=\"false\" aria-controls=\"navbarNavMenuLeftAligned\">
                    <span class=\"navbar-toggler-default\">
                        <i class=\"bi-list mymenuspe\"></i>
                    </span>
                    <span class=\"navbar-toggler-toggled\">
                        <i class=\"bi-x\"></i>
                    </span>
                    </button>
                    <!-- End Toggler -->
                
                    <!-- Collapse -->
                    <div class=\"navbar-nav-wrap-col collapse navbar-collapse\" id=\"navbarNavMenuLeftAligned\">
                    <div class=\"navbar-absolute-top-scroller\">
                        <ul class=\"navbar-nav nav-pills justify-content-start\">
                        ";
        // line 51
        $context["count"] = 0;
        // line 52
        echo "                        ";
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["practices"]) || array_key_exists("practices", $context) ? $context["practices"] : (function () { throw new RuntimeError('Variable "practices" does not exist.', 52, $this->source); })()));
        foreach ($context['_seq'] as $context["_key"] => $context["practice"]) {
            // line 53
            echo "                        ";
            $context["count"] = ((isset($context["count"]) || array_key_exists("count", $context) ? $context["count"] : (function () { throw new RuntimeError('Variable "count" does not exist.', 53, $this->source); })()) + 1);
            // line 54
            echo "                        <!-- Landings -->
                        <li class=\"hs-has-mega-menu nav-item\"
                            data-hs-mega-menu-item-options='{
                                \"desktop\": {
                                ";
            // line 58
            if (((isset($context["count"]) || array_key_exists("count", $context) ? $context["count"] : (function () { throw new RuntimeError('Variable "count" does not exist.', 58, $this->source); })()) > 2)) {
                echo "\"position\": \"right\",";
            }
            // line 59
            echo "                                \"maxWidth\": \"40rem\"
                                }
                            }'>
                            <a id=\"landingsMegaMenu\" class=\"mymenuspe nav-link dropdown-toggle hs-mega-menu-invoker";
            // line 62
            if ((twig_get_attribute($this->env, $this->source, $context["practice"], "slug", [], "any", false, false, false, 62) == (isset($context["pract"]) || array_key_exists("pract", $context) ? $context["pract"] : (function () { throw new RuntimeError('Variable "pract" does not exist.', 62, $this->source); })()))) {
                echo " active";
            }
            echo "\" href=\"#\" role=\"button\" aria-expanded=\"false\">";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["practice"], "designation", [], "any", false, false, false, 62), "html", null, true);
            echo "</a>

                            <!-- Mega Menu -->
                            <div class=\"hs-mega-menu dropdown-menu\" aria-labelledby=\"landingsMegaMenu\" style=\"min-width: 25rem;\">
                            
                            
                            <!-- Main Content -->

                            <div class=\"row\">
                                <div class=\"col-lg d-none d-lg-block\">
                                <div class=\"d-flex align-items-start flex-column bg-light rounded-1 h-100 p-4\">
                                    <span class=\"fs-3 fw-bold d-block\">";
            // line 73
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["practice"], "designation", [], "any", false, false, false, 73), "html", null, true);
            echo "</span>
                                    ";
            // line 75
            echo "                                        <div>
                                            <p>";
            // line 76
            echo twig_get_attribute($this->env, $this->source, $context["practice"], "description", [], "any", false, false, false, 76);
            echo "</p>
                                        </div>
                                    <div class=\"mt-auto\">
                                    <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=";
            // line 79
            echo twig_escape_filter($this->env, $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("practice", ["slug" => twig_get_attribute($this->env, $this->source, $context["practice"], "slug", [], "any", false, false, false, 79), "id" => twig_get_attribute($this->env, $this->source, $context["practice"], "id", [], "any", false, false, false, 79)]), "html", null, true);
            echo ">Découvrir</a></p>
                                    <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=";
            // line 80
            echo $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("client");
            echo ">Nos références</a></p>
                                    </div>
                                </div>
                                </div>

                                <div class=\"col-sm\">
                                    <div class=\"navbar-dropdown-menu-inner\">
                                        ";
            // line 87
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, $context["practice"], "services", [], "any", false, false, false, 87));
            foreach ($context['_seq'] as $context["_key"] => $context["service"]) {
                // line 88
                echo "                                        <a class=\"dropdown-item \" href=";
                echo twig_escape_filter($this->env, $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("service", ["practice" => twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, $context["service"], "practice", [], "any", false, false, false, 88), "slug", [], "any", false, false, false, 88), "id" => twig_get_attribute($this->env, $this->source, $context["service"], "id", [], "any", false, false, false, 88), "slug" => twig_get_attribute($this->env, $this->source, $context["service"], "slug", [], "any", false, false, false, 88)]), "html", null, true);
                echo "> ";
                echo twig_get_attribute($this->env, $this->source, $context["service"], "designation", [], "any", false, false, false, 88);
                echo "</a>
                                        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['service'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 90
            echo "                                    </div>
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
        // line 100
        echo "
                        <!-- Landings -->
                        <li class=\"nav-item\">
                            <a id=\"\" class=\"mymenuspe nav-link\" aria-current=\"page\"  href=";
        // line 103
        echo $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("apropos");
        echo " role=\"button\" aria-expanded=\"false\">Découvrez OLING</a>
                        </li>
                        <!-- End Landings -->



                    

                        ";
        // line 117
        echo "
                        ";
        // line 123
        echo "                        </ul>
                    </div>
                    </div>
                    <!-- End Collapse -->
                </nav>
            </div>
            
        </header>
        ";
        // line 146
        echo "
        <!-- ========== END HEADER ========== -->
        ";
        // line 148
        $this->displayBlock('body', $context, $blocks);
        // line 149
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
                        <div class=\"text-center mx-auto\" style=\"max-width: 32rem;\">
                            <form id=\"newsletter-form\">
                            <!-- Input Card -->
                            <div class=\"input-card border mb-5\">
                                <div class=\"input-card-form\">
                                <label for=\"titreformemail\" class=\"form-label visually-hidden\">Votre email</label>
                                <input type=\"email\" class=\"form-control form-control-lg\" id=\"champsemail\" placeholder=\"Votre email\" aria-label=\"Enter email\">
                                </div>
                                <button type=\"button\" class=\"btn btn-primary btn-lg\" id=\"envoiemail\">S'inscrire à la newsletter</button>
                            </div>
                            <!-- End Input Card -->
                            </form>
                        </div>
                        <!-- End Subscribe -->
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
        // line 189
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["practices"]) || array_key_exists("practices", $context) ? $context["practices"] : (function () { throw new RuntimeError('Variable "practices" does not exist.', 189, $this->source); })()));
        foreach ($context['_seq'] as $context["_key"] => $context["practice"]) {
            // line 190
            echo "                    <li><a class=\"link link-light link-light-75\" href=\"";
            echo twig_escape_filter($this->env, $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("practice", ["slug" => twig_get_attribute($this->env, $this->source, $context["practice"], "slug", [], "any", false, false, false, 190), "id" => twig_get_attribute($this->env, $this->source, $context["practice"], "id", [], "any", false, false, false, 190)]), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["practice"], "designation", [], "any", false, false, false, 190), "html", null, true);
            echo "</a></li>
                    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['practice'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 192
        echo "                    </ul>
                    <!-- End List -->
                </div>
                <!-- End Col -->

                <div class=\"col-4 col-sm-4 col-lg mb-7 mb-lg-0\">
                    <span class=\"text-cap text-white\">La société</span>

                    <!-- List -->
                    <ul class=\"list-unstyled list-py-1 mb-0\">
                    <li><a class=\"link link-light link-light-75\" href=";
        // line 202
        echo $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("apropos");
        echo ">A propos de OLING </a></li>
                    <li><a class=\"link link-light link-light-75\" href=";
        // line 203
        echo $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("team");
        echo ">L'Equipe </a> <span class=\"fs-6 fw-bold text-primary\">&mdash; Nous recrutons</span></li>
                    <li><a class=\"link link-light link-light-75\" href=";
        // line 204
        echo $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("rse");
        echo ">Démarche RSE</a></li>
                    <li><a class=\"link link-light link-light-75\" href=\"https://web.mapsi.fr/\" target=\"_blank\">MAPSI <i class=\"bi-box-arrow-up-right ms-1\"></i></a></li>
                    </ul>
                    <!-- End List -->
                </div>
                <!-- End Col -->

                               <div class=\"col-4 col-sm-4 col-lg\">
                    <span class=\"text-cap text-white\">Contactez-nous</span>
                    <!-- List -->
                    <ul class=\"list-unstyled list-py-2 mb-0\">
                        <li>
                            <div class=\"d-flex\">
                                <div class=\"flex-shrink-0 text-light\">
                                    <i class=\"bi-building\"></i>
                                </div>

                                <div class=\"flex-grow-1 ms-2 text-light\">
                                    <span class=\"text-light\">51 rue Henri Becquerel, 97122 BAIE MAHAULT, Guadeloupe, France</span>
                                </div>
                            </div>
                        </li>
                        <li><a class=\"link link-light link-light-75\" href=";
        // line 226
        echo $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("contact");
        echo ">
                            <div class=\"d-flex\">
                            <div class=\"flex-shrink-0\">
                                <i class=\"bi-envelope\"></i>
                            </div>

                            <div class=\"flex-grow-1 ms-2\">
                                <span>Envoyer un email</span>
                            </div>
                            </div>
                        </a></li>
                    </ul>
                    <!-- End List -->
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
                    <li class=\"list-inline-item\"><a class=\"link link-light link-light-75\" href=";
        // line 283
        echo $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("polrgpd");
        echo ">Politique générale de protection des données</a></li>
                    <li class=\"list-inline-item\"><a class=\"link link-light link-light-75\" href=";
        // line 284
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

    <script>
   
    </script>
    </body>
</html>
";
        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

    }

    // line 7
    public function block_title($context, array $blocks = [])
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "title"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "title"));

        echo "OLING Management";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

    }

    // line 11
    public function block_stylesheets($context, array $blocks = [])
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "stylesheets"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "stylesheets"));

        // line 12
        echo "            ";
        echo $this->extensions['Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension']->renderWebpackLinkTags("app");
        echo "
        ";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

    }

    // line 15
    public function block_javascripts($context, array $blocks = [])
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "javascripts"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "javascripts"));

        // line 16
        echo "            ";
        echo $this->extensions['Symfony\WebpackEncoreBundle\Twig\EntryFilesTwigExtension']->renderWebpackScriptTags("app");
        echo "
        ";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

    }

    // line 148
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
        return array (  511 => 148,  498 => 16,  488 => 15,  475 => 12,  465 => 11,  446 => 7,  411 => 284,  407 => 283,  347 => 226,  322 => 204,  318 => 203,  314 => 202,  302 => 192,  291 => 190,  287 => 189,  245 => 149,  243 => 148,  239 => 146,  229 => 123,  226 => 117,  215 => 103,  210 => 100,  195 => 90,  184 => 88,  180 => 87,  170 => 80,  166 => 79,  160 => 76,  157 => 75,  153 => 73,  135 => 62,  130 => 59,  126 => 58,  120 => 54,  117 => 53,  112 => 52,  110 => 51,  87 => 31,  83 => 30,  69 => 18,  67 => 15,  64 => 14,  62 => 11,  55 => 7,  47 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1, shrink-to-fit=no\">
        <meta name='robots' content='index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' />
        <title>{% block title %}OLING Management{% endblock %}</title>
        <link rel=\"shortcut icon\" href=\"/favicon.ico\">
        <meta name=\"description\" content=\"OLING Management est une société basée en France et en Guadeloupe. Créée en 2012 afin d&#039;offrir, des prestations digitales expertes...\" />
\t    <link rel=\"canonical\" href=\"https://oling.fr/\" />
        {% block stylesheets %}
            {{ encore_entry_link_tags('app') }}
        {% endblock %}

        {% block javascripts %}
            {{ encore_entry_script_tags('app') }}
        {% endblock %}
    </head>
    <body>
        <!-- ========== HEADER ========== -->
        <header id=\"header\" class=\"navbar navbar-expand-lg navbar-center navbar-light navbar-absolute-top navbar-show-hide\"
                data-hs-header-options='{
                    \"fixMoment\": 0,
                    \"fixEffect\": \"slide\"
                }'>
            <div class=\"container\">
                <nav class=\"js-mega-menu navbar-nav-wrap\">
                    <div class=\"navbar-brand-wrapper\">
                    <!-- Default Logo -->
                    <a class=\"navbar-brand\" href={{ path('index')}} aria-label=\"Oling\">
                        <img class=\"navbar-brand-logo\" src=\"{{ asset('./img/logo/logoling.png') }}\" alt=\"Logo\">
                    </a>
                    <!-- End Default Logo -->
                    </div>

                    <!-- Toggler -->
                    <button type=\"button\" class=\"navbar-toggler ms-auto\" data-bs-toggle=\"collapse\" data-bs-target=\"#navbarNavMenuLeftAligned\" aria-label=\"Toggle navigation\" aria-expanded=\"false\" aria-controls=\"navbarNavMenuLeftAligned\">
                    <span class=\"navbar-toggler-default\">
                        <i class=\"bi-list mymenuspe\"></i>
                    </span>
                    <span class=\"navbar-toggler-toggled\">
                        <i class=\"bi-x\"></i>
                    </span>
                    </button>
                    <!-- End Toggler -->
                
                    <!-- Collapse -->
                    <div class=\"navbar-nav-wrap-col collapse navbar-collapse\" id=\"navbarNavMenuLeftAligned\">
                    <div class=\"navbar-absolute-top-scroller\">
                        <ul class=\"navbar-nav nav-pills justify-content-start\">
                        {% set count = 0 %}
                        {% for practice in practices %}
                        {% set count = count + 1 %}
                        <!-- Landings -->
                        <li class=\"hs-has-mega-menu nav-item\"
                            data-hs-mega-menu-item-options='{
                                \"desktop\": {
                                {% if count > 2 %}\"position\": \"right\",{% endif %}
                                \"maxWidth\": \"40rem\"
                                }
                            }'>
                            <a id=\"landingsMegaMenu\" class=\"mymenuspe nav-link dropdown-toggle hs-mega-menu-invoker{% if practice.slug == pract %} active{% endif %}\" href=\"#\" role=\"button\" aria-expanded=\"false\">{{ practice.designation }}</a>

                            <!-- Mega Menu -->
                            <div class=\"hs-mega-menu dropdown-menu\" aria-labelledby=\"landingsMegaMenu\" style=\"min-width: 25rem;\">
                            
                            
                            <!-- Main Content -->

                            <div class=\"row\">
                                <div class=\"col-lg d-none d-lg-block\">
                                <div class=\"d-flex align-items-start flex-column bg-light rounded-1 h-100 p-4\">
                                    <span class=\"fs-3 fw-bold d-block\">{{ practice.designation }}</span>
                                    {# <img class=\"img-fluid rounded-2 mb-2\" src=\"{{ practice.image1 }}\" alt=\"Image Description\"> #}
                                        <div>
                                            <p>{{ practice.description  | raw  }}</p>
                                        </div>
                                    <div class=\"mt-auto\">
                                    <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href={{path('practice',{'slug' : practice.slug, 'id' : practice.id} ) }}>Découvrir</a></p>
                                    <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href={{path('client') }}>Nos références</a></p>
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

                        <!-- Landings -->
                        <li class=\"nav-item\">
                            <a id=\"\" class=\"mymenuspe nav-link\" aria-current=\"page\"  href={{ path('apropos')}} role=\"button\" aria-expanded=\"false\">Découvrez OLING</a>
                        </li>
                        <!-- End Landings -->



                    

                        {# <!-- Log in -->
                        <li class=\"nav-item ms-lg-auto\">
                        
                            
                        </li>
                        <!-- End Log in --> #}

                        {# <!-- Sign up -->
                        <li class=\"nav-item\">
                            <a class=\"btn btn-sm btn-primary d-none d-lg-inline-block\" href={{ path('contact')}}>Contactez-nous</a>
                        </li>
                        <!-- End Sign up --> #}
                        </ul>
                    </div>
                    </div>
                    <!-- End Collapse -->
                </nav>
            </div>
            
        </header>
        {# <div class=\"container mt-10\">
            <nav class=\"\">
                <ul class=\"nav\" id=\"myNav\">
                    <li class=\"nav-item\">
                        <a class=\"nav-link active\" href=\"#section1\" data-scroll=\"#section1\">Section 1</a>
                    </li>
                    <li class=\"nav-item\">
                        <a class=\"nav-link\" href=\"#section2\" data-scroll=\"#section2\">Section 2</a>
                    </li>
                    <li class=\"nav-item\">
                        <a class=\"nav-link\" href=\"#section3\" data-scroll=\"#section3\">Section 3</a>
                    </li>
                </ul>
            </nav>
        </div> #}

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
                        <div class=\"text-center mx-auto\" style=\"max-width: 32rem;\">
                            <form id=\"newsletter-form\">
                            <!-- Input Card -->
                            <div class=\"input-card border mb-5\">
                                <div class=\"input-card-form\">
                                <label for=\"titreformemail\" class=\"form-label visually-hidden\">Votre email</label>
                                <input type=\"email\" class=\"form-control form-control-lg\" id=\"champsemail\" placeholder=\"Votre email\" aria-label=\"Enter email\">
                                </div>
                                <button type=\"button\" class=\"btn btn-primary btn-lg\" id=\"envoiemail\">S'inscrire à la newsletter</button>
                            </div>
                            <!-- End Input Card -->
                            </form>
                        </div>
                        <!-- End Subscribe -->
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
                    <li><a class=\"link link-light link-light-75\" href={{path('apropos') }}>A propos de OLING </a></li>
                    <li><a class=\"link link-light link-light-75\" href={{path('team') }}>L'Equipe </a> <span class=\"fs-6 fw-bold text-primary\">&mdash; Nous recrutons</span></li>
                    <li><a class=\"link link-light link-light-75\" href={{path('rse') }}>Démarche RSE</a></li>
                    <li><a class=\"link link-light link-light-75\" href=\"https://web.mapsi.fr/\" target=\"_blank\">MAPSI <i class=\"bi-box-arrow-up-right ms-1\"></i></a></li>
                    </ul>
                    <!-- End List -->
                </div>
                <!-- End Col -->

                               <div class=\"col-4 col-sm-4 col-lg\">
                    <span class=\"text-cap text-white\">Contactez-nous</span>
                    <!-- List -->
                    <ul class=\"list-unstyled list-py-2 mb-0\">
                        <li>
                            <div class=\"d-flex\">
                                <div class=\"flex-shrink-0 text-light\">
                                    <i class=\"bi-building\"></i>
                                </div>

                                <div class=\"flex-grow-1 ms-2 text-light\">
                                    <span class=\"text-light\">51 rue Henri Becquerel, 97122 BAIE MAHAULT, Guadeloupe, France</span>
                                </div>
                            </div>
                        </li>
                        <li><a class=\"link link-light link-light-75\" href={{path('contact') }}>
                            <div class=\"d-flex\">
                            <div class=\"flex-shrink-0\">
                                <i class=\"bi-envelope\"></i>
                            </div>

                            <div class=\"flex-grow-1 ms-2\">
                                <span>Envoyer un email</span>
                            </div>
                            </div>
                        </a></li>
                    </ul>
                    <!-- End List -->
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
                    <li class=\"list-inline-item\"><a class=\"link link-light link-light-75\" href={{ path('polrgpd')}}>Politique générale de protection des données</a></li>
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

    <script>
   
    </script>
    </body>
</html>
", "base.html.twig", "/Users/florestanrouet/myweb/olingweb/templates/base.html.twig");
    }
}
