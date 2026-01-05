# 📐 ANÁLISIS DETALLADO - MODALIDAD TÉCNICO PROFESIONAL

**Estado:** ANALIZADO Y SINTETIZADO ✅  
**Fecha:** 2 de enero de 2026  
**Versión:** 1.0

---

## 🎯 RESUMEN EJECUTIVO

**La modalidad técnica es un sistema de ponderación variable por RA con cierre por módulo:**

```
MÓDULO FORMATIVO
    ├─ RA1 (activo) 30% → Nota
    ├─ RA2 (activo) 40% → Nota  
    ├─ RA3 (activo) 30% → Nota
    └─ RA4-RA10 (inactivos) 0%
         ↓
    Σ(Nota RA × % RA) / 100 = NOTA FINAL
         ↓
    ≥70 → APROBADO
    <70 → REPROBADO
```

---

## 1️⃣ ESTRUCTURA FUNDACIONAL DE RA

### Principio fundamental

```
CADA MÓDULO TIENE CAPACIDAD PARA RA1-RA10
SOLO ALGUNOS RA ESTÁN MARCADOS COMO ACTIVOS
LOS RA INACTIVOS NO SE CALIFICAN
LOS PORCENTAJES SOLO SE APLICAN A RA ACTIVOS
```

### Visualización del sistema

```
┌─────────────────────────────────────────────┐
│ MÓDULO: Análisis y Diseño de Reportes      │
├─────────────────────────────────────────────┤
│ RA  │ Descripción              │ Activo │ % │
├─────┼──────────────────────────┼────────┼────┤
│ RA1 │ Análisis de requisitos   │   ✅   │30  │
│ RA2 │ Diseño de estructura     │   ✅   │40  │
│ RA3 │ Validación               │   ✅   │30  │
│ RA4 │ (No usado)               │   ❌   │ 0  │
│ RA5 │ (No usado)               │   ❌   │ 0  │
│ RA6 │ (No usado)               │   ❌   │ 0  │
│ RA7 │ (No usado)               │   ❌   │ 0  │
│ RA8 │ (No usado)               │   ❌   │ 0  │
│ RA9 │ (No usado)               │   ❌   │ 0  │
│RA10 │ (No usado)               │   ❌   │ 0  │
├─────┴──────────────────────────┴────────┴────┤
│ SUMA DE PORCENTAJES: 100% ✅               │
└──────────────────────────────────────────────┘
```

### Diagrama de relaciones

```
MÓDULO FORMATIVO
    ├─ RESULTADO DE APRENDIZAJE RA1 (Activo)
    │   └─ NOTA por ESTUDIANTE
    │       └─ RP (opcional)
    │
    ├─ RESULTADO DE APRENDIZAJE RA2 (Activo)
    │   └─ NOTA por ESTUDIANTE
    │       └─ RP (opcional)
    │
    └─ RESULTADO DE APRENDIZAJE RA3 (Inactivo)
        └─ NO CALIFICABLE
```

---

## 2️⃣ VALIDACIÓN DE PORCENTAJES

### Regla crítica

```
┌─────────────────────────────────────────────────┐
│ LA SUMA DE PORCENTAJES DE RA ACTIVOS            │
│ DEBE SER EXACTAMENTE 100%                       │
│                                                 │
│ NO 99%, NO 101%, EXACTAMENTE 100%              │
└─────────────────────────────────────────────────┘
```

### Algoritmo de validación

```javascript
VALIDAR_PORCENTAJES(raModulo):
    
    PASO 1: Filtrar solo RA activos
    raActivos = raModulo.filter(ra => ra.activo === true)
    
    PASO 2: Sumar porcentajes
    totalPorcentaje = raActivos.reduce(
        (sum, ra) => sum + ra.porcentaje, 
        0
    )
    
    PASO 3: Comparar con 100
    SI totalPorcentaje === 100
        RETORNA {válido: true}
    SINO
        RETORNA {
            válido: false,
            error: `Suma = ${totalPorcentaje}%, debe ser 100%`,
            faltante: 100 - totalPorcentaje
        }
```

### Momentos de validación

