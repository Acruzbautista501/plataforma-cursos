<?php
// config/db.php
class Database {
    private $host = 'localhost';
    private $db_name = 'plataforma_cursos';
    private $username = 'root';
    private $password = '';
    public $conn;

    // Método para obtener la conexión
    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }

        return $this->conn;
    }
}

// Instanciar la clase y obtener la conexión
$database = new Database();
$db = $database->getConnection();



?>