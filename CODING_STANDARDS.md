# 📐 Guía de Estilo y Buenas Prácticas

## Convenciones de Código

### Nombres (Naming Conventions)

**Clases**: PascalCase
```php
class StudentController { }
class User { }
class ApiException { }
```

**Métodos y funciones**: camelCase
```php
public function getStudents() { }
private function validateEmail() { }
```

**Constantes**: UPPER_SNAKE_CASE
```php
const MAX_LOGIN_ATTEMPTS = 5;
const DEFAULT_TIMEZONE = 'America/Santo_Domingo';
```

**Variables**: camelCase
```php
$studentName = '';
$isAuthenticated = false;
```

**Propiedades privadas**: Prefijo _ (opcional)
```php
private $_config = [];
private $email;
```

---

## Estructura de Directorios

```
app/
├── Config.php              # Configuración centralizada
├── Database.php            # Conexión a MongoDB
├── Logger.php              # Sistema de logging
├── Response.php            # Respuestas JSON
├── Router.php              # Enrutador
├── Validator.php           # Validación
├── helpers.php             # Funciones globales
├── Controllers/            # Controladores
│   ├── Controller.php      # Base controller
│   ├── AuthController.php
│   ├── StudentController.php
│   └── ...
├── Middleware/             # Middleware
│   ├── AuthMiddleware.php
│   └── VerifyCsrfToken.php
├── Models/                 # Modelos de datos
│   ├── Model.php           # Base model
│   ├── User.php
│   ├── Student.php
│   └── ...
├── Exceptions/             # Excepciones personalizadas
│   └── ApiException.php
└── Utils/                  # Utilidades
    └── Helpers.php         # Helpers: Date, String, Array
```

---

## Patrones de Código

### Controlador Ejemplo
```php
<?php

namespace App\Controllers;

use App\Models\Student;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Validator;

class StudentController extends Controller
{
    // Listar todos
    public function index()
    {
        $students = Student::all();
        return response()->json($students, 200);
    }

    // Obtener uno
    public function show($id)
    {
        $student = Student::find($id);
        
        if (!$student) {
            throw new NotFoundException("Estudiante no encontrado");
        }

        return response()->json($student->toArray(), 200);
    }

    // Crear
    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        $validator = Validator::make($data, [
            'nombre' => 'required|min:3',
            'apellido' => 'required|min:3',
            'email' => 'required|email',
            'grado' => 'required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException('Validación fallida', $validator->errors());
        }

        $student = Student::create($data);

        return response()->json([
            'message' => 'Estudiante creado exitosamente',
            'data' => $student->toArray()
        ], 201);
    }

    // Actualizar
    public function update($id)
    {
        $student = Student::find($id);
        
        if (!$student) {
            throw new NotFoundException("Estudiante no encontrado");
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $student->fill($data)->save();

        return response()->json([
            'message' => 'Estudiante actualizado exitosamente',
            'data' => $student->toArray()
        ], 200);
    }

    // Eliminar
    public function destroy($id)
    {
        $student = Student::find($id);
        
        if (!$student) {
            throw new NotFoundException("Estudiante no encontrado");
        }

        $student->delete();

        return response()->json([
            'message' => 'Estudiante eliminado exitosamente'
        ], 200);
    }
}
```

### Modelo Ejemplo
```php
<?php

namespace App\Models;

class Student extends Model
{
    protected $collectionName = 'students';
    
    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'grado',
        'seccion',
        'estado'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->attributes['estado'] = $this->attributes['estado'] ?? 'activo';
    }

    // Métodos personalizados
    public function getFullName()
    {
        return "{$this->nombre} {$this->apellido}";
    }

    public function isActive()
    {
        return $this->estado === 'activo';
    }
}
```

---

## Mejores Prácticas

### 1. Manejo de Excepciones
```php
try {
    // Operación
} catch (NotFoundException $e) {
    return response()->json($e->render(), $e->getStatusCode());
} catch (\Exception $e) {
    Logger::exception($e);
    return response()->json(['error' => 'Error interno'], 500);
}
```

### 2. Validación de Entrada
```php
$validator = Validator::make($_POST, [
    'email' => 'required|email',
    'password' => 'required|min:8',
]);

if ($validator->fails()) {
    return response()->json(['errors' => $validator->errors()], 422);
}
```

### 3. Autenticación
```php
// Verificar autenticación
if (!isAuthenticated()) {
    return response()->json(['error' => 'No autorizado'], 401);
}

// Obtener usuario actual
$user = getAuthUser();
```

### 4. Logging
```php
Logger::info("Usuario {$user->email} inició sesión");
Logger::error("Error de conexión a BD");
Logger::debug("Query ejecutada", $queryData);
Logger::exception($exception);
```

### 5. Respuestas JSON
```php
// Éxito con datos
return response()->json([
    'message' => 'Operación exitosa',
    'data' => $data
], 200);

// Error con detalles
return response()->json([
    'error' => 'Operación fallida',
    'details' => $details
], 400);
```

