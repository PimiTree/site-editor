<?php
require('../includes/template.php');


function vj_render_home(string $header_text): void
{
    require('../templates/editor.php');
}


vj_render_header_template();
vj_render_home('Editor');
vj_render_footer_template();


