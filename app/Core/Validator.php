<?php
declare(strict_types=1);

namespace App\Core;

/** Server tomonidagi validatsiya (JavaScript o‘chirilgan bo‘lsa ham ishlaydi) */
final class Validator
{
    /** @var array<string,string> */
    private array $errors = [];

    public function __construct(private readonly array $data)
    {
    }

    public function value(string $field): string
    {
        $v = $this->data[$field] ?? '';
        return is_string($v) ? trim($v) : '';
    }

    private function add(string $field, string $message): void
    {
        $this->errors[$field] ??= $message;
    }

    public function required(string $field, string $label): self
    {
        if ($this->value($field) === '') {
            $this->add($field, "«{$label}» maydoni to‘ldirilishi shart.");
        }
        return $this;
    }

    public function length(string $field, string $label, int $min, int $max): self
    {
        $v = $this->value($field);
        if ($v === '') {
            return $this;
        }
        $len = mb_strlen($v);
        if ($len < $min || $len > $max) {
            $this->add($field, "«{$label}» uzunligi {$min}–{$max} belgi oralig‘ida bo‘lishi kerak.");
        }
        return $this;
    }

    public function email(string $field, string $label): self
    {
        $v = $this->value($field);
        if ($v !== '' && (filter_var($v, FILTER_VALIDATE_EMAIL) === false || mb_strlen($v) > 150)) {
            $this->add($field, "«{$label}» to‘g‘ri elektron pochta manzili bo‘lishi kerak.");
        }
        return $this;
    }

    public function in(string $field, string $label, array $allowed): self
    {
        $v = $this->value($field);
        if ($v !== '' && !in_array($v, array_map('strval', $allowed), true)) {
            $this->add($field, "«{$label}» uchun ruxsat etilmagan qiymat tanlangan.");
        }
        return $this;
    }

    public function intBetween(string $field, string $label, int $min, int $max): self
    {
        $v = $this->value($field);
        if ($v === '') {
            return $this;
        }
        $ok = filter_var($v, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);
        if ($ok === false) {
            $this->add($field, "«{$label}» {$min} dan {$max} gacha butun son bo‘lishi kerak.");
        }
        return $this;
    }

    public function regex(string $field, string $pattern, string $message): self
    {
        $v = $this->value($field);
        if ($v !== '' && !preg_match($pattern, $v)) {
            $this->add($field, $message);
        }
        return $this;
    }

    public function matches(string $field, string $other, string $message): self
    {
        if ($this->value($field) !== $this->value($other)) {
            $this->add($field, $message);
        }
        return $this;
    }

    public function rule(string $field, bool $passes, string $message): self
    {
        if (!$passes) {
            $this->add($field, $message);
        }
        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    /** @return array<string,string> */
    public function errors(): array
    {
        return $this->errors;
    }
}
