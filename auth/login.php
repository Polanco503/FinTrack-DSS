<?php
session_start();
require_once __DIR__ . '/../bd/Connections/conn.php';

// Si ya está autenticado, al dashboard
if (!empty($_SESSION['autenticado']) && $_SESSION['autenticado'] === 'SI') {
    header("Location: /Catedra/views/dashboard.php");
    exit;
}

// Procesar solo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        header("Location: /Catedra/index.php?error=" . urlencode("Solicitud inválida (CSRF)."));
        exit;
    }

    $usuario  = trim($_POST['inputUsuario'] ?? '');
    $password = trim($_POST['inputPassword'] ?? '');

    // Validaciones
    if (strlen($usuario) < 4 || strlen($usuario) > 32 || !preg_match('/^[A-Za-z0-9_]+$/', $usuario)) {
        header("Location: /Catedra/index.php?error=" . urlencode("Usuario inválido. Solo letras, números y guión bajo, 4–32 caracteres.") . "&usuario=" . urlencode($usuario));
        exit;
    }
    if (strlen($password) < 5 || strlen($password) > 128) {
        header("Location: /Catedra/index.php?error=" . urlencode("La contraseña debe tener entre 5 y 128 caracteres.") . "&usuario=" . urlencode($usuario));
        exit;
    }

    $sql  = "SELECT id, contrasena_hash, usuario, email, nombres, apellidos FROM usuarios WHERE usuario = ?";
    $stmt = $db->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row && password_verify($password, $row['contrasena_hash'])) {
            $_SESSION['usuario'] = $row['usuario'];
            $_SESSION['email'] = $row['email'] ?? '';
            $_SESSION['nombres'] = $row['nombres'] ?? '';
            $_SESSION['apellidos'] = $row['apellidos'] ?? '';
            $_SESSION['autenticado'] = "SI";
            $_SESSION['usuario_id'] = $row['id'];
            session_regenerate_id(true);
            unset($_SESSION['csrf_token']);

            header("Location: /Catedra/views/dashboard.php");
            exit;
        } else {
            header("Location: /Catedra/index.php?error=" . urlencode("Usuario o contraseña incorrecta.") . "&usuario=" . urlencode($usuario));
            exit;
        }
        $stmt->close();
    } else {
        header("Location: /Catedra/index.php?error=" . urlencode("Error de base de datos.") . "&usuario=" . urlencode($usuario));
        exit;
    }
} else {
    // Si alguien accede directo, lo manda al formulario
    header("Location: /Catedra/index.php");
    exit;
}
