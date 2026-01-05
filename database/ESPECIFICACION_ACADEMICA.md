# 📐 ANÁLISIS DETALLADO - MODALIDAD ACADÉMICA

**Estado:** ANALIZADO Y SINTETIZADO ✅  
**Fecha:** 2 de enero de 2026  
**Versión:** 1.0

---

## 🎯 RESUMEN EJECUTIVO

**La modalidad académica es un sistema de 2 bloques con evaluación periódica:**

```
BLOQUE 70% (Competencias específicas) + BLOQUE 30% (Proyectos)
         ↓
    Promediadas por período
         ↓
    Períodos P1, P2, P3, P4
         ↓
    Nota Final Período = (Prom70 × 0.70) + (Prom30 × 0.30)
```

---

## 1️⃣ ESTRUCTURA FUNDACIONAL

### Elementos del sistema

| Elemento | Definición | Ejemplos |
|----------|-----------|----------|
| **Asignatura** | Curso que se evalúa | ESP-101, MAT-101 |
| **Competencia** | Habilidad evaluada | Comprensión lectora, Producción escrita |
| **Bloque** | Categoría ponderativa | 70% o 30% |
| **Período** | Momento de evaluación | P1, P2, P3, P4 |
| **Nota** | Calificación registrada | 0-100 |
| **RP** | Recuperación Pedagógica | Sustituye nota original |

### Diagrama de relaciones

```
ASIGNATURA
    ├─ COMPETENCIA 1 (Bloque 70%)
    │   └─ NOTA por PERÍODO (P1, P2, P3, P4)
    │       └─ RP (opcional)
    │
    ├─ COMPETENCIA 2 (Bloque 70%)
    │   └─ NOTA por PERÍODO
    │       └─ RP (opcional)
    │
    └─ COMPETENCIA 3 (Bloque 30%)
        └─ NOTA por PERÍODO
            └─ RP (opcional)
```

---

## 2️⃣ REGLA DE BLOQUES

### Principio fundamental

```
CADA COMPETENCIA TIENE UN BLOQUE FIJO (70 o 30)
LA CANTIDAD DE COMPETENCIAS NO CAMBIA EL PESO
EL BLOQUE DETERMINA EL PESO
```

### Ejemplo institucional

```
Lengua Española (ESP-101)
├─ Competencia 1: Comprensión lectora → BLOQUE 70%
├─ Competencia 2: Producción escrita → BLOQUE 70%
└─ Competencia 3: Proyecto integrador → BLOQUE 30%

Matemática (MAT-101)
├─ Competencia 1: Razonamiento lógico → BLOQUE 70%
├─ Competencia 2: Resolución de problemas → BLOQUE 70%
└─ Competencia 3: Proyecto práctico → BLOQUE 30%
```

### Validación crucial

```
✅ PERMITIDO:
   - 2 comp 70% + 1 comp 30%
   - 3 comp 70% + 2 comp 30%
   - 1 comp 70% + 1 comp 30%

❌ NO PERMITIDO:
   - 2 comp 70% sin 30%
   - 3 comp 30% sin 70%
```

---

## 3️⃣ REGISTRO DE NOTAS - REGLAS TÉCNICAS

### Validación de entrada (pre-inserción)

```javascript
VALIDAR(periodo, estudiante, asignatura):
    ├─ SI periodo = NULL → ERROR "Período requerido"
    ├─ SI estudiante ∉ asignatura → ERROR "No enrolado"
    ├─ SI periodo.estado = CERRADO → ERROR "Período cerrado"
    └─ PASAR a siguiente validación

VALIDAR(nota):
    ├─ SI nota < 0 → ERROR "Nota negativa"
    ├─ SI nota > 100 → ERROR "Nota > 100"
    ├─ SI nota = NULL Y rp = NULL → ERROR "Nota requerida"
    └─ NOTA VÁLIDA
```

### Lógica de sustitución RP

```sql
-- Pseudo-SQL para el cálculo
SELECT 
    id_competencia,
    id_estudiante,
    id_periodo,
    CASE 
        WHEN rp IS NOT NULL THEN rp
        ELSE nota
    END AS notaUsada,
    rp,
    nota
FROM notas_academicas
WHERE id_estudiante = ? 
  AND id_periodo = ?
```

### Ejemplo de sustitución

