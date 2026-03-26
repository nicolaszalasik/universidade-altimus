<?php
require_once 'Constants.php';

class Authentication
{
    public function login($user, $pass)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => Constants::getUrlBase() . '/api/login/universidade',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(['email' => $user, 'senha' => $pass]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ));

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpcode != 200) {
            throw new Exception('Erro ao realizar autenticação no Altimus');
        }

        $retorno = json_decode($response);

        if (!isset($retorno->status) || $retorno->status != 200) {
            return false;
        }

        if (!isset($retorno->info)) {
            throw new Exception('Nenhum registro encontrado');
        }

        return $retorno->info;
    }

    public function validateToken($email, $token)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => Constants::getUrlBase() . '/api/login/universidade/valida-token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(['email' => $email]),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
        ));

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpcode != 200) {
            error_log('[Authentication] validateToken httpcode=' . $httpcode . ' response=' . $response);
            throw new Exception('Não foi possível autenticar o usuário');
        }

        $retorno = json_decode($response);

        if (!isset($retorno->status) || $retorno->status != 200) {
            return false;
        }

        if (!isset($retorno->info)) {
            throw new Exception('Nenhum registro encontrado');
        }

        return $retorno->info;
    }
}
