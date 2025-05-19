<?php
class Database {
    private $host;
    private $user;
    private $pass;
    private $data;
    public $db;

    public function __construct($host, $user, $pass, $data) {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->data = $data;
        $this->connect();
    }

    private function connect() {
        $this->db = new mysqli($this->host, $this->user, $this->pass, $this->data);
        if ($this->db->connect_errno) {
            die("No se ha podido conectar a MySQL: (" . $this->db->connect_errno . ")" . $this->db->connect_error);
        }
        $this->db->set_charset("utf8");
    }

    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }
}

?>