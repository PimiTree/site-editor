<?php
require('../includes/template.php');


function vj_render_home(string $header_text): void
{
    require('../templates/404.php');
}


vj_render_header_template();
vj_render_home('404');
vj_render_footer_template();


