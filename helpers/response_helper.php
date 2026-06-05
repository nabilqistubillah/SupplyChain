<?php
// helpers/response_helper.php

if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: " . $url);
        exit;
    }
}

if (!function_exists('redirect_to_route')) {
    function redirect_to_route($module, $action = 'index', $query = []) {
        $queryString = http_build_query(array_merge([
            'module' => $module,
            'action' => $action
        ], $query));
        redirect(BASE_URL . 'index.php?' . $queryString);
    }
}

if (!function_exists('json_response')) {
    function json_response($data, $statusCode = 200) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}
