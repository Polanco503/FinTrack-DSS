<?php
session_start();
if (empty($_SESSION['autenticado']) || $_SESSION['autenticado'] !== 'SI' || empty($_SESSION['usuario_id'])) {
    header("Location: /Catedra/auth/login.php");
    exit;
}

require_once __DIR__ . '/../bd/Connections/conn.php';

$usuario_id = $_SESSION['usuario_id'];
$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID inválido.");
}

// Borrar solo si el movimiento pertenece al usuario actual
$stmt = $db->prepare("DELETE FROM finanzas WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $id, $usuario_id);
$stmt->execute();
$stmt->close();

header("Location: dashboard.php?borrado=1");
exit;
