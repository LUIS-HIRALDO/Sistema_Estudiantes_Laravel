# 📚 API Documentation - Sistema de Gestión Escolar

## Base URL
```
http://localhost:8000/api
```

## Autenticación
Todos los endpoints protegidos requieren un token JWT en el header:
```
Authorization: Bearer {token}
```

---

## 🔐 Autenticación (Auth)

### Registrar Usuario
```http
POST /auth/register
Content-Type: application/json

{
  "email": "usuario@escuela.com",
  "password": "password123",
  "nombre": "Juan",
  "apellido": "Pérez",
  "rol_id": "rol_id_opcional"
}
```

### Iniciar Sesión
```http
POST /auth/login
Content-Type: application/json

{
  "email": "admin@escuela.com",
  "password": "admin123"
}
```

Respuesta:
```json
{
  "message": "Login exitoso",
  "token": "eyJhbGc...",
  "usuario": {
    "id": "....",
    "email": "admin@escuela.com",
    "nombre": "Administrador",
    "apellido": "Sistema"
  }
}
```

### Obtener Perfil
```http
GET /auth/profile
Authorization: Bearer {token}
```

---

## 👥 Estudiantes

### Listar Estudiantes
```http
GET /estudiantes
```

### Crear Estudiante
```http
POST /estudiantes
Content-Type: application/json

{
  "nombre": "Pedro",
  "apellido": "García",
  "email": "pedro@escuela.com",
  "grado": "1",
  "seccion": "A",
  "matricula": "MAT001"
}
```

### Obtener Estudiante
```http
GET /estudiantes/{id}
```

### Actualizar Estudiante
```http
PUT /estudiantes/{id}
Content-Type: application/json

{
  "nombre": "Pedro",
  "apellido": "García",
  "grado": "2"
}
```

### Eliminar Estudiante
```http
DELETE /estudiantes/{id}
```

### Listar por Grado
```http
GET /estudiantes/grado/{grado}
```

---

## 👨‍🏫 Profesores

### Listar Profesores
```http
GET /profesores
```

### Crear Profesor
```http
POST /profesores
Content-Type: application/json

{
  "nombre": "María",
  "apellido": "López",
  "email": "maria@escuela.com",
  "especialidad": "Matemáticas",
  "telefono": "1234567890"
}
```

### Obtener Profesor
```http
GET /profesores/{id}
```

### Actualizar Profesor
```http
PUT /profesores/{id}
Content-Type: application/json

{
  "especialidad": "Física"
}
```

### Eliminar Profesor
```http
DELETE /profesores/{id}
```

### Profesores con Materias
```http
GET /profesores/con-materias
```

---

## 📖 Materias

### Listar Materias
```http
GET /materias
```

### Crear Materia
```http
POST /materias
Content-Type: application/json

{
  "nombre": "Matemáticas",
  "codigo": "MAT101",
  "grado": "1",
  "creditos": 3
}
```

### Obtener Materia
```http
GET /materias/{id}
```

### Actualizar Materia
```http
PUT /materias/{id}
Content-Type: application/json

{
  "nombre": "Matemáticas Avanzada"
}
```

### Eliminar Materia
```http
DELETE /materias/{id}
```

### Materias por Grado
```http
GET /materias/grado/{grado}
```

### Asignar Profesor a Materia
```http
PUT /materias/{id}/profesor
Content-Type: application/json

{
  "profesor_id": "profesor_id"
}
```

---

## 📝 Notas

### Listar Notas
```http
GET /notas
```

### Crear Nota
```http
POST /notas
Content-Type: application/json

{
  "estudiante_id": "student_id",
  "materia_id": "subject_id",
  "valor": 95,
  "fecha": "2024-01-15"
}
```

### Obtener Nota
```http
GET /notas/{id}
```

### Actualizar Nota
```http
PUT /notas/{id}
Content-Type: application/json

{
  "valor": 98
}
```

### Eliminar Nota
```http
DELETE /notas/{id}
```

### Notas por Estudiante
```http
GET /notas/estudiante/{estudianteId}
```

### Notas por Materia
```http
GET /notas/materia/{materiaId}
```

### Estadísticas de Notas
```http
GET /notas/estadisticas
```

---

## ✓ Asistencias

### Listar Asistencias
```http
GET /asistencias
```

### Registrar Asistencia
```http
POST /asistencias
Content-Type: application/json

{
  "estudiante_id": "student_id",
  "materia_id": "subject_id",
  "estado": "presente",
  "fecha": "2024-01-15"
}
```

### Obtener Asistencia
```http
GET /asistencias/{id}
```

### Actualizar Asistencia
```http
PUT /asistencias/{id}
Content-Type: application/json

{
  "estado": "ausente"
}
```

### Eliminar Asistencia
```http
DELETE /asistencias/{id}
```

### Asistencias por Estudiante
```http
GET /asistencias/estudiante/{estudianteId}
```

### Asistencias por Materia
```http
GET /asistencias/materia/{materiaId}
```

### Porcentaje de Asistencia
```http
GET /asistencias/porcentaje/{estudianteId}/{materiaId}
```

---

## 💰 Pagos

### Listar Pagos
```http
GET /pagos
```

