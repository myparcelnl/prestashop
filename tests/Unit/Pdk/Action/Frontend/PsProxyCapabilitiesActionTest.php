<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Action\Frontend;

use MyParcelNL\PrestaShop\Pdk\Api\Service\PsFrontendEndpointService;
use MyParcelNL\PrestaShop\Router\Contract\PsRouterServiceInterface;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPsPdkInstance());

dataset('frontend endpoint urls', [
    'friendly URL (rewriting on)'  => [
        'url'        => 'https://example.com/en/module/myparcelnl/frontend',
        'parameters' => [],
        'expectBase' => 'https://example.com/en/module/myparcelnl/frontend',
    ],
    'query string URL (rewriting off)' => [
        'url'        => 'https://example.com/index.php?fc=module&module=myparcelnl&controller=frontend',
        'parameters' => ['fc' => 'module', 'module' => 'myparcelnl', 'controller' => 'frontend'],
        'expectBase' => 'https://example.com/index.php',
    ],
]);

it('builds the frontend endpoint URL from the router service', function (string $url, array $parameters, string $expectBase) {
    $routerService = new class ($url, $parameters) implements PsRouterServiceInterface {
        private string $url;
        private array $parameters;

        public function __construct(string $url, array $parameters)
        {
            $this->url        = $url;
            $this->parameters = $parameters;
        }

        public function getBaseUrl(string $route): string
        {
            return $this->expectBase();
        }

        public function getRouteToken(string $route): string
        {
            return $this->parameters['_token'] ?? '';
        }

        public function getRouteParameters(string $route): array
        {
            return $this->parameters;
        }

        private function expectBase(): string
        {
            return parse_url($this->url, PHP_URL_SCHEME) . '://'
                . parse_url($this->url, PHP_URL_HOST)
                . parse_url($this->url, PHP_URL_PATH);
        }
    };

    $service = new PsFrontendEndpointService($routerService);

    expect($service->getBaseUrl())->toBe($expectBase);
})->with('frontend endpoint urls');
