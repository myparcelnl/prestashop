<?php

namespace MyParcelNL\PrestaShop\Migration\Util;

final class ToTriStateValue extends TransformValue
{
    public function __construct()
    {
        parent::__construct([$this, 'convert']);
    }

    /**
     * @param  mixed $value
     *
     * @return mixed
     */
    protected function convert($value)
    {
        $triStateService = Pdk::get(TriStateServiceInterface::class);

        return $triStateService->cast($value);
    }
}