### Crear Pago
```http
POST /pagos
Content-Type: application/json

{
  "estudiante_id": "student_id",
  "monto": 50000,
  "concepto": "Matrícula",
  "fecha_pago": "2024-01-15",
  "estado": "pagado"
}
```

### Obtener Pago
```http
GET /pagos/{id}
```

### Actualizar Pago
```http
PUT /pagos/{id}
Content-Type: application/json

{
  "estado": "pendiente"
}
```

### Eliminar Pago
```http
DELETE /pagos/{id}
```

### Pagos por Estudiante
```http
GET /pagos/estudiante/{estudianteId}
```

### Pagos por Estado
```http
GET /pagos/estado/{estado}
```

### Estadísticas de Pagos
```http
GET /pagos/estadisticas
```

---

## 📅 Horarios

### Listar Horarios
```http
GET /horarios
```

### Crear Horario
```http
POST /horarios
Content-Type: application/json

{
  "materia_id": "subject_id",
  "dia": "lunes",
  "hora_inicio": "08:00",
  "hora_fin": "10:00",
  "aula": "101"
}
```

### Obtener Horario
```http
GET /horarios/{id}
```

### Actualizar Horario
```http
PUT /horarios/{id}
Content-Type: application/json

{
  "hora_inicio": "09:00"
}
```

### Eliminar Horario
```http
DELETE /horarios/{id}
```

### Horarios por Materia
```http
GET /horarios/materia/{materiaId}
```

### Horarios por Día
```http
GET /horarios/dia/{dia}
```

---

## 📋 Tareas

### Listar Tareas
```http
GET /tareas
```

### Crear Tarea
```http
POST /tareas
Content-Type: application/json

{
  "materia_id": "subject_id",
  "titulo": "Ejercicios de matemáticas",
  "descripcion": "Resolver páginas 45-67",
  "fecha_entrega": "2024-01-20"
}
```

### Obtener Tarea
```http
GET /tareas/{id}
```

### Actualizar Tarea
```http
PUT /tareas/{id}
Content-Type: application/json

{
  "estado": "completada"
}
```

### Eliminar Tarea
```http
DELETE /tareas/{id}
```

### Tareas Pendientes
```http
GET /tareas/pendientes
```

### Tareas por Materia
```http
GET /tareas/materia/{materiaId}
```

### Marcar Tarea como Completa
```http
PUT /tareas/{id}/completar
```

---

## 🔔 Notificaciones

### Listar Notificaciones
```http
GET /notificaciones
```

### Crear Notificación
```http
POST /notificaciones
Content-Type: application/json

{
  "usuario_id": "user_id",
  "titulo": "Nueva tarea",
  "mensaje": "Se ha asignado una nueva tarea",
  "tipo": "tarea"
}
```

### Obtener Notificación
```http
GET /notificaciones/{id}
```

### Actualizar Notificación
```http
PUT /notificaciones/{id}
Content-Type: application/json

{
  "leida": true
}
```

### Eliminar Notificación
```http
DELETE /notificaciones/{id}
```

### Notificaciones por Usuario
```http
GET /notificaciones/usuario/{usuarioId}
```

### Notificaciones No Leídas
```http
GET /notificaciones/usuario/{usuarioId}/no-leidas
```

### Marcar como Leída
```http
PUT /notificaciones/{id}/leida
```

---

## 💬 Comentarios

### Listar Comentarios
```http
GET /comentarios
```

### Crear Comentario
```http
POST /comentarios
Content-Type: application/json

{
  "estudiante_id": "student_id",
  "profesor_id": "teacher_id",
  "materia_id": "subject_id",
  "contenido": "Buen desempeño en clase"
}
```

### Obtener Comentario
```http
GET /comentarios/{id}
```

### Actualizar Comentario
```http
PUT /comentarios/{id}
Content-Type: application/json

{
  "contenido": "Necesita mejorar"
}
```

### Eliminar Comentario
```http
DELETE /comentarios/{id}
```

### Comentarios por Estudiante
```http
GET /comentarios/estudiante/{estudianteId}
```

### Comentarios por Profesor
```http
GET /comentarios/profesor/{profesorId}
```

### Comentarios por Materia
```http
GET /comentarios/materia/{materiaId}
```

---

## 🎭 Roles

### Listar Roles
```http
GET /roles
```

### Crear Rol
```http
POST /roles
Content-Type: application/json

{
  "nombre": "Coordinador",
  "descripcion": "Coordinador académico",
  "permisos": ["read", "write"]
}
```

### Obtener Rol
```http
GET /roles/{id}
```

### Actualizar Rol
```http
PUT /roles/{id}
Content-Type: application/json

{
  "permisos": ["read", "write", "delete"]
}
```

### Eliminar Rol
```http
DELETE /roles/{id}
```

---

## Estados HTTP Comunes

- `200` - OK
- `201` - Creado
- `400` - Solicitud inválida
- `401` - No autorizado
- `404` - No encontrado
- `405` - Método no permitido
- `500` - Error del servidor

---

## Campos de Paginación (cuando aplique)

```
GET /endpoint?page=1&limit=10&sort=nombre&order=asc
```
