<?php

namespace App;

class Language
{
    const EN   = 'en';
    const ES   = 'es';
    const PTBR = 'pt-br';

    public static function available()
    {
        return [
            static::EN   => __('languages.en'),
            static::ES   => __('languages.es'),
            static::PTBR => __('languages.ptbr'),
        ];
    }
}