| Momento | Acción |
|---------|--------|
| **Al cargar módulo** | Mostrar advertencia si % ≠ 100 |
| **Antes de guardar notas** | BLOQUEAR si % ≠ 100 |
| **Al cerrar módulo** | VERIFICAR % = 100 |
| **En consultas** | Incluir suma en respuesta |

### Ejemplo de validación en acción

```
ESCENARIO 1: RA bien configurados
  RA1: 30% ✅
  RA2: 40% ✅
  RA3: 30% ✅
  ─────────────
  Total: 100% ✅ VÁLIDO

ESCENARIO 2: RA incompletos
  RA1: 30% ✅
  RA2: 40% ✅
  RA3: 25% ❌ (falta 5%)
  ─────────────
  Total: 95% ❌ ERROR: "Falta 5%"

ESCENARIO 3: RA con exceso
  RA1: 35% ⚠️
  RA2: 40% ⚠️
  RA3: 30% ⚠️
  ─────────────
  Total: 105% ❌ ERROR: "Exceso de 5%"
```

### SQL para validar

```sql
-- Verificar suma de porcentajes por módulo
SELECT 
    id_modulo,
    SUM(porcentaje) as suma_porcentaje,
    CASE 
        WHEN SUM(porcentaje) = 100 THEN 'VÁLIDO'
        ELSE CONCAT('INVÁLIDO - Suma: ', SUM(porcentaje))
    END as estado
FROM resultados_aprendizaje
WHERE activo = TRUE
GROUP BY id_modulo
HAVING SUM(porcentaje) != 100;  -- Muestra solo los errores
```

---

## 3️⃣ REGISTRO DE NOTAS POR RA

### Validación de entrada

```javascript
VALIDAR(raActivo, nota, rp):
    ├─ SI raActivo === FALSE
    │   └─ ERROR "No se puede calificar RA inactivo"
    │
    ├─ SI nota = NULL Y rp = NULL
    │   └─ ERROR "RA activo requiere nota o RP"
    │
    ├─ SI nota < 0 ∨ nota > 100
    │   └─ ERROR "Nota fuera de rango (0-100)"
    │
    ├─ SI rp < 0 ∨ rp > 100
    │   └─ ERROR "RP fuera de rango (0-100)"
    │
    └─ VÁLIDO → Proceder
```

### Lógica de sustitución RP

```sql
-- Pseudo-SQL para determinar nota a usar
SELECT 
    id_ra,
    id_estudiante,
    CASE 
        WHEN rp IS NOT NULL THEN rp
        ELSE nota
    END AS notaFinalRA,
    rp,
    nota
FROM notas_tecnicas
WHERE id_estudiante = ? 
  AND id_ra = ?
```

### Ejemplo de sustitución

```
ESCENARIO 1: Sin RP (solo nota original)
┌──────────────────────────┐
│ RA1: Análisis            │
│ Nota: 85                 │
│ RP: NULL                 │
│ Usa: 85 ✅               │
│ % Aporte: 85 × 30% = 25.5│
└──────────────────────────┘

ESCENARIO 2: Con RP (sustituye)
┌──────────────────────────┐
│ RA2: Diseño              │
│ Nota: 60 (original)      │
│ RP: 78 ← SUSTITUYE       │
│ Usa: 78 ✅               │
│ % Aporte: 78 × 40% = 31.2│
└──────────────────────────┘

ESCENARIO 3: RA inactivo (no se ingresa)
┌──────────────────────────┐
│ RA5: (No en módulo)      │
│ Nota: -                  │
│ RP: -                    │
│ Usa: N/A ❌              │
│ % Aporte: 0% (inactivo)  │
└──────────────────────────┘
```

---

## 4️⃣ CÁLCULO DE NOTA FINAL DEL MÓDULO

### Fórmula de ponderación

```
┌────────────────────────────────────────────────────┐
│ NOTA FINAL = Σ (NOTA_RA × PORCENTAJE_RA / 100)    │
│                                                    │
│ Donde:                                             │
│   NOTA_RA = nota o RP (según corresponda)          │
│   PORCENTAJE_RA = % asignado al RA                 │
└────────────────────────────────────────────────────┘
```

### Algoritmo paso a paso

