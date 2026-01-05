<?php

namespace App\Models;

use App\Database;
use PDO;

class Model
{
    protected $table;
    protected $fillable = [];
    protected $attributes = [];
    protected $pdo;

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->pdo = Database::getInstance()->pdo;
    }

    public function save()
    {
        if (isset($this->attributes['id']) && !empty($this->attributes['id'])) {
            return $this->update();
        }
        
        $this->attributes['created_at'] = date('Y-m-d H:i:s');
        $this->attributes['updated_at'] = date('Y-m-d H:i:s');
        
        $columns = array_keys($this->attributes);
        $values = array_values($this->attributes);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $this->pdo->prepare($sql);
        
        if ($stmt->execute($values)) {
            $this->attributes['id'] = $this->pdo->lastInsertId();
            return true;
        }
        return false;
    }

    protected function update()
    {
        $id = $this->attributes['id'];
        unset($this->attributes['id']);
        
        $this->attributes['updated_at'] = date('Y-m-d H:i:s');
        
        $updates = [];
        $values = [];
        foreach ($this->attributes as $key => $value) {
            $updates[] = "$key = ?";
            $values[] = $value;
        }
        
        $values[] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(',', $updates) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        
        $result = $stmt->execute($values);
        $this->attributes['id'] = $id;
        return $result;
    }

    public static function create(array $attributes)
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    public static function find($id)
    {
        $model = new static();
        $sql = "SELECT * FROM {$model->table} WHERE id = ? LIMIT 1";
        $stmt = $model->pdo->prepare($sql);
        $stmt->execute([$id]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new static($row);
        }
        return null;
    }

    public static function all()
    {
        $model = new static();
        $sql = "SELECT * FROM {$model->table}";
        $stmt = $model->pdo->prepare($sql);
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = new static($row);
        }
        return $results;
    }

    public static function where($field, $value)
    {
        $model = new static();
        $sql = "SELECT * FROM {$model->table} WHERE $field = ?";
        $stmt = $model->pdo->prepare($sql);
        $stmt->execute([$value]);
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = new static($row);
        }
        return $results;
    }

    public function delete()
    {
        if (!isset($this->attributes['id'])) {
            return false;
        }
        
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$this->attributes['id']]);
    }

    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable) || empty($this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function toArray()
    {
        return $this->attributes;
    }

    public function getId()
    {
        return $this->attributes['id'] ?? null;
    }
}
