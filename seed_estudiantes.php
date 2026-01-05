<?php
$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjEiLCJlbWFpbCI6ImFkbWluQGVzY3VlbGEuY29tIiwiaWF0IjoxNzY3Mzk1ODc1LCJleHAiOjE3NjgwMDA2NzV9.uQ1JKm6mEUPqoT72BH107ZNv_ytLBcQG-pTKdWhcDp4";

$estudiantes = [
    [
        'nombre' => 'Carlos',
        'apellido' => 'García',
        'email' => 'carlos.garcia@escuela.com',
        'grado' => '1A',
        'estado' => 'activo'
    ],
    [
        'nombre' => 'María',
        'apellido' => 'López',
        'email' => 'maria.lopez@escuela.com',
        'grado' => '1A',
        'estado' => 'activo'
    ],
    [
        'nombre' => 'Juan',
        'apellido' => 'Rodríguez',
        'email' => 'juan.rodriguez@escuela.com',
        'grado' => '2B',
        'estado' => 'activo'
    ],
    [
        'nombre' => 'Ana',
        'apellido' => 'Martínez',
        'email' => 'ana.martinez@escuela.com',
        'grado' => '2B',
        'estado' => 'activo'
    ],
    [
        'nombre' => 'Pedro',
        'apellido' => 'Sánchez',
        'email' => 'pedro.sanchez@escuela.com',
        'grado' => '3C',
        'estado' => 'activo'
    ]
];

$count = 0;
foreach ($estudiantes as $est) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/Sistema_Estudiantes_Laravel/public/index.php/estudiantes');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($est));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        "Authorization: Bearer $token"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 201) {
        echo "✓ Creado: {$est['nombre']} {$est['apellido']}\n";
        $count++;
    } else {
        echo "✗ Error: {$est['nombre']} {$est['apellido']} - Status: $http_code\n";
        echo "Response: $response\n";
    }
}

echo "\n✅ Se crearon $count estudiantes exitosamente";
?>
