# Sistema de Gestión Escolar - PHP + MySQL

Sistema completo de gestión escolar desarrollado en PHP puro con arquitectura similar a Laravel e integración con MySQL.

## 📋 Características

- **12 Módulos Completos**: Estudiantes, Profesores, Materias, Notas, Asistencias, Pagos, Horarios, Tareas, Notificaciones, Comentarios, Roles, Usuarios
- **Autenticación JWT**: Sistema seguro basado en tokens JWT
- **Base de Datos MySQL**: Almacenamiento relacional robusto
- **API RESTful**: 90+ endpoints completamente funcionales
- **Frontend Integrado**: Interfaz web con HTML/CSS/JavaScript vanilla
- **CRUD Completo**: Operaciones de creación, lectura, actualización y eliminación en todos los módulos

## 🚀 Requisitos

- PHP 8.2 o superior
- MySQL 5.7+ (incluido en XAMPP)
- Navegador web moderno

## 📦 Instalación

### 1. Asegurar que MySQL está ejecutándose

**Windows (XAMPP):**
```bash
# Abre XAMPP Control Panel y haz clic en "Start" para MySQL
```

**Linux/Mac:**
```bash
# O ejecutar con Homebrew
brew services start mysql
```

### 2. Configurar Variables de Entorno

Copia el archivo `.env.example` a `.env`:

```bash
cp .env.example .env
```

Edita `.env` con tus valores:

```env
APP_NAME="Sistema Estudiantes"
APP_URL=http://localhost

DB_CONNECTION=mysql
### 3. Inicializar la Base de Datos

```bash
# Esto crea las tablas e inserta datos de prueba
php scripts/seed.php
```

### 4. Ejecutar el Servidor

```bash
# En la carpeta raíz del proyecto
php -S localhost:8000 -t public
```

El sistema estará disponible en: **http://localhost:8000**

## 🔐 Credenciales Iniciales

Para las pruebas iniciales, usa:

- **Email**: admin@escuela.com
- **Contraseña**: admin123

### Para crear un nuevo usuario:

1. Accede a `http://localhost:8000/Sistema_Estudiantes_Laravel/public/login.html`
2. Haz clic en "Registrarse"
3. Completa los datos requeridos
4. Inicia sesión con tus credenciales

## 📚 Estructura del Proyecto

```
Sistema_Estudiantes_Laravel/
├── app/
│   ├── Models/              # 12 modelos MySQL
│   ├── Controllers/         # 12 controladores
│   ├── Middleware/          # Middleware de autenticación
│   ├── Database.php         # Conexión a MySQL (PDO)
│   ├── Response.php         # Respuestas JSON
│   └── Router.php           # Enrutador simple
├── config/                  # Archivos de configuración
├── database/                # Esquema de BD
├── public/
│   ├── index.php           # Punto de entrada
│   ├── login.html          # Página de inicio sesión
│   ├── dashboard.html      # Panel de control
│   ├── estudiantes.html    # Gestión de estudiantes
│   ├── css/                # Estilos
│   └── js/                 # Scripts JavaScript
│   └── index.html          # Página principal
├── scripts/
│   ├── seed.php            # Inicialización de datos
│   └── test.php            # Pruebas básicas
├── .env                    # Configuración del ambiente
├── .env.example            # Plantilla de configuración
├── composer.json           # Dependencias PHP
└── README.md               # Este archivo
├── resources/
│   └── views/              # Vistas (plantillas)
├── .env                    # Variables de entorno
├── .env.example            # Ejemplo de variables
├── composer.json           # Dependencias
└── README.md               # Este archivo
```

## 🔌 API Endpoints

### Autenticación
- `POST /auth/register` - Registrar usuario
- `POST /auth/login` - Iniciar sesión
- `GET /auth/profile` - Obtener perfil

### Estudiantes
- `GET /estudiantes` - Listar todos
- `POST /estudiantes` - Crear
- `GET /estudiantes/{id}` - Obtener por ID
- `PUT /estudiantes/{id}` - Actualizar
- `DELETE /estudiantes/{id}` - Eliminar
- `GET /estudiantes/grado/{grado}` - Filtrar por grado

### Profesores
- `GET /profesores` - Listar todos
- `POST /profesores` - Crear
- `GET /profesores/{id}` - Obtener por ID
- `PUT /profesores/{id}` - Actualizar
- `DELETE /profesores/{id}` - Eliminar

### Materias
- `GET /materias` - Listar todas
- `POST /materias` - Crear
- `GET /materias/{id}` - Obtener por ID
- `PUT /materias/{id}` - Actualizar
- `DELETE /materias/{id}` - Eliminar
- `GET /materias/grado/{grado}` - Filtrar por grado
- `PUT /materias/{id}/profesor` - Asignar profesor

### Notas
- `GET /notas` - Listar todas
- `POST /notas` - Crear
- `GET /notas/{id}` - Obtener por ID
- `GET /notas/estudiante/{estudianteId}` - Por estudiante
- `GET /notas/materia/{materiaId}` - Por materia
- `GET /notas/estadisticas` - Estadísticas generales