```javascript
CALCULAR_NOTA_FINAL_MODULO(raActivos, notas):
    
    PASO 1: Inicializar acumulador
    notaFinal = 0
    
    PASO 2: Iterar sobre cada RA activo
    PARA CADA raActivo EN raActivos:
        
        // Determinar nota a usar
        notaUsada = notas[ra.id].rp ?? notas[ra.id].nota
        
        // Calcular aporte ponderado
        aporte = notaUsada × (raActivo.porcentaje / 100)
        
        // Acumular
        notaFinal += aporte
    
    PASO 3: Retornar con redondeo
    RETORNA ROUND(notaFinal, 2)
```

### Ejemplo numérico completo

```
CONTEXTO:
  Estudiante: María (ID 2)
  Módulo: Programación Web (MOD-02)
  RA activos: 4

CONFIGURACIÓN DE RA:
  RA1 (Metodología): 25%
  RA2 (Estructura): 30%
  RA3 (Estilos): 25%
  RA4 (Testing): 20%
  ────────────────────
  Total: 100% ✅

NOTAS REGISTRADAS:
  RA1: nota=75, rp=NULL → Usa: 75
  RA2: nota=65, rp=80 → Usa: 80 (sustituye)
  RA3: nota=90, rp=NULL → Usa: 90
  RA4: nota=85, rp=NULL → Usa: 85

CÁLCULO PASO A PASO:
  RA1: 75 × (25/100) = 75 × 0.25 = 18.75
  RA2: 80 × (30/100) = 80 × 0.30 = 24.00
  RA3: 90 × (25/100) = 90 × 0.25 = 22.50
  RA4: 85 × (20/100) = 85 × 0.20 = 17.00
                                      ──────
  NOTA FINAL = 18.75 + 24.00 + 22.50 + 17.00 = 82.25 → 82

ESTADO: APROBADO ✅ (≥70)
```

### SQL para el cálculo

```sql
-- Cálculo de nota final ponderada
SELECT 
    nt.id_estudiante,
    nt.id_modulo,
    SUM(
        CASE 
            WHEN nt.rp IS NOT NULL THEN nt.rp * (ra.porcentaje / 100)
            ELSE nt.nota * (ra.porcentaje / 100)
        END
    ) AS notaFinal,
    CASE 
        WHEN SUM(
            CASE 
                WHEN nt.rp IS NOT NULL THEN nt.rp * (ra.porcentaje / 100)
                ELSE nt.nota * (ra.porcentaje / 100)
            END
        ) >= 70 THEN 'APROBADO'
        ELSE 'REPROBADO'
    END AS estado
FROM notas_tecnicas nt
JOIN resultados_aprendizaje ra ON nt.id_ra = ra.id
WHERE nt.id_estudiante = ?
  AND ra.id_modulo = ?
  AND ra.activo = TRUE
GROUP BY nt.id_estudiante, ra.id_modulo
```

---

## 5️⃣ VALIDACIONES ANTES DE GUARDAR

### Matriz de validación completa

| Validación | Condición | Acción | Mensaje |
|-----------|-----------|--------|---------|
| RA Sin Nota | ∃ RA activo sin nota | BLOQUEAR | "Faltan notas de RA activos" |
| Nota Rango | nota < 0 ∨ nota > 100 | BLOQUEAR | "Nota inválida (0-100)" |
| RP Rango | rp < 0 ∨ rp > 100 | BLOQUEAR | "RP inválida (0-100)" |
| Suma % | Σ% activos ≠ 100 | BLOQUEAR | "Porcentajes suman XX%, deben ser 100%" |
| Módulo Cerrado | modulo.cerrado = TRUE | BLOQUEAR | "Módulo cerrado" |
| RA Inactivo | Intentar calificar RA inactivo | BLOQUEAR | "RA inactivo no se puede calificar" |
| Duplicado | Ya existe nota en RA | PERMITIR | Actualizar registro |

### Pseudocódigo de validación

