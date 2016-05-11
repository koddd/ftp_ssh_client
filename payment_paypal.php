<?php

// pay by credit card through paypal
// pay paypal to paypal through paypal

// yandex money wallet to wallet
// yandex money pay by creditcard

// WebMoney

class payment_paypal {

    public $connection;
    public $port;
    public $host;
    public $path;
    public $log;
    public $error;
    public $user_logged_on;

    function __construct() {
        $this->connection = null;
        $this->port = 443;
        $this->host = "www.codeign.local";
        $this->path = '/';
        $this->log = array();
        $this->user_logged_on = false;
        $this->result = (object) array();
        $this->result->headers = array();
        $this->result->body = '';
    }

    function __destruct() {
        $this->close();
    }

    public function log() {
        return $this->log;
    }

    public function result($type = null) {
        if($type == 'headers' || $type == 'body') {
            $result = $this->result->{$type};
        } else {
            $result = $this->result;
        }

        return $result;
    }

    public function close() {
        if($this->connection) {
            if($this->connection) {
                fclose($this->connection);
                $this->connection = null;
            }
        }
    }

    public function getConnection($host, $port = null, $path = null, $headers = array(), $data = '') {
        if(isset($port)) {
            $this->port = $port;
        }

        if(isset($path)) {
            $this->path = $path;
        }

        $this->connection = fsockopen($host, $this->port, $errno, $errstr, 30);

        if($this->connection) {
            $commands = array();

            if(strlen($data) > 0) {
                $commands[] = "POST {$this->path} HTTP/1.1\r\n";
            } else {
                $commands[] = "GET {$this->path} HTTP/1.1\r\n";
            }

            $commands[] = "Host: {$host}\r\n";

            if(isset($headers) && count($headers) > 0) {
                foreach($headers as $h) {
                    $commands[] = $h;
                }
            }

            if(strlen($data) > 0) {
                $commands[] = "Connection: keep-alive\r\n";
                $commands[] = "Keep-Alive: 300\r\n";
                $commands[] = "Content-Type:application/json\r\n";
                $commands[] = "Authorization: Bearer {accessToken}\r\n";
                $commands[] = "Content-Length: ".strlen($data)."\r\n\r\n";
                $commands[] = $data;
            } else {
                $commands[] = "Connection: Close\r\n\r\n";
            }

            foreach($commands as $c) {
                fwrite($this->connection, $c);
            }

            $type = 'headers';

            while(!feof($this->connection)) {
                $string = fgets($this->connection, 128);

                if(preg_match('/^[\n\r\s]$/', $string)) {
                    $type = 'body';
                }

                switch($type) {
                    case 'headers':
                        $this->result->{$type}[] = $string;
                        break;
                    case 'body':
                        $this->result->{$type} .= $string;
                        break;
                }
            }

        } else {
            echo "$errstr ($errno)<br />\n";
        }
    }

}