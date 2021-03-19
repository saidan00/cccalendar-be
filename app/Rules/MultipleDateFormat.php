<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class MultipleDateFormat implements Rule
{
    public $formats;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($formats)
    {
        $this->formats = $formats;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        foreach ($this->formats as $format) {

            // parse date with current format
            $parsed = date_parse_from_format($format, $value);

            // if value matches given format return true=validation succeeded
            if ($parsed['error_count'] === 0 && $parsed['warning_count'] === 0) {
                return true;
            }
        }

        // value did not match any of the provided formats, so return false=validation failed
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $rules = '';
        foreach ($this->formats as $format) {
            $rules .= $format . ', ';
        }
        $rules = substr($rules, 0, -2);

        return 'The :attribute does not match one of these format [' . $rules . '].';
    }
}
