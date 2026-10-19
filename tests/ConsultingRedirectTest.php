<?php

namespace App\Tests;

use App\Controller\PracticeController;
use App\Repository\PracticeRepository;
use App\Repository\ServicesRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class ConsultingRedirectTest extends TestCase
{
    private function redirect(string $alias, array $query, string $baseUrl = ''): Response
    {
        // Both redirects must finish before needing any content or database service.
        $controller = (new \ReflectionClass(PracticeController::class))->newInstanceWithoutConstructor();
        $routes = new RouteCollection();
        $routes->add('amoa_si', new Route('/amoa-si'));
        $container = new Container();
        $container->set('router', new UrlGenerator($routes, new RequestContext($baseUrl, 'GET', 'recipe.example', 'https')));
        $controller->setContainer($container);
        $request = Request::create($alias, 'GET', $query);
        if ($alias === '/practice/consulting') {
            return $controller->practiceHomeConsulting($request);
        }
        $practices = $this->createMock(PracticeRepository::class);
        $services = $this->createMock(ServicesRepository::class);
        $practices->expects(self::never())->method('findOneBy');

        return $controller->practices($practices, $services, 'consulting', $request);
    }

    /** @dataProvider aliasQueries */
    public function testBothAliasesRedirectDirectlyAndPreserveQuery(string $alias, array $query): void
    {
        $response = $this->redirect($alias, $query);
        self::assertSame(301, $response->getStatusCode());
        $location = $response->headers->get('Location');
        self::assertSame('/amoa-si', parse_url($location, PHP_URL_PATH));
        self::assertNull(parse_url($location, PHP_URL_HOST));
        self::assertNull(parse_url($location, PHP_URL_SCHEME));
        self::assertNull(parse_url($location, PHP_URL_FRAGMENT));
        parse_str(parse_url($location, PHP_URL_QUERY) ?? '', $actual);
        // Key order in a query is immaterial; nested list order remains significant.
        ksort($actual);
        ksort($query);
        self::assertSame($query, $actual);
        if ($query === []) {
            self::assertSame('/amoa-si', $location);
        } elseif (count($query) === 1 && isset($query['utm_source'])) {
            self::assertSame('/amoa-si?utm_source=google', $location);
        }
    }

    public static function aliasQueries(): iterable
    {
        foreach (['/consulting', '/practice/consulting'] as $alias) {
            yield $alias.' no parameters' => [$alias, []];
            yield $alias.' single UTM' => [$alias, ['utm_source' => 'google']];
            yield $alias.' UTM triple' => [$alias, ['utm_source' => 'google', 'utm_medium' => 'organic', 'utm_campaign' => 'amoa-si']];
            yield $alias.' application ref' => [$alias, ['ref' => 'partner']];
            yield $alias.' click identifier' => [$alias, ['utm_source' => 'linkedin', 'utm_campaign' => 'erp', 'gclid' => 'test123']];
            yield $alias.' query arrays' => [$alias, ['tags' => ['a', 'b'], 'filters' => ['region' => ['paris', 'lyon']], 'ref' => 'partner']];
            yield $alias.' encoded values' => [$alias, ['ref' => 'été & partenaire', 'empty' => '', 'next' => 'https://external.example/path?a=1#part']];
        }
    }

    public function testConsultingQueryCannotAlterRouteHostSchemeOrFragment(): void
    {
        $query = ['_route' => 'other', '_host' => 'external.example', '_scheme' => 'https', '_fragment' => '//external.example', 'host' => 'external.example', 'scheme' => 'https', 'destination' => '//external.example', 'url' => 'https://external.example'];
        $location = $this->redirect('/consulting', $query)->headers->get('Location');
        self::assertSame('/amoa-si', parse_url($location, PHP_URL_PATH));
        self::assertNull(parse_url($location, PHP_URL_HOST));
        self::assertNull(parse_url($location, PHP_URL_SCHEME));
        self::assertNull(parse_url($location, PHP_URL_FRAGMENT));
        parse_str(parse_url($location, PHP_URL_QUERY), $actual);
        ksort($query);
        ksort($actual);
        self::assertSame($query, $actual);
    }

    public function testConsultingWorksUnderARecipeBasePath(): void
    {
        self::assertSame('/preview/amoa-si?ref=partner', $this->redirect('/consulting', ['ref' => 'partner'], '/preview')->headers->get('Location'));
    }
}
