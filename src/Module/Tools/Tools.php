<?php

namespace MyParcelNL\PrestaShop\Module\Tools;

use Tools as ToolsPresta;

class Tools extends ToolsPresta
{
    /**
     * @param  string $url
     * @param  array  $params
     *
     * @return string
     */
    public static function appendQuery(string $url, array $params): string
    {
        $parsedUrl          = parse_url($url);
        $parsedUrl['query'] = $parsedUrl['query'] ?? '';

        parse_str($parsedUrl['query'], $query);

        if (PHP_VERSION_ID >= 50400) {
            $parsedUrl['query'] = http_build_query($query + $params, PHP_QUERY_RFC1738);
        } else {
            $parsedUrl['query'] = http_build_query($query + $params);
        }

        return self::stringifyUrl($parsedUrl);
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
     * Get the translated value of a string in the following priority:
     *  1. The value of the key in the current language
     *  2. The value of the key in English
     *  3. The first key that exists
     *
     * @param  array $translatable
     *
     * @return void
     */
    public static function getTranslatedString(array $translatable): string
    {
        $context = \Context::getContext();

        if ($context && isset($translatable[$context->language->id])) {
            return $translatable[$context->language->id];
        }

        return $translatable[1] ?? reset($translatable);
    }

    /**
     * @param  mixed $value
     *
     * @return int|null
     */
    public static function intOrNull($value): ?int
    {
        return $value ? (int) $value : null;
    }

    /**
     * Clean comma, spaces and dot signs from numbers
     *
     * @param  string|int|float $val
     *
     * @return string
     */
    public static function normalizeFloat($val): string
    {
        $input  = str_replace(' ', '', (string) $val);
        $number = str_replace(',', '.', $input);
        if (strpos($number, '.')) {
            $groups    = explode('.', $number);
            $lastGroup = array_pop($groups);
            $number    = implode('', $groups) . '.' . $lastGroup;
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
     * Support samesite cookie flag in both php 7.2 (current production) and php >= 7.3 (when we get there)
     * From: https://github.com/GoogleChromeLabs/samesite-examples/blob/master/php.md and
     * https://stackoverflow.com/a/46971326/2308553
     *
     * @see https://www.php.net/manual/en/function.setcookie.php
     *
     * @param  string $name
     * @param  string $value
     * @param  int    $expire
     * @param  string $path
     * @param  string $domain
     * @param  bool   $secure
     * @param  bool   $httponly
     * @param  string $sameSite
     *
     * @return void
     */
    public static function setCookieSameSite(
        string $name,
        string $value,
        int    $expire,
        string $path,
        string $domain,
        bool   $secure,
        bool   $httponly,
        string $sameSite = 'None'
    ): void {
        if (PHP_VERSION_ID < 70300) {
            setcookie($name, $value, $expire, $path . '; samesite=' . $sameSite, $domain, $secure, $httponly);
            return;
        }
        setcookie($name, $value, [
            'expires'  => $expire,
            'path'     => $path,
            'domain'   => $domain,
            'samesite' => $sameSite,
            'secure'   => $secure,
            'httponly' => $httponly,
        ]);
    }

    /**
     * @param  array $parsedUrl
     *
     * @return string
     */
    public static function stringifyUrl(array $parsedUrl): string
    {
        $scheme   = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host     = $parsedUrl['host'] ?? '';
        $port     = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        $user     = $parsedUrl['user'] ?? '';
        $pass     = isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass'] : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = $parsedUrl['path'] ?? '';
        $query    = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';

        return $scheme . $user . $pass . $host . $port . $path . $query . $fragment;
    }
}
