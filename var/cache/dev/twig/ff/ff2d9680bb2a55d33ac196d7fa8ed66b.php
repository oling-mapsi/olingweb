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
  

  <!-- ========== MAIN CONTENT ========== -->
  <main id=\"content\" role=\"main\">
    <!-- Hero -->
    <div class=\"container content-space-t-3\">
      <div class=\"row justify-content-lg-between align-items-lg-center\">
        <div class=\"col-lg-5 mb-5 mb-lg-0\">
          <div class=\"mb-5\">
            <h1 class=\"display-4 text-dark mb-5\"> <span class=\"text-primary\">Innover</span> et <span class=\"text-primary\">réussir</span> collectivement </h1>
            <p class=\"fs-3\">Une alliance d'experts en projets fonctionnels et technologiques pour accompagner les organisations publiques et privées dans leur évolution</p>
          </div>

          <div class=\"d-grid d-sm-flex gap-3 mb-5\">
            <a class=\"btn btn-primary\" href=\"#\">Découvrir les solutions</a>
            <a class=\"btn btn-ghost-dark btn-pointer\" href=";
        // line 22
        echo $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("contact");
        echo ">Contactez-nous</a>
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
            <h2>Votre allié pour mener à bien vos initiatives technologiques</h2>
            <p>Fort d'une expérience solide en management des organisations et intégration de systèmes complexes, OLING propose des solutions adaptées aux besoins uniques de ses clients pour assurer le succès de leurs initiatives d'entreprise.</p>
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
        <h2>Des solutions personnalisées pour des exigences spécifiques</h2>
        <p>OLING s'organise autour de quatre practices, chacune dédiée à un aspect spécifique de l'entreprise : Consulting et AMOA, Expertises et Audit, Business Apps et Microsoft 365. </p>
      </div>
    </div>
    <!-- End Heading -->

    <!-- Step Features -->
    <div class=\"container\">
      <!-- List Step -->
      <ul class=\"list-unstyled list-step list-py-3 mb-0\">

        <li class=\"list-step-item\">
          <div class=\"position-relative rounded-3 py-8 px-4 px-md-10\">
            <div class=\"row align-items-lg-center\">
              <div class=\"col-lg-5 mb-7 mb-lg-0\">
                <div class=\"pe-lg-5\">
                  <div class=\"mb-5\">
                    <span class=\"text-cap text-primary\">Conseil en Organisation et SI</span>
                    <h2>Optimisons ensemble votre organisation et votre SI</h2>
                    <p>Mise en oeuvre de solutions et de compétences adaptées aux besoins d'évolution des organisations</p>
                  </div>

                  ";
        // line 127
        echo "
                  <a class=\"link link-pointer\" href=";
        // line 128
        echo $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("practices");
        echo ">En savoir plus</a>
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
          <div class=\"position-relative rounded-3 py-8 px-4 px-md-10\">
            <div class=\"row align-items-lg-center\">
              <div class=\"col-lg-5 mb-7 mb-lg-0\">
                <div class=\"pe-lg-5\">
                  <div class=\"mb-5\">
                    <span class=\"text-cap text-primary\">Audit et expertises</span>
                    <h2>Un regard externe pour améliorer votre organisation</h2>
                    <p>OLING offre des services d'audit et d'expertises pour aider les entreprises à atteindre leurs objectifs de pilotage, de contrôle et de conformité.</p>
                  </div>


                  ";
        // line 167
        echo "
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
          <div class=\"position-relative rounded-3 py-8 px-4 px-md-10\">
            <div class=\"row align-items-lg-center\">
              <div class=\"col-lg-5 mb-7 mb-lg-0\">
                <div class=\"pe-lg-5\">
                  <div class=\"mb-5\">
                    <span class=\"text-cap text-primary\">Business Apps</span>
                    <h2>Des applications métier sur mesure pour votre organisation</h2>
                    <p>OLING offre des services essentiels d'analyse, de conception, d'accompagnement à l'intégration et de développement d'applications métier, permettant aux entreprises de maîtriser efficacement leurs données et processus grâce à l'adoption des solutions appropriées.</p>
                  </div>

                  ";
        // line 204
        echo "
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
          <div class=\"position-relative rounded-3 py-8 px-4 px-md-10\">
            <div class=\"row align-items-lg-center\">
              <div class=\"col-lg-5 mb-7 mb-lg-0\">
                <div class=\"pe-lg-5\">
                  <div class=\"mb-5\">
                    <span class=\"text-cap text-primary\">Microsoft 365</span>
                    <h2>La transformation digitale, avec Microsoft 365 et OLING</h2>
                    <p>La transformation numérique, avec Microsoft 365 et OLING
