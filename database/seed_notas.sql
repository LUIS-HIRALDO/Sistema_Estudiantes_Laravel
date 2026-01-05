-- ============================================================
-- SCRIPT DE SEEDING - DATOS DE EJEMPLO
-- ============================================================

USE sistema_estudiantes;

-- ============================================================
-- MODALIDAD ACADÉMICA - ASIGNATURAS Y COMPETENCIAS
-- ============================================================

-- Insertar asignaturas
INSERT INTO asignaturas (nombre, codigo, grado, creditos, estado) VALUES
('Lengua Española', 'ESP-101', '5to', 4, 'activo'),
('Matemática', 'MAT-101', '5to', 5, 'activo'),
('Ciencias Naturales', 'CIN-101', '5to', 4, 'activo'),
('Informática', 'INF-101', '5to', 3, 'activo'),
('Lengua Inglesa', 'ING-101', '5to', 3, 'activo');

-- Insertar competencias para Lengua Española (bloque 70%)
INSERT INTO competencias (id_asignatura, nombre, bloque, activo) VALUES
((SELECT id_asignatura FROM asignaturas WHERE codigo = 'ESP-101'), 'Comunicación oral y escrita', '70', TRUE),
((SELECT id_asignatura FROM asignaturas WHERE codigo = 'ESP-101'), 'Argumentación y análisis', '70', TRUE),
((SELECT id_asignatura FROM asignaturas WHERE codigo = 'ESP-101'), 'Valores y convivencia', '30', TRUE);

-- Insertar competencias para Matemática (bloque 70%)
INSERT INTO competencias (id_asignatura, nombre, bloque, activo) VALUES
((SELECT id_asignatura FROM asignaturas WHERE codigo = 'MAT-101'), 'Resolución de problemas', '70', TRUE),
((SELECT id_asignatura FROM asignaturas WHERE codigo = 'MAT-101'), 'Razonamiento lógico', '70', TRUE),
((SELECT id_asignatura FROM asignaturas WHERE codigo = 'MAT-101'), 'Disposición al aprendizaje', '30', TRUE);

-- Insertar competencias para Ciencias Naturales (bloque 70%)
INSERT INTO competencias (id_asignatura, nombre, bloque, activo) VALUES
((SELECT id_asignatura FROM asignaturas WHERE codigo = 'CIN-101'), 'Investigación científica', '70', TRUE),
((SELECT id_asignatura FROM asignaturas WHERE codigo = 'CIN-101'), 'Análisis de fenómenos', '70', TRUE),
((SELECT id_asignatura FROM asignaturas WHERE codigo = 'CIN-101'), 'Responsabilidad ambiental', '30', TRUE);

-- ============================================================
-- MODALIDAD TÉCNICA - MÓDULOS Y RESULTADOS DE APRENDIZAJE
-- ============================================================

-- Insertar módulos formativos
INSERT INTO modulos_formativos (nombre, codigo, especialidad, grado, horas, creditos, estado) VALUES
('Análisis y Diseño de Reportes', 'MOD-01', 'Informática', '5to', 120, 10, 'activo'),
('Programación Web', 'MOD-02', 'Informática', '5to', 150, 12, 'activo'),
('Gestión de Bases de Datos', 'MOD-03', 'Informática', '5to', 100, 8, 'activo'),
('Desarrollo de Aplicaciones', 'MOD-04', 'Informática', '5to', 140, 11, 'activo');