```
Escenario 1: Sin RP
┌─────────────────┐
│ Nota: 75        │
│ RP: NULL        │
│ Usa: 75         │
└─────────────────┘

Escenario 2: Con RP
┌─────────────────┐
│ Nota: 55 (orig) │
│ RP: 82          │
│ Usa: 82         │ ← Sustituye
└─────────────────┘
```

---

## 4️⃣ CÁLCULO BLOQUE 70%

### Algoritmo paso a paso

```
ENTRADA:
  - Lista de notas para competencias BLOQUE 70%
  - En el período evaluado

PASO 1: Aplicar sustitución RP
  competencias70 = [
    {id: 1, notaUsada: 80},
    {id: 2, notaUsada: 90}
  ]

PASO 2: Promediar
  promedio70 = (80 + 90) / 2 = 85

PASO 3: Aplicar peso
  nota70 = 85 × 0.70 = 59.5

SALIDA: 59.5
```

### Validación de completitud

```
ANTES de calcular bloque 70%:

SI existe competencia 70% sin nota EN ESTE PERÍODO
    → ERROR "Faltan notas del bloque 70%"
    → No guardar
    → No calcular
    → Mensaje al usuario

SINO
    → Proceder con cálculo
```

### SQL para el cálculo

```sql
SELECT 
    AVG(CASE 
        WHEN rp IS NOT NULL THEN rp
        ELSE nota
    END) * 0.70 AS nota70
FROM notas_academicas na
JOIN competencias c ON na.id_competencia = c.id_competencia
WHERE na.id_estudiante = ?
  AND na.id_periodo = ?
  AND c.bloque = '70'
  AND c.id_asignatura = ?
```

---

## 5️⃣ CÁLCULO BLOQUE 30%

### Algoritmo idéntico al 70%

```
ENTRADA:
  - Lista de notas para competencias BLOQUE 30%
  - En el período evaluado

PASO 1: Aplicar sustitución RP
  competencias30 = [
    {id: 3, notaUsada: 90}
  ]

PASO 2: Promediar
  promedio30 = 90 / 1 = 90

PASO 3: Aplicar peso
  nota30 = 90 × 0.30 = 27

SALIDA: 27
```

### Caso especial: SIN BLOQUE 30%

```
REGLA: Si una asignatura NO tiene competencias del bloque 30%
       Entonces nota30 = 0
       La nota final será solo el bloque 70%

EJEMPLO:
  Bloque 70%: 70 (después de pesar)
  Bloque 30%: 0 (no existen competencias)
  
  Nota Final = 70 + 0 = 70
```

### SQL para el cálculo

```sql
SELECT 
    COALESCE(
        AVG(CASE 
            WHEN rp IS NOT NULL THEN rp
            ELSE nota
        END) * 0.30,
        0
    ) AS nota30
FROM notas_academicas na
JOIN competencias c ON na.id_competencia = c.id_competencia
WHERE na.id_estudiante = ?
  AND na.id_periodo = ?
  AND c.bloque = '30'
  AND c.id_asignatura = ?
```

---

## 6️⃣ NOTA FINAL DEL PERÍODO

### Fórmula oficial

```
┌────────────────────────────────────────┐
│ notaFinalPeriodo = nota70 + nota30     │
└────────────────────────────────────────┘

Rango válido: 0-100
Redondeo: A entero (ROUND)
```

### Ejemplo completo paso a paso

```
CONTEXTO:
  Estudiante: Juan (ID 1)
  Asignatura: Lengua Española (ESP-101)
  Período: P1

NOTAS REGISTRADAS:
  Competencia 1 (70%): 80
  Competencia 2 (70%): 90
  Competencia 3 (30%): 85

PASO 1: Calcular Bloque 70%
  Promedio = (80 + 90) / 2 = 85
  Nota 70% = 85 × 0.70 = 59.5

PASO 2: Calcular Bloque 30%
  Promedio = 85 / 1 = 85
  Nota 30% = 85 × 0.30 = 25.5

PASO 3: Nota Final
  Nota Final = 59.5 + 25.5 = 85.0 → REDONDEA A 85

RESULTADO: 85 ✅
```

### SQL para insertar nota final

```sql
-- Después de calcular ambos bloques:
INSERT INTO notas_academicas (
    id_estudiante,
    id_competencia_70,
    id_periodo,
    nota_final,
    id_anio
) VALUES (?, NULL, ?, ?, ?)
-- O guardarlo en una tabla de resumen
```

---

## 7️⃣ VALIDACIONES ANTES DE GUARDAR

### Matriz de validación

