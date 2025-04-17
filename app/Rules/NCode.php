<?php
namespace App\Rules;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
class NCode implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->isValidNCode($value)) {
            $fail('کد ملی وارد شده معتبر نیست.');
        }
    }
    private function isValidNCode($code): bool
    {
        if (!preg_match('/^\d{10}$/', $code)) {
            return false;
        }
        $check = (int) $code[9];
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += ((int) $code[$i]) * (10 - $i);
        }
        $remainder = $sum % 11;
        return ($remainder < 2 && $check == $remainder) || ($remainder >= 2 && $check == (11 - $remainder));
    }
}
