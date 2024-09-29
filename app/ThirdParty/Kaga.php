<?php 
namespace App\ThirdParty;

class Kaga
{
    private const a = 3;
    private const b = 5;
    private const c = 7;
    private const d = 3; // 0, 1, 2, 3
    private string $salt = "";
    private static ?self $instance = null;

    private static function getInstance(): static
    {
        if (is_null(static::$instance)) {
            static::$instance = new self();
        }
        return static::$instance;
    }

    public static function salt(string $salt): self
    {
        static::getInstance()->salt = $salt;
        return static::getInstance();
    }

    public static function encode(string $string, string $salt = ""): string
    {
        $instance = static::getInstance();
        if (!empty($salt)) {
            $instance->salt = $salt;
        }
        $instance->salt = self::base64_encode_url($instance->salt);
        $string = self::base64_encode_url($instance->salt . $string);
        $unpack = array_values(unpack('C*', $string));
        $after = [];
        foreach ($unpack as $index => $value) {
            $after[] = static::bit_en($index, $value);
        }
        $temp = json_encode($after);
        $base64 = self::base64_encode_url($temp);
        $split = str_split($base64, self::c);
        $return = '';
        foreach ($split as $value) {
            $return .= strrev($value);
        }
        return strrev(self::base64_encode_url($return . $instance->salt));
    }

    private static function base64_encode_url(string $string): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string));
    }

    private static function bit_en(int $index, int $value): int {
        if (self::d >= 1) {
            $value <<= $index % self::a;
        }
        if (self::d >= 2) {
            $value *= ($index + 1) % self::b ?: 1;
        }
        if (self::d >= 3) {
            $value ^= $index;
        }
        return $value;
    }

    public static function decode(string $string, string $salt = ""): string
    {
        $instance = static::getInstance();
        if (!empty($salt)) {
            $instance->salt = $salt;
        }
        $instance->salt = self::base64_encode_url($instance->salt);
        $encoded = self::base64_decode_url(strrev($string));
        if (!empty($instance->salt) && str_ends_with($encoded, $instance->salt)) {
            $encoded = substr($encoded, 0, -strlen($instance->salt));
        } else if (!empty($instance->salt)) {
            return '';
        }
        $split = str_split($encoded, self::c);
        $encoded = '';
        foreach ($split as $value) {
            $encoded .= strrev($value);
        }
        $string = self::base64_decode_url($encoded);
        $unpack = json_decode($string, true);
        $before = [];
        foreach ($unpack as $index => $value) {
            $before[] = static::bit_de($index, $value);
        }
        $return = self::base64_decode_url(pack('C*', ...$before));
        if (!empty($instance->salt) && str_starts_with($return, $instance->salt)) {
            $return = substr($return, strlen($instance->salt));
        } else if (!empty($instance->salt)) {
            return '';
        }
        return $return;
    }
    private static function base64_decode_url(string $string): string
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $string));
    }

    private static function bit_de(int $index, int $value): int {
        if (self::d >= 3) {
            $value ^= $index;
        }
        if (self::d >= 2) {
            $value /= ($index + 1) % self::b ?: 1;
        }
        if (self::d >= 1) {
            $value >>= $index % self::a;
        }
        return $value;
    }
}