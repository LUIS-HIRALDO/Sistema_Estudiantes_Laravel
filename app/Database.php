<?php

namespace App;

class Database
{
    private static $instance = null;
    public $pdo;

    private function __construct()
    {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? 3306;
        $database = $_ENV['DB_DATABASE'] ?? 'sistema_estudiantes';
        $username = $_ENV['DB_USERNAME'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? '';

        // Primero conectar sin especificar BD para crearla
        $dsn_temp = "mysql:host={$host};port={$port};charset=utf8mb4";
        
        try {
            $conn_temp = new \PDO($dsn_temp, $username, $password);
            $conn_temp->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // Crear BD si no existe
            $conn_temp->exec("CREATE DATABASE IF NOT EXISTS {$database}");
            $conn_temp = null;
            
            // Ahora conectar a la BD específica
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $this->pdo = new \PDO($dsn, $username, $password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $e) {
            die("Error conectando a MySQL: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    public function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function insert($table, $data)
    {
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO {$table} (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
        
        return $this->query($sql, $values);
    }

    public function update($table, $data, $where)
    {
        $set = [];
        $values = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = ?";
            $values[] = $value;
        }
        
        $whereClauses = [];
        foreach ($where as $key => $value) {
            $whereClauses[] = "{$key} = ?";
            $values[] = $value;
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE " . implode(' AND ', $whereClauses);
        
        return $this->query($sql, $values);
    }

    public function delete($table, $where)
    {
        $whereClauses = [];
        $values = [];
        foreach ($where as $key => $value) {
            $whereClauses[] = "{$key} = ?";
            $values[] = $value;
        }
        
        $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $whereClauses);
        
        return $this->query($sql, $values);
    }

    public function select($table, $columns = '*', $where = null)
    {
        $sql = "SELECT {$columns} FROM {$table}";
        $values = [];
        
        if ($where) {
            $whereClauses = [];
            foreach ($where as $key => $value) {
                $whereClauses[] = "{$key} = ?";
                $values[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        return $this->query($sql, $values);
    }

    public function createTables()
    {
        // Crear tabla usuarios
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS usuarios (
                id INT PRIMARY KEY AUTO_INCREMENT,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                nombre VARCHAR(100) NOT NULL,
                apellido VARCHAR(100) NOT NULL,
                rol VARCHAR(50) DEFAULT 'estudiante',
                estado VARCHAR(50) DEFAULT 'activo',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        // Crear tabla roles
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS roles (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nombre VARCHAR(100) NOT NULL UNIQUE,
                descripcion TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Crear tabla estudiantes
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS estudiantes (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nombre VARCHAR(100) NOT NULL,
                apellido VARCHAR(100) NOT NULL,
                email VARCHAR(255),
                grado VARCHAR(10),
                seccion VARCHAR(10),
                matricula VARCHAR(100) UNIQUE,
                usuario_id INT UNIQUE,
                estado VARCHAR(50) DEFAULT 'activo',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        // Crear tabla profesores
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS profesores (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nombre VARCHAR(100) NOT NULL,
                apellido VARCHAR(100) NOT NULL,
                email VARCHAR(255),
                especialidad VARCHAR(100),
                usuario_id INT UNIQUE,
                estado VARCHAR(50) DEFAULT 'activo',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        // Crear tabla materias
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS materias (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nombre VARCHAR(100) NOT NULL,
                codigo VARCHAR(50) UNIQUE,
                grado VARCHAR(10),
                creditos INT,
                profesor_id INT,
                estado VARCHAR(50) DEFAULT 'activo',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        // Crear tabla notas
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS notas (
                id INT PRIMARY KEY AUTO_INCREMENT,
                estudiante_id INT,
                materia_id INT,
                profesor_id INT,
                valor DECIMAL(5,2),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        // Crear tabla asistencias
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS asistencias (
                id INT PRIMARY KEY AUTO_INCREMENT,
                estudiante_id INT,
                materia_id INT,
                profesor_id INT,
                fecha DATE,
                estado VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_asistencia (estudiante_id, materia_id, fecha)
            )
        ");

        // Crear tabla pagos
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS pagos (
                id INT PRIMARY KEY AUTO_INCREMENT,
                estudiante_id INT,
                monto DECIMAL(10,2),
                concepto VARCHAR(100),
                fecha_pago DATE,
                estado VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        // Crear tabla horarios
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS horarios (
                id INT PRIMARY KEY AUTO_INCREMENT,
                materia_id INT,
                profesor_id INT,
                dia VARCHAR(20),
                hora_inicio TIME,
                hora_fin TIME,
                salon VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        // Crear tabla tareas
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS tareas (
                id INT PRIMARY KEY AUTO_INCREMENT,
                titulo VARCHAR(255) NOT NULL,
                descripcion TEXT,
                materia_id INT,
                profesor_id INT,
                fecha_vencimiento DATE,
                estado VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        // Crear tabla notificaciones
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS notificaciones (
                id INT PRIMARY KEY AUTO_INCREMENT,
                usuario_id INT,
                titulo VARCHAR(255),
                mensaje TEXT,
                tipo VARCHAR(50),
                leida BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        // Crear tabla comentarios
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS comentarios (
                id INT PRIMARY KEY AUTO_INCREMENT,
                estudiante_id INT,
                profesor_id INT,
                materia_id INT,
                contenido TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
    }
}

