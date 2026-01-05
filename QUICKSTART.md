# ⚡ Quick Start - Inicio Rápido

## 5 Minutos para tener el sistema funcionando

### Paso 1: Variables de Entorno (30 segundos)
```bash
cd Sistema_Estudiantes_Laravel
copy .env.example .env
```

### Paso 2: Asegurar MySQL Ejecutándose (30 segundos)
**Windows (XAMPP):**
```bash
# Abre XAMPP Control Panel y haz clic en "Start" para MySQL
```

**Linux/Mac:**
```bash
brew services start mysql
```

### Paso 3: Instalar Dependencias (30 segundos, opcional)
```bash
composer install
```

### Paso 4: Inicializar Base de Datos (1 minuto)
```bash
php scripts/seed.php
```

Deberías ver:
```
✅ Base de datos inicializada correctamente

Credenciales:
  Email: admin@escuela.com
  Contraseña: admin123
```

### Paso 5: Iniciar Servidor (30 segundos)
```bash
php -S localhost:8000 -t public
```

### ✅ ¡Listo!

Accede a: **http://localhost:8000**

---

## Primeras Acciones

1. **Login**: Usa admin@escuela.com / admin123
2. **Dashboard**: Explora el panel de control
3. **Datos de Ejemplo**: Ya hay profesores, materias y estudiantes
4. **API**: Documenta en `API.md`

---

## Problemas Comunes

| Problema | Solución |
|----------|----------|
| MySQL connection refused | Inicia MySQL en XAMPP Control Panel |
| Port 8000 en uso | Usa otro: `php -S localhost:8001 -t public` |
| Class not found | Ejecuta: `composer dump-autoload` |
| Permission denied | Usa: `php scripts/seed.php` (no es ejecutable) |

---

## Documentación Rápida

- **API**: `API.md` - Todos los endpoints
- **Instalación**: `INSTALL.md` - Guía completa
- **Cambios**: `CAMBIOS.md` - Lo que se agregó
- **General**: `README.md` - Información del proyecto

---

## Próximos Pasos

1. Lee `INSTALL.md` para más detalles
2. Explora `API.md` para ver los endpoints
3. Modifica `public/css/style.css` para estilos personalizados
4. Crea nuevos controladores en `app/Controllers/`

---

¡Disfruta el sistema! 🚀
