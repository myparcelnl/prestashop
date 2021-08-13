<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service;

use Gett\MyparcelBE\Model\MyParcelRequest as Request;
use Gett\MyparcelBE\Service\Concern\HasApiKey;
use MyParcelNL\Sdk\src\Model\MyParcelRequest;

class Tracktrace extends AbstractEndpoint
{
    use HasApiKey;

    /**
     * @param  int  $shipmentId
     * @param  bool $withDeliveryMoment
     *
     * @return mixed|null
     * @throws \MyParcelNL\Sdk\src\Exception\ApiException
     * @throws \MyParcelNL\Sdk\src\Exception\MissingFieldException
     */
    public function getTrackTrace(int $shipmentId, bool $withDeliveryMoment = false)
    {
        $extraInfo = $withDeliveryMoment ? '?extra_info=delivery_moment' : '';
        $request   = $this->createRequest()
            ->setRequestParameters(
                $this->apiKey,
                null,
                MyParcelRequest::REQUEST_HEADER_RETRIEVE_SHIPMENT
            )
            ->sendRequest('GET', Request::REQUEST_TYPE_TRACKTRACE . "/{$shipmentId}{$extraInfo}");

        return $request->getResult();
    }
}
