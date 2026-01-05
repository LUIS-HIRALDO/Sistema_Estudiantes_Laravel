USE sistema_estudiantes;

-- ============================================================
-- NUEVAS TABLAS PARA MODALIDAD TÉCNICO PROFESIONAL
-- ============================================================

-- 1. Tabla de Estudiantes Técnicos (Separada de la tabla general de estudiantes)
CREATE TABLE IF NOT EXISTS estudiantes_tecnicos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    matricula VARCHAR(20) UNIQUE,
    email VARCHAR(100),
    telefono VARCHAR(20),
    grado VARCHAR(20),
    seccion VARCHAR(20),
    especialidad VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabla de Módulos Formativos (Asegurando existencia)
CREATE TABLE IF NOT EXISTS modulos_formativos (
    id_modulo INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    codigo VARCHAR(20),
    especialidad VARCHAR(100),
    grado VARCHAR(20),
    horas INT,
    creditos INT,
    estado VARCHAR(20) DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Tabla para la Cantidad de Resultados de Aprendizaje (Configuración por Módulo)
-- Almacena cuántos RAs tiene configurado un módulo (1-10)
CREATE TABLE IF NOT EXISTS cantidad_ra_modulos (
    id_cantidad INT PRIMARY KEY AUTO_INCREMENT,
    id_modulo INT NOT NULL,
    cantidad INT NOT NULL, -- El número seleccionado en el dropdown (1-10)
    fecha_configuracion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_modulo) REFERENCES modulos_formativos(id_modulo) ON DELETE CASCADE
);

-- 4. Tabla para las Calificaciones de los RAs (Soporta las 3 oportunidades)
CREATE TABLE IF NOT EXISTS calificaciones_ra_tecnicas (
    id_calificacion INT PRIMARY KEY AUTO_INCREMENT,
    id_estudiante INT NOT NULL,
    id_modulo INT NOT NULL,
    numero_ra INT NOT NULL, -- Indica si es el RA 1, RA 2, etc.
    valor_porcentual DECIMAL(5,2), -- El valor del RA (ej. 100/5 = 20)
    nota_oportunidad_1 DECIMAL(5,2),
    nota_oportunidad_2 DECIMAL(5,2),
    nota_oportunidad_3 DECIMAL(5,2),
    nota_final_ra DECIMAL(5,2), -- La nota obtenida en la oportunidad aprobada
    estado_ra ENUM('APROBADO', 'REPROBADO') DEFAULT 'REPROBADO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_estudiante) REFERENCES estudiantes_tecnicos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_modulo) REFERENCES modulos_formativos(id_modulo) ON DELETE CASCADE
);
