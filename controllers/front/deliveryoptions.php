<?php
/** @noinspection AutoloadingIssuesInspection,PhpUnused */

declare(strict_types=1);

use MyParcelNL\Pdk\App\Endpoint\EndpointRegistry;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Facade\MyParcelModule;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (! defined('_PS_VERSION_')) {
    return;
}

class MyParcelNLDeliveryOptionsModuleFrontController extends FrontControllerCore
{
    public function initContent(): void
    {
        if (! MyParcelModule::isEnabled()) {
            (new JsonResponse(
                ['type' => null, 'title' => 'Service Unavailable', 'status' => 503, 'detail' => 'Module is not enabled'],
                503
            ))->send();
            die();
        }

        $response = $this->handleRequest();
        $response->send();
        die();
    }

    /**
     * Separated from initContent() for testability (die() and module check cannot be tested).
     */
    public function handleRequest(): Response
    {
        $authError = $this->getAuthError();
        if ($authError !== null) {
            return $authError;
        }

        $request = Request::createFromGlobals();
        $handler = Pdk::get(EndpointRegistry::DELIVERY_OPTIONS);

        return $handler->handle($request);
    }

    private function getAuthError(): ?Response
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? (function_exists('apache_request_headers') ? (apache_request_headers()['Authorization'] ?? '') : '')
            ?? '';

        if (strncmp($authHeader, 'Basic ', 6) !== 0) {
            return new JsonResponse(
                ['type' => null, 'title' => 'Unauthorized', 'status' => 401, 'detail' => 'Missing or invalid Authorization header'],
                401,
                ['WWW-Authenticate' => 'Basic realm="MyParcel"']
            );
        }

        $decoded = (string) base64_decode(substr($authHeader, 6), true);
        $key     = explode(':', $decoded, 2)[0];

        if (! $key) {
            return new JsonResponse(
                ['type' => null, 'title' => 'Unauthorized', 'status' => 401, 'detail' => 'Invalid webservice key'],
                401,
                ['WWW-Authenticate' => 'Basic realm="MyParcel"']
            );
        }

        $idAccount = $this->getWebserviceAccountId($key);

        if (! $idAccount) {
            return new JsonResponse(
                ['type' => null, 'title' => 'Unauthorized', 'status' => 401, 'detail' => 'Invalid or inactive webservice key'],
                401,
                ['WWW-Authenticate' => 'Basic realm="MyParcel"']
            );
        }

        if (! $this->hasOrdersGetPermission((int) $idAccount)) {
            return new JsonResponse(
                ['type' => null, 'title' => 'Forbidden', 'status' => 403, 'detail' => 'Webservice key lacks orders GET permission'],
                403
            );
        }

        return null;
    }

    /**
     * @return int|false
     */
    private function getWebserviceAccountId(string $key)
    {
        // Webservice keys are alphanumeric — reject anything else to prevent SQL injection.
        if (! preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
            return false;
        }

        $sql = new DbQuery();
        $sql->select('id_webservice_account');
        $sql->from('webservice_account');
        $sql->where("`key` = '" . $key . "'");
        $sql->where('active = 1');

        $rows = Db::getInstance()->executeS($sql);

        if (empty($rows)) {
            return false;
        }

        return $rows[0]['id_webservice_account'] ?? false;
    }

    private function hasOrdersGetPermission(int $idAccount): bool
    {
        $sql = new DbQuery();
        $sql->select('id_webservice_account');
        $sql->from('webservice_permission');
        $sql->where('id_webservice_account = ' . (int) $idAccount);
        $sql->where("resource = 'orders'");
        $sql->where("method = 'GET'");

        try {
            $rows = Db::getInstance()->executeS($sql);
        } catch (\PrestaShopException $e) {
            return false;
        }

        return ! empty($rows);
    }
}
