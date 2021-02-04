<?php
class Database {

    // учетные данные базы данных
    private $host = "localhost";
    private $db_name = "api_db";
    private $username = "root";
    private $password = "1234";
    public $conn = null;

    // получаем соединение с БД
    public function getConnection(){
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
