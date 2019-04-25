<?php

/**
 * Curl to use in the api
 *
 * If you want to add improvements, please create a fork in our GitHub:
 * https://github.com/myparcelnl
 *
 * @author      Reindert Vetter <reindert@myparcel.nl>
 * @copyright   2010-2017 MyParcel
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US  CC BY-NC-ND 3.0 NL
 * @link        https://github.com/myparcelnl/sdk
 * @since       File available since Release v0.1.0
 */
namespace MyParcelModule\MyParcelNL\Sdk\src\Helper;

use MyParcelModule\Curl\Curl;
/**
 * Class Curl
 */
class MyParcelCurl extends \MyParcelModule\Curl\Curl
{
    /** @var float $minDelay */
    public $minDelay = 0.5;
    /** @var float $maxDelay */
    public $maxDelay = 4.0;
    public $maxRetries = 5;
    public $remainingRetries = 5;
    public function __construct($baseUrl = null)
    {
        parent::__construct($baseUrl);
        $this->setConnectTimeout(60);
        $this->setTimeout(60);
        $this->retryDecider = function ($curl) {
            /** MyParcelCurl $curl */
            if ($curl->remainingRetries > 0 && ($curl->curlErrorCode || $curl->httpStatusCode >= 500)) {
                // Exponential back off
                sleep(min($curl->minDelay * pow(2, $curl->maxRetries - --$curl->remainingRetries), $curl->maxDelay));
                return true;
            }
            return false;
        };
        $this->setDefaultJsonDecoder(true);
    }
    public function execDone()
    {
        parent::execDone();
        // Reset headers
        $this->requestHeaders = null;
    }
}
