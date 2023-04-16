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
          <a class=\"btn btn-primary\" href=";
        // line 20
        echo $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("apropos");
        echo ">A propos de OLING</a>
          <a class=\"btn btn-ghost-dark btn-pointer\" href=";
        // line 21
        echo $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("contact");
        echo ">Contactez-nous</a>
        </div>
      </div>
      <!-- End Col -->
      <div class=\"col-lg-6\">
        <div class=\"position-relative\">
          <div class=\"position-relative\">
            <img class=\"img-fluid rounded-2\" src=\"/img/950x950/img1.jpg\" alt=\"Image Description\">

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

  <div class=\"container content-space-1 content-space-lg-1\">
  </div>

    <!-- Stats -->
  <div class=\"container mt-10 mb-10 content-space-1 content-space-lg-1 border border-2 p-5 rounded-2\">
    <div class=\"row justify-content-lg-between align-items-lg-center\">
      <div class=\"col-lg-5\">
        <div class=\"mb-6\">
          <h2>Votre allié pour mener à bien vos initiatives stratégiques</h2>
            <p>Fort d'une expérience solide en management et intégration de systèmes complexes, OLING propose des solutions adaptées aux besoins uniques de ses clients pour assurer le succès de leurs initiatives d'entreprise.</p>
        </div>
      </div>
      <!-- End Col -->
      <div class=\"col-lg-6\">
        <!-- List -->
        <ul class=\"list-equal-height list-equal-height-2-cols\">
          <li class=\"list-equal-height-item\">
            <h4 class=\"display-5 text-center\">50 %</h4>
            <p class=\"mb-0 text-center\">Secteur public</p>
          </li>
          <li class=\"list-equal-height-item\">
            <h4 class=\"display-5 text-center\">50 %</h4>
            <p class=\"mb-0 text-center\">Secteur privé</p>
          </li>
          <li class=\"list-equal-height-item\">
            <h4 class=\"display-5 text-center\">150+</h4>
            <p class=\"mb-0 text-center\">projets conduits dans les outremers et l'hexagone</p>
          </li>
          <li class=\"list-equal-height-item\">
            <h4 class=\"display-5 text-center\"><sub><i class=\"bi-arrow-up-short text-primary ms-n2\"></i></sub>10</h4>
            <p class=\"mb-0 text-center\">consultants experts</p>
          </li>
        </ul>
        <!-- End List -->
      </div>
      <!-- End Col -->
    </div>
    <!-- End Row -->
  </div>
  <!-- End Stats -->

  <!-- Card Grid -->
