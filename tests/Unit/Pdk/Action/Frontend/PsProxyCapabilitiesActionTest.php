<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Action\Frontend;

use MyParcelNL\Pdk\Api\PdkCapabilitiesActions;
use MyParcelNL\Pdk\App\Action\Capabilities\CapabilitiesAction;
use MyParcelNL\Pdk\App\Action\Contract\ActionInterface;
use MyParcelNL\Pdk\App\Api\PdkEndpoint;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPsPdkInstance());

it('calls proxyCapabilities via the frontend endpoint', function () {
    $mock = new class implements ActionInterface {
        public function handle(Request $request): Response
        {
            return new Response(json_encode(['results' => []]), 200, ['Content-Type' => 'application/json']);
        }
    };

    $reset = mockPdkProperty(CapabilitiesAction::class, $mock);

    try {
        /** @var PdkEndpoint $endpoint */
        $endpoint = Pdk::get(PdkEndpoint::class);
        $request  = new Request(
            ['action' => PdkCapabilitiesActions::PROXY_CAPABILITIES],
            [],
            [],
            [],
            [],
            ['REQUEST_METHOD' => 'POST'],
            json_encode(['recipient' => ['country_code' => 'NL', 'postal_code' => '2132WT']])
        );

        $response = $endpoint->call($request, PdkEndpoint::CONTEXT_FRONTEND);

        expect($response->getStatusCode())->toBe(200);
    } finally {
        $reset();
    }
});
