<?php
/**
 * 2017-2019 DM Productions B.V.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@dmp.nl so we can send you a copy immediately.
 *
 * @author     Michael Dekker <info@mijnpresta.nl>
 * @copyright  2010-2019 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    return;
}

if (!function_exists('mypa_dot')) {
    function mypa_dot($item) {
        return new \MyParcelModule\Firstred\Dot($item);
    }
}

if (!function_exists('mypa_json_encode')) {
    function mypa_json_encode($input, $flags = null) {
        // Escape HTML entities and other risky chars by default
        if ($flags === null) {
            // Using native PHP 5.3+ flags
            $flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
        }

        $json = json_encode($input, $flags);

        // Simulate JSON_UNESCAPED_SLASHES
        $json = str_replace('\\/', '/', $json);

        return $json;
    }
}

if (!function_exists('mypa_stringify_url')) {
    function mypa_stringify_url($parsedUrl) {
        $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'].'://' : '';
        $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
        $port = isset($parsedUrl['port']) ? ':'.$parsedUrl['port'] : '';
        $user = isset($parsedUrl['user']) ? $parsedUrl['user'] : '';
        $pass = isset($parsedUrl['pass']) ? ':'.$parsedUrl['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $query = isset($parsedUrl['query']) ? '?'.$parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#'.$parsedUrl['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }
}

if (!function_exists('mypa_parse_url')) {
    function mypa_parse_url($urlString, $component = -1) {
        return call_user_func_array('parse_url', func_get_args());
    }
}