```javascript
VALIDAR_NOTAS_TECNICAS(datos):
    
    // 1. Validar módulo activo
    SI modulo.cerrado === TRUE
        RETORNA {válido: false, error: "Módulo cerrado"}
    
    // 2. Validar porcentajes
    SI !validarPorcentajes(raModulo)
        RETORNA {válido: false, error: "Porcentajes ≠ 100%"}
    
    // 3. Validar cada nota
    PARA CADA ra EN raModulo:
        SI ra.activo === FALSE
            CONTINUAR (ignorar inactivos)
        
        SI nota = NULL Y rp = NULL
            RETORNA {válido: false, error: `RA${ra.numero} sin nota`}
        
        SI nota < 0 OR nota > 100
            RETORNA {válido: false, error: `RA${ra.numero} nota fuera rango`}
        
        SI rp IS NOT NULL AND (rp < 0 OR rp > 100)
            RETORNA {válido: false, error: `RA${ra.numero} RP fuera rango`}
    
    // 4. Validar estudiante enrolado
    SI estudiante ∉ modulo.especialidad
        RETORNA {válido: false, error: "Estudiante no en especialidad"}
    
    // 5. Todas las validaciones pasaron
    RETORNA {válido: true}
```

---

## 6️⃣ INSERCIÓN EN BASE DE DATOS

### Transacción de guardado

```sql
BEGIN TRANSACTION;

PARA CADA ra_activo EN raActivos:
    INSERT INTO notas_tecnicas (
        id_estudiante,
        id_ra,
        nota,
        rp,
        id_anio,
        created_at,
        updated_at
    ) VALUES (?, ?, ?, ?, ?, NOW(), NOW())
    
    -- Si la nota ya existe:
    ON DUPLICATE KEY UPDATE
        nota = VALUES(nota),
        rp = VALUES(rp),
        updated_at = NOW()

COMMIT;
-- Si alguna INSERT falla: ROLLBACK
```

### Implementación en PHP

```php
try {
    DB::beginTransaction();
    
    // Validar porcentajes ANTES
    $sumaPorcentaje = ResultadoAprendizaje::where('id_modulo', $modulo)
        ->where('activo', true)
        ->sum('porcentaje');
    
    if ($sumaPorcentaje !== 100) {
        throw new Exception("Porcentajes no suman 100%");
    }
    
    // Insertar/actualizar notas
    foreach ($raActivos as $ra) {
        NotaTecnica::updateOrCreate(
            [
                'id_estudiante' => $estudiante,
                'id_ra' => $ra['id'],
                'id_anio' => $anio
            ],
            [
                'nota' => $ra['nota'],
                'rp' => $ra['rp'] ?? null
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

## 7️⃣ CIERRE DE MÓDULO

### Mecanismo de cierre

```
EVENTO: ADMIN cierra Módulo XYZ

ACCIONES:
  ├─ Verificar que todas las notas estén completas
  ├─ Verificar que Σ% = 100%
  ├─ Generar resumen de calificaciones
  ├─ Insertar en tabla cierre_modulos
  ├─ Bloquear edición de notas
  └─ Notificar docentes

TABLA: cierre_modulos
  Campos:
    ├─ id
    ├─ id_modulo
    ├─ id_anio
    ├─ fecha_cierre
    ├─ usuario_cierre
    └─ bloqueado = true
```

### Validación antes de cerrar

```javascript
VALIDAR_CIERRE_MODULO(modulo):
    
    // 1. Verificar completitud de RA
    raActivos = RA.where({activo: true, id_modulo: modulo})
    
    PARA CADA ra EN raActivos:
        notasConRegistro = Notas.where({id_ra: ra.id, módulo: modulo})
        SI notasConRegistro.count() < estudiantes.count()
            RETORNA {
                válido: false,
                error: `RA${ra.numero} incompleto`,
                faltantes: estudiantes.count() - notasConRegistro.count()
            }
    
    // 2. Verificar suma de porcentajes
    SI SUM(porcentaje WHERE activo) ≠ 100
        RETORNA {válido: false, error: "Porcentajes ≠ 100%"}
    
    // 3. Generar reporte de estado
    RETORNA {
        válido: true,
        raActivos: raActivos.count(),
        notasRegistradas: Notas.count(),
        estado: "LISTO PARA CERRAR"
    }
```

### Flujo de cierre completo

```
Admin selecciona Cerrar Módulo
    ↓
Sistema valida:
  ✓ Todas las notas registradas
  ✓ Σ% = 100%
  ✓ Todos los estudiantes tienen nota
    ↓
