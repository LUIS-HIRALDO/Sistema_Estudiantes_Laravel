<?php

namespace App\Exceptions;

class ApiException extends \Exception
{
    protected $status_code;
    protected $errors;

    public function __construct($message = '', $status_code = 400, $errors = [])
    {
        parent::__construct($message);
        $this->status_code = $status_code;
        $this->errors = $errors;
    }

    public function getStatusCode()
    {
        return $this->status_code;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function render()
    {
        $response = [
            'error' => $this->getMessage(),
        ];

        if (!empty($this->errors)) {
            $response['errors'] = $this->errors;
        }

        return $response;
    }
}

class NotFoundException extends ApiException
{
    public function __construct($message = 'Recurso no encontrado')
    {
        parent::__construct($message, 404);
    }
}

class UnauthorizedException extends ApiException
{
    public function __construct($message = 'No autorizado')
    {
        parent::__construct($message, 401);
    }
}

class ValidationException extends ApiException
{
    public function __construct($message = 'Validación fallida', $errors = [])
    {
        parent::__construct($message, 422, $errors);
    }
}

class ConflictException extends ApiException
{
    public function __construct($message = 'Conflicto')
    {
        parent::__construct($message, 409);
    }
}

class InternalServerException extends ApiException
{
    public function __construct($message = 'Error interno del servidor')
    {
        parent::__construct($message, 500);
    }
}
