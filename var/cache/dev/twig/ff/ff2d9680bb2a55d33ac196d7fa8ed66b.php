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

/* index.html.twig */
class __TwigTemplate_1c35536ceb22ef15f5a3559fdb986f18 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->blocks = [
            'title' => [$this, 'block_title'],
            'body' => [$this, 'block_body'],
        ];
    }

    protected function doGetParent(array $context)
    {
        // line 1
        return "base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "index.html.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "index.html.twig"));

        $this->parent = $this->loadTemplate("base.html.twig", "index.html.twig", 1);
        $this->parent->display($context, array_merge($this->blocks, $blocks));
        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

    }

    // line 3
    public function block_title($context, array $blocks = [])
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "title"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "title"));

        echo "OLING";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

    }

    // line 5
    public function block_body($context, array $blocks = [])
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "body"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "block", "body"));

        // line 6
        echo "<body>
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
          <img class=\"navbar-brand-logo\" src=\"/img/logoling.png\" alt=\"Image Description\">
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
                        <a class=\"dropdown-item \" href=\"./index.html\"><i class=\"bi-building me-2\"></i> Schéma directeur</a>
                        <a class=\"dropdown-item \" href=\"./landing-business.html\"><i class=\"bi-briefcase me-2\"></i> Etudes <span class=\"badge text-primary\">New</span></a>
                        <a class=\"dropdown-item active\" href=\"./landing-consulting.html\"><i class=\"bi-chat-right-dots me-2\"></i> Assistance à maitrise d'ouvrage <span class=\"badge text-primary\">New</span></a>
                        <a class=\"dropdown-item \" href=\"./landing-saas.html\"><i class=\"bi-tropical-storm me-2\"></i> Pilotage des projets d'entreprise</a>
                        <a class=\"dropdown-item \" href=\"./landing-services.html\"><i class=\"bi-gear me-2\"></i> Gouvernance des données</a>
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
                        <a class=\"dropdown-item \" href=\"./index.html\"><i class=\"bi-building me-2\"></i> Finances</a>
                        <a class=\"dropdown-item \" href=\"./landing-business.html\"><i class=\"bi-briefcase me-2\"></i> Contrôle de gestion</a>
                        <a class=\"dropdown-item \" href=\"./landing-business.html\"><i class=\"bi-briefcase me-2\"></i> Contrôle interne</a>
                        <a class=\"dropdown-item active\" href=\"./landing-consulting.html\"><i class=\"bi-chat-right-dots me-2\"></i> RGPD <span class=\"badge text-primary\">New</span></a>
                        <a class=\"dropdown-item \" href=\"./landing-saas.html\"><i class=\"bi-tropical-storm me-2\"></i> QSE</a>
                        <a class=\"dropdown-item \" href=\"./landing-services.html\"><i class=\"bi-gear me-2\"></i> QUALIOPI</a>
                        <a class=\"dropdown-item \" href=\"./landing-services.html\"><i class=\"bi-gear me-2\"></i> RSE</a>
                        <a class=\"dropdown-item \" href=\"./landing-services.html\"><i class=\"bi-gear me-2\"></i> Gouvernance des données</a>


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
                        <a class=\"dropdown-item \" href=\"./index.html\"><i class=\"bi-building me-2\"></i> ERP</a>
                        <a class=\"dropdown-item \" href=\"./landing-business.html\"><i class=\"bi-briefcase me-2\"></i> BI et informatique analytique</a>
                        <a class=\"dropdown-item \" href=\"./landing-business.html\"><i class=\"bi-briefcase me-2\"></i> Développement d'applications</a>
                        <a class=\"dropdown-item active\" href=\"./landing-consulting.html\"><i class=\"bi-chat-right-dots me-2\"></i> SIG <span class=\"badge text-primary\">New</span></a>
                        <a class=\"dropdown-item \" href=\"./landing-saas.html\"><i class=\"bi-tropical-storm me-2\"></i> GED / GEC</a>


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
                        <a class=\"dropdown-item \" href=\"./index.html\"><i class=\"bi-building me-2\"></i> Intégration</a>
                        <a class=\"dropdown-item \" href=\"./landing-business.html\"><i class=\"bi-briefcase me-2\"></i> Arborescence</a>
                        <a class=\"dropdown-item \" href=\"./landing-business.html\"><i class=\"bi-briefcase me-2\"></i> Automatisation et Wrokflows</a>


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
                <a class=\"btn btn-primary d-none d-lg-inline-block\" href=\"./page-signup.html\">Contactez-nous</a>
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

  <!-- ========== MAIN CONTENT ========== -->
  <main id=\"content\" role=\"main\">
    <!-- Hero -->
    <div class=\"container content-space-t-3\">
      <div class=\"row justify-content-lg-between align-items-lg-center\">
        <div class=\"col-lg-5 mb-5 mb-lg-0\">
          <div class=\"mb-5\">
            <h1 class=\"display-4 text-dark mb-5\"> <span class=\"text-primary\">Innover</span> ensemble, <span class=\"text-primary\">réussir</span> ensemble </h1>
            <p class=\"fs-3\">OLING est une société composée d'expert en projets fonctionnels et informatiques pour les entreprises publiques et privées</p>
          </div>

          <div class=\"d-grid d-sm-flex gap-3 mb-5\">
            <a class=\"btn btn-primary\" href=\"#\">Découvrir les solutions</a>
            <a class=\"btn btn-ghost-dark btn-pointer\" href=\"#\">Contactez-nous</a>
          </div>
        </div>
        <!-- End Col -->

        <div class=\"col-lg-6\">
          <div class=\"position-relative\">
            <div class=\"position-relative\">
              <img class=\"img-fluid\" src=\"/img/950x950/img1.jpg\" alt=\"Image Description\">

              <div class=\"position-absolute bottom-0 end-0\">
                <img class=\"w-100\" src=\"/svg/illustrations/cubics.svg\" alt=\"SVG\" style=\"max-width: 30rem;\">
              </div>
            </div>

            
          </div>
        </div>
        <!-- End Col -->
      </div>
      <!-- End Row -->
    </div>
    <!-- End Hero -->



    <!-- Stats -->
    <div class=\"container content-space-2 content-space-lg-3\">
      <div class=\"row justify-content-lg-between align-items-lg-center\">
        <div class=\"col-lg-5 mb-9 mb-lg-0\">
          <div class=\"mb-6\">
            <h2>Votre partenaire pour réussir vos projets d'entreprise</h2>
            <p>OLING est une entreprise de conseil experte en projets fonctionnels et informatiques pour les entreprises publiques et privées. </p>
            <p>Avec une forte expérience dans les problématiques d'entreprise et une expertise méthodologique de haut niveau, 
            OLING offre des solutions sur mesure pour répondre aux besoins spécifiques de ses clients, pour la réussite de leurs projets d'entreprise.</p>
          </div>


        </div>
        <!-- End Col -->

        <div class=\"col-lg-6\">
          <!-- List -->
          <ul class=\"list-equal-height list-equal-height-2-cols\">
            <li class=\"list-equal-height-item\">
              <h4 class=\"display-5\">50 %</h4>
              <p class=\"mb-0\">Secteur public</p>
            </li>

            <li class=\"list-equal-height-item\">
              <h4 class=\"display-5\">50 %</h4>
              <p class=\"mb-0\">Secteur privé</p>
            </li>

            <li class=\"list-equal-height-item\">
              <h4 class=\"display-5\">150+</h4>
              <p class=\"mb-0\">projets conduits dans les outremers et l'hexagone</p>
            </li>

            <li class=\"list-equal-height-item\">
              <h4 class=\"display-5\"><sub><i class=\"bi-arrow-up-short text-primary ms-n2\"></i></sub>10</h4>
              <p class=\"mb-0\">consultants experts</p>
            </li>
          </ul>
          <!-- End List -->
        </div>
        <!-- End Col -->
      </div>
      <!-- End Row -->
    </div>
    <!-- End Stats -->


    <!-- Heading -->
    <div class=\"container content-space-t-2 content-space-t-lg-0 content-space-b-1\">
      <div class=\"w-lg-65 text-center mx-lg-auto\">
        <h2>Des solutions sur mesure pour répondre à vos besoins spécifiques</h2>
        <p>OLING est structurée autour de quatre \"practices\", 
        chacune dédiée à un domaine spécifique de l'entreprise : le conseil en organisation et SI, les expertises et audits, 
        les applications métier et la transformation digitale. Grâce à l'utilisation de profils experts et de méthodologies éprouvées, 
        OLING offre des solutions sur mesure pour répondre aux besoins spécifiques de ses clients.</p>
      </div>
    </div>
    <!-- End Heading -->

    <!-- Step Features -->
    <div class=\"container\">
      <!-- List Step -->
      <ul class=\"list-unstyled list-step list-py-3 mb-0\">
        <li class=\"list-step-item\">
          <div class=\"position-relative rounded-3 py-10 px-4 px-md-10\">
            <div class=\"row align-items-lg-center\">
              <div class=\"col-lg-5 mb-7 mb-lg-0\">
                <div class=\"pe-lg-5\">
                  <div class=\"mb-5\">
                    <span class=\"text-cap text-primary\">Conseil en Organisation et SI</span>
                    <h2>Ensemble, optimisons votre organisation et votre système d'information</h2>
                    <p>Solutions pour optimiser votre organisation et votre système d'information</p>
                  </div>

                  <!-- List Checked -->
                  <ul class=\"list-checked list-checked-primary mb-7\">
                    <li class=\"list-checked-item\">Assistance à maîtrise d'ouvrage</li>
                    <li class=\"list-checked-item\">Gouvernance des SI</li>
                    <li class=\"list-checked-item\">Management des processus</li>
                    <li class=\"list-checked-item\">Management des données</li>
                    <li class=\"list-checked-item\">Plan stratégique et pilotage des projets d'entreprise</li>
                  </ul>
                  <!-- End List Checked -->

                  <a class=\"link link-pointer\" href=\"#\">En savoir plus</a>
                </div>
              </div>
              <!-- End Col -->

              <div class=\"col-lg-7\">
                <img class=\"img-fluid\" src=\"/img/mockups/img1.png\" alt=\"Image Description\">
              </div>
              <!-- End Col -->
            </div>
            <!-- End Row -->

            <div class=\"position-absolute top-0 start-0 w-100 w-lg-65 h-65 h-lg-100 bg-light rounded-3 zi-n1 ms-n5\"></div>
          </div>
        </li>

        <li class=\"list-step-item\">
          <div class=\"position-relative rounded-3 py-10 px-4 px-md-10\">
            <div class=\"row align-items-lg-center\">
              <div class=\"col-lg-5 mb-7 mb-lg-0\">
                <div class=\"pe-lg-5\">
                  <div class=\"mb-5\">
                    <span class=\"text-cap text-primary\">Audit et expertises</span>
                    <h2>Un regard extérieur pour améliorer votre organisation</h2>
                    <p>OLING propose des services d'audit et d'expertise pour aider les entreprises publiques et privées à atteindre leurs objectifs.</p>
                  </div>


                  <!-- List Checked -->
                  <ul class=\"list-checked list-checked-primary mb-7\">
                    <li class=\"list-checked-item\">Contrôle de gestion</li>
                    <li class=\"list-checked-item\">Organisation et processus</li>
                    <li class=\"list-checked-item\">SMI QSE</li>
                    <li class=\"list-checked-item\">RSE</li>
                    <li class=\"list-checked-item\">FINANCES</li>
                    <li class=\"list-checked-item\">RGPD</li>
                    <li class=\"list-checked-item\">Contrôle interne</li>
                  </ul>
                  <!-- End List Checked -->

                  <a class=\"link link-pointer\" href=\"#\">En savoir plus</a>
                </div>
              </div>
              <!-- End Col -->

              <div class=\"col-lg-7\">
                <img class=\"img-fluid\" src=\"/img/mockups/img2.png\" alt=\"Image Description\">
              </div>
              <!-- End Col -->
            </div>
            <!-- End Row -->

            <div class=\"position-absolute top-0 start-0 w-100 w-lg-65 h-65 h-lg-100 bg-light rounded-3 zi-n1 ms-n5\"></div>
          </div>
        </li>

        <li class=\"list-step-item\">
          <div class=\"position-relative rounded-3 py-10 px-4 px-md-10\">
            <div class=\"row align-items-lg-center\">
              <div class=\"col-lg-5 mb-7 mb-lg-0\">
                <div class=\"pe-lg-5\">
                  <div class=\"mb-5\">
                    <span class=\"text-cap text-primary\">Business Apps</span>
                    <h2>Des applications métier sur mesure pour votre entreprise</h2>
                    <p>OLING offre des services de développement d'applications métier pour les entreprises publiques et privées</p>
                  </div>

                  <!-- List Checked -->
                  <ul class=\"list-checked list-checked-primary mb-7\">
                    <li class=\"list-checked-item\">Conception, déploiement, AMOA, AMOE des solutions applicatives ERP</li>
                    <li class=\"list-checked-item\">Informatique analytique et BI</li>
                    <li class=\"list-checked-item\">GED/GEC</li>
                    <li class=\"list-checked-item\">SIG</li>
                    <li class=\"list-checked-item\">Développement d'applications métier</li>
                  </ul>
                  <!-- End List Checked -->

                  <a class=\"link link-pointer\" href=\"#\">En savoir plus</a>
                </div>
              </div>
              <!-- End Col -->

              <div class=\"col-lg-7\">
                <img class=\"img-fluid\" src=\"/img/mockups/img3.png\" alt=\"Image Description\">
              </div>
              <!-- End Col -->
            </div>
            <!-- End Row -->

            <div class=\"position-absolute top-0 start-0 w-100 w-lg-65 h-65 h-lg-100 bg-light rounded-3 zi-n1 ms-n5\"></div>
          </div>
        </li>

        <li class=\"list-step-item\">
          <div class=\"position-relative rounded-3 py-10 px-4 px-md-10\">
            <div class=\"row align-items-lg-center\">
              <div class=\"col-lg-5 mb-7 mb-lg-0\">
                <div class=\"pe-lg-5\">
                  <div class=\"mb-5\">
                    <span class=\"text-cap text-primary\">Microsoft 365</span>
                    <h2>La transformation digitale, avec Microsoft 365 et OLING</h2>
                    <p>OLING propose des services de transformation digitale avec la suite Microsoft 365 
                    pour aider les entreprises publiques et privées à s'adapter aux changements technologiques.</p>
                  </div>

                  <!-- List Checked -->
                  <ul class=\"list-checked list-checked-primary mb-7\">
                    <li class=\"list-checked-item\">Mise en place de solutions digitales avec Microsoft 365</li>
                    <li class=\"list-checked-item\">Arborescence métier</li>
                    <li class=\"list-checked-item\">Administration Azure AD</li>
                    <li class=\"list-checked-item\">Messagerie et calendrier</li>
                    <li class=\"list-checked-item\">Collaboratif</li>
                    <li class=\"list-checked-item\">Conseil en stratégie digitale</li>
                    <li class=\"list-checked-item\">Accompagnement au changement</li>
                  </ul>
                  <!-- End List Checked -->

                  <a class=\"link link-pointer\" href=\"#\">Explore this step</a>
                </div>
              </div>
              <!-- End Col -->

              <div class=\"col-lg-7\">
                <img class=\"img-fluid\" src=\"/img/mockups/img2.png\" alt=\"Image Description\">
              </div>
              <!-- End Col -->
            </div>
            <!-- End Row -->

            <div class=\"position-absolute top-0 start-0 w-100 w-lg-65 h-65 h-lg-100 bg-light rounded-3 zi-n1 ms-n5\"></div>
          </div>
        </li>
      </ul>
      <!-- End List Step -->
    </div>
    <!-- End Step Features -->

    
    <!-- Features -->
    <div class=\"overflow-hidden\">
      <div class=\"container content-space-2 content-space-lg-3\">
        <div class=\"position-relative mb-10\">
          <div class=\"row justify-content-lg-between\">
            <div class=\"col-md-6 col-lg-5 mb-7 mb-lg-0\">
              <div class=\"mb-5\">
                <h2>Vous cherchez un partenaire qui fournit une expertise adaptée à votre métier ?</h2>
                <p> OLING, société de conseil spécialisée dans l'accompagnement des entreprises publiques et privées, 
                vous offre une expertise sur-mesure pour répondre à vos défis métiers et informatiques.</p> 
                <p>Découvrez nos capacités pour vous aider à innover, à vous conformer aux réglementations et à optimiser vos processus.</p>
              </div>

              <a class=\"link link-pointer\" href=\"#\">Découvrez notre méthode</a>
            </div>
            <!-- End Col -->

            <div class=\"col-md-6\">
              <div class=\"mb-6\">
                <h3>Un partenaire expert pour une transformation adaptée à votre métier</h3>
              </div>

              <div class=\"mb-3\">
                <i class=\"bi-app-indicator fs-2 text-dark\"></i>
              </div>

              <h5>Services publiques</h5>

              <div class=\"row mb-5\">
                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">Marketing Websites</li>
                    <li class=\"list-checked-item\">Landing Pages</li>
                  </ul>
                </div>
                <!-- End Col -->

                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">Design System</li>
                    <li class=\"list-checked-item\">Icon Design</li>
                  </ul>
                </div>
                <!-- End Col -->
              </div>
              <!-- End Row -->

              <div class=\"mb-3\">
                <i class=\"bi-circle-square fs-2 text-dark\"></i>
              </div>

              <h5>Négoces et distribution</h5>

              <div class=\"row mb-5\">
                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">User Research</li>
                    <li class=\"list-checked-item\">Usability Testing</li>

                  </ul>
                </div>
                <!-- End Col -->

                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">Wireframes</li>
                    <li class=\"list-checked-item\">Some of my current</li>
                  </ul>
                </div>
                <!-- End Col -->
              </div>
              <!-- End Row -->
                <div class=\"mb-3\">
                <i class=\"bi-circle-square fs-2 text-dark\"></i>
              </div>

              <h5>Education</h5>

              <div class=\"row mb-5\">
                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">User Research</li>
                    <li class=\"list-checked-item\">Usability Testing</li>

                  </ul>
                </div>
                <!-- End Col -->

                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">Wireframes</li>
                    <li class=\"list-checked-item\">Some of my current</li>
                  </ul>
                </div>
                <!-- End Col -->


                <div class=\"mb-3\">
                <i class=\"bi-circle-square fs-2 text-dark\"></i>
              </div>

              <h5>Education</h5>

              <div class=\"row mb-5\">
                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">User Research</li>
                    <li class=\"list-checked-item\">Usability Testing</li>

                  </ul>
                </div>
                <!-- End Col -->

                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">Wireframes</li>
                    <li class=\"list-checked-item\">Some of my current</li>
                  </ul>
                </div>
                <!-- End Col -->
              </div>
              <!-- End Row -->




              </div>
              <!-- End Row -->
            </div>
            <!-- End Col -->
          </div>
          <!-- End Row -->
          

          <!-- SVG Shape -->
          <figure class=\"position-absolute top-0 end-0 me-n7\" style=\"width: 4rem;\">
            <img class=\"img-fluid\" src=\"/svg/components/pointer-up.svg\" alt=\"Image Description\">
          </figure>
          <!-- End SVG Shape -->

          <!-- SVG Shape -->
          <figure class=\"position-absolute bottom-0 end-0 start-sm-0 mb-n8 ms-n8\" style=\"width: 12rem;\">
            <img class=\"img-fluid\" src=\"/svg/components/curved-shape.svg\" alt=\"Image Description\">
          </figure>
          <!-- End SVG Shape -->
        </div>

      </div>
    </div>
    <!-- End Features -->

    
  </main>
  <!-- ========== END MAIN CONTENT ========== -->

  <!-- ========== FOOTER ========== -->
  <footer class=\"bg-dark\">
    <div class=\"container\">
      <div class=\"row align-items-center pt-8 pb-4\">
        <div class=\"col-md mb-5 mb-md-0\">
          <h2 class=\"fw-medium text-white-70 mb-0\">Join the thriving<br><span class=\"fw-bold text-white\">Unify</span> business agency</h2>
        </div>
        <!-- End Col -->
      
        <div class=\"col-md-auto\">
          <div class=\"d-grid d-sm-flex gap-3\">
            <a class=\"btn btn-primary\" href=\"#\">Découvrir</a>
            <a class=\"btn btn-ghost-light btn-pointer\" href=\"#\">Contactez nous</a>
          </div>
        </div>
        <!-- End Col -->
      </div>
      <!-- End Row -->

      <div class=\"border-bottom border-white-10\">
        <div class=\"row py-6\">
          <div class=\"col-6 col-sm-4 col-lg mb-7 mb-lg-0\">
            <span class=\"text-cap text-white\">Resources</span>

            <!-- List -->
            <ul class=\"list-unstyled list-py-1 mb-0\">
              <li><a class=\"link link-light link-light-75\" href=\"#\">Blog</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Guidance</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Customer Stories</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Support <i class=\"bi-box-arrow-up-right ms-1\"></i></a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">API</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Packages</a></li>
            </ul>
            <!-- End List -->
          </div>
          <!-- End Col -->

          <div class=\"col-6 col-sm-4 col-lg mb-7 mb-lg-0\">
            <span class=\"text-cap text-white\">Company</span>

            <!-- List -->
            <ul class=\"list-unstyled list-py-1 mb-0\">
              <li><a class=\"link link-light link-light-75\" href=\"#\">Belonging <i class=\"bi-box-arrow-up-right ms-1\"></i></a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Company</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Careers</a> <span class=\"fs-6 fw-bold text-primary\">&mdash; We're hiring</span></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Contacts</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Security</a></li>
            </ul>
            <!-- End List -->
          </div>
          <!-- End Col -->

          <div class=\"col-6 col-sm-4 col-lg mb-7 mb-sm-0\">
            <span class=\"text-cap text-white\">Platform</span>

            <!-- List -->
            <ul class=\"list-unstyled list-py-1 mb-0\">
              <li><a class=\"link link-light link-light-75\" href=\"#\">Web</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Mobile</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">iOS</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Android</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Figma Importing</a></li>
            </ul>
            <!-- End List -->
          </div>
          <!-- End Col -->

          <div class=\"col-6 col-sm-4 col-lg mb-7 mb-sm-0\">
            <span class=\"text-cap text-white\">Legal</span>

            <!-- List -->
            <ul class=\"list-unstyled list-py-1 mb-5\">
              <li><a class=\"link link-light link-light-75\" href=\"#\">Terms of use</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Privacy policy <i class=\"bi-box-arrow-up-right ms-1\"></i></a></li>
            </ul>
            <!-- End List -->

            <span class=\"text-cap text-white\">Docs</span>

            <!-- List -->
            <ul class=\"list-unstyled list-py-1 mb-0\">
              <li><a class=\"link link-light link-light-75\" href=\"#\">Documentation</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Snippets</a></li>
            </ul>
            <!-- End List -->
          </div>
          <!-- End Col -->

          <div class=\"col-6 col-sm-4 col-lg\">
            <span class=\"text-cap text-white\">Follow us</span>

            <!-- List -->
            <ul class=\"list-unstyled list-py-2 mb-0\">
              <li><a class=\"link link-light link-light-75\" href=\"#\">
                <div class=\"d-flex\">
                  <div class=\"flex-shrink-0\">
                    <i class=\"bi-envelope-open-fill\"></i>
                  </div>

                  <div class=\"flex-grow-1 ms-2\">
                    <span>Subscribe by email</span>
                  </div>
                </div>
              </a></li>

              <li><a class=\"link link-light link-light-75\" href=\"#\">
                <div class=\"d-flex\">
                  <div class=\"flex-shrink-0\">
                    <i class=\"bi-facebook\"></i>
                  </div>

                  <div class=\"flex-grow-1 ms-2\">
                    <span>Facebook</span>
                  </div>
                </div>
              </a></li>
            
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
            
              <li><a class=\"link link-light link-light-75\" href=\"#\">
                <div class=\"d-flex\">
                  <div class=\"flex-shrink-0\">
                    <i class=\"bi-slack\"></i>
                  </div>

                  <div class=\"flex-grow-1 ms-2\">
                    <span>Slack</span>
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
            <li class=\"list-inline-item\"><a class=\"link link-light link-light-75\" href=\"#\">Privacy and Policy</a></li>
            <li class=\"list-inline-item\"><a class=\"link link-light link-light-75\" href=\"#\">Terms</a></li>
            <li class=\"list-inline-item\"><a class=\"link link-light link-light-75\" href=\"#\">Status</a></li>
            <li class=\"list-inline-item\">
              <!-- Button Group -->
              <div class=\"btn-group\">
                <a class=\"link link-light link-light-75\" href=\"javascript:;\" id=\"selectLanguage\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
                  <span class=\"d-flex align-items-center\">
                    <img class=\"avatar avatar-xss avatar-circle me-2\" src=\"/svg/flags/1x1/us.svg\" alt=\"Image description\" width=\"16\"/>
                    <span>English</span>
                  </span>
                </a>

                <div class=\"dropdown-menu\">
                  <a class=\"dropdown-item d-flex align-items-center active\" href=\"#\">
                    <img class=\"avatar avatar-xss avatar-circle me-2\" src=\"/svg/flags/1x1/us.svg\" alt=\"Image description\" width=\"16\"/>
                    <span>English</span>
                  </a>
                  <a class=\"dropdown-item d-flex align-items-center\" href=\"#\">
                    <img class=\"avatar avatar-xss avatar-circle me-2\" src=\"/svg/flags/1x1/de.svg\" alt=\"Image description\" width=\"16\"/>
                    <span>Deutsch</span>
                  </a>
                  <a class=\"dropdown-item d-flex align-items-center\" href=\"#\">
                    <img class=\"avatar avatar-xss avatar-circle me-2\" src=\"/svg/flags/1x1/es.svg\" alt=\"Image description\" width=\"16\"/>
                    <span>Español</span>
                  </a>
                </div>
              </div>
              <!-- End Button Group -->
            </li>
          </ul>
          <!-- End List -->
        </div>
        <!-- End Col -->

        <div class=\"col-md-auto\">
          <p class=\"fs-5 text-white-70 mb-0\">© Unify. 2021</p>
        </div>
        <!-- End Col -->
      </div>
      <!-- End Row -->
    </div>
  </footer>
  <!-- ========== END FOOTER ========== -->

  <!-- ========== SECONDARY CONTENTS ========== -->
  <!-- Go To -->
  <a class=\"js-go-to go-to position-fixed\" href=\"javascript:;\" style=\"visibility: hidden;\"
     data-hs-go-to-options='{
       \"offsetTop\": 700,
       \"position\": {
         \"init\": {
           \"right\": \"2rem\"
         },
         \"show\": {
           \"bottom\": \"2rem\"
         },
         \"hide\": {
           \"bottom\": \"-2rem\"
         }
       }
     }'>
    <i class=\"bi-chevron-up\"></i>
  </a>

  <!-- JS Plugins Init. -->
  <script>
    
  </script>
