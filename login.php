<?php
// ============================================================
//  RutaPc – Inicio de sesión
//  login.php
// ============================================================
ob_start(); // <-- TRUCO CLAVE: Guarda el HTML en memoria y evita errores de cabeceras en InfinityFree
require_once __DIR__ . '/includes/auth.php';

redirigirSiAutenticado();


$pageTitle = 'Iniciar sesión';
$mensaje   = '';
$tipoMsg   = '';

// Mensaje tras registro exitoso
if (isset($_GET['registered'])) {
    $mensaje = '¡Cuenta creada! Ya puedes iniciar sesión.';
    $tipoMsg = 'success';
}
// Mensaje tras cerrar sesión
if (isset($_GET['logout'])) {
    $mensaje = 'Sesión cerrada correctamente.';
    $tipoMsg = 'info';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = filter_input(INPUT_POST, 'email',    FILTER_SANITIZE_EMAIL) ?? '';
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW)     ?? '';

    $resultado = iniciarSesion($email, $password);

    if ($resultado['ok']) {
        header('Location: ' . APP_URL . '/index.php');
        exit;
    }

    $mensaje = $resultado['mensaje'];
    $tipoMsg = 'error';
}

$baseUrl = APP_URL;
?>

<?php require_once __DIR__ . '/includes/header.php'; ?>
<link rel="stylesheet" href="CSS/form-login.css">

</head>
<body>
    <header>
        <div class="home-container">
            <a href="index.php" class="home-link">
                <p>Página principal</p>
                <img src="img/home.png" alt="Home">
            </a>
        </div>
    </header>
<main class="auth-wrap">
    <div class="auth-box">

        <div class="containter-head">
            <div class="auth-logo">
                <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
<rect width="32" height="32" rx="8" fill="#3a2416"/>
<path d="M5 24V8L11 18L16 8L21 18L27 8V24"
      fill="none"
      stroke="#d2a679"
      stroke-width="2.8"
      stroke-linecap="round"
      stroke-linejoin="round"/>
               </svg>
                <span class="auth-logo-text">MusicMad</span>
            </div>
            <h1 class="auth-title">Bienvenido de nuevo</h1>
        </div>

        <p class="auth-desc">Ingresa tus credenciales para acceder al sistema.</p>

        <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipoMsg ?>" data-autohide="5000">
            <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="" id="formLogin" class="grid-form" novalidate>

            <div class="form-group full-width">
                <label class="form-label" for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" class="form-control"
                    placeholder="correo@ejemplo.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="email" required>
            </div>

            <div class="form-group full-width">
                <label class="form-label" for="password">Contraseña</label>
                <div style="position:relative;">
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="••••••••" autocomplete="current-password"
                        style="padding-right:46px;" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;">
                Iniciar sesión
            </button>

        </form>

        <div class="divider">o</div>

        <p class="text-center text-sm text-muted">
            ¿No tienes cuenta?
            <a href="registro.php">Crear una cuenta</a>
        </p>


    </div>
</main>
</body>