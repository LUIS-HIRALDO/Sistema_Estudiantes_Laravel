# 📊 Esquema de Base de Datos MySQL

## Información General

- **Servidor**: MySQL 5.7+
- **Base de Datos**: `sistema_estudiantes`
- **Tipo**: SQL/Relacional
- **Conexión**: `mysql://localhost:3306`

---

## Tablas (Tables)

### 1. **usuarios**
```sql
CREATE TABLE usuarios (
  id INT PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  apellido VARCHAR(100) NOT NULL,
  rol VARCHAR(50) DEFAULT 'estudiante',
  estado VARCHAR(50) DEFAULT 'activo',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

Estructura JSON equivalente:
```json
{
  "id": Integer,
  "email": String (unique),
  "password": String (hasheada),
  "nombre": String,
  "apellido": String,
  "rol": String (admin, profesor, estudiante, acudiente),
  "estado": String (enum: activo, inactivo),
  "created_at": TIMESTAMP,
  "updated_at": TIMESTAMP
}
```

**Índices:**
- `email` (único)

---

### 2. **roles**
```json
{
  "_id": ObjectId,
  "nombre": String (Administrador, Profesor, Estudiante, Acudiente),
  "descripcion": String,
  "permisos": Array<String>,
  "created_at": UTCDateTime,
  "updated_at": UTCDateTime
}
```

**Roles por defecto:**
- Administrador - Acceso total
- Profesor - Gestión de calificaciones
- Estudiante - Acceso a propias calificaciones
- Acudiente - Lectura de calificaciones del estudiante

---

### 3. **estudiantes**
```json
{
  "_id": ObjectId,
  "nombre": String,
  "apellido": String,
  "email": String,
  "telefono": String,
  "grado": String,
  "seccion": String,
  "matricula": String (único),
  "numero_matricula": String,
  "fecha_nacimiento": Date,
  "direccion": String,
  "fecha_inscripcion": UTCDateTime,
  "usuario_id": ObjectId (ref: usuarios),
  "estado": String (enum: activo, inactivo, egresado),
  "created_at": UTCDateTime,
  "updated_at": UTCDateTime
}
```

**Índices:**
- `email`
- `matricula` (único)
- `usuario_id` (único)

---

### 4. **profesores**
```json
{
  "_id": ObjectId,
  "nombre": String,
  "apellido": String,
  "email": String,
  "telefono": String,
  "especialidad": String,
  "materia_id": ObjectId (ref: materias, opcional),
  "usuario_id": ObjectId (ref: usuarios),
  "titulo": String,
  "fecha_contratacion": Date,
  "estado": String (enum: activo, inactivo, licencia),
  "created_at": UTCDateTime,
  "updated_at": UTCDateTime
}
```

**Índices:**
- `email`
- `usuario_id` (único)

---

### 5. **materias**
```json
{
  "_id": ObjectId,
  "nombre": String,
  "codigo": String (único),
  "descripcion": String,
  "horas_semana": Number,
  "profesor_id": ObjectId (ref: profesores, opcional),
  "grado": String,
  "creditos": Number,
  "estado": String (enum: activo, inactivo),
  "created_at": UTCDateTime,
  "updated_at": UTCDateTime
}
```

**Índices:**
- `codigo` (único)
- `grado`

---

### 6. **notas**
```json
{
  "_id": ObjectId,
  "estudiante_id": ObjectId (ref: estudiantes),
  "materia_id": ObjectId (ref: materias),
  "profesor_id": ObjectId (ref: profesores),
  "parcial_1": Number,
  "parcial_2": Number,
  "parcial_3": Number,
  "valor": Number (alternativo a parciales),
  "promedio": Number (calculado),
  "fecha": UTCDateTime,
  "observaciones": String,
  "estado": String (enum: registrada, final),
  "created_at": UTCDateTime,
  "updated_at": UTCDateTime
}
```

**Índices:**
- `estudiante_id, materia_id, fecha` (compuesto)

---

### 7. **asistencias**
```json
{
  "_id": ObjectId,
  "estudiante_id": ObjectId (ref: estudiantes),
  "materia_id": ObjectId (ref: materias),
  "profesor_id": ObjectId (ref: profesores),
  "fecha": Date,
  "estado": String (enum: presente, ausente, tarde, excusada),
  "observaciones": String,
  "created_at": UTCDateTime,
  "updated_at": UTCDateTime
}
```

**Índices:**
- `estudiante_id, materia_id, fecha` (compuesto, único)

---

### 8. **pagos**
```json
{
  "_id": ObjectId,
  "estudiante_id": ObjectId (ref: estudiantes),
  "monto": Number,
  "concepto": String (Matrícula, Mensualidad, etc),
  "fecha_pago": Date,
  "metodo": String (enum: efectivo, transferencia, cheque),
  "numero_recibo": String,
  "estado": String (enum: pendiente, pagado, cancelado),
  "observaciones": String,
  "created_at": UTCDateTime,
  "updated_at": UTCDateTime
}
```

**Índices:**
- `estudiante_id`
- `estado`
- `fecha_pago`

---

### 9. **horarios**
```json
{
  "_id": ObjectId,
  "materia_id": ObjectId (ref: materias),
  "profesor_id": ObjectId (ref: profesores),
  "dia": String (lunes, martes, etc),
  "hora_inicio": String (HH:mm),
  "hora_fin": String (HH:mm),
  "salon": String,
  "aula": String,
  "grado": String,
  "estado": String (enum: activo, inactivo),
  "created_at": UTCDateTime,
  "updated_at": UTCDateTime
}
```

**Índices:**
- `materia_id`
- `dia`

---

### 10. **tareas**
```json
{
  "_id": ObjectId,
  "titulo": String,
  "descripcion": String,
  "materia_id": ObjectId (ref: materias),
  "profesor_id": ObjectId (ref: profesores),
  "fecha_creacion": UTCDateTime,
  "fecha_vencimiento": Date,
  "fecha_entrega": Date,
  "puntos": Number,
  "puntos_obtenidos": Number,
  "archivos": Array<String>,
  "estado": String (enum: pendiente, completada, vencida),
  "created_at": UTCDateTime,
  "updated_at": UTCDateTime
}
```

**Índices:**
- `materia_id`
- `estado`
- `fecha_vencimiento`

---

### 11. **notificaciones**
```json
{
  "_id": ObjectId,
  "usuario_id": ObjectId (ref: usuarios),
  "titulo": String,
  "mensaje": String,
  "tipo": String (enum: tarea, calificacion, asistencia, pago),
  "prioridad": String (enum: baja, media, alta),
  "leida": Boolean,
  "fecha_lectura": UTCDateTime,
  "enlace": String (URL relativa),
  "created_at": UTCDateTime,
  "updated_at": UTCDateTime
}
```

**Índices:**
- `usuario_id`
- `leida`

---

### 12. **comentarios**
```json
{
  "_id": ObjectId,
  "estudiante_id": ObjectId (ref: estudiantes),
  "profesor_id": ObjectId (ref: profesores),
  "materia_id": ObjectId (ref: materias),
  "contenido": String,
  "sentimiento": String (enum: positivo, neutro, negativo),
  "tipo": String (enum: comportamiento, academico, social),
  "privado": Boolean,
  "created_at": UTCDateTime,
  "updated_at": UTCDateTime
}
```

**Índices:**
- `estudiante_id`
- `profesor_id`
- `materia_id`

---

## Relaciones (Relationships)

```
usuarios
  ├── rol_id → roles._id
  ├── profesor_id → profesores._id
  └── estudiante_id → estudiantes._id

