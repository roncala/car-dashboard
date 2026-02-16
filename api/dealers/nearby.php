<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/db.php';
require_once __DIR__ . '/../../app/response.php';
require_once __DIR__ . '/../../app/auth_middleware.php';
require_once __DIR__ . '/../../app/validators.php';

$pdo = get_pdo();
$userId = current_user_id(); // optional

$zip = str_clean($_GET['zip'] ?? null, 15);
$lat = to_float($_GET['lat'] ?? null);
$lng = to_float($_GET['lng'] ?? null);
$radius = to_float($_GET['radius_miles'] ?? null) ?? 25.0;
$limit = min(50, max(1, (int)($_GET['limit'] ?? 10)));

if ($userId && !$zip && ($lat === null || $lng === null)) {
  $stmt = $pdo->prepare("SELECT zip_code, latitude, longitude FROM users WHERE user_id = ?");
  $stmt->execute([$userId]);
  $u = $stmt->fetch();
  if ($u) {
    $zip = $zip ?: ($u['zip_code'] ?? null);
    $lat = $lat ?? ($u['latitude'] !== null ? (float)$u['latitude'] : null);
    $lng = $lng ?? ($u['longitude'] !== null ? (float)$u['longitude'] : null);
  }
}

if ($lat !== null && $lng !== null) {
  $sql = "
    SELECT dealer_id, dealer_name, phone, website,
           address_line1, city, state, zip_code, country,
           latitude, longitude,
           (3959 * ACOS(
              COS(RADIANS(:lat)) * COS(RADIANS(latitude)) *
              COS(RADIANS(longitude) - RADIANS(:lng)) +
              SIN(RADIANS(:lat)) * SIN(RADIANS(latitude))
           )) AS distance_miles
    FROM dealers
    WHERE latitude IS NOT NULL AND longitude IS NOT NULL
    HAVING distance_miles <= :radius
    ORDER BY distance_miles ASC
    LIMIT :lim
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':lat', $lat);
  $stmt->bindValue(':lng', $lng);
  $stmt->bindValue(':radius', $radius);
  $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
  $stmt->execute();

  json_response(['mode' => 'geo', 'dealers' => $stmt->fetchAll()]);
}

if (!$zip) bad_request('Provide zip or lat/lng (or log in with saved address).');

$stmt = $pdo->prepare("SELECT * FROM dealers WHERE zip_code = ? LIMIT ?");
$stmt->bindValue(1, $zip);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->execute();

$dealers = $stmt->fetchAll();
if ($dealers) json_response(['mode' => 'zip_exact', 'dealers' => $dealers]);

$zip3 = substr(preg_replace('/\D/', '', $zip), 0, 3);
if (strlen($zip3) === 3) {
  $stmt = $pdo->prepare("SELECT * FROM dealers WHERE zip_code LIKE ? LIMIT ?");
  $stmt->bindValue(1, $zip3 . '%');
  $stmt->bindValue(2, $limit, PDO::PARAM_INT);
  $stmt->execute();
  json_response(['mode' => 'zip_prefix', 'dealers' => $stmt->fetchAll()]);
}

json_response(['mode' => 'none', 'dealers' => []]);

