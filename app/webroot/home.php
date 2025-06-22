<?php

require('../includes/request.php');
require('../templates/common.php');
require('../templates/home.php');


vj_request_method_assert(["GET"]);
$header_params = [
    'title' => 'Home'
];

$view_params = [
    "header_text" => 'Home page 1'
];


echo 'test <br>';
echo $_SERVER['REQUEST_URI'];
echo '<br>';
echo 'test <br>';

vj_render_header_template($header_params);
vj_render_home($view_params);
vj_render_footer_template();