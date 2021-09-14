<?php

namespace Gett\MyparcelBE\Module\Tools;

use Tools as ToolsPresta;

class Tools extends ToolsPresta
{
    /**
     * @param mixed $value
     *
     * @return int|null
     */
    public static function intOrNull($value): ?int
    {
        return $value ? (int) $value : null;
    }

    /**
     * Clean comma, spaces and dot signs from numbers
     * @param string|int|float $val
     * @return string
     */
    public static function normalizeFloat($val): string
    {
        $input = str_replace(' ', '', (string) $val);
        $number = str_replace(',', '.', $input);
        if (strpos($number, '.')) {
            $groups = explode('.', $number);
            $lastGroup = array_pop($groups);
            $number = implode('', $groups) . '.' . $lastGroup;
        }

        return $number;
    }

    /**
     * @param  null|object $object
     *
     * @return array
     */
    public static function objectToArray(?object $object): array
    {
        return json_decode(json_encode($object ?? []), true);
    }

    /**
     * @param  array $array
     *
     * @return null|object
     */
    public static function arrayToObject(array $array): ?object
    {
        if (empty($array)) {
            return null;
        }

        return json_decode(json_encode($array), false);
    }

    /**
     * Support samesite cookie flag in both php 7.2 (current production) and php >= 7.3 (when we get there)
     * From: https://github.com/GoogleChromeLabs/samesite-examples/blob/master/php.md and https://stackoverflow.com/a/46971326/2308553
     *
     * @see https://www.php.net/manual/en/function.setcookie.php
     *
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @param string $sameSite
     * @return void
     */
    public static function setCookieSameSite(
        string $name,
        string $value,
        int $expire,
        string $path,
        string $domain,
        bool $secure,
        bool $httponly,
        string $sameSite = 'None'
    ): void {
        if (PHP_VERSION_ID < 70300) {
            setcookie($name, $value, $expire, $path . '; samesite=' . $sameSite, $domain, $secure, $httponly);
            return;
        }
        setcookie($name, $value, [
            'expires' => $expire,
            'path' => $path,
            'domain' => $domain,
            'samesite' => $sameSite,
            'secure' => $secure,
            'httponly' => $httponly,
        ]);
    }
}
