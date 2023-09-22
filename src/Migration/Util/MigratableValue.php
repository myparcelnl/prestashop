<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Util;

final class MigratableValue
{
    /**
     * @var \MyParcelNL\PrestaShop\Migration\Util\ValueModifierInterface
     */
    private $modifier;

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $target;

    /**
     * @param  string                                                       $source
     * @param  string                                                       $target
     * @param  \MyParcelNL\PrestaShop\Migration\Util\ValueModifierInterface $modifier
     */
    public function __construct(string $source, string $target, ValueModifierInterface $modifier)
    {
        $this->source   = $source;
        $this->target   = $target;
        $this->modifier = $modifier;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @param  mixed $value
     *
     * @return mixed
     */
    public function modify($value)
    {
        return $this->modifier->modify($value);
    }
}
