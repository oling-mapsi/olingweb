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
                <a class=\"navbar-brand\" href=\"./\" aria-label=\"Oling\">
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
                    <!-- Landings -->
                    <li class=\"hs-has-mega-menu nav-item\"
                        data-hs-mega-menu-item-options='{
                            \"desktop\": {
                            \"maxWidth\": \"40rem\"
                            }
                        }'>
                        <a id=\"landingsMegaMenu\" class=\"hs-mega-menu-invoker nav-link dropdown-toggle \" aria-current=\"page\" href=\"#\" role=\"button\" aria-expanded=\"false\">Consulting</a>

                        <!-- Mega Menu -->
                        <div class=\"hs-mega-menu dropdown-menu\" aria-labelledby=\"landingsMegaMenu\" style=\"min-width: 25rem;\">
                        <!-- Main Content -->
                        <div class=\"row\">
                            <div class=\"col-lg d-none d-lg-block\">
                            <div class=\"d-flex align-items-start flex-column bg-light rounded-3 h-100 p-4\">
                                <span class=\"fs-3 fw-bold d-block\">Consulting</span>
                                <p class=\"text-body\">Accompagnement personnalisé pour une transformation digitale réussie et performante.</p>
                                <div class=\"mt-auto\">
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=\"#\">Découvrir</a></p>
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=\"#\">A propos</a></p>
                                </div>
                            </div>
                            </div>

                            <div class=\"col-sm\">
                            <div class=\"navbar-dropdown-menu-inner\">
                                <span class=\"dropdown-header\">Une offre sur mesure</span>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-building me-2\"></i> Schéma directeur</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-briefcase me-2\"></i> Etudes</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-chat-right-dots me-2\"></i> Assistance à maitrise d'ouvrage </a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-tropical-storm me-2\"></i> Pilotage des projets d'entreprise</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-gear me-2\"></i> Gouvernance des données</a>
                            </div>
                            </div>
                        </div>
                        <!-- End Main Content -->
                        </div>
                        <!-- End Mega Menu -->
                    </li>
                    <!-- End Landings -->

                    <!-- Landings -->
                    <li class=\"hs-has-mega-menu nav-item\"
                        data-hs-mega-menu-item-options='{
                            \"desktop\": {
                            \"maxWidth\": \"40rem\"
                            }
                        }'>
                        <a id=\"landingsMegaMenu\" class=\"hs-mega-menu-invoker nav-link dropdown-toggle \" aria-current=\"page\" href=\"#\" role=\"button\" aria-expanded=\"false\">Expertises et Audit</a>

                        <!-- Mega Menu -->
                        <div class=\"hs-mega-menu dropdown-menu\" aria-labelledby=\"landingsMegaMenu\" style=\"min-width: 25rem;\">
                        <!-- Main Content -->
                        <div class=\"row\">
                            <div class=\"col-lg d-none d-lg-block\">
                            <div class=\"d-flex align-items-start flex-column bg-light rounded-3 h-100 p-4\">
                                <span class=\"fs-3 fw-bold d-block\">Expertises et Audit</span>
                                <img class=\"img-fluid rounded-2 mb-2\" src=\"/svg/components/card-2.svg\" alt=\"Image Description\">
                                <p class=\"text-body\">Analyse rigoureuse, amélioration continue, conformité réglementaire, renforcement des processus, soutenant votre croissance durable.</p>
                                
                                <div class=\"mt-auto\">
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=\"#\">Découvrir</a></p>
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=\"#\">A propos</a></p>
                                </div>
                            </div>
                            </div>

                            <div class=\"col-sm\">
                            <div class=\"navbar-dropdown-menu-inner\">
                                <span class=\"dropdown-header\">Des compétences certifiées</span>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-building me-2\"></i> Finances</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-briefcase me-2\"></i> Contrôle de gestion</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-briefcase me-2\"></i> Contrôle interne</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-chat-right-dots me-2\"></i> RGPD </a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-tropical-storm me-2\"></i> QSE</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-gear me-2\"></i> QUALIOPI</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-gear me-2\"></i> RSE</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-gear me-2\"></i> Gouvernance des données</a>


                            </div>
                            </div>
                        </div>
                        <!-- End Main Content -->
                        </div>
                        <!-- End Mega Menu -->
                    </li>
                    <!-- End Landings -->


                    <!-- Landings -->
                    <li class=\"hs-has-mega-menu nav-item\"
                            data-hs-mega-menu-item-options='{
                            \"desktop\": {
                                \"maxWidth\": \"40rem\"
                            }
                            }'>
                        <a id=\"blogMegaMenu\" class=\"hs-mega-menu-invoker nav-link dropdown-toggle \" href=\"#\" role=\"button\" aria-expanded=\"false\">Business Apps</a>

                        <!-- Mega Menu -->
                        <div class=\"hs-mega-menu dropdown-menu\" aria-labelledby=\"landingsMegaMenu\" style=\"min-width: 25rem;\">
                        <!-- Main Content -->
                        <div class=\"row\">
                            <div class=\"col-lg d-none d-lg-block\">
                            <div class=\"d-flex align-items-start flex-column bg-light rounded-3 h-100 p-4\">
                                <span class=\"fs-3 fw-bold d-block\">Expertises et Audit</span>
                                <img class=\"img-fluid rounded-2 mb-2\" src=\"/svg/components/card-1.svg\" alt=\"Image Description\">
                                <p class=\"text-body\">Solutions applicatives innovantes, déploiement efficace, soutien aux opérations, adaptabilité, propulsant votre entreprise vers l'avenir.</p>
                                
                                <div class=\"mt-auto\">
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=\"#\">Découvrir</a></p>
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=\"#\">A propos</a></p>
                                </div>
                            </div>
                            </div>

                            <div class=\"col-sm\">
                            <div class=\"navbar-dropdown-menu-inner\">
                                <span class=\"dropdown-header\">De nombreux déploiements</span>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-building me-2\"></i> ERP</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-briefcase me-2\"></i> BI et informatique analytique</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-briefcase me-2\"></i> Développement d'applications</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-chat-right-dots me-2\"></i> SIG </a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-tropical-storm me-2\"></i> GED / GEC</a>


                            </div>
                            </div>
                        </div>
                        <!-- End Main Content -->
                        </div>
                        <!-- End Mega Menu -->
                    </li>
                    <!-- End Landings -->              

                    

                    <!-- Landings -->
                    <li class=\"hs-has-mega-menu nav-item\"
                            data-hs-mega-menu-item-options='{
                            \"desktop\": {
                                \"maxWidth\": \"40rem\"
                            }
                            }'>
                        <a id=\"blogMegaMenu\" class=\"hs-mega-menu-invoker nav-link dropdown-toggle \" href=\"#\" role=\"button\" aria-expanded=\"false\">Microsoft 365</a>

                        <!-- Mega Menu -->
                        <div class=\"hs-mega-menu dropdown-menu\" aria-labelledby=\"landingsMegaMenu\" style=\"min-width: 25rem;\">
                        <!-- Main Content -->
                        <div class=\"row\">
                            <div class=\"col-lg d-none d-lg-block\">
                            <div class=\"d-flex align-items-start flex-column bg-light rounded-3 h-100 p-4\">
                                <span class=\"fs-3 fw-bold d-block\">Solutions collaboratives</span>
                                <img class=\"img-fluid rounded-2 mb-2\" src=\"/svg/components/card-1.svg\" alt=\"Image Description\">
                                <p class=\"text-body\">Collaboration fluide, gestion optimisée, sécurité renforcée, propulsez votre productivité vers de nouveaux horizons</p>
                                
                                <div class=\"mt-auto\">
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=\"#\">Découvrir</a></p>
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=\"#\">A propos</a></p>
                                </div>
                            </div>
                            </div>

                            <div class=\"col-sm\">
                            <div class=\"navbar-dropdown-menu-inner\">
                                <span class=\"dropdown-header\">Teams, Sharepoints, Onedrive...</span>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-building me-2\"></i> Intégration</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-briefcase me-2\"></i> Arborescence</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-briefcase me-2\"></i> Automatisation et Wrokflows</a>


                            </div>
                            </div>
                        </div>
                        <!-- End Main Content -->
                        </div>
                        <!-- End Mega Menu -->
                    </li>
                    <!-- End Landings -->   

                    <!-- Log in -->
                    <li class=\"nav-item ms-lg-auto\">
                    
                        
                    </li>
                    <!-- End Log in -->

                    <!-- Sign up -->
                    <li class=\"nav-item\">
                        <a class=\"btn btn-primary d-none d-lg-inline-block\" href=";
        // line 237
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
        // line 249
        $this->displayBlock('body', $context, $blocks);
        // line 250
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
        // line 264
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
                    <li><a class=\"link link-light link-light-75\" href=\"#\">Conseil en organisation et système d'information</a></li>
                    <li><a class=\"link link-light link-light-75\" href=\"#\">Expertises et Audit</a></li>
                    <li><a class=\"link link-light link-light-75\" href=\"#\">Business Apps</a></li>
                    <li><a class=\"link link-light link-light-75\" href=\"#\">Microsoft 365 <i class=\"bi-box-arrow-up-right ms-1\"></i></a></li>
                    </ul>
                    <!-- End List -->
                </div>
                <!-- End Col -->

                <div class=\"col-4 col-sm-4 col-lg mb-7 mb-lg-0\">
                    <span class=\"text-cap text-white\">La société</span>

                    <!-- List -->
                    <ul class=\"list-unstyled list-py-1 mb-0\">
                    <li><a class=\"link link-light link-light-75\" href=\"#\">Contact <i class=\"bi-box-arrow-up-right ms-1\"></i></a></li>
                    <li><a class=\"link link-light link-light-75\" href=\"#\">L'Equipe OLING </a> <span class=\"fs-6 fw-bold text-primary\">&mdash; Nous recrutons</span></li>
                    <li><a class=\"link link-light link-light-75\" href=\"#\">Notre démarche RSE</a></li>
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
        // line 349
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

    // line 249
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
        return array (  512 => 249,  499 => 15,  489 => 14,  476 => 11,  466 => 10,  447 => 6,  414 => 349,  326 => 264,  310 => 250,  308 => 249,  293 => 237,  82 => 29,  68 => 17,  66 => 14,  63 => 13,  60 => 10,  54 => 6,  47 => 1,);
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
                <a class=\"navbar-brand\" href=\"./\" aria-label=\"Oling\">
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
                    <!-- Landings -->
                    <li class=\"hs-has-mega-menu nav-item\"
                        data-hs-mega-menu-item-options='{
                            \"desktop\": {
                            \"maxWidth\": \"40rem\"
                            }
                        }'>
                        <a id=\"landingsMegaMenu\" class=\"hs-mega-menu-invoker nav-link dropdown-toggle \" aria-current=\"page\" href=\"#\" role=\"button\" aria-expanded=\"false\">Consulting</a>

                        <!-- Mega Menu -->
                        <div class=\"hs-mega-menu dropdown-menu\" aria-labelledby=\"landingsMegaMenu\" style=\"min-width: 25rem;\">
                        <!-- Main Content -->
                        <div class=\"row\">
                            <div class=\"col-lg d-none d-lg-block\">
                            <div class=\"d-flex align-items-start flex-column bg-light rounded-3 h-100 p-4\">
                                <span class=\"fs-3 fw-bold d-block\">Consulting</span>
                                <p class=\"text-body\">Accompagnement personnalisé pour une transformation digitale réussie et performante.</p>
                                <div class=\"mt-auto\">
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=\"#\">Découvrir</a></p>
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=\"#\">A propos</a></p>
                                </div>
                            </div>
                            </div>

                            <div class=\"col-sm\">
                            <div class=\"navbar-dropdown-menu-inner\">
                                <span class=\"dropdown-header\">Une offre sur mesure</span>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-building me-2\"></i> Schéma directeur</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-briefcase me-2\"></i> Etudes</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-chat-right-dots me-2\"></i> Assistance à maitrise d'ouvrage </a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-tropical-storm me-2\"></i> Pilotage des projets d'entreprise</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-gear me-2\"></i> Gouvernance des données</a>
                            </div>
                            </div>
                        </div>
                        <!-- End Main Content -->
                        </div>
                        <!-- End Mega Menu -->
                    </li>
                    <!-- End Landings -->

                    <!-- Landings -->
                    <li class=\"hs-has-mega-menu nav-item\"
                        data-hs-mega-menu-item-options='{
                            \"desktop\": {
                            \"maxWidth\": \"40rem\"
                            }
                        }'>
                        <a id=\"landingsMegaMenu\" class=\"hs-mega-menu-invoker nav-link dropdown-toggle \" aria-current=\"page\" href=\"#\" role=\"button\" aria-expanded=\"false\">Expertises et Audit</a>

                        <!-- Mega Menu -->
                        <div class=\"hs-mega-menu dropdown-menu\" aria-labelledby=\"landingsMegaMenu\" style=\"min-width: 25rem;\">
                        <!-- Main Content -->
                        <div class=\"row\">
                            <div class=\"col-lg d-none d-lg-block\">
                            <div class=\"d-flex align-items-start flex-column bg-light rounded-3 h-100 p-4\">
                                <span class=\"fs-3 fw-bold d-block\">Expertises et Audit</span>
                                <img class=\"img-fluid rounded-2 mb-2\" src=\"/svg/components/card-2.svg\" alt=\"Image Description\">
                                <p class=\"text-body\">Analyse rigoureuse, amélioration continue, conformité réglementaire, renforcement des processus, soutenant votre croissance durable.</p>
                                
                                <div class=\"mt-auto\">
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=\"#\">Découvrir</a></p>
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=\"#\">A propos</a></p>
                                </div>
                            </div>
                            </div>

                            <div class=\"col-sm\">
                            <div class=\"navbar-dropdown-menu-inner\">
                                <span class=\"dropdown-header\">Des compétences certifiées</span>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-building me-2\"></i> Finances</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-briefcase me-2\"></i> Contrôle de gestion</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-briefcase me-2\"></i> Contrôle interne</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-chat-right-dots me-2\"></i> RGPD </a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-tropical-storm me-2\"></i> QSE</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-gear me-2\"></i> QUALIOPI</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-gear me-2\"></i> RSE</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-gear me-2\"></i> Gouvernance des données</a>


                            </div>
                            </div>
                        </div>
                        <!-- End Main Content -->
                        </div>
                        <!-- End Mega Menu -->
                    </li>
                    <!-- End Landings -->


                    <!-- Landings -->
                    <li class=\"hs-has-mega-menu nav-item\"
                            data-hs-mega-menu-item-options='{
                            \"desktop\": {
                                \"maxWidth\": \"40rem\"
                            }
                            }'>
                        <a id=\"blogMegaMenu\" class=\"hs-mega-menu-invoker nav-link dropdown-toggle \" href=\"#\" role=\"button\" aria-expanded=\"false\">Business Apps</a>

                        <!-- Mega Menu -->
                        <div class=\"hs-mega-menu dropdown-menu\" aria-labelledby=\"landingsMegaMenu\" style=\"min-width: 25rem;\">
                        <!-- Main Content -->
                        <div class=\"row\">
                            <div class=\"col-lg d-none d-lg-block\">
                            <div class=\"d-flex align-items-start flex-column bg-light rounded-3 h-100 p-4\">
                                <span class=\"fs-3 fw-bold d-block\">Expertises et Audit</span>
                                <img class=\"img-fluid rounded-2 mb-2\" src=\"/svg/components/card-1.svg\" alt=\"Image Description\">
                                <p class=\"text-body\">Solutions applicatives innovantes, déploiement efficace, soutien aux opérations, adaptabilité, propulsant votre entreprise vers l'avenir.</p>
                                
                                <div class=\"mt-auto\">
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=\"#\">Découvrir</a></p>
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=\"#\">A propos</a></p>
                                </div>
                            </div>
                            </div>

                            <div class=\"col-sm\">
                            <div class=\"navbar-dropdown-menu-inner\">
                                <span class=\"dropdown-header\">De nombreux déploiements</span>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-building me-2\"></i> ERP</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-briefcase me-2\"></i> BI et informatique analytique</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-briefcase me-2\"></i> Développement d'applications</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-chat-right-dots me-2\"></i> SIG </a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-tropical-storm me-2\"></i> GED / GEC</a>


                            </div>
                            </div>
                        </div>
                        <!-- End Main Content -->
                        </div>
                        <!-- End Mega Menu -->
                    </li>
                    <!-- End Landings -->              

                    

                    <!-- Landings -->
                    <li class=\"hs-has-mega-menu nav-item\"
                            data-hs-mega-menu-item-options='{
                            \"desktop\": {
                                \"maxWidth\": \"40rem\"
                            }
                            }'>
                        <a id=\"blogMegaMenu\" class=\"hs-mega-menu-invoker nav-link dropdown-toggle \" href=\"#\" role=\"button\" aria-expanded=\"false\">Microsoft 365</a>

                        <!-- Mega Menu -->
                        <div class=\"hs-mega-menu dropdown-menu\" aria-labelledby=\"landingsMegaMenu\" style=\"min-width: 25rem;\">
                        <!-- Main Content -->
                        <div class=\"row\">
                            <div class=\"col-lg d-none d-lg-block\">
                            <div class=\"d-flex align-items-start flex-column bg-light rounded-3 h-100 p-4\">
                                <span class=\"fs-3 fw-bold d-block\">Solutions collaboratives</span>
                                <img class=\"img-fluid rounded-2 mb-2\" src=\"/svg/components/card-1.svg\" alt=\"Image Description\">
                                <p class=\"text-body\">Collaboration fluide, gestion optimisée, sécurité renforcée, propulsez votre productivité vers de nouveaux horizons</p>
                                
                                <div class=\"mt-auto\">
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=\"#\">Découvrir</a></p>
                                <p class=\"mb-1\"><a class=\"link link-dark link-pointer\" href=\"#\">A propos</a></p>
                                </div>
                            </div>
                            </div>

                            <div class=\"col-sm\">
                            <div class=\"navbar-dropdown-menu-inner\">
                                <span class=\"dropdown-header\">Teams, Sharepoints, Onedrive...</span>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-building me-2\"></i> Intégration</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-briefcase me-2\"></i> Arborescence</a>
                                <a class=\"dropdown-item \" href=\"./\"><i class=\"bi-briefcase me-2\"></i> Automatisation et Wrokflows</a>


                            </div>
                            </div>
                        </div>
                        <!-- End Main Content -->
                        </div>
                        <!-- End Mega Menu -->
                    </li>
                    <!-- End Landings -->   

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
                    <li><a class=\"link link-light link-light-75\" href=\"#\">Conseil en organisation et système d'information</a></li>
                    <li><a class=\"link link-light link-light-75\" href=\"#\">Expertises et Audit</a></li>
                    <li><a class=\"link link-light link-light-75\" href=\"#\">Business Apps</a></li>
                    <li><a class=\"link link-light link-light-75\" href=\"#\">Microsoft 365 <i class=\"bi-box-arrow-up-right ms-1\"></i></a></li>
                    </ul>
                    <!-- End List -->
                </div>
                <!-- End Col -->

                <div class=\"col-4 col-sm-4 col-lg mb-7 mb-lg-0\">
                    <span class=\"text-cap text-white\">La société</span>

                    <!-- List -->
                    <ul class=\"list-unstyled list-py-1 mb-0\">
                    <li><a class=\"link link-light link-light-75\" href=\"#\">Contact <i class=\"bi-box-arrow-up-right ms-1\"></i></a></li>
                    <li><a class=\"link link-light link-light-75\" href=\"#\">L'Equipe OLING </a> <span class=\"fs-6 fw-bold text-primary\">&mdash; Nous recrutons</span></li>
                    <li><a class=\"link link-light link-light-75\" href=\"#\">Notre démarche RSE</a></li>
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