SI válido:
    INSERT cierre_modulos
    ├─ fecha_cierre = NOW()
    ├─ usuario = admin_id
    └─ bloqueado = TRUE
    
    Interfaz → MODO LECTURA
    
    Notificación: "Módulo cerrado exitosamente"
    
SINO:
    ERROR con lista de faltantes
    
    Docente: "Completa estas notas:"
    - RA3 faltan 2 estudiantes
    - RA5 faltan 4 estudiantes
    
    Reintentar cierre después de completar
```

### SQL para cierre

```sql
-- Insertar cierre
INSERT INTO cierre_modulos (
    id_modulo,
    id_anio,
    fecha_cierre,
    usuario_cierre,
    bloqueado
) VALUES (?, ?, NOW(), ?, TRUE);

-- Bloquear futuras ediciones
ALTER TABLE notas_tecnicas 
ADD CHECK (
    NOT EXISTS (
        SELECT 1 FROM cierre_modulos 
        WHERE id_modulo = notas_tecnicas.id_modulo 
        AND bloqueado = TRUE
    )
);
```

---

## 8️⃣ COMPORTAMIENTO DE LA INTERFAZ

### Estados visuales por situación

```
╔═════════════════════════════════════════╗
║ RA ACTIVO - MÓDULO ABIERTO              ║
╠═════════════════════════════════════════╣
║ Input nota:   ✅ HABILITADO              ║
║ Input RP:     ✅ HABILITADO              ║
║ Btn Guardar:  ✅ DISPONIBLE              ║
║ % mostrado:   ✅ VISIBLE (ej: 30%)       ║
║ Nota Final:   CALCULADA EN TIEMPO REAL   ║
╚═════════════════════════════════════════╝

╔═════════════════════════════════════════╗
║ RA INACTIVO - MÓDULO ABIERTO            ║
╠═════════════════════════════════════════╣
║ Input nota:   ❌ DESHABILITADO (opacity) ║
║ Input RP:     ❌ DESHABILITADO (opacity) ║
║ % mostrado:   0% (grisado)              ║
║ Badge:        🔒 INACTIVO               ║
║ Aporte:       No participa en cálculo   ║
╚═════════════════════════════════════════╝

╔═════════════════════════════════════════╗
║ MÓDULO CERRADO                          ║
╠═════════════════════════════════════════╣
║ Todos inputs: ❌ DESHABILITADOS (readonly)║
║ Btn Guardar:  ❌ OCULTO                  ║
║ Btn Cerrar:   ❌ DESHABILITADO           ║
║ Nota Final:   FIJA (color grisado)      ║
║ Badge:        🔒 CERRADO                ║
║ Estado:       SOLO LECTURA               ║
╚═════════════════════════════════════════╝

╔═════════════════════════════════════════╗
║ CON RP INGRESADO                        ║
╠═════════════════════════════════════════╣
║ Nota original: OSCURECIDA (opacity 0.5) ║
║ RP:            ✅ DESTACADO (bold)       ║
║ Usa:           NOTA DE RP (color verde) ║
║ Aporte:        Calcula con RP           ║
╚═════════════════════════════════════════╝

╔═════════════════════════════════════════╗
║ ERROR: Σ% ≠ 100%                        ║
╠═════════════════════════════════════════╣
║ Inputs:       🟡 AMARILLO (advertencia) ║
║ Btn Guardar:  ❌ DESHABILITADO          ║
║ Msg error:    "Porcentajes suman XX%"  ║
║ Suma visible: ⚠️ ROJO                   ║
╚═════════════════════════════════════════╝
```

### Lógica de deshabilitación en JavaScript

```javascript
// Deshabilitar inputs según estado

// 1. RA inactivo
if (!ra.activo) {
    input_nota.disabled = true;
    input_rp.disabled = true;
    input_nota.style.opacity = '0.5';
    span_porcentaje.style.color = '#999';
    badge.textContent = '🔒 INACTIVO';
}

// 2. Módulo cerrado
if (modulo.cerrado) {
    input_nota.disabled = true;
    input_rp.disabled = true;
    btn_guardar.style.display = 'none';
    div_modulo.style.backgroundColor = '#f5f5f5';
    badge_cierre.textContent = '🔒 CERRADO';
}

