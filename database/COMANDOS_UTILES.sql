#!/bin/bash
# Comandos útiles para gestionar la base de datos del Sistema de Notas

# ============================================================
# CONEXIÓN A LA BASE DE DATOS
# ============================================================

# Conectar a MySQL
# mysql -u root -p ""

# Seleccionar base de datos
# USE sistema_estudiantes;

# ============================================================
# CONSULTAS PARA VERIFICACIÓN
# ============================================================

# Ver todas las tablas
SHOW TABLES;

# Ver estructura de una tabla específica
DESCRIBE notas_academicas;
DESCRIBE notas_tecnicas;

# Contar registros por tabla
SELECT 'anios_escolares' as tabla, COUNT(*) as total FROM anios_escolares
UNION ALL
SELECT 'asignaturas', COUNT(*) FROM asignaturas
UNION ALL
SELECT 'competencias', COUNT(*) FROM competencias
UNION ALL
SELECT 'periodos', COUNT(*) FROM periodos
UNION ALL
SELECT 'notas_academicas', COUNT(*) FROM notas_academicas
UNION ALL
SELECT 'modulos_formativos', COUNT(*) FROM modulos_formativos
UNION ALL
SELECT 'resultados_aprendizaje', COUNT(*) FROM resultados_aprendizaje
UNION ALL
SELECT 'notas_tecnicas', COUNT(*) FROM notas_tecnicas;

# ============================================================
# CONSULTAS DE DATOS ACADÉMICOS
# ============================================================

# Obtener todas las asignaturas
SELECT id_asignatura, nombre, codigo, grado, creditos, estado FROM asignaturas;

# Obtener competencias de una asignatura
SELECT c.id_competencia, c.nombre, c.bloque, a.nombre as asignatura
FROM competencias c
JOIN asignaturas a ON c.id_asignatura = a.id_asignatura
ORDER BY a.nombre, c.bloque DESC;

# Obtener notas académicas de un estudiante (ID 1)
SELECT 
    na.id_nota,
    e.nombre as estudiante,
    a.nombre as asignatura,
    c.nombre as competencia,
    c.bloque,
    p.nombre as periodo,
    na.nota,
    na.rp as recuperacion
FROM notas_academicas na
JOIN estudiantes e ON na.id_estudiante = e.id
JOIN competencias c ON na.id_competencia = c.id_competencia
JOIN asignaturas a ON c.id_asignatura = a.id_asignatura
JOIN periodos p ON na.id_periodo = p.id_periodo
WHERE na.id_estudiante = 1
ORDER BY a.nombre, c.bloque DESC, p.numero;

# Obtener promedio académico por asignatura de un estudiante
SELECT 
    a.nombre as asignatura,
    ROUND(AVG(CASE WHEN c.bloque = '70' THEN na.nota END), 2) as promedio_70,
    ROUND(AVG(CASE WHEN c.bloque = '30' THEN na.nota END), 2) as promedio_30,
    ROUND(
        (AVG(CASE WHEN c.bloque = '70' THEN na.nota END) * 0.70) +
        (AVG(CASE WHEN c.bloque = '30' THEN na.nota END) * 0.30),
        2
    ) as promedio_final
FROM notas_academicas na
JOIN competencias c ON na.id_competencia = c.id_competencia
JOIN asignaturas a ON c.id_asignatura = a.id_asignatura
WHERE na.id_estudiante = 1 AND na.id_anio = 1
GROUP BY a.id_asignatura, a.nombre;

# ============================================================
# CONSULTAS DE DATOS TÉCNICOS
# ============================================================

# Obtener todos los módulos formativos
SELECT id_modulo, nombre, codigo, especialidad, grado, horas, creditos, estado
FROM modulos_formativos
ORDER BY nombre;

# Obtener RA de un módulo (MOD-01)
SELECT 
    id_ra,
    codigo_ra,
    descripcion,
    porcentaje,
    activo
FROM resultados_aprendizaje
WHERE id_modulo = (SELECT id_modulo FROM modulos_formativos WHERE codigo = 'MOD-01')
ORDER BY codigo_ra;