-- Insertar RA para "Análisis y Diseño de Reportes"
INSERT INTO resultados_aprendizaje (id_modulo, codigo_ra, descripcion, porcentaje, activo) VALUES
((SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-01'), 'RA1', 'Identifica requerimientos del cliente', 30, TRUE),
((SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-01'), 'RA2', 'Diseña reportes profesionales', 40, TRUE),
((SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-01'), 'RA3', 'Implementa reportes en herramientas', 30, TRUE),
((SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-01'), 'RA4', 'Sin asignar', 0, FALSE),
((SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-01'), 'RA5', 'Sin asignar', 0, FALSE);

-- Insertar RA para "Programación Web"
INSERT INTO resultados_aprendizaje (id_modulo, codigo_ra, descripcion, porcentaje, activo) VALUES
((SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-02'), 'RA1', 'Estructura documentos HTML/CSS', 25, TRUE),
((SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-02'), 'RA2', 'Programa en JavaScript', 35, TRUE),
((SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-02'), 'RA3', 'Integra bases de datos', 25, TRUE),
((SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-02'), 'RA4', 'Asegura aplicaciones web', 15, TRUE),
((SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-02'), 'RA5', 'Sin asignar', 0, FALSE);

-- Insertar RA para "Gestión de Bases de Datos"
INSERT INTO resultados_aprendizaje (id_modulo, codigo_ra, descripcion, porcentaje, activo) VALUES
((SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-03'), 'RA1', 'Diseña esquemas de BD', 40, TRUE),
((SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-03'), 'RA2', 'Administra sistemas de BD', 30, TRUE),
((SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-03'), 'RA3', 'Optimiza consultas SQL', 30, TRUE),
((SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-03'), 'RA4', 'Sin asignar', 0, FALSE);

-- ============================================================
-- ACTUALIZAR ESTUDIANTES CON MODALIDAD
-- ============================================================

-- Asignar modalidad ACADEMICA a estudiantes existentes
UPDATE estudiantes 
SET modalidad = 'ACADEMICA', especialidad = NULL 
WHERE id BETWEEN 1 AND 5;

-- Asignar modalidad TECNICA con especialidad Informática
UPDATE estudiantes 
SET modalidad = 'TECNICA', especialidad = 'Informática' 
WHERE id BETWEEN 6 AND 10;

-- ============================================================
-- INSERTAR NOTAS DE EJEMPLO - MODALIDAD ACADÉMICA
-- ============================================================

-- Notas para Estudiante 1 (id=1) - Lengua Española, Período 1
INSERT INTO notas_academicas (id_estudiante, id_competencia, id_periodo, nota, rp, id_anio) VALUES
(1, (SELECT id_competencia FROM competencias WHERE nombre = 'Comunicación oral y escrita' LIMIT 1), 1, 85.00, NULL, 1),
(1, (SELECT id_competencia FROM competencias WHERE nombre = 'Argumentación y análisis' LIMIT 1), 1, 80.00, NULL, 1),
(1, (SELECT id_competencia FROM competencias WHERE nombre = 'Valores y convivencia' LIMIT 1), 1, 90.00, NULL, 1);

-- Notas para Estudiante 1 - Período 2
INSERT INTO notas_academicas (id_estudiante, id_competencia, id_periodo, nota, rp, id_anio) VALUES
(1, (SELECT id_competencia FROM competencias WHERE nombre = 'Comunicación oral y escrita' LIMIT 1), 2, 88.00, NULL, 1),
(1, (SELECT id_competencia FROM competencias WHERE nombre = 'Argumentación y análisis' LIMIT 1), 2, 82.00, NULL, 1),
(1, (SELECT id_competencia FROM competencias WHERE nombre = 'Valores y convivencia' LIMIT 1), 2, 92.00, NULL, 1);

-- Notas para Estudiante 2 (id=2) - Matemática
INSERT INTO notas_academicas (id_estudiante, id_competencia, id_periodo, nota, rp, id_anio) VALUES
(2, (SELECT id_competencia FROM competencias WHERE nombre = 'Resolución de problemas' LIMIT 1), 1, 78.00, NULL, 1),
(2, (SELECT id_competencia FROM competencias WHERE nombre = 'Razonamiento lógico' LIMIT 1), 1, 75.00, NULL, 1),
(2, (SELECT id_competencia FROM competencias WHERE nombre = 'Disposición al aprendizaje' LIMIT 1), 1, 88.00, NULL, 1);

-- ============================================================
-- INSERTAR NOTAS DE EJEMPLO - MODALIDAD TÉCNICA
-- ============================================================

-- Notas para Estudiante 6 - Módulo "Análisis y Diseño de Reportes"
INSERT INTO notas_tecnicas (id_estudiante, id_ra, nota, rp, id_anio) VALUES
(6, (SELECT id_ra FROM resultados_aprendizaje WHERE codigo_ra = 'RA1' AND id_modulo = (SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-01') LIMIT 1), 80.00, NULL, 1),
(6, (SELECT id_ra FROM resultados_aprendizaje WHERE codigo_ra = 'RA2' AND id_modulo = (SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-01') LIMIT 1), 85.00, NULL, 1),
(6, (SELECT id_ra FROM resultados_aprendizaje WHERE codigo_ra = 'RA3' AND id_modulo = (SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-01') LIMIT 1), 90.00, NULL, 1);

-- Notas para Estudiante 7 - Módulo "Programación Web"
INSERT INTO notas_tecnicas (id_estudiante, id_ra, nota, rp, id_anio) VALUES
(7, (SELECT id_ra FROM resultados_aprendizaje WHERE codigo_ra = 'RA1' AND id_modulo = (SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-02') LIMIT 1), 82.00, NULL, 1),
(7, (SELECT id_ra FROM resultados_aprendizaje WHERE codigo_ra = 'RA2' AND id_modulo = (SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-02') LIMIT 1), 88.00, NULL, 1),
(7, (SELECT id_ra FROM resultados_aprendizaje WHERE codigo_ra = 'RA3' AND id_modulo = (SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-02') LIMIT 1), 86.00, NULL, 1),
(7, (SELECT id_ra FROM resultados_aprendizaje WHERE codigo_ra = 'RA4' AND id_modulo = (SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-02') LIMIT 1), 84.00, NULL, 1);

-- ============================================================
-- CONSULTAS DE VERIFICACIÓN
-- ============================================================

-- Verificar asignaturas insertadas
SELECT COUNT(*) as total_asignaturas FROM asignaturas;

-- Verificar competencias insertadas
SELECT COUNT(*) as total_competencias FROM competencias;

-- Verificar módulos insertados
SELECT COUNT(*) as total_modulos FROM modulos_formativos;

-- Verificar RA insertados
SELECT COUNT(*) as total_ra FROM resultados_aprendizaje;

-- Verificar notas académicas
SELECT COUNT(*) as total_notas_academicas FROM notas_academicas;

-- Verificar notas técnicas
SELECT COUNT(*) as total_notas_tecnicas FROM notas_tecnicas;

-- Mostrar estudiantes por modalidad
SELECT modalidad, COUNT(*) as cantidad FROM estudiantes GROUP BY modalidad;

-- ============================================================
-- FIN DEL SCRIPT DE SEEDING
-- ============================================================
