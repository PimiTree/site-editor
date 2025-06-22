<?php


require('../includes/database.php');
require('../includes/request.php');



vj_request_method_assert(['GET', 'POST']);

$view_params = [

];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SANITIZED_POST = vj_request_get_post_parameters([
        "name" => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        "email" => FILTER_SANITIZE_EMAIL,
        "password" => FILTER_UNSAFE_RAW,
        "confirm_password" => FILTER_UNSAFE_RAW,
    ]);

    $view_params['post'] = $_SANITIZED_POST;

    if (mb_strlen($_SANITIZED_POST['name']) < 3) {
        $view_params['error'] = 'Name to short';
    } else if ($_SANITIZED_POST['email'] === false) {
        $view_params['error'] = 'Input right email';
    } else if (mb_strlen($_SANITIZED_POST['password']) < 6)  {
        $view_params['error'] = 'Password to short';
    }  else if (preg_match('/[^a-zA-Z0-9=+_!@#$%^&*()_||~)(]/', $_SANITIZED_POST['password']))  {
        $view_params['error'] = 'Password has restricted chars';
    } else if ($_SANITIZED_POST['password'] !== $_SANITIZED_POST['confirm_password']) {
        $view_params['error'] = 'Password does not match';
    } else {
        $connection = vj_db_get_connection();

        $statement = $connection->prepare('SELECT * FROM users WHERE email = :email');
        $statement->bindValue(':email', $_POST['email']);
        $statement->execute();

        if ($statement->rowCount() > 0) {
            $view_params['error'] = 'Email not available';
        } else {
            $statement = $connection->prepare('INSERT INTO users (username, email, password_hash) 
                                                     VALUES (:username, :email, :password_hash)');

            $statement->bindValue(':username', $_SANITIZED_POST['name']);
            $statement->bindValue(':email', $_SANITIZED_POST['email']);
            $statement->bindValue(':password_hash', password_hash($_SANITIZED_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]));
            $result = $statement->execute();

            if ($result === true) {
                header('Location: /sign-in');
                exit();
            }
        }
    }
}


$header_params = [
    'title' => 'Registration',
    'stylesheets' => [
        '/assets/css/components/form.css',
        '/assets/css/registration.css'
    ]
];

require('../templates/common.php');
require('../templates/sign-up.php');
vj_render_header_template($header_params);
vj_render_sign_up($view_params);
vj_render_footer_template();



//
//$password = '123456';
//$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
//
//echo $hash . "<br>";
//echo mb_strlen($hash);
//echo password_verify("sdfrsd", $hash);
//