### Asistencias
- `GET /asistencias` - Listar todas
- `POST /asistencias` - Registrar
- `GET /asistencias/estudiante/{estudianteId}` - Por estudiante
- `GET /asistencias/porcentaje/{estudianteId}/{materiaId}` - Porcentaje

### Pagos
- `GET /pagos` - Listar todos
- `POST /pagos` - Registrar
- `GET /pagos/estudiante/{estudianteId}` - Por estudiante
- `GET /pagos/estado/{estado}` - Por estado
- `GET /pagos/estadisticas` - Estadísticas

### Horarios
- `GET /horarios` - Listar todos
- `POST /horarios` - Crear
- `GET /horarios/materia/{materiaId}` - Por materia
- `GET /horarios/dia/{dia}` - Por día

### Tareas
- `GET /tareas` - Listar todas
- `POST /tareas` - Crear
- `GET /tareas/pendientes` - Pendientes
- `GET /tareas/materia/{materiaId}` - Por materia
- `PUT /tareas/{id}/completar` - Marcar como completa

### Notificaciones
- `GET /notificaciones` - Listar todas
- `POST /notificaciones` - Crear
- `GET /notificaciones/usuario/{usuarioId}` - Por usuario
- `PUT /notificaciones/{id}/leida` - Marcar como leída

### Comentarios
- `GET /comentarios` - Listar todos
- `POST /comentarios` - Crear
- `GET /comentarios/estudiante/{estudianteId}` - Por estudiante

### Roles
- `GET /roles` - Listar todos
- `POST /roles` - Crear
- `GET /roles/{id}` - Obtener por ID

## 🧪 Pruebas con cURL

### Registrar usuario
```bash
curl -X POST http://localhost:8000/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "usuario@test.com",
    "nombre": "Juan",
    "apellido": "Pérez",
    "password": "password123"
  }'
```

### Iniciar sesión
```bash
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "usuario@test.com",
    "password": "password123"
  }'
```

### Listar estudiantes
```bash
curl -X GET http://localhost:8000/estudiantes \
  -H "Authorization: Bearer token_aqui"
```

### Crear estudiante
```bash
curl -X POST http://localhost:8000/estudiantes \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer token_aqui" \
  -d '{
    "nombre": "Carlos",
    "apellido": "González",
    "email": "carlos@escuela.com",
    "grado": "10",
    "telefono": "8095551234"
  }'
```

## 📊 Modelo de Datos

### Estudiante
- nombre, apellido, email, telefono
- grado, numero_matricula
- fecha_nacimiento, direccion
- fecha_inscripcion, estado

### Profesor
- nombre, apellido, email, telefono
- especialidad, materia_id
- titulo, fecha_contratacion, estado

### Materia
- nombre, descripcion
- horas_semana, profesor_id
- grado, creditos, estado

### Nota
- estudiante_id, materia_id
- parcial_1, parcial_2, parcial_3
- promedio (calculado automáticamente)

### Asistencia
- estudiante_id, materia_id, fecha
- estado (presente/ausente/tarde)

### Pago
- estudiante_id, concepto, monto
- fecha_pago, metodo, numero_recibo
- estado (pendiente/pagado)

### Horario
- materia_id, profesor_id
- dia, hora_inicio, hora_fin, salon

### Tarea
- titulo, descripcion, materia_id, profesor_id
- fecha_vencimiento, puntos, estado

### Notificación
- usuario_id, titulo, descripcion
- tipo, prioridad, leida

### Comentario
- estudiante_id, profesor_id, materia_id
- contenido, sentimiento

### Rol
- nombre, descripcion, permisos[]

### Usuario
- email, password (hasheada), nombre, apellido
- rol_id, profesor_id, estudiante_id, estado

## 🔒 Seguridad

- **Contraseñas hasheadas** con bcrypt
- **JWT (JSON Web Tokens)** para autenticación
- **CORS habilitado** para acceso desde diferentes orígenes
- **Validación de entrada** en todos los endpoints
- **Índices únicos** para emails
- **Manejo de errores** seguro

## 🐛 Solución de Problemas

### MongoDB no conecta
- Verifica que MongoDB esté corriendo: `mongod --version`
- Confirma que escucha en localhost:27017
- Revisa las variables en `.env`

### Error "Ruta no encontrada"
- Asegúrate de usar las rutas exactas del sistema
- Las mayúsculas/minúsculas sí importan en las rutas

### Token inválido
- Regenera el token iniciando sesión nuevamente
- Verifica que `JWT_SECRET` sea igual en cliente y servidor

## 📝 Notas

- El sistema usa arquitectura similar a Laravel pero sin dependencias externas (excepto MongoDB)
- Todos los datos se almacenan en MongoDB, sin base de datos relacional
- El frontend es vanilla JavaScript, sin frameworks
- Los timestamps se crean automáticamente en `created_at` y `updated_at`

## 📄 Licencia

Proyecto de código abierto para fines educativos.

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature
3. Commit tus cambios
4. Push a la rama
5. Abre un Pull Request

---

**¿Preguntas o problemas?** Contacta al equipo de desarrollo.
