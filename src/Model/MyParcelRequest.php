<?php

namespace Gett\MyparcelBE\Model;

class MyParcelRequest extends \MyParcelNL\Sdk\src\Model\MyParcelRequest
{
    /**
     * API headers.
     */
    public const REQUEST_HEADER_WEBHOOK = 'Content-type: application/json; charset=utf-8';

    /**
     * Supported request types.
     */
    public const REQUEST_TYPE_WEBHOOK = 'webhook_subscriptions';
    public const REQUEST_TYPE_TRACKTRACE = 'tracktraces';
}
