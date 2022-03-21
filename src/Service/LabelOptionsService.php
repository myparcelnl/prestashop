<?php

declare(strict_types=1);

namespace Gett\MyparcelBE\Service;

use Configuration;
use Gett\MyparcelBE\Constant;
use Gett\MyparcelBE\Service\Concern\HasInstance;

class LabelOptionsService
{
    use HasInstance;

    public const LABEL_FORMAT_A4       = 'a4';
    public const LABEL_FORMAT_A6       = 'a6';
    public const LABEL_OUTPUT_DOWNLOAD = 'download';
    public const LABEL_OUTPUT_OPEN     = 'open';

    /**
     * @return string
     */
    public function getLabelFormat(): string
    {
        return (string) Configuration::get(Constant::LABEL_SIZE_CONFIGURATION_NAME);
    }

    /**
     * @return string
     */
    public function getLabelOutput(): string
    {
        return 'true' === Configuration::get(Constant::LABEL_OPEN_DOWNLOAD_CONFIGURATION_NAME)
            ? self::LABEL_OUTPUT_OPEN
            : self::LABEL_OUTPUT_DOWNLOAD;
    }

    /**
     * @return string[]
     */
    public function getLabelPosition(): array
    {
        $startPosition  = (int) Configuration::get(Constant::LABEL_POSITION_CONFIGURATION_NAME);
        $labelPositions = [];

        for ($i = $startPosition; $i <= 4; $i++) {
            $labelPositions[] = (string) $i;
        }

        return $labelPositions;
    }
}
