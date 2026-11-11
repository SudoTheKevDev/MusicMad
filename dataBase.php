<?php

class Database {
    // Variables para la configuración de la base de datos
    private $host = "127.0.0.1"; // Cambia esto si tu base de datos no está en localhost
    private $db_name = "dorian-db"; // Cambia esto por el nombre de tu BD
    private $username = "prueba-Credencial";            // Tu usuario de MySQL
    private $password = "prueba-Credencial";                // Tu contraseña de MySQL
    public $conn;

    // Esta función intenta conectarse a la base de datos y retorna la conexión
    public function getConnection() {
        $this->conn = null;

        try {
            // Creamos la conexión usando PDO
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            
            // Le decimos a PDO que nos avise si hay errores
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // Si algo falla, mostramos el error
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>