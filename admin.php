<?php
  require_once 'dataBase.php';
  require_once __DIR__ . '/includes/auth.php';

  //Iniciamos la conexión a la base de datos
  $database = new Database();
  $db = $database->getConnection();

  $query = "SELECT * FROM info_productos";
  $stmt = $db->prepare($query);
  $stmt->execute();
  $productos = $stmt->fetchAll(PDO::FETCH_ASSOC); //Le dice a la BD que mande TODOS los registros que
                                                  //se encontraron con la consulta SQL
  //FETCH_ASSOC, pide que cree un arreglo asociativo, donde las claves para acceder a los datos sean 
  // exactamente los mismos nombres de las columnas de la tabla en la BD (descripcion, precio, tipo). 
  //Todo lo guarda en la variable $productos                                               
requerirLogin();
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
    <title>Admin | RutaPc</title>
    <link rel="stylesheet" href="CSS/catalogo.css">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#3a2416">
    <link rel="apple-touch-icon" href="img/icon-192.png">
    <script src="js/pwa.js" defer></script>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%230f4c81'/><text y='22' x='4' font-size='20' font-family='sans-serif' fill='%2300d4ff'>R</text></svg>">

</head>

<body>
  <script> 
    function showForm(tipo) {
      //Obtener los contenedores -> vincular lo de html con javascript
      const servicios = document.getElementById("servicios");
      const articulos = document.getElementById("articulos");

      if (tipo == 'servicios') {
        servicios.classList.remove('hidden'); //Quita el hidden al formulario de registro
        articulos.classList.add('hidden'); //Agrega el hidden al formulario de login -> se oculta
      } else {
        servicios.classList.add('hidden'); // Agrega el hidden al formulario de registro -> se oculta
        articulos.classList.remove('hidden'); //Quita el hidden al formulario de login
      }
    }
  </script>

  <!-- Encavezado -->
  <header class="hero">
    <div class="home-container">
      <a href="index.php" class="home-link">
        <p>Página principal</p>
        <img src="img/home.png" alt="Home">
      </a>
    </div>
    <!-- Lado izquierdo con imagen -->
    <div class="hero-image">
      <img src="img/panel.jpg" alt="Fondo tecnológico" class="fondo">
      <h1 class="logo">Administrador</h1>
      <h2>Bienvenido</h2>
      <!-- Lado derecho con degradado y botones -->
      <div class="buttons">
        <button class="tab-btn" onclick="showForm('servicios')">
          <img src="img/imgPer.png" alt="Usuario">
          Servicios
        </button>
        <button class="tab-btn" onclick="showForm('articulos')">
          <img src="img/imgPer.png" alt="Usuario">
          Artículos</button>
      </div>
    </div>
  </header>

  <div class="container"> <!--Para poner todos los cuadritos-->
    <div id="servicios"> <!--Divisón para la parte de Servicios-->
      <?php 
      // Recorremos los productos y solo imprimimos los que sean 'servicios'
      foreach ($productos as $prod) { 
        if ($prod['tipo'] == 'servicio') {
      ?>

      <!--Reparación equipo de computo-->
      <div class="cuadritos-container"> 

        <?php if ($prod['id'] == 1) { ?> <!--Para comprobar que sea el id 1 AKA el de reparación-->

        <div class="slider"><!--Clase para imágenes deslizantes-->
          <figure>
            <img src="img/foto1.jpg" alt="Foto 1">
            <img src="img/foto2.jpeg" alt="Foto 2">
            <img src="img/foto3.jpeg" alt="Foto 3">
            <img src="img/foto4.jpeg" alt="Foto 4">
            <img src="img/foto1.jpeg" alt="Foto 1">
          </figure>
        </div>
        
        <?php } elseif ($prod['id'] == 2) { ?> <!--Para comprobar que sea el id 2 AKA el de instalación-->
        
        <!--Instalación-->
        <div class="slider"><!--Clase para imágenes deslizantes-->
          <figure>
            <img src="img/foto5.jpg" alt="Foto 5">
            <img src="img/foto6.jpeg" alt="Foto 6">
            <img src="img/foto7.jpeg" alt="Foto 7">
            <img src="img/foto8.jpeg" alt="Foto 8">
            <img src="img/foto5.jpg" alt="Foto 6">
          </figure>
        </div>

        <?php } ?> <!--Se necesita cerrar el if aquí, puesto que ambos usan estas 2 últimas líneas-->

        <h1><?php echo htmlspecialchars($prod['descripcion']); ?></h1>
        <h2><?php echo htmlspecialchars($prod['precio']); ?></h2>
        <!-- <h1>Reparación e instalación de cámaras de seguridad y sistemas de circuito cerrado.</h1>
        <h2>$150</h2> -->
        <button class="btn-modificar">
            <a href="form-modificar.php?id=<?php echo $prod['id']; ?>" class="modificar-link">
            <!-- Esto te manda a la dirección "form-modificar.php" y ADEMÁS envía un dato, 
            en este caso el Id del producto donde presione el botón "Modificar" -->
                <span>Modificar</span>
            </a>
        </button>
      </div>

      <?php 
        }
      } 
      ?>
    </div>

    <div id="articulos" class="hidden"> <!--Divisón para la parte de Artículos-->
      <?php 
      // Recorremos los productos y solo imprimimos los que sean 'servicios'
      foreach ($productos as $prod) { 
        if ($prod['tipo'] == 'articulos') {
      ?>
      
      <div class="cuadritos-container"> <!--Cuadrito Artículo-->
        <img src="img/<?php echo htmlspecialchars($prod['imagen_url']); ?>" alt="Imagen producto">
        <h1><?php echo htmlspecialchars($prod['descripcion']); ?></h1>
        <h2><?php echo htmlspecialchars($prod['precio']); ?></h2>
        <h2>Articulos Disponibles: <?php echo htmlspecialchars($prod['stock']); ?></h2>
        <button class="btn-modificar">
            <a href="form-modificar.php?id=<?php echo $prod['id']; ?>" class="modificar-link">
            <!-- Esto te manda a la dirección "form-modificar.php" y ADEMÁS envía un dato, 
            en este caso el Id del producto donde presione el botón "Modificar" -->  
                <span>Modificar</span>
            </a>
        </button>
      </div>

    <?php 
        }
      } 
      ?>      <!--No se necesita repetir todo el proceso, el mismo foreach lo repite hasta que 
      ya no haya más resultados-->
    </div>
  
    </div>
  </div>

</body>

</html>