OLING propose des services de transformation numérique avec la suite Microsoft 365 pour aider les organisations publiques et privées à collaborer et à s'adapter aux évolutions technologiques.</p>
                  </div>

                  ";
        // line 244
        echo "
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
                <h2>Recherchez-vous un partenaire fournissant une expertise adaptée à votre secteur d'activité ?</h2>
                <p> OLING, société de conseil spécialisée dans l'accompagnement des entreprises publiques et privées, 
                vous offre une expertise sur-mesure pour répondre à vos défis métiers et informatiques.</p> 
                <p>Découvrez nos compétences pour vous aider à innover, à respecter les exigences spécifiques, les réglementations et à optimiser les processus de chaque métier.</p>
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
                    <li class=\"list-checked-item\">Conception de schéma directeur SI et digitalisation</li>
                    <li class=\"list-checked-item\">Conformité ISO RGPD PCA</li>
                    <li class=\"list-checked-item\">Audit et étude SI (Infrastructure, serveur, réseau, téléphonie)</li>
                  </ul>
                </div>
                <!-- End Col -->

                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">Appuie à l’intégration d’applications métier/li>
                    <li class=\"list-checked-item\">Délégation de service SI</li>
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
                    <li class=\"list-checked-item\">Audit et conseil SI et Organisation</li>
                    <li class=\"list-checked-item\">Accompagnement à l’intégration de solutions applicatives métiers CRM, ERP (achat, logistique, finance)</li>
                    <li class=\"list-checked-item\">Organisation de la collaboration sur Office 365</li>

                  </ul>
                </div>
                <!-- End Col -->

                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">Audit et conseil SI et Organisation</li>
                    <li class=\"list-checked-item\">Management de transition SI, Conformité, etc.</li>
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
                    <li class=\"list-checked-item\">Conformité QSE, Qualiopi, RGPD</li>
                    <li class=\"list-checked-item\">Digitalisation des processus et des documents/li>

                  </ul>
                </div>
                <!-- End Col -->

                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">Expertises et audit</li>
                    <li class=\"list-checked-item\">Recherche de programmes et élaboration des dossiers de candidatures et de demande de subventions</li>
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
          

          ";
        // line 387
        echo "        </div>

      </div>
    </div>
    <!-- End Features -->

    

    
  </main>
  <!-- ========== END MAIN CONTENT ========== -->

  

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
        return array (  434 => 387,  300 => 244,  269 => 204,  239 => 167,  208 => 128,  205 => 127,  106 => 22,  88 => 6,  78 => 5,  59 => 3,  36 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("{% extends 'base.html.twig' %}

{% block title %}OLING{% endblock %}

{% block body %}
<body>
  

  <!-- ========== MAIN CONTENT ========== -->
  <main id=\"content\" role=\"main\">
    <!-- Hero -->
    <div class=\"container content-space-t-3\">
      <div class=\"row justify-content-lg-between align-items-lg-center\">
        <div class=\"col-lg-5 mb-5 mb-lg-0\">
          <div class=\"mb-5\">
            <h1 class=\"display-4 text-dark mb-5\"> <span class=\"text-primary\">Innover</span> et <span class=\"text-primary\">réussir</span> collectivement </h1>
            <p class=\"fs-3\">Une alliance d'experts en projets fonctionnels et technologiques pour accompagner les organisations publiques et privées dans leur évolution</p>
          </div>

          <div class=\"d-grid d-sm-flex gap-3 mb-5\">
            <a class=\"btn btn-primary\" href=\"#\">Découvrir les solutions</a>
            <a class=\"btn btn-ghost-dark btn-pointer\" href={{ path('contact')}}>Contactez-nous</a>
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
            <h2>Votre allié pour mener à bien vos initiatives technologiques</h2>
            <p>Fort d'une expérience solide en management des organisations et intégration de systèmes complexes, OLING propose des solutions adaptées aux besoins uniques de ses clients pour assurer le succès de leurs initiatives d'entreprise.</p>
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
        <h2>Des solutions personnalisées pour des exigences spécifiques</h2>
        <p>OLING s'organise autour de quatre practices, chacune dédiée à un aspect spécifique de l'entreprise : Consulting et AMOA, Expertises et Audit, Business Apps et Microsoft 365. </p>
      </div>
    </div>
    <!-- End Heading -->

    <!-- Step Features -->
    <div class=\"container\">
      <!-- List Step -->
      <ul class=\"list-unstyled list-step list-py-3 mb-0\">

        <li class=\"list-step-item\">
          <div class=\"position-relative rounded-3 py-8 px-4 px-md-10\">
            <div class=\"row align-items-lg-center\">
              <div class=\"col-lg-5 mb-7 mb-lg-0\">
                <div class=\"pe-lg-5\">
                  <div class=\"mb-5\">
                    <span class=\"text-cap text-primary\">Conseil en Organisation et SI</span>
                    <h2>Optimisons ensemble votre organisation et votre SI</h2>
                    <p>Mise en oeuvre de solutions et de compétences adaptées aux besoins d'évolution des organisations</p>
                  </div>

                  {# <!-- List Checked -->
                  <ul class=\"list-checked list-checked-primary mb-7\">
                    <li class=\"list-checked-item\">Assistance à maîtrise d'ouvrage métier et SI</li>
                    <li class=\"list-checked-item\">Gouvernance des SI</li>
                    <li class=\"list-checked-item\">Management des processus</li>
                    <li class=\"list-checked-item\">Management des données</li>
                    <li class=\"list-checked-item\">Plan stratégique et pilotage des projets d'entreprise</li>
                  </ul>
                  <!-- End List Checked --> #}

                  <a class=\"link link-pointer\" href={{ path('practices')}}>En savoir plus</a>
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
          <div class=\"position-relative rounded-3 py-8 px-4 px-md-10\">
            <div class=\"row align-items-lg-center\">
              <div class=\"col-lg-5 mb-7 mb-lg-0\">
                <div class=\"pe-lg-5\">
                  <div class=\"mb-5\">
                    <span class=\"text-cap text-primary\">Audit et expertises</span>
                    <h2>Un regard externe pour améliorer votre organisation</h2>
                    <p>OLING offre des services d'audit et d'expertises pour aider les entreprises à atteindre leurs objectifs de pilotage, de contrôle et de conformité.</p>
                  </div>


                  {# <!-- List Checked -->
                  <ul class=\"list-checked list-checked-primary mb-7\">
                    <li class=\"list-checked-item\">Contrôle de gestion</li>
                    <li class=\"list-checked-item\">Organisation et processus</li>
                    <li class=\"list-checked-item\">SMI QSE</li>
                    <li class=\"list-checked-item\">RSE</li>
                    <li class=\"list-checked-item\">FINANCES</li>
                    <li class=\"list-checked-item\">RGPD</li>
                    <li class=\"list-checked-item\">Contrôle interne</li>
                  </ul>
                  <!-- End List Checked --> #}

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
          <div class=\"position-relative rounded-3 py-8 px-4 px-md-10\">
            <div class=\"row align-items-lg-center\">
              <div class=\"col-lg-5 mb-7 mb-lg-0\">
                <div class=\"pe-lg-5\">
                  <div class=\"mb-5\">
                    <span class=\"text-cap text-primary\">Business Apps</span>
                    <h2>Des applications métier sur mesure pour votre organisation</h2>
                    <p>OLING offre des services essentiels d'analyse, de conception, d'accompagnement à l'intégration et de développement d'applications métier, permettant aux entreprises de maîtriser efficacement leurs données et processus grâce à l'adoption des solutions appropriées.</p>
                  </div>

                  {# <!-- List Checked -->
                  <ul class=\"list-checked list-checked-primary mb-7\">
                    <li class=\"list-checked-item\">Conception, déploiement, AMOA, AMOE des solutions applicatives ERP</li>
                    <li class=\"list-checked-item\">Informatique analytique et BI</li>
                    <li class=\"list-checked-item\">GED/GEC</li>
                    <li class=\"list-checked-item\">SIG</li>
                    <li class=\"list-checked-item\">Développement d'applications métier</li>
                  </ul>
                  <!-- End List Checked --> #}

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
          <div class=\"position-relative rounded-3 py-8 px-4 px-md-10\">
            <div class=\"row align-items-lg-center\">
              <div class=\"col-lg-5 mb-7 mb-lg-0\">
                <div class=\"pe-lg-5\">
                  <div class=\"mb-5\">
                    <span class=\"text-cap text-primary\">Microsoft 365</span>
                    <h2>La transformation digitale, avec Microsoft 365 et OLING</h2>
                    <p>La transformation numérique, avec Microsoft 365 et OLING
OLING propose des services de transformation numérique avec la suite Microsoft 365 pour aider les organisations publiques et privées à collaborer et à s'adapter aux évolutions technologiques.</p>
                  </div>

                  {# <!-- List Checked -->
                  <ul class=\"list-checked list-checked-primary mb-7\">
                    <li class=\"list-checked-item\">Mise en place de solutions digitales avec Microsoft 365</li>
                    <li class=\"list-checked-item\">Arborescence métier</li>
                    <li class=\"list-checked-item\">Administration Azure AD</li>
                    <li class=\"list-checked-item\">Messagerie et calendrier</li>
                    <li class=\"list-checked-item\">Collaboratif</li>
                    <li class=\"list-checked-item\">Conseil en stratégie digitale</li>
                    <li class=\"list-checked-item\">Accompagnement au changement</li>
                  </ul>
                  <!-- End List Checked --> #}

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
                <h2>Recherchez-vous un partenaire fournissant une expertise adaptée à votre secteur d'activité ?</h2>
                <p> OLING, société de conseil spécialisée dans l'accompagnement des entreprises publiques et privées, 
                vous offre une expertise sur-mesure pour répondre à vos défis métiers et informatiques.</p> 
                <p>Découvrez nos compétences pour vous aider à innover, à respecter les exigences spécifiques, les réglementations et à optimiser les processus de chaque métier.</p>
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
                    <li class=\"list-checked-item\">Conception de schéma directeur SI et digitalisation</li>
                    <li class=\"list-checked-item\">Conformité ISO RGPD PCA</li>
                    <li class=\"list-checked-item\">Audit et étude SI (Infrastructure, serveur, réseau, téléphonie)</li>
                  </ul>
                </div>
                <!-- End Col -->

                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">Appuie à l’intégration d’applications métier/li>
                    <li class=\"list-checked-item\">Délégation de service SI</li>
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
                    <li class=\"list-checked-item\">Audit et conseil SI et Organisation</li>
                    <li class=\"list-checked-item\">Accompagnement à l’intégration de solutions applicatives métiers CRM, ERP (achat, logistique, finance)</li>
                    <li class=\"list-checked-item\">Organisation de la collaboration sur Office 365</li>

                  </ul>
                </div>
                <!-- End Col -->

                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">Audit et conseil SI et Organisation</li>
                    <li class=\"list-checked-item\">Management de transition SI, Conformité, etc.</li>
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
                    <li class=\"list-checked-item\">Conformité QSE, Qualiopi, RGPD</li>
                    <li class=\"list-checked-item\">Digitalisation des processus et des documents/li>

                  </ul>
                </div>
                <!-- End Col -->

                <div class=\"col-6\">
                  <ul class=\"list-checked list-checked-soft-bg-primary\">
                    <li class=\"list-checked-item\">Expertises et audit</li>
                    <li class=\"list-checked-item\">Recherche de programmes et élaboration des dossiers de candidatures et de demande de subventions</li>
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
          

          {# <!-- SVG Shape -->
          <figure class=\"position-absolute top-0 end-0 me-n7\" style=\"width: 4rem;\">
            <img class=\"img-fluid\" src=\"/svg/components/pointer-up.svg\" alt=\"Image Description\">
          </figure>
          <!-- End SVG Shape -->

          <!-- SVG Shape -->
          <figure class=\"position-absolute bottom-0 end-0 start-sm-0 mb-n8 ms-n8\" style=\"width: 12rem;\">
            <img class=\"img-fluid\" src=\"/svg/components/curved-shape.svg\" alt=\"Image Description\">
          </figure>
          <!-- End SVG Shape --> #}
        </div>

      </div>
    </div>
    <!-- End Features -->

    

    
  </main>
  <!-- ========== END MAIN CONTENT ========== -->

  

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
