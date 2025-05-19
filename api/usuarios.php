<?php
// C:\xampp\htdocs\Catedra\api\usuarios.php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  exit; // para preflight
}


require_once __DIR__ . '/../bd/Connections/conn.php';
header('Content-Type: application/json');
// session_start();  // <<< ya lo hace conn.php

if (empty($_SESSION['utenticado']) || $_SESSION['utenticado']!=='SI') {
  http_response_code(401);
  echo json_encode(['error'=>'No autorizado']);
  exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$id     = $_GET['id'] ?? null;

switch ($method) {
  case 'GET':
    if ($id) {
      // Obtener un usuario
      $stmt = $db->prepare(
        "SELECT id, usuario, nombres, apellidos, email, created_at
           FROM usuarios WHERE id=?"
      );
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $user = $stmt->get_result()->fetch_assoc();
      http_response_code($user?200:404);
      echo json_encode($user ?: ['error'=>'No encontrado']);
    } else {
      // Listar todos
      $rs = $db->query(
        "SELECT id, usuario, nombres, apellidos, email, created_at
           FROM usuarios"
      );
      echo json_encode($rs->fetch_all(MYSQLI_ASSOC));
    }
    break;

  case 'POST':
    $data = json_decode(file_get_contents('php://input'), true);
    // 1) Validar al menos usuario y contraseña
    if (empty($data['usuario']) || empty($data['contrasena'])) {
        http_response_code(400);
        echo json_encode(['error'=>'Faltan datos']);
        exit;
    }
    // 2) Hashear la contraseña
    $hash = password_hash($data['contrasena'], PASSWORD_DEFAULT);
    // 3) Insertar en BD
    $stmt = $db->prepare("
      INSERT INTO usuarios
        (usuario, contrasena_hash, nombres, apellidos, email)
      VALUES (?,?,?,?,?)
    ");
    $stmt->bind_param(
      "sssss",
      $data['usuario'], $hash,
      $data['nombres']    ?? '',
      $data['apellidos']  ?? '',
      $data['email']      ?? ''
    );
    $stmt->execute();
    http_response_code(201);
    echo json_encode(['id'=>$stmt->insert_id]);
    break;

    case 'PUT':
  if (! $id) {
    http_response_code(400);
    echo json_encode(['error'=>'ID requerido']);
    exit;
  }
  $data = json_decode(file_get_contents('php://input'), true);
  // Validaciones mínimas
  if (empty($data['nombres']) && empty($data['apellidos']) && empty($data['email'])) {
    http_response_code(400);
    echo json_encode(['error'=>'Nada que actualizar']);
    exit;
  }
  // Construye dinámicamente el UPDATE
  $fields = [];
  $params = [];
  if (isset($data['nombres']))   { $fields[] = 'nombres=?';   $params[] = $data['nombres']; }
  if (isset($data['apellidos'])) { $fields[] = 'apellidos=?'; $params[] = $data['apellidos']; }
  if (isset($data['email']))     { $fields[] = 'email=?';     $params[] = $data['email']; }
  $sql = 'UPDATE usuarios SET '.implode(',',$fields).' WHERE id=?';
  $params[] = $id;
  $types = str_repeat('s', count($params)-1) . 'i';
  $stmt = $db->prepare($sql);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  echo json_encode(['updated'=>$stmt->affected_rows]);
  break;


    case 'DELETE':
  if (!$id) { http_response_code(400); die(json_encode(['error'=>'ID requerido'])); }
  $stmt = $db->prepare("DELETE FROM usuarios WHERE id=?");
  $stmt->bind_param("i",$id);
  $stmt->execute();
  echo json_encode(['deleted'=>$stmt->affected_rows]);
  break;

  default:
    http_response_code(405);
    header('Allow: GET, POST, PUT, DELETE');
    echo json_encode(['error'=>'Método no permitido']);
}
