<?php
require_once __DIR__ . '../../Conn/conn.php';

class Maquinas
{
    public static function todas()
    {
        $pdo = conn::getConn();
        $stmt = $pdo->query("SELECT id, nome AS title FROM maquinas");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
