<?php
// ============================================================
//  RutaPc – Funciones de autenticación
//  includes/auth.php
// ============================================================

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Helpers ─────────────────────────────────────────────────

function estaAutenticado(): bool
{
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

/**
 * Redirige al login si NO hay sesión activa.
 */
function requerirLogin(): void
{
    if (!estaAutenticado() || $_SESSION['usuario_rol'] !== 'administrador') {
        header('Location: index.php');
        exit;
    }
}

/**
 * Redirige al dashboard si YA hay sesión activa.
 */
function redirigirSiAutenticado(): void
{
    if (estaAutenticado()) {
        header('Location: index.php');
        exit;
    }

}

function sesion(string $clave): mixed
{
    return $_SESSION[$clave] ?? null;
}


function cerrarSesion(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

// ── Registro ─────────────────────────────────────────────────

function registrarUsuario(array $datos): array
{
    $nombre   = trim($datos['nombre']   ?? '');
    $apellido = trim($datos['apellido'] ?? '');
    $email    = trim($datos['email']    ?? '');
    $password = $datos['password']      ?? '';
    $confirm  = $datos['confirm']       ?? '';
    $rol      = $datos['rol']           ?? 'empleado';

    if (empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
        return ['ok' => false, 'mensaje' => 'Todos los campos son obligatorios.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'mensaje' => 'El correo electrónico no es válido.'];
    }
    if (strlen($password) < 8) {
        return ['ok' => false, 'mensaje' => 'La contraseña debe tener al menos 8 caracteres.'];
    }
    if ($password !== $confirm) {
        return ['ok' => false, 'mensaje' => 'Las contraseñas no coinciden.'];
    }
    if (!in_array($rol, ['administrador', 'empleado'], true)) {
        $rol = 'empleado';
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        return ['ok' => false, 'mensaje' => 'Ya existe una cuenta con ese correo.'];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $pdo->prepare(
        'INSERT INTO usuarios (nombre, apellido, avatar, bio, color_perfil, email, password, rol)
         VALUES (:nombre, :apellido, :avatar, :bio, :color, :email, :password, :rol)'
    );
    $stmt->execute([
        ':nombre'   => htmlspecialchars($nombre,   ENT_QUOTES, 'UTF-8'),
        ':apellido' => htmlspecialchars($apellido, ENT_QUOTES, 'UTF-8'),
        ':avatar'   => 'img/avatares/Avatares0.png',
        ':bio'      => '',
        ':color'    => '#00d4ff',
        ':email'    => $email,
        ':password' => $hash,
        ':rol'      => $rol,
    ]);

    return ['ok' => true, 'mensaje' => '¡Cuenta creada exitosamente! Ya puedes iniciar sesión.'];
}

// ── Login ─────────────────────────────────────────────────────

function iniciarSesion(string $email, string $password): array
{
    $email    = trim($email);
    $password = trim($password);

    if (empty($email) || empty($password)) {
        return ['ok' => false, 'mensaje' => 'Completa todos los campos.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'mensaje' => 'Correo electrónico no válido.'];
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare(
        'SELECT id, nombre, apellido, avatar, bio, color_perfil, email, password, rol, activo
         FROM usuarios WHERE email = :email LIMIT 1'
    );
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($password, $usuario['password'])) {
        return ['ok' => false, 'mensaje' => '¡Credenciales incorrectas!'];
    }
    if (!$usuario['activo']) {
        return ['ok' => false, 'mensaje' => 'Tu cuenta está desactivada. Contacta al administrador.'];
    }
    
// Solo regenera el ID en localhost (XAMPP). En InfinityFree causa bucles con el firewall.
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if ($host === 'localhost' || $host === '127.0.0.1') {
        session_regenerate_id(true);
    }
 
    $_SESSION['usuario_id']       = $usuario['id'];
    $_SESSION['usuario_nombre']   = $usuario['nombre'];
    $_SESSION['usuario_apellido'] = $usuario['apellido'];
    $_SESSION['usuario_email']    = $usuario['email'];
    $_SESSION['usuario_rol']      = $usuario['rol'];
    $_SESSION['usuario_avatar']   = $usuario['avatar']       ?: 'img/avatares/Avatares0.png';
    $_SESSION['usuario_bio']      = $usuario['bio']          ?? '';
    $_SESSION['usuario_color']    = $usuario['color_perfil'] ?: '#00d4ff';

    return ['ok' => true, 'mensaje' => 'Bienvenido/a, ' . htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8') . '.'];
}

// ── Perfil de usuario ───────────────────────────────────────
//  Etiqueta de perfil: avatar + biografía + color de acento.
//  (Solo avatares predefinidos, guardados en el propio servidor;
//  no se consulta ninguna API externa).

/** Avatares predefinidos disponibles para elegir en "Mi perfil". */
function avataresDisponibles(): array
{
    $avatares = [];
    for ($i = 0; $i < 10; $i++) {
        $avatares[] = "img/avatares/Avatares{$i}.png";
    }
    return $avatares;
}

/** Trae de la BD los datos de perfil del usuario autenticado. */
function obtenerPerfil(int $usuarioId): ?array
{
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        'SELECT id, nombre, apellido, avatar, bio, color_perfil, email, rol
         FROM usuarios WHERE id = :id LIMIT 1'
    );
    $stmt->execute([':id' => $usuarioId]);
    $usuario = $stmt->fetch();

    return $usuario ?: null;
}

/**
 * Actualiza avatar, biografía y color del usuario autenticado
 * y refresca la sesión para que el cambio se vea al instante.
 */
function actualizarPerfil(int $usuarioId, array $datos): array
{
    $avatar = $datos['avatar'] ?? '';
    $bio    = trim($datos['bio'] ?? '');
    $color  = $datos['color']  ?? '#00d4ff';

    if (!in_array($avatar, avataresDisponibles(), true)) {
        return ['ok' => false, 'mensaje' => 'Selecciona un avatar válido.'];
    }
    if (mb_strlen($bio) > 280) {
        $bio = mb_substr($bio, 0, 280);
    }
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
        $color = '#00d4ff';
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare(
        'UPDATE usuarios SET avatar = :avatar, bio = :bio, color_perfil = :color WHERE id = :id'
    );
    $stmt->execute([
        ':avatar' => $avatar,
        ':bio'    => htmlspecialchars($bio, ENT_QUOTES, 'UTF-8'),
        ':color'  => $color,
        ':id'     => $usuarioId,
    ]);

    $_SESSION['usuario_avatar'] = $avatar;
    $_SESSION['usuario_bio']    = $bio;
    $_SESSION['usuario_color']  = $color;

    return ['ok' => true, 'mensaje' => '¡Perfil actualizado correctamente!'];
}
