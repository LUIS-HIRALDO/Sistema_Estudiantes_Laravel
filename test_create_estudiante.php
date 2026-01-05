<?php
$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjEiLCJlbWFpbCI6ImFkbWluQGVzY3VlbGEuY29tIiwiaWF0IjoxNzY3Mzk1ODc1LCJleHAiOjE3NjgwMDA2NzV9.uQ1JKm6mEUPqoT72BH107ZNv_ytLBcQG-pTKdWhcDp4";

$data = [
    'nombre' => 'Carlos',
    'apellido' => 'García',
    'email' => 'carlos.garcia@escuela.com',
    'grado' => '1A',
    'estado' => 'activo'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/Sistema_Estudiantes_Laravel/public/index.php/estudiantes');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    "Authorization: Bearer $token"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status: $http_code\n";
echo "Response:\n";
echo $response;
?>
