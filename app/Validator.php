<?php

namespace App;

class Validator
{
    private $errors = [];
    private $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function validate(array $rules)
    {
        foreach ($rules as $field => $rule) {
            $this->validateField($field, $rule);
        }
        return empty($this->errors);
    }

    private function validateField($field, $rules)
    {
        $value = $this->data[$field] ?? null;
        $ruleArray = explode('|', $rules);

        foreach ($ruleArray as $rule) {
            $this->checkRule($field, $value, trim($rule));
        }
    }

    private function checkRule($field, $value, $rule)
    {
        if (str_contains($rule, ':')) {
            [$rule_name, $param] = explode(':', $rule, 2);
        } else {
            $rule_name = $rule;
            $param = null;
        }

        match ($rule_name) {
            'required' => $this->required($field, $value),
            'email' => $this->email($field, $value),
            'min' => $this->min($field, $value, $param),
            'max' => $this->max($field, $value, $param),
            'numeric' => $this->numeric($field, $value),
            'unique' => $this->unique($field, $value, $param),
            'confirmed' => $this->confirmed($field, $value),
            'regex' => $this->regex($field, $value, $param),
            'date' => $this->date($field, $value),
            'in' => $this->in($field, $value, $param),
            'array' => $this->isArray($field, $value),
            default => null,
        };
    }

    private function required($field, $value)
    {
        if (empty($value) && $value !== '0') {
            $this->addError($field, "El campo $field es requerido");
        }
    }

    private function email($field, $value)
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "El campo $field debe ser un email válido");
        }
    }

    private function min($field, $value, $param)
    {
        if ($value && strlen($value) < $param) {
            $this->addError($field, "El campo $field debe tener al menos $param caracteres");
        }
    }

    private function max($field, $value, $param)
    {
        if ($value && strlen($value) > $param) {
            $this->addError($field, "El campo $field no puede tener más de $param caracteres");
        }
    }

    private function numeric($field, $value)
    {
        if ($value && !is_numeric($value)) {
            $this->addError($field, "El campo $field debe ser numérico");
        }
    }

    private function unique($field, $value, $param)
    {
        // Implementar búsqueda en tabla MySQL
        $db = Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM $param WHERE $field = ?";
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->execute([$value]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            $this->addError($field, "El valor de $field ya existe");
        }
    }

    private function confirmed($field, $value)
    {
        $confirmed_field = $field . '_confirmation';
        if ($value !== ($this->data[$confirmed_field] ?? null)) {
            $this->addError($field, "El campo $field no coincide");
        }
    }

    private function regex($field, $value, $pattern)
    {
        if ($value && !preg_match($pattern, $value)) {
            $this->addError($field, "El campo $field no tiene un formato válido");
        }
    }

    private function date($field, $value)
    {
        if ($value && !strtotime($value)) {
            $this->addError($field, "El campo $field debe ser una fecha válida");
        }
    }

    private function in($field, $value, $param)
    {
        $values = explode(',', str_replace(' ', '', $param));
        if ($value && !in_array($value, $values)) {
            $this->addError($field, "El valor de $field no es válido");
        }
    }

    private function isArray($field, $value)
    {
        if ($value && !is_array($value)) {
            $this->addError($field, "El campo $field debe ser un array");
        }
    }

    public function errors()
    {
        return $this->errors;
    }

    public function fails()
    {
        return !empty($this->errors);
    }

    public function passed()
    {
        return empty($this->errors);
    }

    private function addError($field, $message)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public static function make(array $data, array $rules)
    {
        $validator = new self($data);
        $validator->validate($rules);
        return $validator;
    }
}
