<?php

class Constants
{
    public static function getUrlBase(): string {
        $url = getenv('ALTIMUS_URL_BASE');
        error_log('[Constants] ALTIMUS_URL_BASE = ' . ($url ?: 'NAO DEFINIDA'));
        if (!$url) {
            throw new Exception('Variável de ambiente ALTIMUS_URL_BASE não definida');
        }
        return $url;
    }

    public static function getSenhaPadrao(): string {
        $senha = getenv('ALTIMUS_SENHA_PADRAO');
        if (!$senha) {
            throw new Exception('Variável de ambiente ALTIMUS_SENHA_PADRAO não definida');
        }
        return $senha;
    }

    public function __construct()
    {

    }
}