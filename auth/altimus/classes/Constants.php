<?php

class Constants
{
    const URL_BASE_PROD  = 'https://api.altimus.com.br';
    const URL_BASE_LOCAL = 'http://localhost:8000';
    const SENHA_PADRAO   = 'aCY$so3v05i8';

    public static function getApiUrl(): string
    {
        global $CFG;
        $isLocal = isset($CFG->wwwroot) && strpos($CFG->wwwroot, 'localhost') !== false;
        return $isLocal ? self::URL_BASE_LOCAL : self::URL_BASE_PROD;
    }
}
