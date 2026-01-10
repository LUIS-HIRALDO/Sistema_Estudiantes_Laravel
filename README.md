# Sistema de Gestión Académica

Sistema completo para gestionar todos los datos de un colegio/escuela desarrollado con Laravel 12.

## Características

- 🔐 **Sistema de Login**: Acceso seguro con redirección automática desde la página principal
- 👨‍🎓 **Gestión de Estudiantes**: CRUD completo para estudiantes con información detallada
- 👨‍🏫 **Gestión de Profesores**: Administración de personal docente
- 📚 **Gestión de Cursos**: Organización de cursos por grado y sección
- 📖 **Gestión de Materias**: Asignación de materias a cursos y profesores
- 📊 **Sistema de Calificaciones**: Registro y seguimiento de calificaciones por período

## Estructura de Base de Datos

### Estudiantes
- Información personal (nombre, apellido, email, teléfono)
- Datos de nacimiento y género
- Información de contacto de emergencia (padres)
- Estado del estudiante (Activo, Inactivo, Graduado)

### Profesores
- Información personal y de contacto
- Especialidad y fecha de contratación
- Estado (Activo, Inactivo)

### Cursos
- Código, nombre, grado y sección
- Año escolar y capacidad
- Estado del curso

### Materias
- Código y nombre de la materia
- Asignación de profesor
- Créditos y descripción
- Relación con cursos

### Calificaciones
- Registro de calificaciones por estudiante y materia
- Períodos académicos (parciales, bimestres, etc.)
- Observaciones y comentarios

## Instalación

1. Clonar el repositorio
2. Instalar dependencias:
   ```bash
   composer install
   npm install
   ```

3. Configurar el archivo `.env`:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configurar la base de datos en `.env`

5. Ejecutar migraciones:
   ```bash
   php artisan migrate
   ```

6. Iniciar el servidor de desarrollo:
   ```bash
   php artisan serve
   ```

## Uso

- La URL raíz `/` redirige automáticamente a `/login`
- Acceso al sistema a través de la página de login
- Panel de administración para gestionar estudiantes, profesores, cursos y calificaciones

## Requisitos del Sistema

- PHP 8.2 o superior
- Composer
- MySQL/MariaDB o PostgreSQL
- Node.js y NPM (para assets)

## Tecnologías Utilizadas

- **Backend**: Laravel 12
- **Base de Datos**: MySQL/PostgreSQL
- **Frontend**: Blade Templates, CSS
- **Autenticación**: Laravel Auth
