<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Util;

final class TransformValue implements ValueModifierInterface
{
    /**
     * @var callable-string|callable
     */
    private $transformer;

    /**
     * @param  callable-string|callable $transformer
     */
    public function __construct($transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @param  mixed $value
     *
     * @return mixed
     */
    public function modify($value)
    {
        return call_user_func($this->transformer, $value);
    }
}