estudiantes
  └── usuario_id → usuarios._id

profesores
  ├── usuario_id → usuarios._id
  ├── materia_id → materias._id
  ├── notas.profesor_id ← notas._id
  ├── asistencias.profesor_id ← asistencias._id
  ├── horarios.profesor_id ← horarios._id
  ├── tareas.profesor_id ← tareas._id
  └── comentarios.profesor_id ← comentarios._id

materias
  ├── profesor_id → profesores._id
  ├── notas.materia_id ← notas._id
  ├── asistencias.materia_id ← asistencias._id
  ├── horarios.materia_id ← horarios._id
  ├── tareas.materia_id ← tareas._id
  └── comentarios.materia_id ← comentarios._id

notas
  ├── estudiante_id → estudiantes._id
  ├── materia_id → materias._id
  └── profesor_id → profesores._id

asistencias
  ├── estudiante_id → estudiantes._id
  ├── materia_id → materias._id
  └── profesor_id → profesores._id

pagos
  └── estudiante_id → estudiantes._id

horarios
  ├── materia_id → materias._id
  └── profesor_id → profesores._id

tareas
  ├── materia_id → materias._id
  └── profesor_id → profesores._id

notificaciones
  └── usuario_id → usuarios._id

comentarios
  ├── estudiante_id → estudiantes._id
  ├── profesor_id → profesores._id
  └── materia_id → materias._id
```

---

## Consultas Comunes

### Obtener calificaciones de un estudiante
```javascript
db.notas.find({ "estudiante_id": ObjectId(...) })
```

### Obtener materias de un grado
```javascript
db.materias.find({ "grado": "1" })
```

### Obtener asistencia de un estudiante en una materia
```javascript
db.asistencias.find({ 
  "estudiante_id": ObjectId(...),
  "materia_id": ObjectId(...)
})
```

### Obtener pagos pendientes de un estudiante
```javascript
db.pagos.find({
  "estudiante_id": ObjectId(...),
  "estado": "pendiente"
})
```

### Obtener horario de un día
```javascript
db.horarios.find({ "dia": "lunes" })
```

---

## Reglas de Integridad

1. **Email único**: Cada usuario y profesor tiene email único
2. **Matrícula única**: Cada estudiante tiene número único
3. **Sin duplicados**: Asistencias por estudiante+materia+fecha son únicas
4. **Cascada**: Al eliminar usuario, se debe actualizar referencias
5. **Validación**: Todas las referencias deben ser ObjectId válidos

---

## Tips de Performance

1. **Índices**: Están creados automáticamente por seed.php
2. **Agregaciones**: Usar pipeline de MongoDB para reportes
3. **Proyecciones**: Seleccionar solo campos necesarios
4. **Paginación**: Usar `limit()` y `skip()` para grandes datasets
5. **Caching**: Considerar Redis para datos frecuentes

---

## Versión de Schema

- **Versión**: 1.0
- **Última actualización**: 2024-01-02
- **Compatibilidad**: MongoDB 4.4+
