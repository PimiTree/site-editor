<?php

require('../config/config.php');
require('../includes/database.php');
require('../includes/session.php');
require('../includes/request.php');


vj_request_method_assert(['GET', 'POST']);


session_start();

$connection = vj_db_get_connection();
$session_validation = vj_session_validate($connection);



if (is_array($session_validation) && $session_validation['session'] === true) {
  header('Location: /');
  exit();
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
                    exit();
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
    'title' => 'Sign-in',
    'stylesheets' => [
        '/assets/css/components/form.css',
        '/assets/css/login.css'
    ]
];

require('../templates/common.php');
require('../templates/sign-in.php');
vj_render_header_template($header_params);
vj_render_sign_in($view_params);
vj_render_footer_template();
