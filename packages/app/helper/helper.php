<?php

/**
 * Resolves and sanitizes POST input with strict validation rules.
 *
 * Notes:
 * - XSS: strips tags and encodes special chars for plain-text usage.
 * - SQL injection: use parameterized queries at DB layer; this helper enforces
 *   allow-lists/patterns to reduce unsafe input early.
 *
 * @param string $key POST field key.
 * @param mixed $default Fallback when missing/invalid.
 * @param array $options Validation options:
 *   - type: string one of string|int|bool (default string)
 *   - allowed: array allow-list of accepted values
 *   - max_length: int (default 255)
 *   - pattern: string regex
 * @return mixed
 */
function resolvePost(string $key, mixed $default = null, array $options = []): mixed
{
    $value = filter_input(INPUT_POST, $key, FILTER_UNSAFE_RAW, FILTER_NULL_ON_FAILURE);

    if ($value === null || $value === false) {
        return $default;
    }

    if (is_string($value)) {
        $value = trim(str_replace("\0", '', $value));
    }

    $type = isset($options['type']) && is_string($options['type']) ? strtolower($options['type']) : 'string';

    if ($type === 'bool') {
        if (is_bool($value)) {
            return $value;
        }
        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if (in_array($normalized, ['1', 'true', 'on', 'yes'], true)) {
                return true;
            }
            if (in_array($normalized, ['0', 'false', 'off', 'no'], true)) {
                return false;
            }
        }
        if (is_int($value)) {
            if ($value === 1) {
                return true;
            }
            if ($value === 0) {
                return false;
            }
        }
        return $default;
    }

    if ($type === 'int') {
        if (is_int($value)) {
            return $value;
        }
        $int_value = filter_var($value, FILTER_VALIDATE_INT);
        return ($int_value !== false) ? (int) $int_value : $default;
    }

    if (!is_string($value)) {
        return $default;
    }

    $max_length = isset($options['max_length']) && is_int($options['max_length']) ? $options['max_length'] : 255;
    $clean = substr(strip_tags($value), 0, $max_length);

    if (isset($options['pattern']) && is_string($options['pattern']) && !preg_match($options['pattern'], $clean)) {
        return $default;
    }

    if (isset($options['allowed'])) {
        $allowed = $options['allowed'];
        if (!is_array($allowed) || !in_array($clean, $allowed, true)) {
            return $default;
        }
    }

    return htmlspecialchars($clean, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}