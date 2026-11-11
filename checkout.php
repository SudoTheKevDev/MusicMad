<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ============================================================
//  RutaPc – Checkout
//  checkout.php
// ============================================================

require_once 'dataBase.php';
require_once 'includes/auth.php';

// Solo usuarios autenticados
if (!estaAutenticado()) {
    header('Location: login.php');
    exit;
}

// PHPMailer (instalado vía Composer: composer require phpmailer/phpmailer)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once 'vendor/autoload.php';

$database = new Database();
$db       = $database->getConnection();

$usuarioId = $_SESSION['usuario_id'];
$errores   = [];
$exito     = false;

// ── Cargar carrito activo ────────────────────────────────────
$stmtCarrito = $db->prepare("SELECT id FROM carrito WHERE usuario_id = :uid LIMIT 1");
$stmtCarrito->execute([':uid' => $usuarioId]);
$carritoRow  = $stmtCarrito->fetch(PDO::FETCH_ASSOC);

if (!$carritoRow) {
    header('Location: catalogo.php');
    exit;
}
$carritoId = $carritoRow['id'];

// Cargar ítems del carrito
$stmtItems = $db->prepare("
    SELECT dc.producto_id, dc.cantidad,
           ip.descripcion, ip.precio, ip.imagen_url
    FROM detalle_carrito dc
    INNER JOIN info_productos ip ON ip.id = dc.producto_id
    WHERE dc.carrito_id = :cid
    ORDER BY dc.id ASC
");
$stmtItems->execute([':cid' => $carritoId]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

if (empty($items)) {
    header('Location: carrito.php');
    exit;
}

$total = 0;
foreach ($items as $item) {
    $total += $item['precio'] * $item['cantidad'];
}

// ── Datos del usuario ────────────────────────────────────────
$stmtUser = $db->prepare("SELECT nombre, apellido, email FROM usuarios WHERE id = :uid");
$stmtUser->execute([':uid' => $usuarioId]);
$usuario  = $stmtUser->fetch(PDO::FETCH_ASSOC);

// ── Función de envío de email ────────────────────────────────
function enviarEmail(string $para, string $asunto, string $cuerpoHtml): bool
{
    $mail = new PHPMailer(true);
    try {
        // Configuración SMTP Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'prueba-Credencial';      // Tu correo Gmail
        $mail->Password   = 'prueba-Credencial';  // Contraseña de aplicación Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('RutaPcventa@gmail.com', 'RutaPcventa@gmail.com');
        $mail->addAddress($para);
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpoHtml;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Error PHPMailer: ' . $mail->ErrorInfo);
        return false;
    }
}

function format_currency($v): string
{
    return '$' . number_format($v, 2);
}

// ── Construir tabla de productos para el email ───────────────
function tablaProductosEmail(array $items, float $total): string
{
    $filas = '';
    foreach ($items as $item) {
        $subtotal = $item['precio'] * $item['cantidad'];
        $filas .= "
        <tr>
            <td style='padding:8px 12px;border-bottom:1px solid #e2e8f0;'>
                " . htmlspecialchars($item['descripcion']) . "
            </td>
            <td style='padding:8px 12px;border-bottom:1px solid #e2e8f0;text-align:center;'>
                {$item['cantidad']}
            </td>
            <td style='padding:8px 12px;border-bottom:1px solid #e2e8f0;text-align:right;'>
                " . format_currency($item['precio']) . "
            </td>
            <td style='padding:8px 12px;border-bottom:1px solid #e2e8f0;text-align:right;'>
                " . format_currency($subtotal) . "
            </td>
        </tr>";
    }

    return "
    <table width='100%' cellpadding='0' cellspacing='0'
           style='border-collapse:collapse;font-family:sans-serif;font-size:14px;'>
        <thead>
            <tr style='background:#0f4c81;color:#fff;'>
                <th style='padding:10px 12px;text-align:left;'>Producto</th>
                <th style='padding:10px 12px;text-align:center;'>Cant.</th>
                <th style='padding:10px 12px;text-align:right;'>Precio</th>
                <th style='padding:10px 12px;text-align:right;'>Subtotal</th>
            </tr>
        </thead>
        <tbody>$filas</tbody>
        <tfoot>
            <tr>
                <td colspan='3'
                    style='padding:10px 12px;text-align:right;font-weight:bold;'>Total:</td>
                <td style='padding:10px 12px;text-align:right;font-weight:bold;color:#0f4c81;'>
                    " . format_currency($total) . "
                </td>
            </tr>
        </tfoot>
    </table>";
}

// ── Procesar formulario ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $direccion = trim($_POST['direccion'] ?? '');
    $telefono  = trim($_POST['telefono']  ?? '');
    $notas     = trim($_POST['notas']     ?? '');

    if (empty($direccion)) $errores[] = 'La dirección de envío es obligatoria.';
    if (empty($telefono))  $errores[] = 'El teléfono es obligatorio.';

    if (empty($errores)) {
        // 1. Insertar pedido en BD
        $stmtPedido = $db->prepare("
            INSERT INTO pedidos (usuario_id, carrito_id, direccion, telefono, notas, total)
            VALUES (:uid, :cid, :dir, :tel, :not, :tot)
        ");
        $stmtPedido->execute([
            ':uid' => $usuarioId,
            ':cid' => $carritoId,
            ':dir' => htmlspecialchars($direccion, ENT_QUOTES, 'UTF-8'),
            ':tel' => htmlspecialchars($telefono,  ENT_QUOTES, 'UTF-8'),
            ':not' => htmlspecialchars($notas,     ENT_QUOTES, 'UTF-8'),
            ':tot' => $total,
        ]);
        $pedidoId = $db->lastInsertId();

        // 2. Vaciar el carrito
        $db->prepare("DELETE FROM detalle_carrito WHERE carrito_id = :cid")
           ->execute([':cid' => $carritoId]);
        $db->prepare("DELETE FROM carrito WHERE id = :cid")
           ->execute([':cid' => $carritoId]);

        // 3. Emails ──────────────────────────────────────────
        $tabla     = tablaProductosEmail($items, $total);
        $nombreCliente = htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']);
        $notasHtml = !empty($notas)
            ? "<p><strong>Notas:</strong> " . htmlspecialchars($notas) . "</p>"
            : '';

        // — Email al CLIENTE —
        $emailCliente = "
        <div style='font-family:sans-serif;max-width:600px;margin:auto;color:#1a202c;'>
            <div style='background:#0f4c81;padding:24px 32px;border-radius:8px 8px 0 0;'>
                <h1 style='color:#00d4ff;margin:0;font-size:28px;'>RutaPc</h1>
                <p style='color:#fff;margin:4px 0 0;'>Confirmación de pedido</p>
            </div>
            <div style='background:#f7fafc;padding:32px;border-radius:0 0 8px 8px;'>
                <p>Hola, <strong>{$nombreCliente}</strong>.</p>
                <p>Recibimos tu pedido <strong>#$pedidoId</strong>. 
                   Nos pondremos en contacto contigo pronto para coordinar la entrega.</p>

                <h3 style='color:#0f4c81;margin-top:24px;'>Resumen del pedido</h3>
                $tabla

                <div style='margin-top:20px;background:#fff;border:1px solid #e2e8f0;
                            border-radius:6px;padding:16px;'>
                    <p style='margin:0 0 6px;'><strong>Dirección de envío:</strong> 
                        " . htmlspecialchars($direccion) . "</p>
                    <p style='margin:0 0 6px;'><strong>Teléfono:</strong> 
                        " . htmlspecialchars($telefono) . "</p>
                    $notasHtml
                </div>

                <p style='margin-top:24px;color:#718096;font-size:13px;'>
                    Si tienes dudas, escríbenos a 
                    <a href='mailto:RutaPcventa@gmail.com' style='color:#0f4c81;'>RutaPcventa@gmail.com</a>.
                </p>
            </div>
        </div>";

        enviarEmail($usuario['email'], "Pedido #$pedidoId confirmado – RutaPc", $emailCliente);

        // — Email al DUEÑO —
        $emailDueno = "
        <div style='font-family:sans-serif;max-width:600px;margin:auto;color:#1a202c;'>
            <div style='background:#0f4c81;padding:24px 32px;border-radius:8px 8px 0 0;'>
                <h1 style='color:#00d4ff;margin:0;font-size:28px;'>RutaPc</h1>
                <p style='color:#fff;margin:4px 0 0;'>Nuevo pedido recibido</p>
            </div>
            <div style='background:#f7fafc;padding:32px;border-radius:0 0 8px 8px;'>
                <p>Se registró el pedido <strong>#$pedidoId</strong>.</p>

                <h3 style='color:#0f4c81;margin-top:0;'>Datos del cliente</h3>
                <div style='background:#fff;border:1px solid #e2e8f0;
                            border-radius:6px;padding:16px;margin-bottom:20px;'>
                    <p style='margin:0 0 6px;'><strong>Nombre:</strong> {$nombreCliente}</p>
                    <p style='margin:0 0 6px;'><strong>Email:</strong> 
                        <a href='mailto:{$usuario['email']}' style='color:#0f4c81;'>
                            {$usuario['email']}
                        </a>
                    </p>
                    <p style='margin:0 0 6px;'><strong>Teléfono:</strong> 
                        " . htmlspecialchars($telefono) . "</p>
                    <p style='margin:0;'><strong>Dirección:</strong> 
                        " . htmlspecialchars($direccion) . "</p>
                    $notasHtml
                </div>

                <h3 style='color:#0f4c81;'>Productos</h3>
                $tabla
            </div>
        </div>";

        enviarEmail('RutaPcventa@gmail.com', "Nuevo pedido #$pedidoId de {$nombreCliente}", $emailDueno);

        $exito = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | RutaPc</title>
    <link rel="stylesheet" href="CSS/catalogo.css">
    <link rel="stylesheet" href="CSS/checkout.css">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#3a2416">
    <link rel="apple-touch-icon" href="img/icon-192.png">
    <script src="js/pwa.js" defer></script>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%230f4c81'/><text y='22' x='4' font-size='20' font-family='sans-serif' fill='%2300d4ff'>R</text></svg>">
</head>
<body>

<!-- Encabezado igual al catálogo -->
<header class="hero">
    <div class="home-container">
        <a href="index.php" class="home-link">
            <p>Página principal</p>
            <img src="img/home.png" alt="Home">
        </a>
    </div>
    <div class="hero-image">
        <img src="img/panel.jpg" alt="Fondo tecnológico" class="fondo">
        <h1 class="logo">Checkout</h1>
        <div class="buttons">
            <a href="carrito.php" class="tab-btn">
                <img src="img/imgPer.png" alt="Carrito">
                Volver al carrito
            </a>
        </div>
    </div>
</header>

<div class="container checkout-wrapper">

<?php if ($exito): ?>
    <!-- ── Pantalla de éxito ── -->
    <div class="checkout-exito">
        <div class="exito-icono">✓</div>
        <h2>¡Pedido registrado!</h2>
        <p>Te enviamos una confirmación a <strong><?php echo htmlspecialchars($usuario['email']); ?></strong>.</p>
        <p>Nos pondremos en contacto contigo pronto para coordinar la entrega.</p>
        <a href="catalogo.php" class="btnAgregar">Seguir comprando</a>
    </div>

<?php else: ?>
    <div class="checkout-layout">

        <!-- ── Columna izquierda: formulario ── -->
        <div class="checkout-form-col">
            <h2 class="checkout-titulo">Datos de envío</h2>

            <?php if (!empty($errores)): ?>
                <div class="checkout-errores">
                    <?php foreach ($errores as $e): ?>
                        <p><?php echo htmlspecialchars($e); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="checkout-form">

                <div class="form-group">
                    <label for="direccion">Dirección de envío</label>
                    <input type="text" id="direccion" name="direccion"
                           placeholder="Calle, número, colonia, ciudad"
                           value="<?php echo htmlspecialchars($_POST['direccion'] ?? ''); ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono de contacto</label>
                    <input type="tel" id="telefono" name="telefono"
                           placeholder="10 dígitos"
                           value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="notas">Notas del pedido <span class="opcional">(opcional)</span></label>
                    <textarea id="notas" name="notas" rows="3"
                              placeholder="Instrucciones especiales, referencias, etc."><?php
                        echo htmlspecialchars($_POST['notas'] ?? '');
                    ?></textarea>
                </div>

                <button type="submit" class="btnAgregar btn-confirmar">
                    Confirmar pedido
                </button>
            </form>
        </div>

        <!-- ── Columna derecha: resumen ── -->
        <div class="checkout-resumen-col">
            <h2 class="checkout-titulo">Resumen</h2>
            <div class="resumen-lista">
                <?php foreach ($items as $item): ?>
                <div class="resumen-item">
                    <span class="resumen-nombre">
                        <?php echo htmlspecialchars($item['descripcion']); ?>
                        <em>× <?php echo $item['cantidad']; ?></em>
                    </span>
                    <span class="resumen-precio">
                        <?php echo format_currency($item['precio'] * $item['cantidad']); ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="resumen-total">
                <span>Total</span>
                <span><?php echo format_currency($total); ?></span>
            </div>
        </div>

    </div>
<?php endif; ?>

</div>
</body>
</html>
