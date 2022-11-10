<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use MyParcelNL\PrestaShop\Model\MyParcelRequest as Request;
use MyParcelNL\PrestaShop\Service\Concern\HasApiKey;
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
     * @throws \MyParcelNL\Sdk\src\Exception\AccountNotActiveException
     * @throws \Exception
     */
    public function getTrackTrace(int $shipmentId, bool $withDeliveryMoment = false)
    {
        $extraInfo = $withDeliveryMoment ? '?extra_info=delivery_moment' : '';
        $request   = $this->createRequest()
            ->setRequestParameters(
                $this->apiKey,
                null,
                MyParcelRequest::HEADER_ACCEPT_APPLICATION_PDF
            )
            ->sendRequest('GET', Request::REQUEST_TYPE_TRACKTRACE . "/{$shipmentId}{$extraInfo}");

        return $request->getResult();
    }
}
