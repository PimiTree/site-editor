<?php
require('../templates/common.php');
require('../templates/404.php');

$header_params = [
    'title' => '404'
];

$view_params = [
    "text" => '404'
];

vj_render_header_template($header_params);
vj_render_404($view_params);
vj_render_footer_template();


