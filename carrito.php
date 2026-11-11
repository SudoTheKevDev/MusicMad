<?php
session_start();
require_once 'dataBase.php';
require_once 'includes/auth.php';

// Solo usuarios autenticados pueden ver el carrito
if (!estaAutenticado()) {
  header('Location: login.php');
  exit;
}

$database = new Database();
$db = $database->getConnection();

$usuarioId = $_SESSION['usuario_id'];

// --- Obtener o crear carrito del usuario ---
function obtenerCarritoId($db, $usuarioId) {
  $stmt = $db->prepare("SELECT id FROM carrito WHERE usuario_id = :uid LIMIT 1");
  $stmt->execute([':uid' => $usuarioId]);
  $carrito = $stmt->fetch(PDO::FETCH_ASSOC);
  return $carrito ? $carrito['id'] : null;
}

// --- Procesar acciones POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $carritoId = obtenerCarritoId($db, $usuarioId);

  if ($action === 'remove' && isset($_POST['producto_id']) && $carritoId) {
    $stmt = $db->prepare(
      "DELETE FROM detalle_carrito WHERE carrito_id = :cid AND producto_id = :pid"
    );
    $stmt->execute([':cid' => $carritoId, ':pid' => (int) $_POST['producto_id']]);
  }

  if ($action === 'update' && isset($_POST['producto_id'], $_POST['cantidad']) && $carritoId) {
    $nuevaCantidad = (int) $_POST['cantidad'];
    if ($nuevaCantidad <= 0) {
      // Si la cantidad llega a 0 o menos, eliminar el renglón
      $stmt = $db->prepare(
        "DELETE FROM detalle_carrito WHERE carrito_id = :cid AND producto_id = :pid"
      );
      $stmt->execute([':cid' => $carritoId, ':pid' => (int) $_POST['producto_id']]);
    } else {
      $stmt = $db->prepare(
        "UPDATE detalle_carrito SET cantidad = :cant WHERE carrito_id = :cid AND producto_id = :pid"
      );
      $stmt->execute([
        ':cant' => $nuevaCantidad,
        ':cid'  => $carritoId,
        ':pid'  => (int) $_POST['producto_id']
      ]);
    }
  }

  if ($action === 'clear' && $carritoId) {
    $stmt = $db->prepare("DELETE FROM detalle_carrito WHERE carrito_id = :cid");
    $stmt->execute([':cid' => $carritoId]);
  }

  header('Location: ' . $_SERVER['PHP_SELF']);
  exit;
}

// --- Leer el carrito desde la BD ---
$carritoId = obtenerCarritoId($db, $usuarioId);
$items = [];
$total = 0;

if ($carritoId) {
  $stmtItems = $db->prepare("
    SELECT dc.producto_id, dc.cantidad,
           ip.descripcion, ip.precio, ip.imagen_url, ip.tipo
    FROM detalle_carrito dc
    INNER JOIN info_productos ip ON ip.id = dc.producto_id
    WHERE dc.carrito_id = :cid
    ORDER BY dc.id ASC
  ");
  $stmtItems->execute([':cid' => $carritoId]);
  $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

  foreach ($items as $item) {
    $total += $item['precio'] * $item['cantidad'];
  }
}

function format_currency($value) {
  return '$' . number_format($value, 2);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Carrito | MusicMad</title>
  <link rel="stylesheet" href="CSS/catalogo.css">
  <link rel="stylesheet" href="CSS/carrito.css">
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="#3a2416">
  <link rel="apple-touch-icon" href="img/icon-192.png">
  <script src="js/pwa.js" defer></script>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%233a2416'/><text y='22' x='2' font-size='20' font-family='Georgia' font-weight='bold' fill='%23d2a679'>M</text></svg>"></head>
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
      <div  class="layout-carrito"> </div>
      <h1 class="logoCarrito">Mi Carrito</h1>
   </div>
  </header>

  <div class="container carrito-wrapper">

    <?php if (empty($items)): ?>
      <div class="carrito-vacio">
        <p>Tu carrito está vacío.</p>
        <a href="catalogo.php" class="btnAgregar">Ver catálogo</a>
      </div>

    <?php else: ?>
      <table class="carrito-tabla">
        <thead>
          <tr>
            <th>Producto</th>
            <th>Precio unitario</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
          <tr>
            <td>
              <?php if (!empty($item['imagen_url'])): ?>
                <img src="img/<?php echo htmlspecialchars($item['imagen_url']); ?>"
                     alt="<?php echo htmlspecialchars($item['descripcion']); ?>"
                     class="carrito-img">
              <?php endif; ?>
              <?php echo htmlspecialchars($item['descripcion']); ?>
            </td>
            <td><?php echo format_currency($item['precio']); ?></td>

            <!-- Cambiar cantidad -->
            <td>
              <form method="post" class="form-inline">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="producto_id" value="<?php echo $item['producto_id']; ?>">
                <input type="number" name="cantidad"
                       value="<?php echo $item['cantidad']; ?>"
                       min="0" class="input-cantidad"
                       onchange="this.form.submit()">
              </form>
            </td>

            <td><?php echo format_currency($item['precio'] * $item['cantidad']); ?></td>

            <!-- Eliminar producto -->
            <td>
              <form method="post">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="producto_id" value="<?php echo $item['producto_id']; ?>">
                <button type="submit" class="btnEliminar">Eliminar</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Total y acciones globales -->
      <div class="carrito-footer">
        <p class="carrito-total"><strong>Total: <?php echo format_currency($total); ?></strong></p>
        <div class="carrito-acciones">
          <form method="post" style="display:inline;">
            <input type="hidden" name="action" value="clear">
            <button type="submit" class="btnEliminar">Vaciar carrito</button>
          </form>
          <a href="checkout.php" class="btnAgregar">Proceder al pago</a>
        </div>
      </div>
    <?php endif; ?>

  </div>
</body>
    </div>

</html>

