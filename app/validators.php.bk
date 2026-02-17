<?php
// app/validators.php
declare(strict_types=1);

function str_clean(?string $v, int $maxLen = 1000): ?string {
  if ($v === null) return null;
  $v = trim(preg_replace('/\s+/', ' ', $v));
  if ($v === '') return null;
  if (mb_strlen($v) > $maxLen) $v = mb_substr($v, 0, $maxLen);
  return $v;
}

function validate_email(?string $email): ?string {
  $email = str_clean($email, 150);
  if (!$email) return null;
  return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
}

function to_int($v, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): ?int {
  if ($v === null || $v === '') return null;
  if (!is_numeric($v)) return null;
  $n = (int)$v;
  if ($n < $min || $n > $max) return null;
  return $n;
}

function to_float($v): ?float {
  if ($v === null || $v === '') return null;
  if (!is_numeric($v)) return null;
  return (float)$v;
}

