<?php
session_start();
if (empty($_SESSION['autenticado']) || $_SESSION['autenticado'] !== 'SI' || empty($_SESSION['usuario_id'])) {
    header("Location: /Catedra/auth/login.php");
    exit;
}
require_once __DIR__ . '/../bd/Connections/conn.php';
$usuario_id = $_SESSION['usuario_id'];

$movimientos = [];
$stmt = $db->prepare("SELECT id, tipo, monto, descripcion, fecha FROM finanzas WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $movimientos[] = $row;
}
$stmt->close();

// Crear eventos para el calendario
$eventos = [];
foreach ($movimientos as $mov) {
    $eventos[] = [
        "id"    => $mov['id'],
        "title" => ucfirst($mov['tipo']) . ": $" . number_format($mov['monto'],2) . ($mov['descripcion'] ? ' - ' . $mov['descripcion'] : ''),
        "start" => substr($mov['fecha'], 0, 10),
        "color" => $mov['tipo'] == 'ingreso' ? '#198754' : '#dc3545',
        // puedes agregar más datos si necesitas
        "descripcion" => $mov['descripcion'],
        "monto" => $mov['monto'],
        "tipo" => $mov['tipo'],
        "fecha" => $mov['fecha'],
    ];
}
?>
<!doctype html>
<html lang="es">
<head>
    <title>Calendario de Movimientos</title>
    <?php require_once __DIR__ . '/../basics/head.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet" />
</head>
<body>
    <?php require_once __DIR__ . '/../basics/menu.php'; ?>
    <div class="container mt-4">
        <h2>Calendario de movimientos</h2>
        <div class="row">
            <div class="col-md-8">
                <div id="calendario"></div>
            </div>
            <div class="col-md-4">
                <div id="detalleMovimiento" class="mt-4"></div>
            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/../basics/scripts.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script>
        // Guardar eventos como variable JS
        const eventos = <?= json_encode($eventos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendario');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                height: 600,
                events: eventos,
                eventClick: function(info) {
                    info.jsEvent.preventDefault();

                    // Busca el evento completo
                    var evento = eventos.find(e => e.id == info.event.id);

                    // Panel detalle con botones
                    var detalle = `
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">${info.event.title}</h5>
                                <p class="card-text">
                                    <b>Tipo:</b> ${evento.tipo.charAt(0).toUpperCase() + evento.tipo.slice(1)}<br>
                                    <b>Monto:</b> $${parseFloat(evento.monto).toFixed(2)}<br>
                                    <b>Descripción:</b> ${evento.descripcion ? evento.descripcion : '(sin descripción)'}<br>
                                    <b>Fecha:</b> ${evento.fecha}
                                </p>
                                <button onclick="window.location.href='editar.php?id=${evento.id}'" class="btn btn-primary me-2">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <button onclick="borrarMovimiento(${evento.id})" class="btn btn-danger">
                                    <i class="bi bi-trash"></i> Borrar
                                </button>
                            </div>
                        </div>
                    `;
                    document.getElementById('detalleMovimiento').innerHTML = detalle;
                }
            });
            calendar.render();
        });

        function borrarMovimiento(id) {
            if (confirm("¿Seguro que deseas borrar este movimiento?")) {
                window.location.href = 'borrar.php?id=' + id;
            }
        }
    </script>
    <?php require_once __DIR__ . '/../basics/footer.php'; ?>
</body>
</html>
