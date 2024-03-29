<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Webhook\Repository;

use MyParcelNL\Pdk\App\Webhook\Repository\AbstractPdkWebhooksRepository;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository;
use MyParcelNL\PrestaShop\Configuration\Contract\PsConfigurationServiceInterface;

class PsWebhooksRepository extends AbstractPdkWebhooksRepository
{
    /**
     * @var \MyParcelNL\PrestaShop\Configuration\Contract\PsConfigurationServiceInterface
     */
    private $configurationService;

    public function __construct(
        StorageInterface                $storage,
        WebhookSubscriptionRepository   $subscriptionRepository,
        PsConfigurationServiceInterface $configurationService
    ) {
        parent::__construct($storage, $subscriptionRepository);
        $this->configurationService = $configurationService;
    }

    public function getAll(): WebhookSubscriptionCollection
    {
        return $this->retrieve(Pdk::get('settingKeyWebhooks'), [$this, 'getFromStorage']);
    }

    public function getHashedUrl(): ?string
    {
        return $this->configurationService->get(Pdk::get('settingKeyWebhookHash')) ?: null;
    }

    public function remove(string $hook): void
    {
        $items = $this->getAll();

        $this->store(
            $items->filter(function (WebhookSubscription $item) use ($hook) {
                return $item->hook !== $hook;
            })
        );
    }

    public function store(WebhookSubscriptionCollection $subscriptions): void
    {
        $this->configurationService->set(Pdk::get('settingKeyWebhooks'), $subscriptions->toArray());
    }

    public function storeHashedUrl(string $url): void
    {
        $this->configurationService->set(Pdk::get('settingKeyWebhookHash'), $url);
    }

    private function getFromStorage(): WebhookSubscriptionCollection
    {
        $items = $this->configurationService->get(Pdk::get('settingKeyWebhooks'));

        return new WebhookSubscriptionCollection($items);
    }
}
