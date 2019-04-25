<?php

/**
 * A services to check if the API-key is correct
 *
 * If you want to add improvements, please create a fork in our GitHub:
 * https://github.com/myparcelnl
 *
 * @author      Reindert Vetter <reindert@myparcel.nl>
 * @copyright   2010-2017 MyParcel
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US  CC BY-NC-ND 3.0 NL
 * @link        https://github.com/myparcelnl/sdk
 * @since       File available since Release v1.1.7
 */
namespace MyParcelModule\MyParcelNL\Sdk\src\Services;

use MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest;
class CheckApiKeyService
{
    private $api_key;
    public function apiKeyIsCorrect()
    {
        try {
            $request = new \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest();
            $request->setUserAgent($request->getUserAgentFromComposer())->setRequestParameters($this->getApiKey(), '', \MyParcelModule\MyParcelNL\Sdk\src\Model\MyParcelRequest::REQUEST_HEADER_RETRIEVE_SHIPMENT)->sendRequest('GET');
            if ($request->getResult() === null) {
                throw new \Exception('Unable to connect to MyParcel.');
            }
        } catch (\Exception $exception) {
            if (strpos($exception, 'Access Denied') > 1) {
                return false;
            }
        }
        return true;
    }
    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->api_key;
    }
    /**
     * @param mixed $api_key
     *
     * @return CheckApiKeyService
     */
    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
        return $this;
    }
}
