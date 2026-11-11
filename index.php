

<?php
ob_start(); // <-- TRUCO CLAVE: Guarda el HTML en memoria y evita errores de cabeceras en InfinityFree
// ============================================================
//  RutaPc – Barra de navegación
//  includes/auth.php
// ============================================================
require_once __DIR__ . '/includes/auth.php';


date_default_timezone_set('America/Mexico_City');
$usuarioNombre = sesion('usuario_nombre') ?? '';
$usuarioRol    = sesion('usuario_rol')    ?? '';
$currentFile   = basename($_SERVER['PHP_SELF']);
$baseUrl       = defined('APP_URL') ? APP_URL : '';

$hora   = (int) date('H');
$saludo = match(true) {
    $hora >= 5  && $hora < 12 => 'Buenos días',
    $hora >= 12 && $hora < 19 => 'Buenas tardes',
    default                   => 'Buenas noches',
};

?>



<?php require_once __DIR__ . '/includes/header.php'; ?>

  <link rel="stylesheet" href= "CSS/Styke.css">
</head>
<body> 

  <!-- Encabezado -->
  <header class="hero">

  <!-- Lado izquierdo con imagen -->
  <div class="hero-image">

    <div class="cerrarSesion-divider">
      <a href="logout.php" class="cerrarSesion-link">
        <p>Cerrar sesión<p>
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
      </a>
    </div>

    <img src="img/papelArrugado.jpg" alt="Fondo tecnológico" class="fondo">
    <h1 class="logo">MusicMad</h1>
     <!-- Lado derecho con degradado y botones -->
    

      <div class="buttons">
<?php if (estaAutenticado() == false): ?>
  <button class="btn">
    <img src="img/imgPer.png" alt="Usuario">
    <a href="login.php"> Iniciar sesión
    </a>
  </button> 

  <?php else: ?>
<button class="btn" id="btnUsuario">
    <img src="<?= htmlspecialchars(sesion('usuario_avatar') ?? 'img/avatares/Avatares0.png', ENT_QUOTES, 'UTF-8') ?>"
         alt="Mi perfil" class="btn-avatar">
    <a href="perfil.php">
      <?= htmlspecialchars($usuarioNombre, ENT_QUOTES, 'UTF-8') ?>,
      <?= htmlspecialchars(' ', ENT_QUOTES, 'UTF-8') ?>
      <?= htmlspecialchars($saludo, ENT_QUOTES, 'UTF-8') ?>
    </a>
  </button>
<?php endif; ?>


<?php if (estaAutenticado() == false): ?>
  <button class="btn" id="carritoBtn">
    <img src="img/imgPer.png" alt="Usuario">
    <a href="login.php"> Carrito
    </a>
  </button> 

  <?php else: ?>
<button class="btn" id="carritoBtn">
    <img src="img/carrito.png" alt="Usuario">
    <a href="carrito.php">
      Carrito
    </a>
  </button> 

  <?php endif; ?>

        <button class="btn">
          <img src="img/imgMe.png" alt="Catálogo">
          <a href="catalogo.php">Cátalogo</a>
        </button>
      
        <button class="btn" id= "btn-nosotros">
          <img src= "img/imgNosotros.png" alt="Nosotros">
          <a href="#nosotros-container">Nosotros</a>
        </button>


<?php if ($usuarioRol === 'administrador'): ?>
  <button class="btn">
    <img src="img/imgMod.png" alt="Modificar">
    <a href=" admin.php">
      Modificar
    </a>
  </button> 
<?php endif; ?>




</div> </div> </header>

<div class="nosotros-container" id="nosotros-container">
  <section id="about">
    <h2><h2>¿Quiénes somos?</h2>
<p>
Somos un proyecto musical formado por dos estudiantes del CETI, apasionados por el arte y comprometidos con hacer vibrar tu talento.
 Nos especializamos en la venta de instrumentos musicales, audio profesional, soporte técnico y mantenimiento especializado,
  adaptándonos al ritmo y presupuesto de cada músico
</p>

<p>
Nuestro objetivo es brindar un servicio confiable, transparente y profesional,
 manteniendo una comunicación cercana durante todo el proceso. A pesar de ser un 
 equipo pequeño, contamos con experiencia en proyectos académicos y particulares, 
 así como referencias que respaldan la calidad de nuestro trabajo.
</p>


  </section>
<div id="redes-container">
  <div class="red-social">  
    <a href="https://wa.me/3332407791" class="redes-link">
      <img src="img/contacto.jpg" alt="whatsapp">
      <p>WhatsApp</p>
    </a>
  </div>

  <div class="red-social">
    <a href="https://www.instagram.com/la_ruta_pc/" class="redes-link">
      <img src="img/instagram.jpg" alt="instagram">
      <p>Instagram</p>
    </a>
  </div> 
</div>

</div>




</div>

<footer>
    <small>2026 &copy; MusicMad</small>
  </footer>

</body>

</html>


<!-- ============================================================
// PIE DE PÁGINA
// Cierra la estructura HTML e incluye scripts globales.
// ============================================================-->
