<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service\Consignment;

use Configuration;
use Gett\MyparcelBE\Collection\ConsignmentCollection;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Logger\ApiLogger;
use Gett\MyparcelBE\Service\LabelOptionsService;
use Tools;

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
        ApiLogger::addLog(sprintf("Downloading labels %s", implode(', ', $labelIds)));
        $apiKey     = Configuration::get(Constant::API_KEY_CONFIGURATION_NAME);
        $collection = ConsignmentCollection::findMany($labelIds, $apiKey);

        if ($collection->isEmpty()) {
            return null;
        }

        $positions = $this->getPositions();

        if ($this->isDownload()) {
            return $collection
                ->setLinkOfLabels($positions)
                ->getLinkOfLabels();
        }

        return $collection
            ->setPdfOfLabels($positions)
            ->getLabelPdf();
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