<div class=\"container content-space-1\">
  <!-- Heading -->
  <div class=\"w-lg-65 text-center mx-lg-auto mb-5 mb-sm-7 mb-lg-10\">
      <h2>Des solutions personnalisées pour des exigences spécifiques</h2>
      <p>OLING s'organise autour de quatre practices, chacune dédiée à un aspect spécifique de l'entreprise : Consulting et AMOA, Expertises et Audit, Business Apps et Microsoft 365. </p>
  </div>
  <!-- End Heading -->

  <div class=\"row row-cols-1 row-cols-sm-2 row-cols-lg-4 gx-0 gx-md-0\">
    ";
        // line 93
        $context["count"] = 0;
        // line 94
        echo "    ";
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((isset($context["practices"]) || array_key_exists("practices", $context) ? $context["practices"] : (function () { throw new RuntimeError('Variable "practices" does not exist.', 94, $this->source); })()));
        foreach ($context['_seq'] as $context["_key"] => $context["practice"]) {
            // line 95
            echo "    ";
            $context["count"] = ((isset($context["count"]) || array_key_exists("count", $context) ? $context["count"] : (function () { throw new RuntimeError('Variable "count" does not exist.', 95, $this->source); })()) + 1);
            // line 96
            echo "    <div class=\"col mb-2 mb-md-4\">
      <!-- Card -->
      <a class=\"card card-borderless card-transition-zoom bg-soft-light h-100\"
      style=\"";
            // line 99
            if (((isset($context["count"]) || array_key_exists("count", $context) ? $context["count"] : (function () { throw new RuntimeError('Variable "count" does not exist.', 99, $this->source); })()) == 2)) {
                echo "margin-top:30px";
            }
            if (((isset($context["count"]) || array_key_exists("count", $context) ? $context["count"] : (function () { throw new RuntimeError('Variable "count" does not exist.', 99, $this->source); })()) == 3)) {
                echo "margin-top:60px";
            }
            if (((isset($context["count"]) || array_key_exists("count", $context) ? $context["count"] : (function () { throw new RuntimeError('Variable "count" does not exist.', 99, $this->source); })()) == 4)) {
                echo "margin-top:90px";
            }
            echo "\" 
       href=";
            // line 100
            echo twig_escape_filter($this->env, $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("practice", ["slug" => twig_get_attribute($this->env, $this->source, $context["practice"], "slug", [], "any", false, false, false, 100), "id" => twig_get_attribute($this->env, $this->source, $context["practice"], "id", [], "any", false, false, false, 100)]), "html", null, true);
            echo ">
        ";
            // line 102
            echo "        <div class=\"card-transition-zoom-item\">
          <img class=\"card-img\" src=\"";
            // line 103
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["practice"], "image1", [], "any", false, false, false, 103), "html", null, true);
            echo "\" alt=\"Image Description\">
        </div>
        <div class=\"card-body\">
          <span class=\"card-subtitle text-body my-3\"><span class=\"fw-bold text-dark\">";
            // line 106
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["practice"], "designation", [], "any", false, false, false, 106), "html", null, true);
            echo "</span></span>
          <h4 class=\"card-title\">";
            // line 107
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["practice"], "introduction", [], "any", false, false, false, 107), "html", null, true);
            echo "</h4>
          <p>";
            // line 108
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["practice"], "DescriptionShort", [], "any", false, false, false, 108), "html", null, true);
            echo "</p>
        </div>
      </a>
      <!-- End Card -->
    </div>
    <!-- End Col -->
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['practice'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 115
        echo "
  </div>
  <!-- End Row -->
</div>
<!-- End Card Grid -->


  ";
        // line 174
        echo "
  <!-- Features -->
  <div class=\"overflow-hidden\">
    <div class=\"container content-space-1 content-space-lg-2\">
      <div class=\"position-relative mb-8\">
        <div class=\"row justify-content-lg-between\">
          <div class=\"col-md-6 col-lg-6 mb-7 mb-lg-0\">
              <div class=\"mb-5\">
                <h2>Recherchez-vous un partenaire fournissant une expertise adaptée à votre secteur d'activité ?</h2>
                <p> OLING, société de conseil spécialisée dans l'accompagnement des entreprises publiques et privées, 
                vous offre une expertise sur-mesure pour répondre à vos défis métiers et informatiques.</p> 
                <p>Découvrez nos compétences pour vous aider à innover, à respecter les exigences spécifiques, les réglementations et à optimiser les processus de chaque métier.</p>
              </div>

              <a class=\"link link-pointer\" href=";
        // line 188
        echo $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("metiers");
        echo ">Découvrez nos expériences par secteur d'activité</a>
          </div>
          <!-- End Col -->
          <div class=\"col-md-6 col-lg-6 pt-10\">
            <img class=\"img-fluid\" src=\"";
        // line 192
        echo twig_escape_filter($this->env, $this->extensions['Symfony\Bridge\Twig\Extension\AssetExtension']->getAssetUrl("./img/spe/arrow.png"), "html", null, true);
        echo "\" alt=\"Image Description\">
              
          </div>
          <!-- End Col -->
        </div>
        <!-- End Row -->
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
        return array (  276 => 192,  269 => 188,  253 => 174,  244 => 115,  231 => 108,  227 => 107,  223 => 106,  217 => 103,  214 => 102,  210 => 100,  198 => 99,  193 => 96,  190 => 95,  185 => 94,  183 => 93,  108 => 21,  104 => 20,  88 => 6,  78 => 5,  59 => 3,  36 => 1,);
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
          <a class=\"btn btn-primary\" href={{path('apropos') }}>A propos de OLING</a>
          <a class=\"btn btn-ghost-dark btn-pointer\" href={{ path('contact')}}>Contactez-nous</a>
        </div>
      </div>
      <!-- End Col -->
      <div class=\"col-lg-6\">
        <div class=\"position-relative\">
          <div class=\"position-relative\">
            <img class=\"img-fluid rounded-2\" src=\"/img/950x950/img1.jpg\" alt=\"Image Description\">

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

  <div class=\"container content-space-1 content-space-lg-1\">
  </div>

    <!-- Stats -->
  <div class=\"container mt-10 mb-10 content-space-1 content-space-lg-1 border border-2 p-5 rounded-2\">
    <div class=\"row justify-content-lg-between align-items-lg-center\">
      <div class=\"col-lg-5\">
        <div class=\"mb-6\">
          <h2>Votre allié pour mener à bien vos initiatives stratégiques</h2>
            <p>Fort d'une expérience solide en management et intégration de systèmes complexes, OLING propose des solutions adaptées aux besoins uniques de ses clients pour assurer le succès de leurs initiatives d'entreprise.</p>
        </div>
      </div>
      <!-- End Col -->
      <div class=\"col-lg-6\">
        <!-- List -->
        <ul class=\"list-equal-height list-equal-height-2-cols\">
          <li class=\"list-equal-height-item\">
            <h4 class=\"display-5 text-center\">50 %</h4>
            <p class=\"mb-0 text-center\">Secteur public</p>
          </li>
          <li class=\"list-equal-height-item\">
            <h4 class=\"display-5 text-center\">50 %</h4>
            <p class=\"mb-0 text-center\">Secteur privé</p>
          </li>
          <li class=\"list-equal-height-item\">
            <h4 class=\"display-5 text-center\">150+</h4>
            <p class=\"mb-0 text-center\">projets conduits dans les outremers et l'hexagone</p>
          </li>
          <li class=\"list-equal-height-item\">
            <h4 class=\"display-5 text-center\"><sub><i class=\"bi-arrow-up-short text-primary ms-n2\"></i></sub>10</h4>
            <p class=\"mb-0 text-center\">consultants experts</p>
          </li>
        </ul>
        <!-- End List -->
      </div>
      <!-- End Col -->
    </div>
    <!-- End Row -->
  </div>
  <!-- End Stats -->

  <!-- Card Grid -->
<div class=\"container content-space-1\">
  <!-- Heading -->
  <div class=\"w-lg-65 text-center mx-lg-auto mb-5 mb-sm-7 mb-lg-10\">
      <h2>Des solutions personnalisées pour des exigences spécifiques</h2>
      <p>OLING s'organise autour de quatre practices, chacune dédiée à un aspect spécifique de l'entreprise : Consulting et AMOA, Expertises et Audit, Business Apps et Microsoft 365. </p>
  </div>
  <!-- End Heading -->

  <div class=\"row row-cols-1 row-cols-sm-2 row-cols-lg-4 gx-0 gx-md-0\">
    {% set count = 0 %}
    {% for practice in practices %}
    {% set count = count + 1 %}
    <div class=\"col mb-2 mb-md-4\">
      <!-- Card -->
      <a class=\"card card-borderless card-transition-zoom bg-soft-light h-100\"
      style=\"{% if count == 2 %}margin-top:30px{% endif %}{% if count == 3 %}margin-top:60px{% endif %}{% if count == 4 %}margin-top:90px{% endif %}\" 
       href={{path('practice',{'slug' : practice.slug, 'id' : practice.id} ) }}>
        {#  #}
        <div class=\"card-transition-zoom-item\">
          <img class=\"card-img\" src=\"{{ practice.image1 }}\" alt=\"Image Description\">
        </div>
        <div class=\"card-body\">
          <span class=\"card-subtitle text-body my-3\"><span class=\"fw-bold text-dark\">{{ practice.designation }}</span></span>
          <h4 class=\"card-title\">{{ practice.introduction }}</h4>
          <p>{{ practice.DescriptionShort }}</p>
        </div>
      </a>
      <!-- End Card -->
    </div>
    <!-- End Col -->
    {% endfor %}

  </div>
  <!-- End Row -->
</div>
<!-- End Card Grid -->


  {# <!-- Heading -->
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
      {% for practice in practices %}
      <li class=\"list-step-item\">
        <div class=\"position-relative rounded-3 py-8 px-4 px-md-10\">
          <div class=\"row align-items-lg-center\">
            <div class=\"col-lg-5 mb-7 mb-lg-0\">
              <div class=\"pe-lg-5\">
                <div class=\"mb-5\">
                  <span class=\"text-cap text-primary\">{{ practice.designation }}</span>
                  <h2>{{ practice.introduction }}</h2>
                  <p>{{ practice.DescriptionShort }}</p>
                   <a class=\"link link-pointer\" href={{path('practice',{'slug' : practice.slug, 'id' : practice.id} ) }}>En savoir plus</a>
                </div>
              </div>
            </div>
            <!-- End Col -->
            <div class=\"col-lg-7\">
              <div class=\"video-bg\">
                <div class=\"js-video-bg video-bg-content\"
                    data-hs-video-bg-options='{
                      \"videoId\": \"/img/video/working-in-office\"
                    }'>
                </div>
              </div> 
              <img class=\"img-fluid rounded\" src=\"{{ practice.image1 }}\" alt=\"Image Description\">
            </div>
            <!-- End Col -->
          </div>
          <!-- End Row -->
          <div class=\"position-absolute top-0 start-0 w-100 w-lg-65 h-65 h-lg-100 bg-light rounded-3 zi-n1 ms-n5\">
          </div>
        </div>
      </li>
      {% endfor %}
    </ul>
    <!-- End List Step -->
  </div>
  <!-- End Step Features --> #}

  <!-- Features -->
  <div class=\"overflow-hidden\">
    <div class=\"container content-space-1 content-space-lg-2\">
      <div class=\"position-relative mb-8\">
        <div class=\"row justify-content-lg-between\">
          <div class=\"col-md-6 col-lg-6 mb-7 mb-lg-0\">
              <div class=\"mb-5\">
                <h2>Recherchez-vous un partenaire fournissant une expertise adaptée à votre secteur d'activité ?</h2>
                <p> OLING, société de conseil spécialisée dans l'accompagnement des entreprises publiques et privées, 
                vous offre une expertise sur-mesure pour répondre à vos défis métiers et informatiques.</p> 
                <p>Découvrez nos compétences pour vous aider à innover, à respecter les exigences spécifiques, les réglementations et à optimiser les processus de chaque métier.</p>
              </div>

              <a class=\"link link-pointer\" href={{ path('metiers')}}>Découvrez nos expériences par secteur d'activité</a>
          </div>
          <!-- End Col -->
          <div class=\"col-md-6 col-lg-6 pt-10\">
            <img class=\"img-fluid\" src=\"{{ asset('./img/spe/arrow.png') }}\" alt=\"Image Description\">
              
          </div>
          <!-- End Col -->
        </div>
        <!-- End Row -->
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