| Validación | Condición | Acción | Mensaje |
|-----------|-----------|--------|---------|
| Competencia Sin Nota | ∃ competencia sin nota | BLOQUEAR SAVE | "Falta registrar notas" |
| Nota Rango | nota < 0 ∨ nota > 100 | BLOQUEAR SAVE | "Nota inválida (0-100)" |
| Período Cerrado | periodo.estado = CERRADO | BLOQUEAR SAVE | "Período cerrado" |
| No Enrolado | estudiante ∉ asignatura | BLOQUEAR SAVE | "Estudiante no en asignatura" |
| RP > 100 | rp > 100 | BLOQUEAR SAVE | "RP no puede ser > 100" |
| Duplicado | Ya existe nota en período | PERMITIR UPDATE | Actualizar registro |

### Pseudocódigo de validación completa

```javascript
VALIDAR_ANTES_DE_GUARDAR(datos):
    ├─ Validar período activo
    ├─ Validar estudiante enrolado
    ├─ Validar rango de notas (0-100)
    ├─ Validar que todas las competencias tengan nota
    ├─ Validar que no sea duplicado
    └─ SI todas pasan → RETORNA true
       SINO → RETORNA false + mensaje error
```

---

## 8️⃣ FLUJO DE INSERCIÓN EN BASE DE DATOS

### Transacción de guardado

```sql
BEGIN TRANSACTION;

PARA CADA competencia en datos:
    INSERT INTO notas_academicas (
        id_estudiante,
        id_competencia,
        id_periodo,
        nota,
        rp,
        id_anio,
        created_at,
        updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
    
    -- Si la nota ya existe:
    ON DUPLICATE KEY UPDATE
        nota = VALUES(nota),
        rp = VALUES(rp),
        updated_at = NOW()

COMMIT;
-- Si alguna INSERT falla: ROLLBACK
```

### Validación de transacción

```php
try {
    DB::beginTransaction();
    
    foreach ($competencias as $comp) {
        NotaAcademica::updateOrCreate(
            [
                'id_estudiante' => $estudiante,
                'id_competencia' => $comp['id'],
                'id_periodo' => $periodo,
                'id_anio' => $anio
            ],
            [
                'nota' => $comp['nota'],
                'rp' => $comp['rp'] ?? null
            ]
        );
    }
    
    DB::commit();
    return response()->json(['success' => true]);
    
} catch (Exception $e) {
    DB::rollback();
    return response()->json(['error' => $e->getMessage()], 422);
}
```

---

## 9️⃣ CIERRE DE PERÍODO

### Mecanismo de cierre

```
EVENTO: Administrador cierra período P1

ACCIÓN:
  ├─ Generar resumen de calificaciones
  ├─ Bloquear edición de notas en este período
  ├─ Registrar cierre en tabla cierres
  └─ Notificar docentes

TABLA: cierres_asignaturas
  Campos:
    ├─ id
    ├─ id_asignatura
    ├─ id_periodo
    ├─ fecha_cierre
    ├─ usuario_cierre
    └─ bloqueado = true
```

### Validación de cierre

```sql
-- Antes de permitir UPDATE en notas:

SELECT COUNT(*) as periodo_cerrado
FROM cierres_asignaturas
WHERE id_asignatura = ?
  AND id_periodo = ?
  AND bloqueado = TRUE

-- SI resultado > 0 → BLOQUEAR UPDATE
```

### Flujo de cierre

```
Usuario ADMIN
    ↓
Click "Cerrar P1"
    ↓
Validar que todas las notas estén completas
    ↓
SI completas:
    INSERT cierres_asignaturas
    ├─ fecha_cierre = NOW()
    ├─ usuario = $admin_id
    └─ bloqueado = TRUE
    
SINO:
    ERROR: "Faltan notas para cerrar"
    ↓
Mostrar reporte de faltantes
    ↓
Docente completa faltantes
    ↓
Reintentar cierre
```

---

## 🔟 COMPORTAMIENTO DE LA INTERFAZ

### Estados visuales del período