# Obtener notas técnicas de un estudiante (ID 6)
SELECT 
    nt.id_nota,
    e.nombre as estudiante,
    m.nombre as modulo,
    ra.codigo_ra,
    ra.descripcion,
    ra.porcentaje,
    nt.nota,
    nt.rp as recuperacion,
    ROUND((nt.nota * ra.porcentaje / 100), 2) as nota_ponderada
FROM notas_tecnicas nt
JOIN resultados_aprendizaje ra ON nt.id_ra = ra.id_ra
JOIN modulos_formativos m ON ra.id_modulo = m.id_modulo
JOIN estudiantes e ON nt.id_estudiante = e.id
WHERE nt.id_estudiante = 6 AND nt.id_anio = 1
ORDER BY m.nombre, ra.codigo_ra;

# Obtener nota final ponderada de un estudiante por módulo
SELECT 
    e.nombre,
    m.nombre as modulo,
    COUNT(ra.id_ra) as cantidad_ra,
    SUM(ra.porcentaje) as suma_porcentaje,
    ROUND(SUM((nt.nota * ra.porcentaje) / 100), 2) as nota_final,
    CASE 
        WHEN SUM((nt.nota * ra.porcentaje) / 100) >= 70 THEN 'APROBADO'
        ELSE 'REPROBADO'
    END as estado
FROM notas_tecnicas nt
JOIN resultados_aprendizaje ra ON nt.id_ra = ra.id_ra
JOIN modulos_formativos m ON ra.id_modulo = m.id_modulo
JOIN estudiantes e ON nt.id_estudiante = e.id
WHERE nt.id_estudiante = 6 AND nt.id_anio = 1 AND ra.activo = TRUE
GROUP BY m.id_modulo, m.nombre, e.nombre;

# ============================================================
# CONSULTAS DE ESTUDIANTES
# ============================================================

# Listar estudiantes por modalidad
SELECT 
    id,
    nombre,
    apellido,
    grado,
    modalidad,
    especialidad,
    estado
FROM estudiantes
ORDER BY modalidad, grado, nombre;

# Contar estudiantes por modalidad
SELECT modalidad, COUNT(*) as cantidad FROM estudiantes GROUP BY modalidad;

# Contar estudiantes por grado y modalidad
SELECT grado, modalidad, COUNT(*) as cantidad 
FROM estudiantes 
GROUP BY grado, modalidad 
ORDER BY grado, modalidad;

# ============================================================
# CONSULTAS DE VERIFICACIÓN DE INTEGRIDAD
# ============================================================

# Verificar que todos los RA tengan porcentaje correcto (suma = 100 para activos)
SELECT 
    m.id_modulo,
    m.nombre,
    COUNT(ra.id_ra) as total_ra,
    SUM(CASE WHEN ra.activo = TRUE THEN 1 ELSE 0 END) as ra_activos,
    SUM(CASE WHEN ra.activo = TRUE THEN ra.porcentaje ELSE 0 END) as suma_porcentaje
FROM modulos_formativos m
LEFT JOIN resultados_aprendizaje ra ON m.id_modulo = ra.id_modulo
GROUP BY m.id_modulo, m.nombre;

# Verificar estudiantes sin notas
SELECT e.id, e.nombre, e.apellido, e.modalidad
FROM estudiantes e
WHERE e.modalidad = 'ACADEMICA'
AND e.id NOT IN (SELECT DISTINCT id_estudiante FROM notas_academicas);

SELECT e.id, e.nombre, e.apellido, e.modalidad
FROM estudiantes e
WHERE e.modalidad = 'TECNICA'
AND e.id NOT IN (SELECT DISTINCT id_estudiante FROM notas_tecnicas);

# Verificar notas duplicadas (si existen)
SELECT id_estudiante, id_competencia, id_periodo, COUNT(*) 
FROM notas_academicas 
GROUP BY id_estudiante, id_competencia, id_periodo 
HAVING COUNT(*) > 1;

SELECT id_estudiante, id_ra, COUNT(*) 
FROM notas_tecnicas 
GROUP BY id_estudiante, id_ra 
HAVING COUNT(*) > 1;

# ============================================================
# INSERTAR DATOS DE PRUEBA ADICIONALES
# ============================================================

# Insertar una nota académica
INSERT INTO notas_academicas (id_estudiante, id_competencia, id_periodo, nota, id_anio)
VALUES (2, 1, 1, 85.50, 1);

