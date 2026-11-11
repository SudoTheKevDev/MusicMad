<?php
  require_once 'dataBase.php';
  require_once 'includes/auth.php';

  // Iniciamos la conexión a la base de datos
  $database = new Database();
  $db = $database->getConnection();

  $query = "SELECT * FROM info_productos";
  $stmt = $db->prepare($query);
  $stmt->execute();
  $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // --- Lógica del carrito en BD ---
  // Solo procesamos el POST si el usuario está autenticado
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && estaAutenticado()) {

    if ($_POST['action'] === 'add' && isset($_POST['product_id'])) {
      $productId = (int) $_POST['product_id'];
      $usuarioId = $_SESSION['usuario_id']; // Asegúrate que tu auth guarda esto en sesión

      // 1. Buscar si ya existe un carrito activo para este usuario
      $stmtCarrito = $db->prepare("SELECT id FROM carrito WHERE usuario_id = :uid LIMIT 1");
      $stmtCarrito->execute([':uid' => $usuarioId]);
      $carrito = $stmtCarrito->fetch(PDO::FETCH_ASSOC);

      if ($carrito) {
        $carritoId = $carrito['id'];
      } else {
        // Crear nuevo carrito para el usuario
        $stmtNuevo = $db->prepare("INSERT INTO carrito (usuario_id, fecha_creacion) VALUES (:uid, NOW())");
        $stmtNuevo->execute([':uid' => $usuarioId]);
        $carritoId = $db->lastInsertId();
      }

      // 2. Verificar si el producto ya está en el detalle del carrito
      $stmtDetalle = $db->prepare(
        "SELECT id, cantidad FROM detalle_carrito WHERE carrito_id = :cid AND producto_id = :pid"
      );
      $stmtDetalle->execute([':cid' => $carritoId, ':pid' => $productId]);
      $detalle = $stmtDetalle->fetch(PDO::FETCH_ASSOC);

      if ($detalle) {
        // Ya existe: incrementar cantidad
        $stmtUpdate = $db->prepare(
          "UPDATE detalle_carrito SET cantidad = cantidad + 1 WHERE id = :id"
        );
        $stmtUpdate->execute([':id' => $detalle['id']]);
      } else {
        // No existe: insertar nuevo renglón
        $stmtInsert = $db->prepare(
          "INSERT INTO detalle_carrito (carrito_id, producto_id, cantidad) VALUES (:cid, :pid, 1)"
        );
        $stmtInsert->execute([':cid' => $carritoId, ':pid' => $productId]);
      }

      // Redirigir para evitar reenvío del formulario
      header('Location: ' . $_SERVER['PHP_SELF']);
      exit;
    }
  }
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Catálogo | MusicMad</title>
<link rel="stylesheet" href="CSS/catalogo.css">
<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#3a2416">
<link rel="apple-touch-icon" href="img/icon-192.png">
<script src="js/pwa.js" defer></script>
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%233a2416'/><text y='22' x='2' font-size='20' font-family='Georgia' font-weight='bold' fill='%23d2a679'>M</text></svg>"></head>

<body>
  <script>
    function showForm(tipo) {
      const servicios = document.getElementById("servicios");
      const articulos = document.getElementById("articulos");

      if (tipo == 'servicios') {
        servicios.classList.remove('hidden');
        articulos.classList.add('hidden');
      } else {
        servicios.classList.add('hidden');
        articulos.classList.remove('hidden');
      }
    }
  </script>

  <!-- Encabezado -->
  <header class="hero">
    <div class="home-container">
      <a href="index.php" class="home-link">
        <p>Página principal</p>
        <img src="img/home.png" alt="Home">
      </a>
    </div>
    <div class="hero-image">
      <h1 class="logo">Catálogo</h1>
      <div class="buttons">
        <button class="tab-btn" onclick="showForm('servicios')">
          <img src="img/imgPer.png" alt="Usuario">
          Servicios
        </button>
        <button class="tab-btn" onclick="showForm('articulos')">
          <img src="img/imgPer.png" alt="Usuario">
          Artículos
        </button>
      </div>
    </div>
  </header>

  <div class="container">

    <!-- SERVICIOS -->
    <div id="servicios">
      <?php
      foreach ($productos as $prod) {
        if ($prod['tipo'] == 'servicio') {
      ?>
      <div class="fotos-container">

        <?php if ($prod['id'] == 1) { ?>
        <div class="slider">
          <figure>
            <img src="img/foto1.jpg" alt="Foto 1">
            <img src="img/foto2.jpeg" alt="Foto 2">
            <img src="img/foto3.jpeg" alt="Foto 3">
            <img src="img/foto4.jpeg" alt="Foto 4">
            <img src="img/foto1.jpg" alt="Foto 1">
          </figure>
        </div>
        <?php } elseif ($prod['id'] == 2) { ?>
        <div class="slider">
          <figure>
            <img src="img/foto5.jpg" alt="Foto 5">
            <img src="img/foto6.jpeg" alt="Foto 6">
            <img src="img/foto7.jpeg" alt="Foto 7">
            <img src="img/foto8.jpeg" alt="Foto 8">
            <img src="img/foto5.jpg" alt="Foto 5">
          </figure>
        </div>
        <?php } ?>

        <h1><?php echo htmlspecialchars($prod['descripcion']); ?></h1>
        <h2><?php echo htmlspecialchars($prod['precio']); ?></h2>

        <?php if (estaAutenticado()): ?>
        <form method="post">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
          <button type="submit" class="btnAgregar">Agregar al carrito</button>
        </form>
        <?php else: ?>
          <p class="aviso-login"><a href="login.php">Inicia sesión</a> para agregar al carrito.</p>
        <?php endif; ?>

      </div>
      <?php
        }
      }
      ?>
    </div>

    <!-- ARTÍCULOS -->
    <div id="articulos" class="hidden">
      <?php
      foreach ($productos as $prod) {
        if ($prod['tipo'] == 'articulos') {
      ?>
      <div class="fotos-container">
        <img src="img/<?php echo htmlspecialchars($prod['imagen_url']); ?>" alt="Imagen producto">
        <h1><?php echo htmlspecialchars($prod['descripcion']); ?></h1>
        <h2><?php echo htmlspecialchars($prod['precio']); ?></h2>
        <h2>Articulos Disponibles: <?php echo htmlspecialchars($prod['stock']); ?></h2>

        <?php if (estaAutenticado()): ?>
        <form method="post">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
          <button type="submit" class="btnAgregar">Agregar al carrito</button>
        </form>
        <?php else: ?>
          <p class="aviso-login"><a href="login.php">Inicia sesión</a> para agregar al carrito.</p>
        <?php endif; ?>

      </div>
      <?php
        }
      }
      ?>
    </div>

  </div>
</body>
</html>