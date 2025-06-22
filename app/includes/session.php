<?php

declare(strict_types=1);



function jv_get_new_secret_token(string $user_id, string $db_session_id): array
{
    $user_finger_print = [
        'id' => $user_id,
        'db_session_id' => $db_session_id,
        'exp' => time() + 2,
    ];

    $iv = openssl_random_pseudo_bytes(SESSION_ENCRYPTION_IV_LEN);
    $tag = '';
    $aad = json_encode([
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    ]);

    $token = openssl_encrypt(
        json_encode($user_finger_print),
        SESSION_ENCRYPTION_METHOD,
        SESSION_ENCRYPTION_SECRET,
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

function vj_session_create(string $user_id, PDO $connection): bool
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    } else {
        session_start();
        session_regenerate_id();
    }

    $db_session_id = vj_db_session_create($user_id, $connection);

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

function vj_session_validate(PDO $connection): array|false
{
    if (!isset($_SESSION['data'])) {
        // session isn't set
        return false;
    };

    $db_session = vj_db_get_session_by_id($_SESSION['id'], $connection);

    if ($db_session === false) {
        // db session not exists
        unset($_SESSION['data']);
        return false;
    }

    $aad = json_encode([
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    ]);

    $php_session_data = openssl_decrypt(
        $_SESSION['data']['token'],
        SESSION_ENCRYPTION_METHOD,
        SESSION_ENCRYPTION_SECRET,
        OPENSSL_RAW_DATA,
        hex2bin($db_session['iv']),
        hex2bin($db_session['tag']),
        $aad
    );

    $user_finger_print = json_decode($php_session_data, true);

    $is_ip_valid = $db_session['user_ip'] === $_SERVER['REMOTE_ADDR'];
    $is_user_agent_valid = $db_session['user_agent'] === $_SERVER['HTTP_USER_AGENT'];
    $is_user_id_valid = $db_session['user_id'] === $user_finger_print['id'];
    $is_session_id_valid = $db_session['id'] === $user_finger_print['db_session_id'];
    $is_session_exp_valid = time() <= $user_finger_print['exp'];

    if (!$is_ip_valid
        || !$is_user_agent_valid
        || !$is_user_id_valid
        || !$is_session_id_valid
    ) {
        // Invalid session
        vj_session_kill();
        vj_db_delete_session_by_id($db_session['id'], $connection);
        return [
            'session' => false,
        ];
    }

    $user = vj_db_get_user_by_id($db_session['user_id'], $connection);

    if ($user === false) {
        // user not exists
        vj_session_kill();
        vj_db_delete_session_by_id($db_session['id'], $connection);
        return [
            'session' => false,
        ];
    }

    if ($is_session_exp_valid !== true) {
        vj_session_create($user['id'], $connection);
        vj_db_delete_session_by_id($db_session['id'], $connection);
    }

    return [
        'user' => $user,
        'session' => true,
    ];
}

function vj_session_kill(): void
{
    session_unset();
    session_destroy();
    unset($_SESSION);
}
