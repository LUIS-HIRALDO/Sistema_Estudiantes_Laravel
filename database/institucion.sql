CREATE TABLE IF NOT EXISTS institucion (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    codigo VARCHAR(50),
    tanda VARCHAR(50),
    telefono VARCHAR(50),
    distrito VARCHAR(100),
    regional VARCHAR(100),
    provincia VARCHAR(100),
    municipio VARCHAR(100),
    direccion TEXT,
    director VARCHAR(150),
    logo_url VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO institucion (nombre, codigo, tanda, telefono, distrito, regional, provincia, municipio)
SELECT 'Centro Educativo Ejemplo', '00000', 'Jornada Escolar Extendida', '809-000-0000', '00-00', '00', 'Provincia', 'Municipio'
WHERE NOT EXISTS (SELECT * FROM institucion);
