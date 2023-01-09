<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service\Consignment;

use Configuration;
use MyParcelNL\PrestaShop\Collection\ConsignmentCollection;
use MyParcelNL\PrestaShop\Constant;
use MyParcelNL\PrestaShop\Logger\DeprecatedApiLogger;
use MyParcelNL\PrestaShop\Service\LabelOptionsService;
use MyParcelNL\PrestaShop\Timer;
use MyParcelNL\Pdk\Facade\DefaultLogger;
use Tools;

/**
 * @deprecated
 */
class Download
{
    /**
     * @param  array $labelIds
     *
     * @return null|string
     * @throws \Exception
     */
    public function downloadLabel(array $labelIds): ?string
    {
        DefaultLogger::debug('Downloading labels', ['labelIds' => $labelIds]);
        $timer = new Timer();

        $apiKey     = Configuration::get(Constant::API_KEY_CONFIGURATION_NAME);
        $collection = ConsignmentCollection::findMany($labelIds, $apiKey);

        if ($collection->isEmpty()) {
            DefaultLogger::warning('ConsignmentCollection is empty', ['labelIds' => $labelIds]);
            return null;
        }

        $positions = $this->getPositions();

        if ($this->isDownload()) {
            $response = $collection
                ->setLinkOfLabels($positions)
                ->getLinkOfLabels();
        } else {
            $response = $collection
                ->setPdfOfLabels($positions)
                ->getLabelPdf();
        }

        DefaultLogger::debug(
            'Finished downloading labels',
            [
                'format'    => $this->isDownload() ? 'link' : 'pdf',
                'timeTaken' => $timer->getTimeTaken(),
            ]
        );

        return $response;
    }

    /**
     * @return array|null
     */
    private function getPositions(): ?array
    {
        $service     = LabelOptionsService::getInstance();
        $labelFormat = Tools::getValue('labelFormat') ?: $service->getLabelFormat();

        if (LabelOptionsService::LABEL_FORMAT_A6 === $labelFormat) {
            return null;
        }

        $position = Tools::getValue('labelPosition');

        return $position ? explode(',', $position) : $service->getLabelPosition();
    }

    /**
     * @return bool
     */
    private function isDownload(): bool
    {
        $output = Tools::getValue('labelOutput')
            ?: LabelOptionsService::getInstance()
                ->getLabelOutput();

        return LabelOptionsService::LABEL_OUTPUT_DOWNLOAD === $output;
    }
}
