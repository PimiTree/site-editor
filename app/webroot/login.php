<?php

require('../includes/database.php');
require('../includes/session.php');
require('../includes/request.php');


vj_request_method_assert(['GET', 'POST']);


session_start();
//
//vj_session_kill();
//exit();

$connection = vj_db_get_connection();

if (isset($_SESSION['data'])) {

    $db_session = vj_db_get_session_by_id($_SESSION['id'], $connection);

    if ($db_session !== false) {

        $aad = json_encode([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        ]);

        $method = 'aes-256-gcm';
        $passPhrase = '1234';
        $php_session_data = openssl_decrypt(
            $_SESSION['data']['token'],
            $method,
            $passPhrase,
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
            vj_session_kill_on_validation('login');
        }

        $user = vj_db_get_user_by_id($db_session['user_id'], $connection);

        if ($user === false) {
            // user not exists
            vj_session_kill_on_validation('login');
        }

        if (!$is_session_exp_valid) {
            // session refresh
            $result = vj_session_create($user['id'], $connection);

            if ($result === false) {
                $view_params['error'] = "Uups internal server error";
            } else {
                header('Location: /500');
            }

        }


//    header('location: /');

        exit();
    }
}


$view_params = [

];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SANITIZED_POST = vj_request_get_post_parameters([
        'email' => FILTER_VALIDATE_EMAIL,
        'password' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
    ]);

    $view_params['email'] = $_SANITIZED_POST['email'];


    if ($_SANITIZED_POST['email'] === false) {
        $view_params["error"] = 'Please enter a valid email address';
    } else if (mb_strlen($_SANITIZED_POST['password']) < 6) {
        $view_params["error"] = 'Password to short';
    } else {

        $user_statement = vj_db_get_user_by_email($_SANITIZED_POST['email'], $connection);

        if ($user_statement->rowCount() > 0) {
            $user = $user_statement->fetch(PDO::FETCH_ASSOC);

            $result = password_verify($_SANITIZED_POST['password'], $user['password_hash']);

            if ($result === true) {

                $result = vj_session_create($user['id'], $connection);

                if ($result === false) {
                    $view_params['error'] = "Uups internal server error";
                } else {
                    header('Location: /');
                }
            } else {
                $view_params['email'] = $user['email'];
                $view_params["error"] = 'Not verified';
            }
        } else {
            $view_params["error"] = 'Not verified';
        }
    }
}

$header_params = [
    'title' => 'Login',
    'stylesheets' => [
        '/assets/css/components/form.css',
        '/assets/css/login.css'
    ]
];

require('../templates/common.php');
require('../templates/login.php');
vj_render_header_template($header_params);
vj_render_login($view_params);
vj_render_footer_template();