// 3. Existe RP
if (rp !== null) {
    input_nota.disabled = true;
    input_nota.style.opacity = '0.5';
    input_rp.classList.add('highlight');
    span_nota_usada.textContent = rp;
    span_nota_usada.style.color = '#22c55e';
}

// 4. Error de porcentajes
if (sumaPorcentajes !== 100) {
    btn_guardar.disabled = true;
    div_error.style.display = 'block';
    div_error.textContent = 
        `Porcentajes suman ${sumaPorcentajes}%, deben ser 100%`;
}

// 5. RA sin nota
if (ra.activo && nota === null && rp === null) {
    input_nota.style.borderColor = '#ef4444';
    input_nota.classList.add('required');
    btn_guardar.disabled = true;
}
```

---

## 9️⃣ CASOS ESPECIALES

### Caso 1: Módulo con 10 RA activos

```
SITUACIÓN:
  Todos los RA1-RA10 están activos
  Cada uno con porcentaje específico

CONFIGURACIÓN:
  RA1: 10%
  RA2: 10%
  RA3: 10%
  RA4: 10%
  RA5: 10%
  RA6: 10%
  RA7: 10%
  RA8: 10%
  RA9: 10%
  RA10: 10%
  ────────────
  Total: 100% ✅

NOTAS:
  RA1: 80 → 80 × 0.10 = 8.0
  RA2: 85 → 85 × 0.10 = 8.5
  RA3: 90 → 90 × 0.10 = 9.0
  RA4: 75 → 75 × 0.10 = 7.5
  RA5: 88 → 88 × 0.10 = 8.8
  RA6: 92 → 92 × 0.10 = 9.2
  RA7: 80 → 80 × 0.10 = 8.0
  RA8: 85 → 85 × 0.10 = 8.5
  RA9: 89 → 89 × 0.10 = 8.9
  RA10: 86 → 86 × 0.10 = 8.6
                             ────
  Nota Final: 84.9 → 85 ✅ APROBADO
```

### Caso 2: Módulo con 1 RA

```
SITUACIÓN:
  Solo RA1 está activo
  El resto inactivos

CONFIGURACIÓN:
  RA1: 100% ← SOLO ESTE
  RA2-RA10: 0% (inactivos)
  ────────────
  Total: 100% ✅

CÁLCULO:
  Nota RA1: 75
  Nota Final = 75 × (100/100) = 75 × 1.0 = 75
  
  ESTADO: APROBADO ✅ (≥70)
```

### Caso 3: Módulo con 5 RA

```
SITUACIÓN:
  RA1-RA5 activos, RA6-RA10 inactivos

CONFIGURACIÓN:
  RA1: 20%
  RA2: 25%
  RA3: 20%
  RA4: 20%
  RA5: 15%
  RA6-RA10: 0% (inactivos)
  ────────────
  Total: 100% ✅

NOTAS CON RP:
  RA1: nota=60, rp=75 → Usa 75 → 75 × 0.20 = 15.0
  RA2: nota=80, rp=NULL → Usa 80 → 80 × 0.25 = 20.0
  RA3: nota=70, rp=NULL → Usa 70 → 70 × 0.20 = 14.0
  RA4: nota=65, rp=82 → Usa 82 → 82 × 0.20 = 16.4
  RA5: nota=88, rp=NULL → Usa 88 → 88 × 0.15 = 13.2
                                              ─────
  Nota Final: 78.6 → 79 ✅ APROBADO
  
  Mejora gracias a RP: De 67.3 → 78.6 (+11.3 puntos)
```

### Caso 4: Reprobación

```
SITUACIÓN:
  Estudiante con notas bajas en todos los RA

NOTAS:
  RA1: 50 × 30% = 15.0
  RA2: 55 × 40% = 22.0
  RA3: 60 × 30% = 18.0
                  ─────
  Nota Final: 55 ❌ REPROBADO (<70)

CONSECUENCIAS:
  ├─ Genera plan de retaguardia
  ├─ Asigna tutor de apoyo
  ├─ Programa sesión de refuerzo
  └─ Próxima oportunidad: Recuperación pedagógica