</body>
";
        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

    }

    public function getTemplateName()
    {
        return "index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  88 => 6,  78 => 5,  59 => 3,  36 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("{% extends 'base.html.twig' %}

{% block title %}OLING{% endblock %}

{% block body %}
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
          <img class=\"navbar-brand-logo\" src=\"/img/logoling.png\" alt=\"Image Description\">
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
                        <a class=\"dropdown-item \" href=\"./index.html\"><i class=\"bi-building me-2\"></i> Schéma directeur</a>
                        <a class=\"dropdown-item \" href=\"./landing-business.html\"><i class=\"bi-briefcase me-2\"></i> Etudes <span class=\"badge text-primary\">New</span></a>
                        <a class=\"dropdown-item active\" href=\"./landing-consulting.html\"><i class=\"bi-chat-right-dots me-2\"></i> Assistance à maitrise d'ouvrage <span class=\"badge text-primary\">New</span></a>
                        <a class=\"dropdown-item \" href=\"./landing-saas.html\"><i class=\"bi-tropical-storm me-2\"></i> Pilotage des projets d'entreprise</a>
                        <a class=\"dropdown-item \" href=\"./landing-services.html\"><i class=\"bi-gear me-2\"></i> Gouvernance des données</a>
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
                        <a class=\"dropdown-item \" href=\"./index.html\"><i class=\"bi-building me-2\"></i> Finances</a>
                        <a class=\"dropdown-item \" href=\"./landing-business.html\"><i class=\"bi-briefcase me-2\"></i> Contrôle de gestion</a>
                        <a class=\"dropdown-item \" href=\"./landing-business.html\"><i class=\"bi-briefcase me-2\"></i> Contrôle interne</a>
                        <a class=\"dropdown-item active\" href=\"./landing-consulting.html\"><i class=\"bi-chat-right-dots me-2\"></i> RGPD <span class=\"badge text-primary\">New</span></a>
                        <a class=\"dropdown-item \" href=\"./landing-saas.html\"><i class=\"bi-tropical-storm me-2\"></i> QSE</a>
                        <a class=\"dropdown-item \" href=\"./landing-services.html\"><i class=\"bi-gear me-2\"></i> QUALIOPI</a>
                        <a class=\"dropdown-item \" href=\"./landing-services.html\"><i class=\"bi-gear me-2\"></i> RSE</a>
                        <a class=\"dropdown-item \" href=\"./landing-services.html\"><i class=\"bi-gear me-2\"></i> Gouvernance des données</a>


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
                        <a class=\"dropdown-item \" href=\"./index.html\"><i class=\"bi-building me-2\"></i> ERP</a>
                        <a class=\"dropdown-item \" href=\"./landing-business.html\"><i class=\"bi-briefcase me-2\"></i> BI et informatique analytique</a>
                        <a class=\"dropdown-item \" href=\"./landing-business.html\"><i class=\"bi-briefcase me-2\"></i> Développement d'applications</a>
                        <a class=\"dropdown-item active\" href=\"./landing-consulting.html\"><i class=\"bi-chat-right-dots me-2\"></i> SIG <span class=\"badge text-primary\">New</span></a>
                        <a class=\"dropdown-item \" href=\"./landing-saas.html\"><i class=\"bi-tropical-storm me-2\"></i> GED / GEC</a>


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
                        <a class=\"dropdown-item \" href=\"./index.html\"><i class=\"bi-building me-2\"></i> Intégration</a>
                        <a class=\"dropdown-item \" href=\"./landing-business.html\"><i class=\"bi-briefcase me-2\"></i> Arborescence</a>
                        <a class=\"dropdown-item \" href=\"./landing-business.html\"><i class=\"bi-briefcase me-2\"></i> Automatisation et Wrokflows</a>


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
                <a class=\"btn btn-primary d-none d-lg-inline-block\" href=\"./page-signup.html\">Contactez-nous</a>
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

  <!-- ========== MAIN CONTENT ========== -->
  <main id=\"content\" role=\"main\">
    <!-- Hero -->
    <div class=\"container content-space-t-3\">
      <div class=\"row justify-content-lg-between align-items-lg-center\">
        <div class=\"col-lg-5 mb-5 mb-lg-0\">
          <div class=\"mb-5\">
            <h1 class=\"display-4 text-dark mb-5\"> <span class=\"text-primary\">Innover</span> ensemble, <span class=\"text-primary\">réussir</span> ensemble </h1>
            <p class=\"fs-3\">OLING est une société composée d'expert en projets fonctionnels et informatiques pour les entreprises publiques et privées</p>
          </div>

          <div class=\"d-grid d-sm-flex gap-3 mb-5\">
            <a class=\"btn btn-primary\" href=\"#\">Découvrir les solutions</a>
            <a class=\"btn btn-ghost-dark btn-pointer\" href=\"#\">Contactez-nous</a>
          </div>
        </div>
        <!-- End Col -->

        <div class=\"col-lg-6\">
          <div class=\"position-relative\">
            <div class=\"position-relative\">
              <img class=\"img-fluid\" src=\"/img/950x950/img1.jpg\" alt=\"Image Description\">

              <div class=\"position-absolute bottom-0 end-0\">
                <img class=\"w-100\" src=\"/svg/illustrations/cubics.svg\" alt=\"SVG\" style=\"max-width: 30rem;\">
              </div>
            </div>

            
          </div>
        </div>
        <!-- End Col -->
      </div>
      <!-- End Row -->
    </div>
    <!-- End Hero -->



    <!-- Stats -->
    <div class=\"container content-space-2 content-space-lg-3\">
      <div class=\"row justify-content-lg-between align-items-lg-center\">
        <div class=\"col-lg-5 mb-9 mb-lg-0\">
          <div class=\"mb-6\">
            <h2>Votre partenaire pour réussir vos projets d'entreprise</h2>
            <p>OLING est une entreprise de conseil experte en projets fonctionnels et informatiques pour les entreprises publiques et privées. </p>
            <p>Avec une forte expérience dans les problématiques d'entreprise et une expertise méthodologique de haut niveau, 
            OLING offre des solutions sur mesure pour répondre aux besoins spécifiques de ses clients, pour la réussite de leurs projets d'entreprise.</p>
          </div>


        </div>
        <!-- End Col -->

        <div class=\"col-lg-6\">
          <!-- List -->
          <ul class=\"list-equal-height list-equal-height-2-cols\">
            <li class=\"list-equal-height-item\">
              <h4 class=\"display-5\">50 %</h4>
              <p class=\"mb-0\">Secteur public</p>
            </li>

            <li class=\"list-equal-height-item\">
              <h4 class=\"display-5\">50 %</h4>
              <p class=\"mb-0\">Secteur privé</p>
            </li>

            <li class=\"list-equal-height-item\">
              <h4 class=\"display-5\">150+</h4>
              <p class=\"mb-0\">projets conduits dans les outremers et l'hexagone</p>
            </li>

            <li class=\"list-equal-height-item\">
              <h4 class=\"display-5\"><sub><i class=\"bi-arrow-up-short text-primary ms-n2\"></i></sub>10</h4>
              <p class=\"mb-0\">consultants experts</p>
            </li>
          </ul>
          <!-- End List -->
        </div>
        <!-- End Col -->
      </div>
      <!-- End Row -->
    </div>
    <!-- End Stats -->


    <!-- Heading -->
    <div class=\"container content-space-t-2 content-space-t-lg-0 content-space-b-1\">
      <div class=\"w-lg-65 text-center mx-lg-auto\">
        <h2>Des solutions sur mesure pour répondre à vos besoins spécifiques</h2>
        <p>OLING est structurée autour de quatre \"practices\", 
        chacune dédiée à un domaine spécifique de l'entreprise : le conseil en organisation et SI, les expertises et audits, 
        les applications métier et la transformation digitale. Grâce à l'utilisation de profils experts et de méthodologies éprouvées, 
        OLING offre des solutions sur mesure pour répondre aux besoins spécifiques de ses clients.</p>
      </div>
    </div>
    <!-- End Heading -->

    <!-- Step Features -->
    <div class=\"container\">
      <!-- List Step -->
      <ul class=\"list-unstyled list-step list-py-3 mb-0\">
        <li class=\"list-step-item\">
          <div class=\"position-relative rounded-3 py-10 px-4 px-md-10\">
            <div class=\"row align-items-lg-center\">
              <div class=\"col-lg-5 mb-7 mb-lg-0\">
                <div class=\"pe-lg-5\">
                  <div class=\"mb-5\">
                    <span class=\"text-cap text-primary\">Conseil en Organisation et SI</span>
                    <h2>Ensemble, optimisons votre organisation et votre système d'information</h2>
                    <p>Solutions pour optimiser votre organisation et votre système d'information</p>
                  </div>

                  <!-- List Checked -->
                  <ul class=\"list-checked list-checked-primary mb-7\">
                    <li class=\"list-checked-item\">Assistance à maîtrise d'ouvrage</li>
                    <li class=\"list-checked-item\">Gouvernance des SI</li>
                    <li class=\"list-checked-item\">Management des processus</li>
                    <li class=\"list-checked-item\">Management des données</li>
                    <li class=\"list-checked-item\">Plan stratégique et pilotage des projets d'entreprise</li>
                  </ul>
                  <!-- End List Checked -->

                  <a class=\"link link-pointer\" href=\"#\">En savoir plus</a>
                </div>
              </div>
              <!-- End Col -->

              <div class=\"col-lg-7\">
                <img class=\"img-fluid\" src=\"/img/mockups/img1.png\" alt=\"Image Description\">
              </div>
              <!-- End Col -->
            </div>
            <!-- End Row -->

            <div class=\"position-absolute top-0 start-0 w-100 w-lg-65 h-65 h-lg-100 bg-light rounded-3 zi-n1 ms-n5\"></div>
          </div>
        </li>

        <li class=\"list-step-item\">
          <div class=\"position-relative rounded-3 py-10 px-4 px-md-10\">
            <div class=\"row align-items-lg-center\">
              <div class=\"col-lg-5 mb-7 mb-lg-0\">
                <div class=\"pe-lg-5\">
                  <div class=\"mb-5\">
                    <span class=\"text-cap text-primary\">Audit et expertises</span>
                    <h2>Un regard extérieur pour améliorer votre organisation</h2>
                    <p>OLING propose des services d'audit et d'expertise pour aider les entreprises publiques et privées à atteindre leurs objectifs.</p>
                  </div>


                  <!-- List Checked -->
                  <ul class=\"list-checked list-checked-primary mb-7\">
                    <li class=\"list-checked-item\">Contrôle de gestion</li>
                    <li class=\"list-checked-item\">Organisation et processus</li>
                    <li class=\"list-checked-item\">SMI QSE</li>
                    <li class=\"list-checked-item\">RSE</li>
                    <li class=\"list-checked-item\">FINANCES</li>
                    <li class=\"list-checked-item\">RGPD</li>
                    <li class=\"list-checked-item\">Contrôle interne</li>
                  </ul>
                  <!-- End List Checked -->

                  <a class=\"link link-pointer\" href=\"#\">En savoir plus</a>
                </div>
              </div>
              <!-- End Col -->

              <div class=\"col-lg-7\">
                <img class=\"img-fluid\" src=\"/img/mockups/img2.png\" alt=\"Image Description\">
              </div>
              <!-- End Col -->
            </div>
            <!-- End Row -->

            <div class=\"position-absolute top-0 start-0 w-100 w-lg-65 h-65 h-lg-100 bg-light rounded-3 zi-n1 ms-n5\"></div>
          </div>
        </li>

        <li class=\"list-step-item\">
          <div class=\"position-relative rounded-3 py-10 px-4 px-md-10\">
            <div class=\"row align-items-lg-center\">
              <div class=\"col-lg-5 mb-7 mb-lg-0\">
                <div class=\"pe-lg-5\">
                  <div class=\"mb-5\">
                    <span class=\"text-cap text-primary\">Business Apps</span>
                    <h2>Des applications métier sur mesure pour votre entreprise</h2>
                    <p>OLING offre des services de développement d'applications métier pour les entreprises publiques et privées</p>
                  </div>

                  <!-- List Checked -->
                  <ul class=\"list-checked list-checked-primary mb-7\">
                    <li class=\"list-checked-item\">Conception, déploiement, AMOA, AMOE des solutions applicatives ERP</li>
                    <li class=\"list-checked-item\">Informatique analytique et BI</li>
                    <li class=\"list-checked-item\">GED/GEC</li>
                    <li class=\"list-checked-item\">SIG</li>
                    <li class=\"list-checked-item\">Développement d'applications métier</li>
                  </ul>
                  <!-- End List Checked -->

                  <a class=\"link link-pointer\" href=\"#\">En savoir plus</a>
                </div>
              </div>
              <!-- End Col -->

              <div class=\"col-lg-7\">
                <img class=\"img-fluid\" src=\"/img/mockups/img3.png\" alt=\"Image Description\">
              </div>
              <!-- End Col -->
            </div>
            <!-- End Row -->

            <div class=\"position-absolute top-0 start-0 w-100 w-lg-65 h-65 h-lg-100 bg-light rounded-3 zi-n1 ms-n5\"></div>
          </div>
        </li>

        <li class=\"list-step-item\">
          <div class=\"position-relative rounded-3 py-10 px-4 px-md-10\">
            <div class=\"row align-items-lg-center\">
              <div class=\"col-lg-5 mb-7 mb-lg-0\">
                <div class=\"pe-lg-5\">
                  <div class=\"mb-5\">
                    <span class=\"text-cap text-primary\">Microsoft 365</span>
                    <h2>La transformation digitale, avec Microsoft 365 et OLING</h2>
                    <p>OLING propose des services de transformation digitale avec la suite Microsoft 365 
                    pour aider les entreprises publiques et privées à s'adapter aux changements technologiques.</p>
                  </div>

                  <!-- List Checked -->
                  <ul class=\"list-checked list-checked-primary mb-7\">
                    <li class=\"list-checked-item\">Mise en place de solutions digitales avec Microsoft 365</li>
                    <li class=\"list-checked-item\">Arborescence métier</li>
                    <li class=\"list-checked-item\">Administration Azure AD</li>
                    <li class=\"list-checked-item\">Messagerie et calendrier</li>
                    <li class=\"list-checked-item\">Collaboratif</li>
                    <li class=\"list-checked-item\">Conseil en stratégie digitale</li>
                    <li class=\"list-checked-item\">Accompagnement au changement</li>
                  </ul>
                  <!-- End List Checked -->

                  <a class=\"link link-pointer\" href=\"#\">Explore this step</a>
                </div>
              </div>
              <!-- End Col -->

              <div class=\"col-lg-7\">
                <img class=\"img-fluid\" src=\"/img/mockups/img2.png\" alt=\"Image Description\">
              </div>
              <!-- End Col -->
            </div>
            <!-- End Row -->

            <div class=\"position-absolute top-0 start-0 w-100 w-lg-65 h-65 h-lg-100 bg-light rounded-3 zi-n1 ms-n5\"></div>
          </div>
        </li>
      </ul>
      <!-- End List Step -->
    </div>
    <!-- End Step Features -->

    
    <!-- Features -->
    <div class=\"overflow-hidden\">
      <div class=\"container content-space-2 content-space-lg-3\">
        <div class=\"position-relative mb-10\">
          <div class=\"row justify-content-lg-between\">
            <div class=\"col-md-6 col-lg-5 mb-7 mb-lg-0\">
              <div class=\"mb-5\">
                <h2>Vous cherchez un partenaire qui fournit une expertise adaptée à votre métier ?</h2>
                <p> OLING, société de conseil spécialisée dans l'accompagnement des entreprises publiques et privées, 
                vous offre une expertise sur-mesure pour répondre à vos défis métiers et informatiques.</p> 
                <p>Découvrez nos capacités pour vous aider à innover, à vous conformer aux réglementations et à optimiser vos processus.</p>
              </div>

              <a class=\"link link-pointer\" href=\"#\">Découvrez notre méthode</a>
            </div>
            <!-- End Col -->

            <div class=\"col-md-6\">
              <div class=\"mb-6\">
                <h3>Un partenaire expert pour une transformation adaptée à votre métier</h3>
              </div>

              <div class=\"mb-3\">
                <i class=\"bi-app-indicator fs-2 text-dark\"></i>
              </div>

              <h5>Services publiques</h5>

              <div class=\"row mb-5\">
                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">Marketing Websites</li>
                    <li class=\"list-checked-item\">Landing Pages</li>
                  </ul>
                </div>
                <!-- End Col -->

                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">Design System</li>
                    <li class=\"list-checked-item\">Icon Design</li>
                  </ul>
                </div>
                <!-- End Col -->
              </div>
              <!-- End Row -->

              <div class=\"mb-3\">
                <i class=\"bi-circle-square fs-2 text-dark\"></i>
              </div>

              <h5>Négoces et distribution</h5>

              <div class=\"row mb-5\">
                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">User Research</li>
                    <li class=\"list-checked-item\">Usability Testing</li>

                  </ul>
                </div>
                <!-- End Col -->

                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">Wireframes</li>
                    <li class=\"list-checked-item\">Some of my current</li>
                  </ul>
                </div>
                <!-- End Col -->
              </div>
              <!-- End Row -->
                <div class=\"mb-3\">
                <i class=\"bi-circle-square fs-2 text-dark\"></i>
              </div>

              <h5>Education</h5>

              <div class=\"row mb-5\">
                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">User Research</li>
                    <li class=\"list-checked-item\">Usability Testing</li>

                  </ul>
                </div>
                <!-- End Col -->

                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">Wireframes</li>
                    <li class=\"list-checked-item\">Some of my current</li>
                  </ul>
                </div>
                <!-- End Col -->


                <div class=\"mb-3\">
                <i class=\"bi-circle-square fs-2 text-dark\"></i>
              </div>

              <h5>Education</h5>

              <div class=\"row mb-5\">
                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">User Research</li>
                    <li class=\"list-checked-item\">Usability Testing</li>

                  </ul>
                </div>
                <!-- End Col -->

                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">Wireframes</li>
                    <li class=\"list-checked-item\">Some of my current</li>
                  </ul>
                </div>
                <!-- End Col -->
              </div>
              <!-- End Row -->




              </div>
              <!-- End Row -->
            </div>
            <!-- End Col -->
          </div>
          <!-- End Row -->
          

          <!-- SVG Shape -->
          <figure class=\"position-absolute top-0 end-0 me-n7\" style=\"width: 4rem;\">
            <img class=\"img-fluid\" src=\"/svg/components/pointer-up.svg\" alt=\"Image Description\">
          </figure>
          <!-- End SVG Shape -->

          <!-- SVG Shape -->
          <figure class=\"position-absolute bottom-0 end-0 start-sm-0 mb-n8 ms-n8\" style=\"width: 12rem;\">
            <img class=\"img-fluid\" src=\"/svg/components/curved-shape.svg\" alt=\"Image Description\">
          </figure>
          <!-- End SVG Shape -->
        </div>

      </div>
    </div>
    <!-- End Features -->

    
  </main>
  <!-- ========== END MAIN CONTENT ========== -->

  <!-- ========== FOOTER ========== -->
  <footer class=\"bg-dark\">
    <div class=\"container\">
      <div class=\"row align-items-center pt-8 pb-4\">
        <div class=\"col-md mb-5 mb-md-0\">
          <h2 class=\"fw-medium text-white-70 mb-0\">Join the thriving<br><span class=\"fw-bold text-white\">Unify</span> business agency</h2>
        </div>
        <!-- End Col -->
      
        <div class=\"col-md-auto\">
          <div class=\"d-grid d-sm-flex gap-3\">
            <a class=\"btn btn-primary\" href=\"#\">Découvrir</a>
            <a class=\"btn btn-ghost-light btn-pointer\" href=\"#\">Contactez nous</a>
          </div>
        </div>
        <!-- End Col -->
      </div>
      <!-- End Row -->

      <div class=\"border-bottom border-white-10\">
        <div class=\"row py-6\">
          <div class=\"col-6 col-sm-4 col-lg mb-7 mb-lg-0\">
            <span class=\"text-cap text-white\">Resources</span>

            <!-- List -->
            <ul class=\"list-unstyled list-py-1 mb-0\">
              <li><a class=\"link link-light link-light-75\" href=\"#\">Blog</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Guidance</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Customer Stories</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Support <i class=\"bi-box-arrow-up-right ms-1\"></i></a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">API</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Packages</a></li>
            </ul>
            <!-- End List -->
          </div>
          <!-- End Col -->

          <div class=\"col-6 col-sm-4 col-lg mb-7 mb-lg-0\">
            <span class=\"text-cap text-white\">Company</span>

            <!-- List -->
            <ul class=\"list-unstyled list-py-1 mb-0\">
              <li><a class=\"link link-light link-light-75\" href=\"#\">Belonging <i class=\"bi-box-arrow-up-right ms-1\"></i></a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Company</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Careers</a> <span class=\"fs-6 fw-bold text-primary\">&mdash; We're hiring</span></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Contacts</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Security</a></li>
            </ul>
            <!-- End List -->
          </div>
          <!-- End Col -->

          <div class=\"col-6 col-sm-4 col-lg mb-7 mb-sm-0\">
            <span class=\"text-cap text-white\">Platform</span>

            <!-- List -->
            <ul class=\"list-unstyled list-py-1 mb-0\">
              <li><a class=\"link link-light link-light-75\" href=\"#\">Web</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Mobile</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">iOS</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Android</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Figma Importing</a></li>
            </ul>
            <!-- End List -->
          </div>
          <!-- End Col -->

          <div class=\"col-6 col-sm-4 col-lg mb-7 mb-sm-0\">
            <span class=\"text-cap text-white\">Legal</span>

            <!-- List -->
            <ul class=\"list-unstyled list-py-1 mb-5\">
              <li><a class=\"link link-light link-light-75\" href=\"#\">Terms of use</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Privacy policy <i class=\"bi-box-arrow-up-right ms-1\"></i></a></li>
            </ul>
            <!-- End List -->

            <span class=\"text-cap text-white\">Docs</span>

            <!-- List -->
            <ul class=\"list-unstyled list-py-1 mb-0\">
              <li><a class=\"link link-light link-light-75\" href=\"#\">Documentation</a></li>
              <li><a class=\"link link-light link-light-75\" href=\"#\">Snippets</a></li>
            </ul>
            <!-- End List -->
          </div>
          <!-- End Col -->

          <div class=\"col-6 col-sm-4 col-lg\">
            <span class=\"text-cap text-white\">Follow us</span>

            <!-- List -->
            <ul class=\"list-unstyled list-py-2 mb-0\">
              <li><a class=\"link link-light link-light-75\" href=\"#\">
                <div class=\"d-flex\">
                  <div class=\"flex-shrink-0\">
                    <i class=\"bi-envelope-open-fill\"></i>
                  </div>

                  <div class=\"flex-grow-1 ms-2\">
                    <span>Subscribe by email</span>
                  </div>
                </div>
              </a></li>

              <li><a class=\"link link-light link-light-75\" href=\"#\">
                <div class=\"d-flex\">
                  <div class=\"flex-shrink-0\">
                    <i class=\"bi-facebook\"></i>
                  </div>

                  <div class=\"flex-grow-1 ms-2\">
                    <span>Facebook</span>
                  </div>
                </div>
              </a></li>
            
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
            
              <li><a class=\"link link-light link-light-75\" href=\"#\">
                <div class=\"d-flex\">
                  <div class=\"flex-shrink-0\">
                    <i class=\"bi-slack\"></i>
                  </div>

                  <div class=\"flex-grow-1 ms-2\">
                    <span>Slack</span>
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
            <li class=\"list-inline-item\"><a class=\"link link-light link-light-75\" href=\"#\">Privacy and Policy</a></li>
            <li class=\"list-inline-item\"><a class=\"link link-light link-light-75\" href=\"#\">Terms</a></li>
            <li class=\"list-inline-item\"><a class=\"link link-light link-light-75\" href=\"#\">Status</a></li>
            <li class=\"list-inline-item\">
              <!-- Button Group -->
              <div class=\"btn-group\">
                <a class=\"link link-light link-light-75\" href=\"javascript:;\" id=\"selectLanguage\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
                  <span class=\"d-flex align-items-center\">
                    <img class=\"avatar avatar-xss avatar-circle me-2\" src=\"/svg/flags/1x1/us.svg\" alt=\"Image description\" width=\"16\"/>
                    <span>English</span>
                  </span>
                </a>

                <div class=\"dropdown-menu\">
                  <a class=\"dropdown-item d-flex align-items-center active\" href=\"#\">
                    <img class=\"avatar avatar-xss avatar-circle me-2\" src=\"/svg/flags/1x1/us.svg\" alt=\"Image description\" width=\"16\"/>
                    <span>English</span>
                  </a>
                  <a class=\"dropdown-item d-flex align-items-center\" href=\"#\">
                    <img class=\"avatar avatar-xss avatar-circle me-2\" src=\"/svg/flags/1x1/de.svg\" alt=\"Image description\" width=\"16\"/>
                    <span>Deutsch</span>
                  </a>
                  <a class=\"dropdown-item d-flex align-items-center\" href=\"#\">
                    <img class=\"avatar avatar-xss avatar-circle me-2\" src=\"/svg/flags/1x1/es.svg\" alt=\"Image description\" width=\"16\"/>
                    <span>Español</span>
                  </a>
                </div>
              </div>
              <!-- End Button Group -->
            </li>
          </ul>
          <!-- End List -->
        </div>
        <!-- End Col -->

        <div class=\"col-md-auto\">
          <p class=\"fs-5 text-white-70 mb-0\">© Unify. 2021</p>
        </div>
        <!-- End Col -->
      </div>
      <!-- End Row -->
    </div>
  </footer>
  <!-- ========== END FOOTER ========== -->

  <!-- ========== SECONDARY CONTENTS ========== -->
  <!-- Go To -->
  <a class=\"js-go-to go-to position-fixed\" href=\"javascript:;\" style=\"visibility: hidden;\"
     data-hs-go-to-options='{
       \"offsetTop\": 700,
       \"position\": {
         \"init\": {
           \"right\": \"2rem\"
         },
         \"show\": {
           \"bottom\": \"2rem\"
         },
         \"hide\": {
           \"bottom\": \"-2rem\"
         }
       }
     }'>
    <i class=\"bi-chevron-up\"></i>
  </a>

  <!-- JS Plugins Init. -->
  <script>
    
  </script>
</body>
{% endblock %}
", "index.html.twig", "/Users/florestanrouet/myweb/olingweb/templates/index.html.twig");
    }
}