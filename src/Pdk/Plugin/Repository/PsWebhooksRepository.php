<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Pdk\Plugin\Repository;

use MyParcelNL\Pdk\Plugin\Webhook\Repository\AbstractPdkWebhooksRepository;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Webhook\Collection\WebhookSubscriptionCollection;
use MyParcelNL\Pdk\Webhook\Model\WebhookSubscription;
use MyParcelNL\Pdk\Webhook\Repository\WebhookSubscriptionRepository;
use MyParcelNL\PrestaShop\Service\Configuration\ConfigurationServiceInterface;

class PsWebhooksRepository extends AbstractPdkWebhooksRepository
{
    /**
     * @var \MyParcelNL\PrestaShop\Service\Configuration\ConfigurationServiceInterface
     */
    private $configurationService;

    public function __construct(
        StorageInterface              $storage,
        WebhookSubscriptionRepository $subscriptionRepository,
        ConfigurationServiceInterface $configurationService
    ) {
        parent::__construct($storage, $subscriptionRepository);
        $this->configurationService = $configurationService;
    }

    public function getAll(): WebhookSubscriptionCollection
    {
        return $this->retrieve('myparcelnl_webhooks', [$this, 'getFromStorage']);
    }

    public function getHashedUrl(): ?string
    {
        return $this->configurationService->get('myparcelnl_webhooks_hash', null);
    }

    public function remove(string $hook): void
    {
        $items = $this->getAll();
        $this->store(
            $items->filter(function (WebhookSubscription $item) use ($hook) {
                return $item->getHook() !== $hook;
            })
        );
    }

    public function store(WebhookSubscriptionCollection $subscriptions): void
    {
        $this->configurationService->set('myparcelnl_webhooks', $subscriptions->toArray());
    }

    public function storeHashedUrl(string $url): void
    {
        $this->configurationService->set('myparcelnl_webhooks_hash', $url);
    }

    private function getFromStorage(): WebhookSubscriptionCollection
    {
        $items = $this->configurationService->get('myparcelnl_webhooks', null);

        return new WebhookSubscriptionCollection($items);
    }
}
