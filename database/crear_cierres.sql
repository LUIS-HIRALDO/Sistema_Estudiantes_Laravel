-- Tabla de cierres de asignaturas (si no existe)
CREATE TABLE IF NOT EXISTS cierres_asignaturas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    id_asignatura INT NOT NULL,
    id_periodo INT NOT NULL,
    id_anio INT NOT NULL,
    
    fecha_cierre DATETIME NOT NULL,
    usuario_cierre INT,
    
    bloqueado BOOLEAN DEFAULT TRUE,
    observaciones TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_cierre (id_asignatura, id_periodo, id_anio),
    
    INDEX idx_bloqueado (bloqueado),
    INDEX idx_fecha (fecha_cierre),
    INDEX idx_asignatura (id_asignatura),
    INDEX idx_periodo (id_periodo)
);

-- Tabla de cierres de módulos (si no existe)
CREATE TABLE IF NOT EXISTS cierre_modulos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    id_modulo INT NOT NULL,
    id_anio INT NOT NULL,
    
    fecha_cierre DATETIME NOT NULL,
    usuario_cierre INT,
    
    bloqueado BOOLEAN DEFAULT TRUE,
    observaciones TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_cierre (id_modulo, id_anio),
    
    INDEX idx_bloqueado (bloqueado),
    INDEX idx_fecha (fecha_cierre),
    INDEX idx_modulo (id_modulo)
);

-- Verificar que la tabla resultados_aprendizaje tiene los campos correctos
ALTER TABLE resultados_aprendizaje 
ADD COLUMN IF NOT EXISTS activo BOOLEAN DEFAULT FALSE AFTER porcentaje,
ADD COLUMN IF NOT EXISTS numero_ra INT AFTER codigo_ra;

-- Verificar que la tabla notas_tecnicas tiene check correcto
ALTER TABLE notas_tecnicas 
ADD CHECK (nota IS NULL OR (nota >= 0 AND nota <= 100)),
ADD CHECK (rp IS NULL OR (rp >= 0 AND rp <= 100));

-- Verificar que la tabla notas_academicas tiene check correcto
ALTER TABLE notas_academicas 
ADD CHECK (nota IS NULL OR (nota >= 0 AND nota <= 100)),
ADD CHECK (rp IS NULL OR (rp >= 0 AND rp <= 100));

-- Verificación final
SELECT '✅ Tablas de cierres creadas/verificadas' as estado;
SELECT COUNT(*) as total_cierres_asignaturas FROM cierres_asignaturas;
SELECT COUNT(*) as total_cierre_modulos FROM cierre_modulos;
