<?php


function vj_request_method_assert(array $methods): void {
  if (!in_array($_SERVER['REQUEST_METHOD'], $methods)) {
    header('Location: /404');
    exit;
  }
}

function vj_request_get_post_parameters ($parameters): array {
    return filter_input_array(INPUT_POST, $parameters, true);
}