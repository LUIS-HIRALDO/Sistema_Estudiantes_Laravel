<?php

// Función global response que no requiere namespace
if (!function_exists('response')) {
    function response() {
        return new class {
            public function json($data, $status = 200) {
                http_response_code($status);
                header('Content-Type: application/json');
                echo json_encode($data);
                exit;
            }
        };
    }
}
