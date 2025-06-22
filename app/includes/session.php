<?php

declare(strict_types=1);

use JetBrains\PhpStorm\NoReturn;

function jv_get_new_secret_token(string $user_id, string $db_session_id): array
{
    $user_finger_print = [
        'id' => $user_id,
        'db_session_id' => $db_session_id,
        'exp' => time() + 2,
    ];

    $passPhrase = '1234';
    $method = 'aes-256-gcm';
    $ivLen = openssl_cipher_iv_length($method);
    $iv = openssl_random_pseudo_bytes($ivLen);
    $tag = '';
    $aad = json_encode([
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    ]);

    $token = openssl_encrypt(
        json_encode($user_finger_print),
        $method,
        $passPhrase,
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        $aad,
        16
    );

    return [
        'token' => $token,
        'tag' => $tag,
        'iv' => $iv
    ];
}

function vj_session_kill(): void
{
    session_unset();
    session_destroy();
    unset($_SESSION);
}

function vj_session_create(string $user_id, PDO $connection): bool {
    session_regenerate_id(true);

    $db_session_id =  vj_db_session_create($user_id, $connection);

    $credentials = jv_get_new_secret_token($user_id, $db_session_id);

    if ($credentials['token'] === false) {
        return false;
    } else {
        vj_db_set_session_tag($db_session_id, $credentials, $connection);

        $_SESSION['data'] = $credentials;
        $_SESSION['id'] = $db_session_id;
    }

    return true;
}

function vj_session_kill_on_validation(string $location): void {
    vj_session_kill();
    header("Location: /$location");
    exit();
}

function vj_session_validate(PDO $connection): void {

}
