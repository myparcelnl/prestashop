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
            die(1);
        }

        $response = $this->handleRequest();
        $response->send();
        die(1);
    }

    /**
     * Separated from initContent() for testability (die(1) and module check cannot be tested).
     */
    public function handleRequest(): Response
    {
        $authError = $this->authenticate();
        if ($authError !== null) {
            return $authError;
        }

        $request = Request::createFromGlobals();
        $handler = Pdk::get(EndpointRegistry::DELIVERY_OPTIONS);

        return $handler->handle($request);
    }

    private function authenticate(): ?Response
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (! str_starts_with($authHeader, 'Basic ')) {
            return new JsonResponse(
                ['type' => null, 'title' => 'Unauthorized', 'status' => 401, 'detail' => 'Missing or invalid Authorization header'],
                401,
                ['WWW-Authenticate' => 'Basic realm="MyParcel"']
            );
        }

        $key = rtrim((string) base64_decode(substr($authHeader, 6), true), ':');

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
        $sql = new DbQuery();
        $sql->select('id_webservice_account');
        $sql->from('webservice_account');
        $sql->where('active = 1');

        $rows = Db::getInstance()->executeS($sql);

        if (empty($rows)) {
            return false;
        }

        return $rows[0]['id_webservice_account'] ?? false;
    }

    private function hasOrdersGetPermission(int $idAccount): bool
    {
        // NOTE: SELECT id_webservice_account (not `GET`) so MockPsDb can parse the column.
        // GET = 1 in WHERE works because both are word characters matched by the mock's regex.
        $sql = new DbQuery();
        $sql->select('id_webservice_account');
        $sql->from('webservice_account_rule');
        $sql->where('id_webservice_account = ' . (int) $idAccount);
        $sql->where("resource = 'orders'");
        $sql->where('GET = 1');

        $rows = Db::getInstance()->executeS($sql);

        return ! empty($rows);
    }
}