```
╔═════════════════════════════════════════╗
║ PERÍODO ACTIVO                          ║
╠═════════════════════════════════════════╣
║ Input notas:  ✅ HABILITADO              ║
║ Input RP:     ✅ HABILITADO              ║
║ Btn Guardar:  ✅ DISPONIBLE              ║
║ Nota Final:   CALCULADA EN TIEMPO REAL   ║
╚═════════════════════════════════════════╝

╔═════════════════════════════════════════╗
║ PERÍODO CERRADO                         ║
╠═════════════════════════════════════════╣
║ Input notas:  ❌ DESHABILITADO (readonly)║
║ Input RP:     ❌ DESHABILITADO (readonly)║
║ Btn Guardar:  ❌ OCULTO                  ║
║ Nota Final:   MOSTRADA (fija)           ║
║ Badge:        🔒 CERRADO                ║
╚═════════════════════════════════════════╝

╔═════════════════════════════════════════╗
║ CON RP INGRESADO                        ║
╠═════════════════════════════════════════╣
║ Nota original: OSCURECIDA (opacity 0.5) ║
║ RP:            ✅ DESTACADO (bold)       ║
║ Usa:           NOTA DE RP (color verde) ║
║ Btn Guardar:   ✅ DISPONIBLE             ║
╚═════════════════════════════════════════╝

╔═════════════════════════════════════════╗
║ ERROR CÁLCULO                           ║
╠═════════════════════════════════════════╣
║ Input con error: 🔴 BORDE ROJO          ║
║ Btn Guardar:     ❌ DESHABILITADO        ║
║ Msg error:       EXPLICATIVA             ║
╚═════════════════════════════════════════╝
```

### Disabling en frontend

```javascript
// DESHABILITAR input según estado

// 1. Si período cerrado
if (periodo.estado === 'CERRADO') {
    input_nota.disabled = true;
    input_rp.disabled = true;
    btn_guardar.style.display = 'none';
}

// 2. Si existe RP
if (rp !== null) {
    input_nota.disabled = true;
    input_nota.style.opacity = '0.5';
    input_rp.classList.add('highlight');
}

// 3. Si hay error en validación
if (!validar_notas()) {
    btn_guardar.disabled = true;
    div_error.style.display = 'block';
}
```

---

## 1️⃣1️⃣ CASOS ESPECIALES

### Caso 1: Asignatura sin bloque 30%

```
SITUACIÓN:
  Asignatura XYZ tiene solo 2 competencias (ambas 70%)
  No tiene competencias de bloque 30%

CÁLCULO:
  nota70 = promedio_comp70 × 0.70
  nota30 = 0  (no existe bloque 30)
  notaFinal = nota70 + 0 = nota70

EJEMPLO:
  Comp 1: 80 (70%) → Promedio 85
  Comp 2: 90 (70%)
  
  nota70 = 85 × 0.70 = 59.5
  nota30 = 0
  notaFinal = 59.5  ← Solo el 70%
```

### Caso 2: Reprobación

```
DEFINICIÓN: notaFinal < 70

ESTADOS POSIBLES:
  ├─ Nota Final 0-69 → REPROBADO
  ├─ Nota Final 70-79 → APROBADO CON DEFICIENCIA
  ├─ Nota Final 80-89 → APROBADO
  └─ Nota Final 90-100 → EXCELENTE

INDICADOR VISUAL:
  < 70 → 🔴 ROJO (Reprobado)
  70-79 → 🟡 AMARILLO (Deficiencia)
  80-89 → 🟢 VERDE (Aprobado)
  90+ → 🔵 AZUL (Excelente)

ACCIONES POSTERIORES:
  SI reprobado ENTONCES
    ├─ Generar plan de mejora
    ├─ Programar retaguardia
    └─ Notificar tutor
```

### Caso 3: Múltiples RP en período

```
ESCENARIO:
  Competencia 1: nota=50, rp=75
  Competencia 2: nota=60, rp=80
  Competencia 3: nota=45, rp=70

CÁLCULO:
  Usa: 75, 80, 70 (todas las RP)
  Promedio = (75+80+70)/3 = 75
  notaFinal = 75 × 0.70 = 52.5 (asumiendo 100% bloque 70)

NOTA: No se mezcla nota original con RP
      Se usa TODA la RP o TODA la nota original
```

---

## 1️⃣2️⃣ ESTRUCTURA SQL DE SOPORTE

### Tabla notas_academicas (actualizada)

