<?php


class Currency {
    // подключение к базе данных
    private $conn;

    // конструктор для соединения с базой данных
    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll(): array {
        // выбираем все записи
        $query = "SELECT c.* FROM currency c WHERE c.date=CURRENT_DATE ORDER BY c.code";

        // подготовка запроса
        $stmt = $this->conn->prepare($query);

        // выполняем запрос
        $stmt->execute();

        $currency = [];
        if ($stmt->rowCount()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $currency[] = [
                    "code" => $row['code'],
                    "date" => $row['date'],
                    "rate" => $row['rate']
                ];
            }
        }
        return $currency;
    }

    public function getOne(string $code) {
        // получение одной записи валюты на текущую дату по code
        $query = "SELECT c.* FROM currency c WHERE c.date=CURRENT_DATE AND c.code = :code LIMIT 1";

        // подготовка запроса
        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':code', strtoupper($code));

        $stmt->execute();

        // получаем извлеченную строку
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert(array $data): void {
        // добавляем новые записи по валюте
        $this->conn->beginTransaction();

        foreach ($data as $insertRow) {

            $query = "INSERT INTO currency (date, code, rate) 
            VALUES (:date,:code,:rate) 
             ON DUPLICATE KEY UPDATE rate=rate";

            // подготовка запроса
            $stmt = $this->conn->prepare($query);

            foreach ($insertRow as $column => $value) {
                $stmt->bindValue(":{$column}", $value);
            }
            $stmt->execute();
        }
        $this->conn->commit();
    }


}