# Insertar una nota técnica
INSERT INTO notas_tecnicas (id_estudiante, id_ra, nota, id_anio)
VALUES (6, 1, 80.00, 1);

# ============================================================
# ACTUALIZAR DATOS
# ============================================================

# Actualizar una nota académica
UPDATE notas_academicas 
SET nota = 88.50 
WHERE id_nota = 1;

# Actualizar una nota técnica
UPDATE notas_tecnicas 
SET nota = 82.00 
WHERE id_nota = 1;

# Cambiar recuperación
UPDATE notas_academicas 
SET rp = 75.00 
WHERE id_nota = 1;

# ============================================================
# ELIMINAR DATOS
# ============================================================

# Eliminar una nota académica (CUIDADO: verificar primero)
-- DELETE FROM notas_academicas WHERE id_nota = 1;

# Eliminar una nota técnica (CUIDADO: verificar primero)
-- DELETE FROM notas_tecnicas WHERE id_nota = 1;

# ============================================================
# CONSULTAS DE REPORTE
# ============================================================

# Reporte general de estudiantes y sus notas
SELECT 
    e.id,
    e.nombre,
    e.apellido,
    e.grado,
    e.modalidad,
    e.especialidad,
    (SELECT COUNT(*) FROM notas_academicas WHERE id_estudiante = e.id) as notas_academicas,
    (SELECT COUNT(*) FROM notas_tecnicas WHERE id_estudiante = e.id) as notas_tecnicas
FROM estudiantes e
ORDER BY e.modalidad, e.grado, e.nombre;

# Reporte de completitud de datos
SELECT 
    'Asignaturas' as elemento,
    (SELECT COUNT(*) FROM asignaturas) as total,
    (SELECT COUNT(DISTINCT id_asignatura) FROM competencias) as con_competencias,
    ROUND((SELECT COUNT(DISTINCT id_asignatura) FROM competencias) / 
          (SELECT COUNT(*) FROM asignaturas) * 100, 2) as porcentaje
UNION ALL
SELECT 
    'Módulos',
    (SELECT COUNT(*) FROM modulos_formativos),
    (SELECT COUNT(DISTINCT id_modulo) FROM resultados_aprendizaje),
    ROUND((SELECT COUNT(DISTINCT id_modulo) FROM resultados_aprendizaje) /
          (SELECT COUNT(*) FROM modulos_formativos) * 100, 2)
UNION ALL
SELECT 
    'RA Activos',
    (SELECT COUNT(*) FROM resultados_aprendizaje WHERE activo = TRUE),
    (SELECT COUNT(DISTINCT id_ra) FROM notas_tecnicas),
    ROUND((SELECT COUNT(DISTINCT id_ra) FROM notas_tecnicas) /
          (SELECT COUNT(*) FROM resultados_aprendizaje WHERE activo = TRUE) * 100, 2);

# ============================================================
# BACKUP Y RESTAURACIÓN
# ============================================================

# Hacer backup de la estructura
-- mysqldump -u root -p "" --no-data sistema_estudiantes > estructura_backup.sql

# Hacer backup de datos
-- mysqldump -u root -p "" --no-create-info sistema_estudiantes > datos_backup.sql

# Hacer backup completo
-- mysqldump -u root -p "" sistema_estudiantes > backup_completo.sql

# Restaurar desde backup
-- mysql -u root -p "" sistema_estudiantes < backup_completo.sql

# ============================================================
# OPTIMIZACIÓN
# ============================================================

# Analizar tablas para optimizar
ANALYZE TABLE notas_academicas;
ANALYZE TABLE notas_tecnicas;
ANALYZE TABLE competencias;
ANALYZE TABLE resultados_aprendizaje;

# Optimizar tablas
OPTIMIZE TABLE notas_academicas;
OPTIMIZE TABLE notas_tecnicas;
OPTIMIZE TABLE competencias;
OPTIMIZE TABLE resultados_aprendizaje;

# Ver estadísticas de índices
SELECT * FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = 'sistema_estudiantes' AND TABLE_NAME = 'notas_academicas';

# ============================================================
# FIN DE COMANDOS ÚTILES
# ============================================================
