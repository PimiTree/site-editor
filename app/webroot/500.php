<?php
require('../templates/common.php');
require('../templates/500.php');

$header_params = [
    'title' => '500'
];

$view_params = [
    "text" => '500'
];

vj_render_header_template($header_params);
vj_render_500($view_params);
vj_render_footer_template();


