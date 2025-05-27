<?php
require_once 'config.php';

class Database {
    private $conn;

    public function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME,
                DB_USERNAME,
                DB_PASSWORD
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("ERROR: Could not connect. " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function select($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function selectOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    public function insert($table, $data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);

        return $this->conn->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }

        $sql = "UPDATE {$table} SET " . implode(", ", $set) . " WHERE {$where}";
        $params = array_merge($data, $whereParams);

        $this->query($sql, $params);
        return true;
    }

    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $this->query($sql, $params);
        return true;
    }

    public function generateOrderId() {
        $year = date('Y');
        $sql = "SELECT id FROM orders WHERE id LIKE :year ORDER BY id DESC LIMIT 1";
        $result = $this->selectOne($sql, ['year' => $year . '%']);

        if ($result) {
            $lastId = $result['id'];
            $sequence = (int)substr($lastId, 5) + 1;
        } else {
            $sequence = 1;
        }

        return $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}

$database = new Database();
?>
