<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Util;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface;
use MyParcelNL\Pdk\Types\Service\TriStateService;

final class ToTriStateValue extends TransformValue
{
    /**
     * @var string
     */
    private $type;

    public function __construct(string $type = TriStateService::TYPE_COERCED)
    {
        parent::__construct([$this, 'convert']);

        $this->type = $type;
    }

    /**
     * @param  mixed $value
     *
     * @return mixed
     */
    protected function convert($value)
    {
        /** @var TriStateServiceInterface $service */
        $service = Pdk::get(TriStateServiceInterface::class);

        switch ($this->type) {
            case TriStateService::TYPE_COERCED:
                return $service->coerce($value);

            case TriStateService::TYPE_STRICT:
                return $service->cast($value);

            case TriStateService::TYPE_STRING:
                return $service->coerceString($value);
        }

        return $value;
    }
}
