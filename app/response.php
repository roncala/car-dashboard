<?php
// app/response.php
declare(strict_types=1);

function json_response($data, int $status = 200): void {
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data);
  exit;
}

function bad_request(string $msg = 'Bad request'): void {
  json_response(['error' => $msg], 400);
}

function unauthorized(string $msg = 'Unauthorized'): void {
  json_response(['error' => $msg], 401);
}

function forbidden(string $msg = 'Forbidden'): void {
  json_response(['error' => $msg], 403);
}

function not_found(string $msg = 'Not found'): void {
  json_response(['error' => $msg], 404);
}