---

## Docstrings y Comentarios

### Método con Docstring
```php
/**
 * Obtiene un estudiante por su ID
 *
 * @param string $id ID del estudiante
 * @return Student|null Instancia del estudiante o null
 * @throws NotFoundException Si no existe el estudiante
 */
public function getStudent($id)
{
    $student = Student::find($id);
    
    if (!$student) {
        throw new NotFoundException("Estudiante no encontrado");
    }

    return $student;
}
```

### Clase con Docstring
```php
/**
 * Controlador de Estudiantes
 * 
 * Maneja las operaciones CRUD para estudiantes
 * 
 * @package App\Controllers
 */
class StudentController extends Controller
{
    // ...
}
```

---

## Validación de Datos

```php
$rules = [
    'nombre' => 'required|min:3|max:100',
    'email' => 'required|email',
    'edad' => 'required|numeric|min:18|max:120',
    'rol' => 'in:admin,profesor,estudiante',
    'foto' => 'nullable|url',
];

$validator = Validator::make($data, $rules);
```

---

## Errores y Excepciones

### Lanzar Excepciones
```php
// No encontrado
throw new NotFoundException("El recurso no existe");

// No autorizado
throw new UnauthorizedException("Credenciales inválidas");

// Validación fallida
throw new ValidationException("Datos inválidos", $errors);

// Conflicto (ej: email duplicado)
throw new ConflictException("El email ya está registrado");

// Error interno
throw new InternalServerException("Error procesando solicitud");
```

---

## Utilidades Comunes

### String Helper
```php
StringHelper::slug("Nombre del Artículo")  // "nombre-del-articulo"
StringHelper::camelCase("user_name")       // "userName"
StringHelper::snakeCase("userName")        // "user_name"
StringHelper::truncate("Long text...", 20) // "Long text......"
```

### Array Helper
```php
ArrayHelper::only($data, ['id', 'name'])   // Solo esas claves
ArrayHelper::except($data, ['password'])   // Menos password
ArrayHelper::get($data, 'user.name')       // Acceso profundo
ArrayHelper::pluck($users, 'email')        // Array de emails
```

### Date Helper
```php
DateHelper::now()                          // Timestamp actual
DateHelper::parse("2024-01-15")            // Convertir fecha
DateHelper::format($date, 'Y-m-d H:i:s')  // Formatear
```

---

## Testing de Endpoints

### Usar cURL
```bash
# GET
curl http://localhost:8000/api/students

# POST
curl -X POST http://localhost:8000/api/students \
  -H "Content-Type: application/json" \
  -d '{"nombre":"Juan","apellido":"Pérez"}'

# Con autenticación
curl -H "Authorization: Bearer {token}" \
  http://localhost:8000/api/students
```

### Usar Postman
1. Importar colección de requests
2. Configurar variables de entorno
3. Ejecutar requests secuencialmente

---

## Performance

### Optimizaciones
- Usar índices en campos frecuentes
- Limitar fields en proyecciones
- Paginar resultados grandes
- Cachear datos estáticos
- Usar agregaciones MongoDB

### Ejemplo de Paginación
```php
$page = $_GET['page'] ?? 1;
$limit = $_GET['limit'] ?? 10;
$skip = ($page - 1) * $limit;

$students = Student::all()
    ->slice($skip, $limit);
```

---

## Seguridad

### Buenas Prácticas
- ✅ Hash passwords con bcrypt
- ✅ Validar siempre entrada
- ✅ Usar JWT para API
- ✅ HTTPS en producción
- ✅ Sanitizar output
- ✅ Usar prepared statements
- ✅ Implementar rate limiting
- ✅ Logging de eventos críticos

### Ejemplo Seguro
```php
$password = password_hash($raw_password, PASSWORD_BCRYPT);

if (!password_verify($input, $password)) {
    Logger::warning("Intento de login fallido");
    throw new UnauthorizedException("Credenciales inválidas");
}
```

---

## Checklist de Código

Antes de hacer commit:

- [ ] Código sigue las convenciones de nombres
- [ ] Métodos tienen máximo 30 líneas
- [ ] Clases tienen responsabilidad única
- [ ] Hay validación de entrada
- [ ] Hay manejo de excepciones
- [ ] Código está comentado/documentado
- [ ] Sin variables no usadas
- [ ] Sin código comentado muerto
- [ ] Sin console.log() o var_dump()
- [ ] Tests pasan (si existen)

---

## Referencias

- [PSR-12: Guía de estilo PHP](https://www.php-fig.org/psr/psr-12/)
- [MongoDB Best Practices](https://docs.mongodb.com/manual/reference/limits-and-thresholds/)
- [OWASP Security Guidelines](https://owasp.org/)

---

**Versión**: 1.0  
**Última actualización**: 2024-01-02
