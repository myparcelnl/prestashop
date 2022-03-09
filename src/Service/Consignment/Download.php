<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service\Consignment;

use Configuration;
use Gett\MyparcelBE\Collection\ConsignmentCollection;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Logger\ApiLogger;
use Gett\MyparcelBE\Service\LabelOptionsService;
use Gett\MyparcelBE\Timer;
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
        ApiLogger::addLog(sprintf('Downloading labels %s', implode(', ', $labelIds)));
        $timer = new Timer();

        $apiKey     = Configuration::get(Constant::API_KEY_CONFIGURATION_NAME);
        $collection = ConsignmentCollection::findMany($labelIds, $apiKey);

        if ($collection->isEmpty()) {
            ApiLogger::addLog('Collection is empty', ApiLogger::WARNING);
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

        ApiLogger::addLog(
            sprintf(
                'Finished downloading labels as %s in %d ms',
                $this->isDownload() ? 'link' : 'PDF',
                $timer->getTimeTaken()
            )
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