```

---

## 🔟 DIFERENCIAS CON MODALIDAD ACADÉMICA

### Tabla comparativa completa

| Aspecto | Académica | Técnica |
|---------|-----------|---------|
| **Estructura base** | Competencias | Resultados de Aprendizaje (RA) |
| **Cantidad** | N competencias | RA1-RA10 (solo activos) |
| **Pesos** | Fijos (70%+30%) | Variables por RA |
| **Validación %** | No requiere suma | DEBE SUM = 100% |
| **Períodos** | P1, P2, P3, P4 | Sin períodos |
| **Unidad de cierre** | Período | Módulo |
| **Cálculo** | Promedio simple + peso | Suma ponderada |
| **Bloqueo** | Por período | Por módulo |
| **RP manejo** | Sustituye nota | Sustituye nota |
| **Indicador éxito** | Promedio final | Nota ponderada |
| **Rango aprobación** | Típicamente ≥70 | Exactamente ≥70 |

---

## 1️⃣1️⃣ ESTRUCTURA SQL DE SOPORTE

### Tabla resultados_aprendizaje (modificada)

```sql
CREATE TABLE resultados_aprendizaje (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    id_modulo INT NOT NULL,
    codigo_ra VARCHAR(10),          -- RA1, RA2, ... RA10
    numero_ra INT,                  -- 1, 2, ..., 10
    descripcion VARCHAR(255) NOT NULL,
    
    activo BOOLEAN DEFAULT FALSE,
    porcentaje DECIMAL(5,2),        -- 0-100, NULL si inactivo
    
    grado INT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_modulo) REFERENCES modulos_formativos(id),
    
    UNIQUE KEY unique_ra (id_modulo, numero_ra),
    
    CHECK (porcentaje IS NULL OR (porcentaje >= 0 AND porcentaje <= 100)),
    CHECK (numero_ra >= 1 AND numero_ra <= 10),
    
    INDEX idx_activo (activo),
    INDEX idx_modulo (id_modulo)
);
```

### Tabla notas_tecnicas (actualizada)

```sql
CREATE TABLE notas_tecnicas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    id_estudiante INT NOT NULL,
    id_ra INT NOT NULL,
    id_anio INT NOT NULL,
    
    nota DECIMAL(5,2),              -- 0-100
    rp DECIMAL(5,2),                -- 0-100, NULL si no existe
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id),
    FOREIGN KEY (id_ra) REFERENCES resultados_aprendizaje(id),
    FOREIGN KEY (id_anio) REFERENCES anios_escolares(id_anio),
    
    UNIQUE KEY unique_nota (id_estudiante, id_ra, id_anio),
    
    CHECK (nota IS NULL OR (nota >= 0 AND nota <= 100)),
    CHECK (rp IS NULL OR (rp >= 0 AND rp <= 100)),
    CHECK (nota IS NOT NULL OR rp IS NOT NULL)
);
```

### Tabla cierre_modulos (nueva)

```sql
CREATE TABLE cierre_modulos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    
    id_modulo INT NOT NULL,
    id_anio INT NOT NULL,
    
    fecha_cierre DATETIME NOT NULL,
    usuario_cierre INT NOT NULL,
    
    bloqueado BOOLEAN DEFAULT TRUE,
    observaciones TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_modulo) REFERENCES modulos_formativos(id),
    FOREIGN KEY (id_anio) REFERENCES anios_escolares(id_anio),
    FOREIGN KEY (usuario_cierre) REFERENCES usuarios(id),
    
    UNIQUE KEY unique_cierre (id_modulo, id_anio),
    
    INDEX idx_bloqueado (bloqueado)
);
```

---

## 1️⃣2️⃣ CONSULTAS SQL ÚTILES

### Verificar suma de porcentajes

```sql
-- Identificar módulos con % incorrectos
SELECT 
    mf.id,
    mf.nombre,
    SUM(ra.porcentaje) as suma_porcentaje,
    CASE 
        WHEN SUM(ra.porcentaje) = 100 THEN '✅ OK'
        ELSE '❌ ERROR'
    END as estado
