# 🚀 Guía de Instalación - Sistema de Gestión Escolar

## Requisitos Previos

- **PHP 8.2** o superior
- **MySQL** 5.7 o superior (o MariaDB 10.3+)
- **XAMPP** (recomendado para desarrollo rápido)
- **Composer** (opcional)
- **Git** (opcional)

## Instalación Paso a Paso

### 1. Clonar o Descargar el Proyecto

```bash
# Si usas Git
git clone https://github.com/usuario/Sistema_Estudiantes_Laravel.git
cd Sistema_Estudiantes_Laravel

# O descarga el archivo .zip y extrae en tu carpeta
```

### 2. Verificar MySQL/XAMPP

Asegúrate de que MySQL esté ejecutándose:

**Windows (XAMPP):**
```bash
# Inicia XAMPP Control Panel
# Haz clic en "Start" para Apache y MySQL
```

**Linux/Mac:**
```bash
# Inicia MySQL
brew services start mysql

# O
sudo systemctl start mongod
```

**Verificar conexión:**
```bash
# Abre otra terminal/consola
mysql

# Deberías ver el cliente MySQL
```

### 3. Configurar Variables de Entorno

Copia el archivo `.env.example` a `.env`:

```bash
# Windows
copy .env.example .env

# Linux/Mac
cp .env.example .env
```

Edita el archivo `.env` con tus valores:

```env
APP_NAME="Sistema Estudiantes"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=sistema_estudiantes
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=tu_clave_secreta_muy_segura

ADMIN_EMAIL=admin@escuela.com
ADMIN_PASSWORD=admin123
```

### 4. Instalar Dependencias de PHP

```bash
# Con Composer (recomendado)
composer install

# O si no tienes Composer instalado, descarga las dependencias manualmente
# Las dependencias ya están en la carpeta vendor/
```

### 5. Inicializar la Base de Datos

Ejecuta el script de inicialización para crear datos por defecto:

```bash
# Windows
php scripts\seed.php

# Linux/Mac
php scripts/seed.php
```

Deberías ver un mensaje como:
```
Inicializando base de datos...
✓ Índices creados
✓ Roles creados
✓ Usuario administrador creado
✓ Profesores creados
✓ Materias creadas
✓ Estudiantes creados

✅ Base de datos inicializada correctamente

Credenciales:
  Email: admin@escuela.com
  Contraseña: admin123
```

### 6. Iniciar el Servidor

```bash
# Opción 1: Usando PHP built-in server
php -S localhost:8000 -t public

# Opción 2: Usando composer script
composer start

# Opción 3: En background (Windows)
start php -S localhost:8000 -t public

# Opción 3: En background (Linux/Mac)
php -S localhost:8000 -t public &
```

El servidor estará disponible en: **http://localhost:8000**

## Verificar Instalación

1. **Abre tu navegador** y ve a: `http://localhost:8000`
2. Deberías ver la página de inicio
3. Haz clic en "Login" o accede a `http://localhost:8000/login.html`
4. Usa las credenciales:
   - **Email**: admin@escuela.com
   - **Contraseña**: admin123

## Estructura de Carpetas

```
Sistema_Estudiantes_Laravel/
├── app/                          # Código de la aplicación
│   ├── Controllers/              # Controladores
│   ├── Middleware/               # Middleware de autenticación
│   ├── Models/                   # Modelos de datos
│   ├── Config.php               # Configuración centralizada
│   ├── Database.php             # Conexión a MySQL
│   ├── Response.php             # Manejador de respuestas
│   ├── Router.php               # Enrutador
│   └── helpers.php              # Funciones auxiliares
├── config/                       # Archivos de configuración
│   ├── app.php                  # Configuración de la app
│   ├── auth.php                 # Configuración de autenticación
│   └── database.php             # Configuración de BD
├── public/                       # Acceso público (raíz del servidor)
│   ├── index.php                # Punto de entrada
│   ├── index.html               # Página de inicio
│   ├── login.html               # Página de login
│   ├── dashboard.html           # Panel de control
│   ├── estudiantes.html         # Gestión de estudiantes
│   ├── css/                     # Estilos
│   └── js/                      # Scripts JavaScript
├── resources/
│   └── views/                   # Vistas/plantillas
├── routes/                      # Definiciones de rutas (si existen)
├── scripts/
│   ├── seed.php                # Script de inicialización
│   └── inicializar.php          # Inicialización alternativa
├── vendor/                      # Dependencias (generadas por Composer)
├── .env                         # Variables de entorno (crear)
├── .env.example                 # Template de variables de entorno
├── .gitignore                   # Archivos a ignorar en Git
├── composer.json                # Dependencias de PHP
├── API.md                       # Documentación de API
├── INSTALL.md                   # Esta guía
└── README.md                    # Información general

```

## Troubleshooting

### Error: "MySQL connection refused"

**Solución:**
- Verifica que MySQL esté ejecutándose (XAMPP Control Panel)
- Comprueba que el host y puerto sean correctos en `.env`
- Usa `mysql -u root -p` para verificar la conexión

### Error: "Class not found"

**Solución:**
- Ejecuta: `composer dump-autoload`
- Limpia el cache de PHP si lo hay

### Error: "Permission denied" en scripts

**Solución:**
```bash
# Linux/Mac
chmod +x scripts/seed.php
php scripts/seed.php
```

### La página de login no funciona

**Solución:**
- Asegúrate de estar en `http://localhost:8000/login.html`
- Verifica que la base de datos esté inicializada
- Abre la consola del navegador (F12) para ver errores

### Error en las peticiones AJAX

**Solución:**
- Verifica que el servidor esté corriendo
- Comprueba los headers de CORS
- Revisa la consola del navegador para más detalles

## Primeros Pasos

### 1. Iniciar Sesión
- Ve a http://localhost:8000/login.html
- Email: `admin@escuela.com`
- Contraseña: `admin123`

### 2. Cambiar Contraseña del Admin
- Después de iniciar sesión, ve a perfil
- Cambia tu contraseña por seguridad

### 3. Crear Nuevos Usuarios
- Como administrador, ve a la sección de usuarios
- Crea nuevos profesores, estudiantes o acudientes

### 4. Explorar el Sistema
- Familiarízate con el dashboard
- Crea materias, horarios, calificaciones, etc.

## Documentación Adicional

- **API.md**: Documentación completa de todos los endpoints
- **README.md**: Información general del proyecto
- **config/**: Configuraciones de la aplicación

## Soporte y Ayuda

Si encuentras problemas:

1. **Revisa los logs**: Abre la consola del navegador (F12)
2. **Verifica la conexión a MySQL**: Usa phpMyAdmin o el cliente de MySQL
3. **Consulta la documentación**: Lee los archivos README.md y API.md
4. **Prueba los endpoints**: Usa Postman o Insomnia para probar la API

## Próximos Pasos

1. Customiza los estilos en `public/css/style.css`
2. Modifica los HTML en `public/` según tus necesidades
3. Agrega más funcionalidades en los Controllers
4. Configura tu dominio en `.env` (APP_URL)
5. Deploy en un servidor de producción

¡Buena suerte! 🎉
