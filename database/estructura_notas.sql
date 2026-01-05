-- ============================================================
-- SISTEMA DE NOTAS - ESTRUCTURA DE BASE DE DATOS
-- ============================================================

USE sistema_estudiantes;

-- 1️⃣ TABLAS COMUNES (AMBAS MODALIDADES)

-- Tabla: estudiantes (MODIFICADA)
ALTER TABLE estudiantes 
ADD COLUMN modalidad ENUM('ACADEMICA','TECNICA') DEFAULT 'ACADEMICA' AFTER seccion,
ADD COLUMN especialidad VARCHAR(100) AFTER modalidad;

-- Tabla: anios_escolares (NUEVA)
CREATE TABLE IF NOT EXISTS anios_escolares (
    id_anio INT PRIMARY KEY AUTO_INCREMENT,
    anio VARCHAR(20) NOT NULL UNIQUE,
    activo BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar año actual
INSERT INTO anios_escolares (anio, activo) VALUES ('2025-2026', TRUE)
ON DUPLICATE KEY UPDATE activo = TRUE;

-- 2️⃣ MODALIDAD ACADÉMICA

-- Tabla: asignaturas (NUEVA - similar a materias existentes)
CREATE TABLE IF NOT EXISTS asignaturas (
    id_asignatura INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    codigo VARCHAR(20),
    grado VARCHAR(20) NOT NULL,
    creditos INT,
    estado VARCHAR(20) DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: competencias (NUEVA)
CREATE TABLE IF NOT EXISTS competencias (
    id_competencia INT PRIMARY KEY AUTO_INCREMENT,
    id_asignatura INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    bloque ENUM('70','30') NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_asignatura) REFERENCES asignaturas(id_asignatura) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: periodos (NUEVA)
CREATE TABLE IF NOT EXISTS periodos (
    id_periodo INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(10) NOT NULL UNIQUE,
    numero INT
);

-- Insertar períodos estándar
INSERT INTO periodos (nombre, numero) VALUES 
('P1', 1), ('P2', 2), ('P3', 3), ('P4', 4)
ON DUPLICATE KEY UPDATE numero = VALUES(numero);

-- Tabla: notas_academicas (NUEVA)
CREATE TABLE IF NOT EXISTS notas_academicas (
    id_nota INT PRIMARY KEY AUTO_INCREMENT,
    id_estudiante INT NOT NULL,
    id_competencia INT NOT NULL,
    id_periodo INT NOT NULL,
    nota DECIMAL(5,2) NOT NULL,
    rp DECIMAL(5,2),
    id_anio INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id),
    FOREIGN KEY (id_competencia) REFERENCES competencias(id_competencia) ON DELETE CASCADE,
    FOREIGN KEY (id_periodo) REFERENCES periodos(id_periodo),
    FOREIGN KEY (id_anio) REFERENCES anios_escolares(id_anio),
    UNIQUE KEY unique_nota (id_estudiante, id_competencia, id_periodo, id_anio)
);

-- 3️⃣ MODALIDAD TÉCNICO PROFESIONAL

-- Tabla: modulos_formativos (NUEVA)
CREATE TABLE IF NOT EXISTS modulos_formativos (
    id_modulo INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    codigo VARCHAR(20),
    especialidad VARCHAR(100) NOT NULL,
    grado VARCHAR(20) NOT NULL,
    horas INT,
    creditos INT,
    estado VARCHAR(20) DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: resultados_aprendizaje (NUEVA)
CREATE TABLE IF NOT EXISTS resultados_aprendizaje (
    id_ra INT PRIMARY KEY AUTO_INCREMENT,
    id_modulo INT NOT NULL,
    codigo_ra VARCHAR(5) NOT NULL,
    descripcion TEXT,
    porcentaje DECIMAL(5,2) DEFAULT 0,
    activo BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_modulo) REFERENCES modulos_formativos(id_modulo) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_ra (id_modulo, codigo_ra)
);

-- Tabla: notas_tecnicas (NUEVA)
CREATE TABLE IF NOT EXISTS notas_tecnicas (
    id_nota INT PRIMARY KEY AUTO_INCREMENT,
    id_estudiante INT NOT NULL,
    id_ra INT NOT NULL,
    nota DECIMAL(5,2) NOT NULL,
    rp DECIMAL(5,2),
    id_anio INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id),
    FOREIGN KEY (id_ra) REFERENCES resultados_aprendizaje(id_ra) ON DELETE CASCADE,
    FOREIGN KEY (id_anio) REFERENCES anios_escolares(id_anio),
    UNIQUE KEY unique_nota (id_estudiante, id_ra, id_anio)
);

-- ============================================================
-- ÍNDICES PARA OPTIMIZAR BÚSQUEDAS
-- ============================================================

CREATE INDEX idx_notas_academicas_estudiante ON notas_academicas(id_estudiante);
CREATE INDEX idx_notas_academicas_periodo ON notas_academicas(id_periodo);
CREATE INDEX idx_notas_academicas_anio ON notas_academicas(id_anio);
CREATE INDEX idx_notas_tecnicas_estudiante ON notas_tecnicas(id_estudiante);
CREATE INDEX idx_notas_tecnicas_anio ON notas_tecnicas(id_anio);
CREATE INDEX idx_competencias_asignatura ON competencias(id_asignatura);
CREATE INDEX idx_ra_modulo ON resultados_aprendizaje(id_modulo);
CREATE INDEX idx_estudiantes_grado ON estudiantes(grado);
CREATE INDEX idx_estudiantes_modalidad ON estudiantes(modalidad);

-- ============================================================
-- FIN DEL SCRIPT
-- ============================================================