FROM modulos_formativos mf
LEFT JOIN resultados_aprendizaje ra ON ra.id_modulo = mf.id AND ra.activo = TRUE
GROUP BY mf.id, mf.nombre
ORDER BY suma_porcentaje;
```

### Notas de un estudiante por módulo

```sql
-- Ver todas las notas ponderadas
SELECT 
    e.nombre as estudiante,
    mf.nombre as modulo,
    ra.codigo_ra,
    ra.descripcion,
    ra.porcentaje,
    CASE WHEN nt.rp IS NOT NULL THEN nt.rp ELSE nt.nota END as notaUsada,
    CASE WHEN nt.rp IS NOT NULL THEN nt.rp ELSE nt.nota END * (ra.porcentaje / 100) as aporte
FROM notas_tecnicas nt
JOIN estudiantes e ON nt.id_estudiante = e.id
JOIN resultados_aprendizaje ra ON nt.id_ra = ra.id
JOIN modulos_formativos mf ON ra.id_modulo = mf.id
WHERE e.id = ?
ORDER BY mf.nombre, ra.numero_ra;
```

### Calcular nota final

```sql
-- Nota final ponderada por módulo
SELECT 
    nt.id_estudiante,
    e.nombre,
    mf.nombre as modulo,
    SUM(
        CASE WHEN nt.rp IS NOT NULL THEN nt.rp ELSE nt.nota END 
        * (ra.porcentaje / 100)
    ) as notaFinal,
    CASE 
        WHEN SUM(CASE WHEN nt.rp IS NOT NULL THEN nt.rp ELSE nt.nota END * (ra.porcentaje / 100)) >= 70 
        THEN '✅ APROBADO'
        ELSE '❌ REPROBADO'
    END as estado
FROM notas_tecnicas nt
JOIN estudiantes e ON nt.id_estudiante = e.id
JOIN resultados_aprendizaje ra ON nt.id_ra = ra.id
JOIN modulos_formativos mf ON ra.id_modulo = mf.id
WHERE ra.activo = TRUE
GROUP BY nt.id_estudiante, mf.id
ORDER BY e.nombre, mf.nombre;
```

---

## 📋 CHECKLIST DE IMPLEMENTACIÓN

### Backend Controller

```
Método: getCalificacionesTecnicas()
  ├─ Filtrar por estudiante
  ├─ Filtrar por módulo
  ├─ Obtener RA activos del módulo
  ├─ Validar suma de porcentajes
  ├─ Aplicar sustitución RP
  ├─ Calcular nota final ponderada
  └─ Retornar JSON con estado

Método: saveCalificacionesTecnicas()
  ├─ Validar entrada
  ├─ Validar porcentajes = 100%
  ├─ Validar módulo activo
  ├─ Iniciar transacción
  ├─ Insertar/actualizar notas
  ├─ Calcular nota final
  ├─ Confirmar transacción
  └─ Retornar confirmación

Método: cerrarModulo()
  ├─ Validar permisos (admin)
  ├─ Validar completitud de notas
  ├─ Validar porcentajes
  ├─ Insertar en cierres_modulos
  ├─ Bloquear futuras ediciones
  └─ Retornar confirmación
```

### Model: CalificacionTecnica

```php
class CalificacionTecnica extends Model {
    
    // Relaciones
    public function estudiante() { }
    public function ra() { }
    public function modulo() { }
    
    // Scopes
    public function scopePorModulo($query, $modulo) { }
    public function scopePorEstudiante($query, $est) { }
    public function scopeActivos($query) { }
    
    // Métodos de cálculo
    public function calcularNotaFinal() { }
    public function validarPorcentajes() { }
    public function obtenerRAActivos() { }
    
    // Validaciones
    public function validarNotas() { }
    public function validarModuloActivo() { }
}
```

---

## 📝 PRÓXIMOS PASOS

1. ✅ **Análisis completado** - Texto sintetizado
2. ⏳ **Crear Controllers** - CalificacionesAcademicasController + CalificacionesTecnicasController
3. ⏳ **Crear Models** - CalificacionAcademica + CalificacionTecnica
4. ⏳ **Implementar validaciones** - Según matrices arriba
5. ⏳ **Testing** - Con casos de prueba

---

**Documento completado.**  
Ahora tenemos especificaciones detalladas para ambas modalidades. 👇

**¿Comenzamos con los Controllers?**
