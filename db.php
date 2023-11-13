<?php

use Dtkahl\SimpleConfig\Config;

$config = new Config(require("./config.php"));

class DB
{
    public String $items_limit;

    private String $host;
    private String $db_name;
    private String $username;
    private String $password;

    private String $bans_table;
    private String $mutes_table;
    private String $kicks_table;
    private String $history_table;

    private mysqli|false $db;

    public function __construct() {
        $config = new Config(require("./config.php"));

        $this->items_limit = $config->get('max_items_per_page');

        $this->host = $config->get('database.host');
        $this->db_name = $config->get('database.db_name');
        $this->username = $config->get('database.username');
        $this->password = $config->get('database.password');

        $this->bans_table = $config->get('database.bans_table');
        $this->mutes_table = $config->get('database.mutes_table');
        $this->kicks_table = $config->get('database.kicks_table');
        $this->history_table = $config->get('database.history_table');

        $this->db = mysqli_connect(
            $this->host,
            $this->username,
            $this->password,
            $this->db_name,
        );

        mysqli_set_charset($this->db, "utf8mb4");
    }

    public function getItem($id, $type) {
        $tables = [
            'bans' => $this->bans_table,
            'mutes' => $this->mutes_table,
            'kicks' => $this->kicks_table,
        ];
        $table = $tables[$type];
        $query = "SELECT * FROM $table WHERE id='$id'";
        $result = mysqli_query($this->db, $query);

        return mysqli_fetch_all($result, MYSQLI_ASSOC)[0];
    }

    public function getBansCount($q) {
        $result = mysqli_query($this->db, "SELECT count(*) as total from $this->bans_table JOIN $this->history_table USING(uuid) WHERE name LIKE '%$q%'");
        $data = mysqli_fetch_all($result);
        return $data[0][0];
    }

    public function getMutesCount($q) {
        $result = mysqli_query($this->db, "SELECT count(*) as total from $this->mutes_table JOIN $this->history_table USING(uuid) WHERE name LIKE '%$q%'");
        $data = mysqli_fetch_all($result);

        return $data[0][0];
    }

    public function getKicksCount($q) {
        $result = mysqli_query($this->db, "SELECT count(*) as total from $this->kicks_table JOIN $this->history_table USING(uuid) WHERE name LIKE '%$q%'");
        $data = mysqli_fetch_all($result);
        return $data[0][0];
    }

    public function getItems($table, $order=false, $offset=0, $q) {
        $query = "SELECT $table.*, $this->history_table.name FROM $table JOIN $this->history_table USING(uuid) WHERE name LIKE '%$q%'";
        if($order) {
            $query .= " ORDER BY $order DESC";
        }
        $query .= " LIMIT $offset, $this->items_limit";

        $result = mysqli_query($this->db, $query);

        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    public function getBans($page, $q) {
        return $this->getItems($this->bans_table, 'time', $page * $this->items_limit, $q);
    }

    public function getMutes($page, $q) {
        return $this->getItems($this->mutes_table, 'time', $page * $this->items_limit, $q);
    }

    public function getKicks($page, $q) {
        return $this->getItems($this->kicks_table, 'time', $page * $this->items_limit, $q);
    }

    public function getName($uuid) {
        $query = "SELECT name FROM $this->history_table WHERE uuid='$uuid'";
        $result = mysqli_query($this->db, $query);

        return mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['name'];
    }
}