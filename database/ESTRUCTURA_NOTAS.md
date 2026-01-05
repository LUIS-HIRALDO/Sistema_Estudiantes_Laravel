# 📊 ESTRUCTURA DE BASE DE DATOS - SISTEMA DE NOTAS

## ✅ Estado: COMPLETAMENTE IMPLEMENTADO

---

## 1️⃣ TABLAS COMUNES (AMBAS MODALIDADES)

### 📘 estudiantes
```sql
CREATE TABLE estudiantes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(255),
    grado VARCHAR(10),
    seccion VARCHAR(10),
    modalidad ENUM('ACADEMICA','TECNICA') DEFAULT 'ACADEMICA',  -- ✅ NUEVO
    especialidad VARCHAR(100),  -- ✅ NUEVO
    matricula VARCHAR(100) UNIQUE,
    usuario_id INT UNIQUE,
    estado VARCHAR(50) DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Cambios realizados:**
- ✅ Agregado campo `modalidad` (ACADEMICA o TECNICA)
- ✅ Agregado campo `especialidad` para estudiantes técnicos
- ✅ Índices creados para `grado` y `modalidad`

---

### 📘 usuarios
```sql
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100),
    rol_id INT,
    estado VARCHAR(50) DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

### 📘 anios_escolares
```sql
CREATE TABLE anios_escolares (
    id_anio INT PRIMARY KEY AUTO_INCREMENT,
    anio VARCHAR(20) NOT NULL UNIQUE,  -- Ej: "2025-2026"
    activo BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Datos iniciales insertados:**
- 📅 2025-2026 (ACTIVO)

---

## 2️⃣ MODALIDAD ACADÉMICA

### 📘 asignaturas
```sql
CREATE TABLE asignaturas (
    id_asignatura INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    codigo VARCHAR(20),
    grado VARCHAR(20) NOT NULL,
    creditos INT,
    estado VARCHAR(20) DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Relacionada con:**
- competencias (1:N)

---

### 📘 competencias
```sql
CREATE TABLE competencias (
    id_competencia INT PRIMARY KEY AUTO_INCREMENT,
    id_asignatura INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    bloque ENUM('70','30') NOT NULL,  -- Bloque de evaluación
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_asignatura) REFERENCES asignaturas(id_asignatura) ON DELETE CASCADE
);
```

**Explicación:**
- `bloque '70'` = Competencia del bloque teórico (70% de la nota)
- `bloque '30'` = Competencia del bloque de valores/convivencia (30% de la nota)

---

### 📘 periodos
```sql
CREATE TABLE periodos (
    id_periodo INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(10) NOT NULL UNIQUE,
    numero INT
);
```

**Datos iniciales:**
- P1 (período 1)
- P2 (período 2)
- P3 (período 3)
- P4 (período 4)

---

### 📘 notas_academicas
```sql
CREATE TABLE notas_academicas (
    id_nota INT PRIMARY KEY AUTO_INCREMENT,
    id_estudiante INT NOT NULL,
    id_competencia INT NOT NULL,
    id_periodo INT NOT NULL,
    nota DECIMAL(5,2) NOT NULL,
    rp DECIMAL(5,2),  -- Recuperación
    id_anio INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id),
    FOREIGN KEY (id_competencia) REFERENCES competencias(id_competencia) ON DELETE CASCADE,
    FOREIGN KEY (id_periodo) REFERENCES periodos(id_periodo),
    FOREIGN KEY (id_anio) REFERENCES anios_escolares(id_anio),
    UNIQUE KEY unique_nota (id_estudiante, id_competencia, id_periodo, id_anio)
);
```

**Índices creados:**
- `idx_notas_academicas_estudiante`
- `idx_notas_academicas_periodo`
- `idx_notas_academicas_anio`

---

## 3️⃣ MODALIDAD TÉCNICO PROFESIONAL

### 📘 modulos_formativos
```sql
CREATE TABLE modulos_formativos (
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
```

**Ejemplos:**
- Análisis y Diseño de Reportes (5to Técnico)
- Programación Web (5to Técnico)
- Gestión de Bases de Datos (5to Técnico)

---

### 📘 resultados_aprendizaje (RA)
```sql
CREATE TABLE resultados_aprendizaje (
    id_ra INT PRIMARY KEY AUTO_INCREMENT,
    id_modulo INT NOT NULL,
    codigo_ra VARCHAR(5) NOT NULL,  -- RA1, RA2, ... RA10
    descripcion TEXT,
    porcentaje DECIMAL(5,2) DEFAULT 0,  -- % de ponderación (0-100)
    activo BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_modulo) REFERENCES modulos_formativos(id_modulo) ON DELETE CASCADE,
    UNIQUE KEY unique_ra (id_modulo, codigo_ra)
);
```

**Ejemplos de RA:**
- RA1: Identifica requerimientos (30%)
- RA2: Diseña reportes (40%)
- RA3: Implementa reportes (30%)
- RA4-RA10: Sin asignar (0% - inactivos)

---

### 📘 notas_tecnicas
```sql
CREATE TABLE notas_tecnicas (
    id_nota INT PRIMARY KEY AUTO_INCREMENT,
    id_estudiante INT NOT NULL,
    id_ra INT NOT NULL,
    nota DECIMAL(5,2) NOT NULL,
    rp DECIMAL(5,2),  -- Recuperación
    id_anio INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id),
    FOREIGN KEY (id_ra) REFERENCES resultados_aprendizaje(id_ra) ON DELETE CASCADE,
    FOREIGN KEY (id_anio) REFERENCES anios_escolares(id_anio),
    UNIQUE KEY unique_nota (id_estudiante, id_ra, id_anio)
);
```

**Índices creados:**
- `idx_notas_tecnicas_estudiante`
- `idx_notas_tecnicas_anio`

---

## 📈 RELACIONES ENTRE TABLAS

### Modalidad Académica
```
anios_escolares (1) ──────┐
                            │
periodos (1) ──────┐        │
                   │        │
asignaturas (1) ───┼──> notas_academicas <── estudiantes (N)
    │               │
    └─> competencias ┘
```

### Modalidad Técnica
```
anios_escolares (1) ──────┐
                           │
modulos_formativos (1) ───┼──> notas_tecnicas <── estudiantes (N)
    │                      │
    └─> resultados_aprendizaje ┘
```

---

## 🔍 ÍNDICES CREADOS

```sql
CREATE INDEX idx_notas_academicas_estudiante ON notas_academicas(id_estudiante);
CREATE INDEX idx_notas_academicas_periodo ON notas_academicas(id_periodo);
CREATE INDEX idx_notas_academicas_anio ON notas_academicas(id_anio);
CREATE INDEX idx_notas_tecnicas_estudiante ON notas_tecnicas(id_estudiante);
CREATE INDEX idx_notas_tecnicas_anio ON notas_tecnicas(id_anio);
CREATE INDEX idx_competencias_asignatura ON competencias(id_asignatura);
CREATE INDEX idx_ra_modulo ON resultados_aprendizaje(id_modulo);
CREATE INDEX idx_estudiantes_grado ON estudiantes(grado);
CREATE INDEX idx_estudiantes_modalidad ON estudiantes(modalidad);
```

---

## 🎯 CONSULTAS ÚTILES

### Obtener notas académicas de un estudiante
```sql
SELECT na.*, c.nombre as competencia, p.nombre as periodo, a.nombre as asignatura
FROM notas_academicas na
JOIN competencias c ON na.id_competencia = c.id_competencia
JOIN periodos p ON na.id_periodo = p.id_periodo
JOIN asignaturas a ON c.id_asignatura = a.id_asignatura
WHERE na.id_estudiante = ? AND na.id_anio = ?;
```

### Obtener notas técnicas de un estudiante
```sql
SELECT nt.*, ra.codigo_ra, ra.descripcion, ra.porcentaje, m.nombre as modulo
FROM notas_tecnicas nt
JOIN resultados_aprendizaje ra ON nt.id_ra = ra.id_ra
JOIN modulos_formativos m ON ra.id_modulo = m.id_modulo
WHERE nt.id_estudiante = ? AND nt.id_anio = ?;
```

### Calcular promedio de un estudiante (modalidad académica)
```sql
SELECT 
    e.nombre, e.apellido,
    a.nombre as asignatura,
    AVG(na.nota) as promedio
FROM notas_academicas na
JOIN estudiantes e ON na.id_estudiante = e.id
JOIN competencias c ON na.id_competencia = c.id_competencia
JOIN asignaturas a ON c.id_asignatura = a.id_asignatura
WHERE na.id_estudiante = ? AND na.id_anio = ?
GROUP BY a.id_asignatura;
```

### Calcular nota final técnica ponderada
```sql
SELECT 
    e.nombre, e.apellido,
    m.nombre as modulo,
    SUM((nt.nota * ra.porcentaje) / 100) as nota_final_ponderada
FROM notas_tecnicas nt
JOIN resultados_aprendizaje ra ON nt.id_ra = ra.id_ra
JOIN modulos_formativos m ON ra.id_modulo = m.id_modulo
JOIN estudiantes e ON nt.id_estudiante = e.id
WHERE nt.id_estudiante = ? AND nt.id_anio = ? AND ra.activo = TRUE
GROUP BY m.id_modulo;
```

---

## 📋 TAREAS PENDIENTES

### Backend (Controllers)
- [ ] CalificacionesAcademicasController
  - GET /calificaciones-academicas
  - POST /calificaciones-academicas
  - PUT /calificaciones-academicas/{id}
  - DELETE /calificaciones-academicas/{id}

- [ ] CalificacionesTecnicasController
  - GET /calificaciones-tecnicas
  - POST /calificaciones-tecnicas
  - PUT /calificaciones-tecnicas/{id}
  - DELETE /calificaciones-tecnicas/{id}

### Models
- [ ] CalificacionAcademica
- [ ] CalificacionTecnica

### Seeding de datos
- [ ] Asignaturas de ejemplo
- [ ] Competencias de ejemplo
- [ ] Módulos formativos de ejemplo
- [ ] Resultados de aprendizaje de ejemplo

---

## ✅ RESUMEN

| Elemento | Estado | Notas |
|----------|--------|-------|
| Tabla `estudiantes` | ✅ Modificada | Agregados campos `modalidad` y `especialidad` |
| Tabla `asignaturas` | ✅ Creada | Nueva tabla para modalidad académica |
| Tabla `competencias` | ✅ Creada | Nueva tabla para modalidad académica |
| Tabla `periodos` | ✅ Creada | Con datos iniciales (P1-P4) |
| Tabla `notas_academicas` | ✅ Creada | Con restricción UNIQUE |
| Tabla `modulos_formativos` | ✅ Creada | Nueva tabla para modalidad técnica |
| Tabla `resultados_aprendizaje` | ✅ Creada | Nueva tabla para modalidad técnica |
| Tabla `notas_tecnicas` | ✅ Creada | Con restricción UNIQUE |
| Índices | ✅ Creados | 9 índices para optimizar búsquedas |
| Año escolar | ✅ Insertado | 2025-2026 como año activo |

La estructura de base de datos está **100% lista** para comenzar con la implementación de los Controllers y APIs.
