<?php
    /** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

    declare(strict_types=1);

    namespace MyParcelNL\PrestaShop\Pdk\Action\Backend\Account;

    use MyParcelNL\Pdk\Account\Collection\ShopCollection;
    use MyParcelNL\Pdk\Account\Model\Shop;
    use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
    use MyParcelNL\Pdk\Carrier\Model\Carrier;
    use MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository;
    use MyParcelNL\Pdk\Facade\Pdk;
    use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
    use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
    use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
    use Psr\Log\LoggerInterface;
    use RuntimeException;
    use Symfony\Component\HttpFoundation\Request;
    use function MyParcelNL\Pdk\Tests\factory;
    use function MyParcelNL\Pdk\Tests\mockPdkProperty;
    use function MyParcelNL\Pdk\Tests\usesShared;

    usesShared(new UsesMockPsPdkInstance());

    it('completes account update even when carrier sync fails', function () {
        TestBootstrapper::hasAccount(
            TestBootstrapper::API_KEY_VALID,
            factory(ShopCollection::class)->push(
                factory(Shop::class)->withCarriers(
                    factory(CarrierCollection::class)->push(factory(Carrier::class)->fromPostNL())
                )
            )
        );

        // Replace CarrierCapabilitiesRepository with one that always throws
        $mock = new class extends CarrierCapabilitiesRepository {
            public function __construct() {}

            public function getContractDefinitions(?string $carrier = null): CarrierCollection
            {
                throw new RuntimeException('API returned 401: Permission Denied');
            }
        };

        $reset = mockPdkProperty(CarrierCapabilitiesRepository::class, $mock);

        /** @var PsUpdateAccountAction $action */
        $action  = Pdk::get(PsUpdateAccountAction::class);
        $request = new Request([], [], [], [], [], [], json_encode([
            'data' => [
                'account_settings' => [
                    'apiKey' => TestBootstrapper::API_KEY_VALID,
                ],
            ],
        ]));

        // Should not throw — failures are caught and logged
        $response = $action->handle($request);

        expect($response->getStatusCode())->toBe(200);

    // Error should be logged
        /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
        $logger    = Pdk::get(LoggerInterface::class);
        $errorLogs = array_filter($logger->getLogs(), function (array $log) {
            return 'error' === $log['level'];
        });

        expect($errorLogs)->not->toBeEmpty();

        $reset();
    });
