<?php

namespace Gett\MyparcelBE\Service\Consignment;

use Configuration;
use Context;
use Gett\MyparcelBE\Collection\ConsignmentCollection;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Logger\Logger;
use Gett\MyparcelBE\Service\Platform\PlatformServiceFactory;
use Tools;

class Download
{
    /**
     * @var string
     */
    private $api_key;

    /**
     * @var array
     */
    private $request;

    /**
     * @var \Configuration
     */
    private $configuration;

    /**
     * @param  string         $apiKey
     * @param  array          $request
     * @param  \Configuration $configuration
     */
    public function __construct(string $apiKey, array $request, Configuration $configuration)
    {
        $this->api_key       = $apiKey;
        $this->request       = $request;
        $this->configuration = $configuration;
    }

    /**
     * @param  array $labelIds
     *
     * @return void
     * @throws \Exception
     */
    public function downloadLabel(array $labelIds): void
    {
        $platformService = PlatformServiceFactory::create();

        try {
            $collection = ConsignmentCollection::findMany($labelIds, $this->api_key);

            if (! empty($collection->getConsignments())) {
                $collection->setPdfOfLabels($this->fetchPositions());
                $isPdf = is_string($collection->getLabelPdf());

                if ($isPdf) {
                    $collection->downloadPdfOfLabels(
                        'true' === $this->configuration::get(
                            Constant::LABEL_OPEN_DOWNLOAD_CONFIGURATION_NAME,
                            false
                        )
                    );
                }
                Logger::addLog($collection->toJson());
                if (! $isPdf) {
                    Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminOrders'));
                }
            } else {
                Tools::redirectAdmin(Context::getContext()->link->getAdminLink('AdminOrders'));
            }
        } catch (\Exception $e) {
            Logger::addLog($e->getMessage(), true, true);
        }
    }

    private function fetchPositions()
    {
        if ($this->request['format'] == 'a6') {
            return false;
        }

        return $this->request['position'];
    }
}
