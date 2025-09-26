<?php
namespace App\Core;

class Validator 
{
    protected $errors = [];

    public function __construct(
        protected array $data,
        protected array $rules = []
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $rules) {
            $rules = explode('|', $rules);
            $value = trim($this->data[$field] ?? '');

            foreach ($rules as $rule) {
                [$name, $param] = array_pad(explode(':', $rule), 2, null);

                $error = match ($name) {
                    'required' => $value === '' ? "$field es obligatorio." : null,
                    'min'      => strlen($value) < (int)$param ? "$field debe tener al menos $param caracteres." : null,
                    'max'      => strlen($value) > (int)$param ? "$field no debe superar $param caracteres." : null,
                    'url'      => filter_var($value, FILTER_VALIDATE_URL) === false ? "$field debe ser una URL válida." : null,
                    'email'    => filter_var($value, FILTER_VALIDATE_EMAIL) === false ? "$field debe ser un email válido." : null,
                    'same'     => $value !== ($this->data[$param] ?? null) ? "Las contraseñas deben coincidir." : null,
                    default    => null,
                };

                if ($error) {
                    $this->errors[$field][] = $error;
                    break;
                }
            }
        }
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
