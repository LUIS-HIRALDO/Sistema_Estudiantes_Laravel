<?php
$url = 'http://localhost/Sistema_Estudiantes_Laravel/public/index.php/institucion';
$data = [
    'nombre' => 'Centro de Prueba',
    'codigo' => '12345',
    'tanda' => 'Matutina',
    'telefono' => '809-555-5555',
    'distrito' => '10-01',
    'regional' => '10',
    'provincia' => 'Santo Domingo',
    'municipio' => 'Santo Domingo Este'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true
    ]
];

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "Response Headers:\n";
print_r($http_response_header);
echo "\nResponse Body:\n";
var_dump($result);
