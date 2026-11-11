<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php
    require_once 'dataBase.php';

    //Iniciamos la conexión a la base de datos
    $database = new Database();
    $db = $database->getConnection();

    if (isset($_GET['id'])){//Obtener los datos actuales para mandarlos al formulario
        $id = $_GET['id'];

        $query = "SELECT * FROM info_productos WHERE id = :id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        //Si el id no existe, regresa al admin
        if (!$producto) {
            header("Location: admin.php");
            exit;
        }
    }

    //Ejecutar el UPDATE al presionar el botón
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_nueva = $_POST['id'];
        $descrip_nueva = $_POST['descripcion'];
        $precio_nuevo = $_POST['precio'];
        $stock_nuevo = $_POST['stock'];

        $query_update = "UPDATE info_productos SET 
        descripcion = :descripcion,
        precio = :precio,
        stock = :stock WHERE id = :id";
        $stmt_update = $db->prepare($query_update);

        $stmt_update->bindParam(':descripcion', $descrip_nueva);
        $stmt_update->bindParam(':precio', $precio_nuevo);
        $stmt_update->bindParam(':stock', $stock_nuevo);
        $stmt_update->bindParam(':id', $id_nueva, PDO::PARAM_INT);

        if($stmt_update->execute()) {
            //Si se guardó con exito, te manda a admin.php para ver los cambios
            header("Location: admin.php");
            exit;
        } else {
            echo "<script>alert('Error al actualizar el registro');</script>";
        }
    }
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Modificar | RutaPc</title>
    <link rel="stylesheet" href="CSS/form-modificar.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%230f4c81'/><text y='22' x='4' font-size='20' font-family='sans-serif' fill='%2300d4ff'>R</text></svg>">

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
        <div id="form-mod">
            <!--Formulario para modificar los datos-->
            <h2>Plataforma de cambios</h2>
            <form action="form-modificar.php" method="POST" id="formularioModificar"><!--Aquí se pone todo para hacer el formulario-->
                <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">    
            
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <input type="text" id="descripcion" name="descripcion" value="<?php echo htmlspecialchars($producto['descripcion']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="precio">Precio</label>
                    <input type="number" id="precio" name="precio" min="0" step="0.01" value="<?php echo htmlspecialchars($producto['precio']); ?>" required>
                </div>          
<?php 
    if ($producto['id'] > 2) {
?>
                <div class="form-group">
                    <label for="stock">Articulos Disponibles</label>
                    <input type="number" id="stock" name="stock" min="0" value="<?php echo htmlspecialchars($producto['stock']); ?>" required>
                </div> 
<?php 
    }
?>
                <button type="submit">Ejecutar cambios</button>
            </form>
        </div>

        <button class="btn-regresar" onclick="">
            <a href="admin.php" class="regresar-link">
                <span>Regresar</span>
            </a>
        </button>

    </div>

</body>

</html>