```sql
CREATE TABLE notas_academicas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    id_estudiante INT NOT NULL,
    id_competencia INT NOT NULL,
    id_periodo INT NOT NULL,
    id_anio INT NOT NULL,
    
    nota DECIMAL(5,2),          -- 0-100
    rp DECIMAL(5,2),            -- 0-100, NULL si no existe
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id),
    FOREIGN KEY (id_competencia) REFERENCES competencias(id),
    FOREIGN KEY (id_periodo) REFERENCES periodos(id),
    FOREIGN KEY (id_anio) REFERENCES anios_escolares(id_anio),
    
    UNIQUE KEY unique_nota (id_estudiante, id_competencia, id_periodo, id_anio),
    
    CHECK (nota >= 0 AND nota <= 100),
    CHECK (rp IS NULL OR (rp >= 0 AND rp <= 100))
);
```

### Tabla competencias (actualizada)

```sql
CREATE TABLE competencias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    id_asignatura INT NOT NULL,
    codigo_competencia VARCHAR(20) UNIQUE,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    
    bloque ENUM('70', '30') NOT NULL,  -- El peso
    
    grado INT NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_asignatura) REFERENCES asignaturas(id),
    
    INDEX idx_bloque (bloque),
    INDEX idx_asignatura (id_asignatura)
);
```

### Tabla cierres_asignaturas (nueva)

```sql
CREATE TABLE cierres_asignaturas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    id_asignatura INT NOT NULL,
    id_periodo INT NOT NULL,
    id_anio INT NOT NULL,
    
    fecha_cierre DATETIME NOT NULL,
    usuario_cierre INT NOT NULL,
    
    bloqueado BOOLEAN DEFAULT TRUE,
    observaciones TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_asignatura) REFERENCES asignaturas(id),
    FOREIGN KEY (id_periodo) REFERENCES periodos(id),
    FOREIGN KEY (id_anio) REFERENCES anios_escolares(id_anio),
    FOREIGN KEY (usuario_cierre) REFERENCES usuarios(id),
    
    UNIQUE KEY unique_cierre (id_asignatura, id_periodo, id_anio),
    
    INDEX idx_bloqueado (bloqueado)
);
```

---

## 1️⃣3️⃣ COMPARACIÓN CON TÉCNICA

### Diferencias fundamentales

| Aspecto | Académica | Técnica |
|---------|-----------|---------|
| **Estructura** | Competencias + Bloques | Resultados de Aprendizaje |
| **Pesos** | Fijos (70% + 30%) | Variables por RA |
| **Cálculo** | Promedio simple + peso | Suma ponderada |
| **Períodos** | P1, P2, P3, P4 | Por módulo |
| **Evaluación** | Competencias | RA (RA1-RA10) |
| **Bloqueo** | Por período | Por módulo |
| **RP** | Sustituye nota | Sustituye nota |

---

## 📋 CHECKLIST DE IMPLEMENTACIÓN

### Backend Controller

```
Método: getCalificacionesAcademicas()
  ├─ Filtrar por estudiante
  ├─ Filtrar por período
  ├─ Filtrar por asignatura
  ├─ Aplicar sustitución RP
  ├─ Calcular nota final
  └─ Retornar JSON

Método: saveCalificacionesAcademicas()
  ├─ Validar entrada
  ├─ Validar período activo
  ├─ Iniciar transacción
  ├─ Insertar/actualizar notas
  ├─ Calcular nota final
  ├─ Confirmar transacción
  └─ Retornar confirmación

Método: cerrarPeriodo()
  ├─ Validar permisos (admin)
  ├─ Validar completitud de notas
  ├─ Insertar en cierres_asignaturas
  ├─ Bloquear futuras ediciones
  └─ Retornar confirmación
```

### Model: CalificacionAcademica

```php
class CalificacionAcademica extends Model {
    
    // Relaciones
    public function estudiante() { }
    public function competencia() { }
    public function periodo() { }
    
    // Scopes
    public function scopePorPeriodo($query, $periodo) { }
    public function scopePorEstudiante($query, $est) { }
    
    // Métodos de cálculo
    public function calcularBloque70() { }
    public function calcularBloque30() { }
    public function calcularNotaFinal() { }
    
    // Validaciones
    public function validarNotas() { }
    public function validarPeriodoActivo() { }
}
```

---

## 📝 PRÓXIMOS PASOS

1. ✅ **Análisis completado** - Texto sintetizado
2. ⏳ **Esperar especificación técnica** - Modalidad técnica
3. ⏳ **Crear Controllers** - Una vez se confirme ambas
4. ⏳ **Implementar Models** - Con relaciones y cálculos
5. ⏳ **Testing** - Con casos de prueba

---

**Documento completado.**  
Listo para recibir la especificación de la **Modalidad Técnica**. 👇
