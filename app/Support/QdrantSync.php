<?php

namespace App\Support;

class QdrantSync
{
    protected static bool $bypass = false;

    public static function bypass(): bool
    {
        return self::$bypass;
    }

    public static function setBypass(bool $value): void
    {
        self::$bypass = $value;
    }
}
