<?php
// ============================================================
//  MusicMad – Mi perfil
//  perfil.php
//
//  Etiqueta de usuario editable (avatar + biografía + color),
//  igual en espíritu a la actividad 1.4, pero:
//   - Sin consumo de la API de GitHub (solo avatares locales).
//   - Los datos se guardan en la base de datos de MusicMad,
//     no en localStorage ni en memoria temporal.
// ============================================================
ob_start();
require_once __DIR__ . '/includes/auth.php';

if (!estaAutenticado()) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Mi perfil';
$mensaje   = '';
$tipoMsg   = '';

$usuarioId = (int) sesion('usuario_id');
$perfil    = obtenerPerfil($usuarioId);

if (!$perfil) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'avatar' => filter_input(INPUT_POST, 'avatar', FILTER_UNSAFE_RAW) ?? '',
        'bio'    => filter_input(INPUT_POST, 'bio',    FILTER_UNSAFE_RAW) ?? '',
        'color'  => filter_input(INPUT_POST, 'color',  FILTER_UNSAFE_RAW) ?? '#00d4ff',
    ];

    $resultado = actualizarPerfil($usuarioId, $datos);
    $mensaje   = $resultado['mensaje'];
    $tipoMsg   = $resultado['ok'] ? 'success' : 'error';

    // Refrescamos con los datos ya guardados
    $perfil = obtenerPerfil($usuarioId);
}

$avatares = avataresDisponibles();
$baseUrl  = APP_URL;
?>

<?php require_once __DIR__ . '/includes/header.php'; ?>
<link rel="stylesheet" href="CSS/form-perfil.css">
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

    <main class="perfil-wrap">
        <div class="perfil-box">

            <div class="perfil-head">
                <div class="auth-logo">
                    <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
                        <rect width="32" height="32" rx="8" fill="#3a2416" />
                        <path d="M5 24V8L11 18L16 8L21 18L27 8V24" fill="none" stroke="#d2a679"
                            stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <h1>Mi perfil</h1>
            </div>
            <p class="perfil-desc">Elige tu avatar, cuenta algo sobre ti y personaliza el color de tu tarjeta.</p>

            <?php if ($mensaje): ?>
                <div class="alert alert-<?= $tipoMsg ?>">
                    <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <div class="perfil-grid">

                <!-- Columna izquierda: formulario -->
                <section class="perfil-form-col">
                    <form method="POST" action="" id="form-perfil" novalidate>

                        <input type="hidden" id="input-avatar" name="avatar"
                            value="<?= htmlspecialchars($perfil['avatar'], ENT_QUOTES, 'UTF-8') ?>">

                        <div class="form-group">
                            <label>Elige un avatar</label>
                            <div id="avatar-selector" class="avatar-grid">
                                <?php foreach ($avatares as $ruta): ?>
                                    <img
                                        src="<?= htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8') ?>"
                                        alt="Avatar"
                                        class="avatar-option"
                                        data-url="<?= htmlspecialchars($ruta, ENT_QUOTES, 'UTF-8') ?>">
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Nombre a mostrar</label>
                            <p class="perfil-static">
                                <?= htmlspecialchars($perfil['nombre'] . ' ' . $perfil['apellido'], ENT_QUOTES, 'UTF-8') ?>
                                <br>
                                <small>(El nombre se cambia desde tu cuenta, no desde aquí.)</small>
                            </p>
                        </div>

                        <div class="form-group">
                            <label for="input-bio">Biografía</label>
                            <textarea id="input-bio" name="bio" rows="3" maxlength="280"
                                placeholder="Cuéntanos sobre ti..."><?= htmlspecialchars($perfil['bio'], ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="input-color">Color de acento</label>
                            <input type="color" id="input-color" name="color"
                                value="<?= htmlspecialchars($perfil['color_perfil'], ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <button type="submit" class="btn-primary">Guardar cambios</button>
                    </form>

                    <a href="index.php" class="back-link">&larr; Volver a la página principal</a>
                </section>

                <!-- Columna derecha: vista previa en vivo -->
                <section class="perfil-preview-col">
                    <article class="profile-card" id="profile-card">
                        <div class="profile-card-header" id="preview-header"
                            style="background-color: <?= htmlspecialchars($perfil['color_perfil'], ENT_QUOTES, 'UTF-8') ?>;"></div>
                        <img id="avatar-preview" class="profile-card-avatar"
                            src="<?= htmlspecialchars($perfil['avatar'], ENT_QUOTES, 'UTF-8') ?>" alt="Avatar">
                        <div class="profile-card-body">
                            <h3 id="name-preview">
                                <?= htmlspecialchars($perfil['nombre'] . ' ' . $perfil['apellido'], ENT_QUOTES, 'UTF-8') ?>
                            </h3>
                            <span class="profile-card-role" id="role-preview">
                                <?= htmlspecialchars(ucfirst($perfil['rol']), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                            <p class="profile-card-bio" id="bio-preview">
                                <?= $perfil['bio'] !== '' ? htmlspecialchars($perfil['bio'], ENT_QUOTES, 'UTF-8') : 'Aún no escribes una biografía...' ?>
                            </p>
                        </div>
                    </article>
                </section>

            </div>
        </div>
    </main>

    <script src="js/perfil.js"></script>
</body>
</html>
