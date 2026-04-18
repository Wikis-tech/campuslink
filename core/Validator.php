<?php
/**
 * CampusLink - Input Validator
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

class Validator
{
    private array $errors = [];
    private array $data   = [];

    // ============================================================
    // Constructor
    // ============================================================
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    // ============================================================
    // Static factory
    // ============================================================
    public static function make(array $data, array $rules): self
    {
        $v = new self($data);
        $v->validate($rules);
        return $v;
    }

    // ============================================================
    // Run validation rules
    // ============================================================
    public function validate(array $rules): void
    {
        foreach ($rules as $field => $ruleString) {
            $value = $this->data[$field] ?? null;
            $ruleList = explode('|', $ruleString);

            foreach ($ruleList as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }
    }

    // ============================================================
    // Apply individual rule
    // ============================================================
    private function applyRule(string $field, mixed $value, string $rule): void
    {
        // Skip further rules if already has error for this field
        // (comment out if you want all errors)
        // if (isset($this->errors[$field])) return;

        $label = ucfirst(str_replace('_', ' ', $field));

        // Rule with parameter: e.g. min:6
        if (str_contains($rule, ':')) {
            [$ruleName, $param] = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $param = null;
        }

        switch ($ruleName) {
            case 'required':
                if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                    $this->errors[$field] = "$label is required.";
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = "$label must be a valid email address.";
                }
                break;

            case 'min':
                if (!empty($value) && strlen((string)$value) < (int)$param) {
                    $this->errors[$field] = "$label must be at least $param characters.";
                }
                break;

            case 'max':
                if (!empty($value) && strlen((string)$value) > (int)$param) {
                    $this->errors[$field] = "$label must not exceed $param characters.";
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->errors[$field] = "$label must be a number.";
                }
                break;

            case 'integer':
                if (!empty($value) && !ctype_digit((string)$value)) {
                    $this->errors[$field] = "$label must be a whole number.";
                }
                break;

            case 'alpha':
                if (!empty($value) && !ctype_alpha(str_replace(' ', '', (string)$value))) {
                    $this->errors[$field] = "$label must contain only letters.";
                }
                break;

            case 'alphanumeric':
                if (!empty($value) && !ctype_alnum(str_replace([' ', '-', '_'], '', (string)$value))) {
                    $this->errors[$field] = "$label must contain only letters and numbers.";
                }
                break;

            case 'phone':
                // Nigerian phone format: 08012345678 or +2348012345678
                $cleaned = preg_replace('/[^0-9+]/', '', (string)$value);
                if (!empty($value) && !preg_match('/^(\+234|0)[789][01]\d{8}$/', $cleaned)) {
                    $this->errors[$field] = "$label must be a valid Nigerian phone number.";
                }
                break;

            case 'url':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->errors[$field] = "$label must be a valid URL.";
                }
                break;

            case 'in':
                $allowed = explode(',', $param);
                if (!empty($value) && !in_array($value, $allowed)) {
                    $this->errors[$field] = "$label must be one of: " . implode(', ', $allowed) . ".";
                }
                break;

            case 'not_in':
                $forbidden = explode(',', $param);
                if (!empty($value) && in_array($value, $forbidden)) {
                    $this->errors[$field] = "$label contains an invalid value.";
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                $confirmValue = $this->data[$confirmField] ?? null;
                if ($value !== $confirmValue) {
                    $this->errors[$field] = "$label confirmation does not match.";
                }
                break;

            case 'password':
                // At least 8 chars, one uppercase, one lowercase, one number
                if (!empty($value)) {
                    if (strlen($value) < 8) {
                        $this->errors[$field] = "$label must be at least 8 characters.";
                    } elseif (!preg_match('/[A-Z]/', $value)) {
                        $this->errors[$field] = "$label must contain at least one uppercase letter.";
                    } elseif (!preg_match('/[a-z]/', $value)) {
                        $this->errors[$field] = "$label must contain at least one lowercase letter.";
                    } elseif (!preg_match('/[0-9]/', $value)) {
                        $this->errors[$field] = "$label must contain at least one number.";
                    }
                }
                break;

            case 'school_email':
                // Must match the school's email domain
                if (!empty($value)) {
                    $domain = strtolower(trim(substr(strrchr($value, '@'), 1)));
                    if ($domain !== strtolower(SCHOOL_DOMAIN)) {
                        $this->errors[$field] = "$label must be a valid " . SCHOOL_NAME . " email address (@" . SCHOOL_DOMAIN . ").";
                    }
                }
                break;

            case 'matric':
                // Matric number format (adjust regex to match your school)
                if (!empty($value) && !preg_match('/^[A-Z0-9\/\-]{6,20}$/i', $value)) {
                    $this->errors[$field] = "$label must be a valid matriculation number.";
                }
                break;

            case 'accepted':
                if (empty($value) || $value === '0' || $value === 'false') {
                    $this->errors[$field] = "You must accept the $label.";
                }
                break;

            case 'date':
                if (!empty($value) && !strtotime($value)) {
                    $this->errors[$field] = "$label must be a valid date.";
                }
                break;

            case 'after':
                if (!empty($value)) {
                    $compareDate = $param === 'today' ? date('Y-m-d') : $param;
                    if (strtotime($value) <= strtotime($compareDate)) {
                        $this->errors[$field] = "$label must be a date after $compareDate.";
                    }
                }
                break;

            case 'rating':
                if (!empty($value) && (!is_numeric($value) || $value < 1 || $value > 5)) {
                    $this->errors[$field] = "$label must be between 1 and 5.";
                }
                break;

            case 'age':
                // Minimum age (18)
                if (!empty($value)) {
                    $birthDate = new DateTime($value);
                    $today = new DateTime();
                    $age = $today->diff($birthDate)->y;
                    if ($age < (int)$param) {
                        $this->errors[$field] = "You must be at least $param years old.";
                    }
                }
                break;
        }
    }

    // ============================================================
    // Check if validation passed
    // ============================================================
    public function passes(): bool
    {
        return empty($this->errors);
    }

    // ============================================================
    // Check if validation failed
    // ============================================================
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    // ============================================================
    // Get all errors
    // ============================================================
    public function errors(): array
    {
        return $this->errors;
    }

    // ============================================================
    // Get first error for a field
    // ============================================================
    public function firstError(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    // ============================================================
    // Get first overall error message
    // ============================================================
    public function firstErrorMessage(): ?string
    {
        return !empty($this->errors) ? array_values($this->errors)[0] : null;
    }

    // ============================================================
    // Get validated data
    // ============================================================
    public function validated(): array
    {
        return $this->data;
    }
}