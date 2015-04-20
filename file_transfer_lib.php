<?php

class FileTransfer {

    public $connection;
    public $connection_type;
    public $port;
    public $passive_port;
    public $timeout;
    public $status;
    public $log;

    public function __construct() {
        $this->connection = false;
        $this->connection_type = 'ftp';
        $this->port = 21;
        $this->passive_port = false;
        $this->timeout = 20;
        $this->status = false;
        $this->user_logged_on = false;
        $this->log = array();
    }

    public function __destruct() {
        $this->close();
    }

    public function getConnection($type = 'ftp', $user, $passwd, $host, $port = 21) {
        $this->connection_type = $type;

        return $this;
    }

    public function upload($filename) {

    }

    public function pwd() {

        return $this->path;
    }

    public function close() {

    }

    public function log() {
        return $this->log;
    }
}



$factory = new FileTransfer();

try {
    $conn = $factory->getConnection('ftp', 'user', 'pass', '127.0.0.1');
    echo $conn->pwd() . "<br>\n";
    echo $conn->upload('test_text.txt')."<br>";
    // print_r($factory->exec('ls -al'));
    echo "<pre>".print_r($factory->log(), true)."</pre>";
} catch (Exception $e) {
    echo $e->getMessage();
}

/*
try {
	$conn = $factory->getConnection('ssh', 'user', 'pass', 'hostname.com', 2222);
	$conn->cd('/var/www')->download('dump.tar.gz')->close();
} catch(Exception $e) {
	echo $e->getMessage();
}
*/


