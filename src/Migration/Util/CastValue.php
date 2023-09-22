<?php

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Migration\Util;

use DateTimeImmutable;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface;

final class CastValue implements ValueModifierInterface
{
    public const CAST_ARRAY     = 'array';
    public const CAST_BOOL      = 'bool';
    public const CAST_CENTS     = 'cents';
    public const CAST_DATE      = 'date';
    public const CAST_FLOAT     = 'float';
    public const CAST_INT       = 'int';
    public const CAST_STRING    = 'string';
    public const CAST_TRI_STATE = 'tristate';

    /**
     * @var string
     */
    private $castType;

    /**
     * @var \MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface
     */
    private $currencyService;

    /**
     * @var bool
     */
    private $optional;

    /**
     * @var \MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface
     */
    private $triStateService;

    public function __construct(string $castType, bool $optional = false)
    {
        $this->castType        = $castType;
        $this->currencyService = Pdk::get(CurrencyServiceInterface::class);
        $this->triStateService = Pdk::get(TriStateServiceInterface::class);
        $this->optional        = $optional;
    }

    /**
     * @param  mixed $value
     *
     * @return mixed
     * @throws \Exception
     */
    public function modify($value)
    {
        return $this->castValue($this->castType, $value);
    }

    /**
     * @param  string $cast
     * @param  mixed  $value
     *
     * @return mixed
     * @throws \Exception
     */
    protected function castValue(string $cast, $value)
    {
        if ($this->optional && ! $value) {
            return null;
        }

        switch ($cast) {
            case self::CAST_BOOL:
                return (bool) $value;

            case self::CAST_INT:
                return (int) $value;

            case self::CAST_STRING:
                return (string) $value;

            case self::CAST_FLOAT:
                return (float) $value;

            case self::CAST_ARRAY:
                return (array) $value;

            case self::CAST_DATE:
                return new DateTimeImmutable($value);

            case self::CAST_CENTS:
                return $this->currencyService->convertToCents($this->castValue(self::CAST_FLOAT, $value));

            case self::CAST_TRI_STATE:
                return $this->triStateService->coerce($value);

            default:
                return $value;
        }
    }
}
