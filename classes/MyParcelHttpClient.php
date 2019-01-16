<?php
/**
 * 2017-2019 DM Productions B.V.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@dmp.nl so we can send you a copy immediately.
 *
 * @author     Michael Dekker <info@mijnpresta.nl>
 * @copyright  2010-2019 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

namespace MyParcelModule;

use Closure;
use Configuration;
use Context;
use ErrorException;
use Logger;
use MyParcel;
use MyParcelModule\Curl\CaseInsensitiveArray;
use MyParcelModule\Curl\Curl;
use PrestaShopException;
use Validate;

if (!defined('_PS_VERSION_')) {
    return;
}

/**
 * Class MyParcelHttpClient
 */
class MyParcelHttpClient extends Curl
{
    /** @var Curl $myparcelClient */
    protected static $myparcelClient;
    /** @var string $requestBody */
    protected $requestBody;
    /** @var float $minDelay */
    public $minDelay = 0.5;
    /** @var float $maxDelay */
    public $maxDelay = 4.0;
    public $maxRetries = MyParcel::CONNECTION_ATTEMPTS;
    public $remainingRetries = MyParcel::CONNECTION_ATTEMPTS;

    /**
     * @return Curl
     *
     * @throws ErrorException
     * @throws PrestaShopException
     */
    public static function getInstance()
    {
        if (!static::$myparcelClient) {
            $curl = new static();
            $curl->retryDecider = $curl->getRetryDecider();
            $curl->setConnectTimeout(MyParcel::API_TIMEOUT);
            $curl->setTimeout(MyParcel::API_TIMEOUT);
            $curl->setDefaultHeaders();
            $curl->setDefaultJsonDecoder(true);
            static::$myparcelClient = $curl;
        }

        return static::$myparcelClient;
    }

    /**
     * Set default headers
     *
     * @throws PrestaShopException
     */
    public function setDefaultHeaders()
    {
        $this->setHeader('Authorization', 'basic '.base64_encode(Configuration::get(MyParcel::API_KEY)));
        $this->setUserAgent(MyParcel::getUserAgent());
    }

    /**
     * Set default User-Agent
     */
    public function setDefaultUserAgent()
    {
        $this->setUserAgent(MyParcel::getUserAgent());
    }

    /**
     * @return Closure
     */
    public function getRetryDecider() {
        return function (MyParcelHttpClient $curl) {
            if ($curl->remainingRetries > 0 && ($curl->curlErrorCode || $curl->httpStatusCode >= 500)) {
                // Exponential back off
                sleep(min($curl->minDelay * pow(2, $curl->maxRetries - --$curl->remainingRetries), $curl->maxDelay));

                return true;
            }

            return false;
        };
    }

    /**
     * Set Opt
     *
     * @access public
     * @param  $option
     * @param  $value
     *
     * @return boolean
     */
    public function setOpt($option, $value)
    {
        if ($option === CURLOPT_POSTFIELDS) {
            $this->requestBody = $value;
        }

        return parent::setOpt($option, $value);
    }

    /**
     * Reset request headers when done
     *
     * @throws PrestaShopException
     */
    public function execDone()
    {
        parent::execDone();
        if (Configuration::get(MyParcel::LOG_API)) {
            if (Validate::isLoadedObject(Context::getContext()->customer)) {
                $subject = 'Customer';
                $id = Context::getContext()->customer->id;
            } else {
                $subject = null;
                $id = null;
            }

            /** @var CaseInsensitiveArray $requestHeaders */
            $requestHeaders = $this->requestHeaders;
            $requestHeaders->rewind();
            list ($method) = explode(' ', $requestHeaders->offsetGet('request-line'));
            $rawRequest = array(
                "{$method} {$this->url} HTTP/1.1",
            );
            for ($i = 0; $i < $requestHeaders->count(); $i++) {
                if (!in_array(strtolower($requestHeaders->key()), array('host', 'request-line'))) {
                    $rawRequest[] = "{$requestHeaders->key()}: {$requestHeaders->current()}";
                }
                $requestHeaders->next();
            }
            $rawRequest[] = '';
            $rawRequest[] = '';

            $requestLog = base64_encode(implode("\n", $rawRequest).$this->requestBody);
            if (\Tools::strlen($requestLog)) {
                Logger::addLog(
                    $requestLog,
                    1,
                    null,
                    $subject,
                    $id
                );
            }
            $responseLog = base64_encode($this->rawResponseHeaders.$this->rawResponse);
            if (\Tools::strlen($responseLog)) {
                Logger::addLog(
                    $responseLog,
                    1,
                    null,
                    $subject,
                    $id
                );
            }
        }

        // Reset headers
        $this->requestHeaders = null;
        $this->setDefaultHeaders();
    }
}
