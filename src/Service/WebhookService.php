<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Curl\Curl;
use Exception;
use MyParcelNL\PrestaShop\Model\MyParcelRequest as Request;
use MyParcelNL\PrestaShop\Model\Webhook\Subscription;
use MyParcelNL\PrestaShop\Service\Concern\HasApiKey;
use MyParcelNL\Sdk\src\Model\MyParcelRequest;

class WebhookService extends AbstractEndpoint
{
    use HasApiKey;

    /**
     * @param  \MyParcelNL\PrestaShop\Model\Webhook\Subscription $subscription
     *
     * @return mixed|null
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     */
    public function addSubscription(Subscription $subscription)
    {
        $request = $this->createRequest()
            ->setRequestParameters(
                $this->apiKey,
                $subscription->encode(),
                Request::REQUEST_HEADER_WEBHOOK
            )
            ->sendRequest('POST', Request::REQUEST_TYPE_WEBHOOK);

        return $request->getResult();
    }

    /**
     * @param  string $id
     *
     * @return bool
     * @throws \Exception
     */
    public function deleteSubscription(string $id): bool
    {
        return 204 === $this->delete(Request::REQUEST_TYPE_WEBHOOK . '/' . $id);
    }

    /**
     * This is necessary because the MyParcelNL SDK does not support DELETE requests at the moment.
     *
     * @param  string $url
     *
     * @return int
     * @throws \Exception
     */
    private function delete(string $url): int
    {
        $content = (new Curl())
            ->setHeader('Authorization', 'basic ' . base64_encode($this->apiKey))
            ->setHeader('User-Agent', $this->createRequest()->getUserAgentHeader())
            ->setHeader('Content-Type', 'application/json; charset=utf-8')
            ->delete(MyParcelRequest::REQUEST_URL . '/' . $url);

        if ($content->http_status_code >= 300) {
            throw new Exception('Request failed');
        }

        return $content->http_status_code;
    }
}
