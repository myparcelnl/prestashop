<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Controller;

use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookManagerInterface;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @property \MyParcelNL $module
 * @noinspection PhpUnused
 */
final class WebhookController extends AbstractAdminController
{
    private const PRESTASHOP_TOKEN_PARAMETER = '_token';

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(): Response
    {
        $request = $this->createNormalizedRequest();

        Logger::debug('Webhook received', ['request' => $request->query]);

        try {
            /** @var PdkWebhookManagerInterface $webhookManager */
            $webhookManager = Pdk::get(PdkWebhookManagerInterface::class);
            $response       = $webhookManager->call($request);
        } catch (Throwable $e) {
            Logger::error('Failed to execute webhook', [
                'exception' => $e,
                'query'     => $request->query->all(),
            ]);

            return new Response($e->getMessage(), 400);
        }

        return $response;
    }

    /**
     * Remove the _token parameter that's included with all requests before passing it to the PDK endpoints.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function createNormalizedRequest(): Request
    {
        $request = Request::createFromGlobals();
        $request->query->remove(self::PRESTASHOP_TOKEN_PARAMETER);

        return $request;
    }
}
