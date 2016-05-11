<?php

class FileTransfer {

    public $connection;
    public $sconnection;
    public $active_port;
    public $connection_type;
    public $port;
    public $passive_port;
    public $timeout;
    public $status;
    public $log;

    function __construct() {
        $this->connection = false;
        $this->connection_type = 'ftp';
        $this->port = 21;
        $this->host = null;
        $this->passive_port = 0;
        $this->active_port = 0;

        $this->timeout = 20;
        $this->status = false;
        $this->user_logged_on = false;
        $this->log = array();
        $this->sconnection = false;
    }

    function __destruct() {
        $this->log[] = "DESTRUCT";
        $this->close();
    }

    public function getConnection($type = 'ftp', $user, $passwd, $host, $port = null) {

        $this->host = $host;
        $this->connection_type = $type;

        switch($type) {
            case 'ssh':
                $this->port = 22;
                break;
            case 'ftp':
                $this->port = 21;
                break;
        }

        if(isset($port)) {
            $this->port = $port;
        }

        $this->connection = fsockopen($host, $this->port);

        if($this->connection) {

            $commands = array(
                "USER ".$user."\r\n",
                "PASS ".$passwd."\r\n",
                "CLNT HandMade (UTF-8)\r\n",
                "OPTS UTF8 ON\r\n",
                // "PASV\r\n",
                // "TYPE A\r\n",
            );

            $empty_event = false;

            // Welcome message
            while ($this->connection) {
                $result = fgets($this->connection, 128);
                $this->log[] = trim($result);

                if(preg_match('/^220/', $result)) {
                    if(!$empty_event) {
                        fwrite($this->connection, "NOOP\r\n");
                        $empty_event = true;
                    }
                }

                if(preg_match('/^5+|^4+/', $result)) {
                    break 1;
                }
            }

            $this->log[] = "======================================";

            foreach($commands as $c) {
                fwrite($this->connection, $c);
                $result = fgets($this->connection, 128);
                $this->log[] = trim($c);
                $this->log[] = trim($result);

                if(preg_match('/^230/', $result)) {
                    $this->status = true;
                    $this->user_logged_on = true;
                }

                if(preg_match('/^5+|^4+/', $result)) {
                    break 1;
                }
            }

            $this->log[] = "======================================";
        }



        return $this;
    }

    public function help() {

        if($this->connection && $this->user_logged_on) {
            fputs($this->connection, "HELP\r\n");
            $output = 0;
            while($this->connection) {
                $result = fgets($this->connection, 128);
                $this->log[] = trim($result);

                if($output == 0) {
                    if(preg_match('/^2/', $result)) {
                        $output++;
                    }
                } else {
                    if(preg_match('/^2/', $result)) {
                        $output++;
                        break 1;
                    }
                }

                if(preg_match('/^5{3}|4{3}/', $result)) {
                    break 1;
                }

            }
        }

    }

    private function __command($command) {

        if($this->connection && $this->user_logged_on) {
            fputs($this->connection, $command."\r\n");

            while($this->connection) {
                $result = fgets($this->connection, 128);
                $this->log[] = trim($result);

                if(preg_match('/^227.*\(([0-9,]+)\)/', $result, $matches)) {
                    $this->passive_port = $matches[1];

                    if(preg_match('/(\d+),(\d+)$/', $this->passive_port, $match)) {
                        $this->active_port = $match[1]*256+$match[2];
                    }
                    // $this->__command("PORT ".$this->passive_port);
                }

                if(preg_match('/^5+|4+/', $result)) {
                    break 1;
                }

                if(preg_match('/^2+/', $result)) {
                    break 1;
                }
            }
        }

    }

    public function sconn($host, $port) {
        $this->sconnection = fsockopen($this->host, $this->active_port);
        $this->sc = true;
        $this->output = '';

        if($this->sc) {
            if($this->sconnection) {
                while($this->sconnection) {
                    // $this->output .= fgets($this->sconnection, 1024);
                }
            }
        }

    }

    public function getlist($list = "-l") {
        if($this->connection && $this->user_logged_on) {
            $this->__command("PASV");
            $this->__command("TYPE A");

            $this->output = '';

            $this->sconnection = fsockopen($this->host, $this->active_port);
            $this->sc = true;

            $this->log[] = "";
            $this->log[] = trim("LIST ".$list);
            fputs($this->connection, "LIST ".$list."\r\n");

            while($this->connection) {
                $result = fgets($this->connection, 128);

                if($result) {
                    if(preg_match('/^150/', $result)) {
                        $this->log[] = trim($result);

                        if($this->sconnection) {
                            while($this->sconnection) {
                                $sresult = fgets($this->sconnection, 1024);
                                if($sresult) {
                                    $this->log[] = trim($sresult);
                                } else {
                                    break 1;
                                }
                            }
                        }
                    }

                    if(preg_match('/^5|4.+/', $result)) {
                        $this->log[] = trim($result);
                        break 1;
                    }

                    if(preg_match('/^226/', $result)) {
                        $this->log[] = trim($result);
                        break 1;
                    }
                } else {
                    break 1;
                }
            }
        }
    }

    public function upload($from_path, $filename) {

        if(!file_exists($from_path)) {
            $this->log[] = "File not exists ".$from_path;
            return false;
        }

        if($this->connection && $this->user_logged_on) {
            $this->__command("PASV");
            $this->__command("TYPE I");
            $this->file_output = '';
            $this->sconnection = fsockopen($this->host, $this->active_port);

            $this->log[] = "STOR ".$filename;
            fputs($this->connection, "STOR ".$filename."\r\n");

            while($this->connection) {
                $result = fgets($this->connection, 128);

                if($result) {
                    if(preg_match('/^150/', $result)) {
                        $this->log[] = trim($result);
                    }

                    if(preg_match('/^226/', $result)) {
                        $this->log[] = trim($result);
                        break 1;
                    }

                    if(preg_match('/^5|4.+/', $result)) {
                        $this->log[] = trim($result);
                        break 1;
                    }

                    if($this->sconnection) {
                        $fp = fopen($from_path, "r");

                        while(!feof($fp)) {
                            $line = fgets($fp, 4096);
                            fputs($this->sconnection, $line);
                            $this->log[] = trim($line);
                        }

                        fclose($this->sconnection);
                        $this->sconnection = false;
                    }

                } else {
                    break 1;
                }
            }
        }
    }

    public function download($filename) {

        if($this->connection && $this->user_logged_on) {

            $this->__command("TYPE I");
            $this->__command("PASV");

            $this->file_downloaded = '';
            $this->sconnection = fsockopen($this->host, $this->active_port);

            $this->log[] = "RETR ".$filename;
            fputs($this->connection, "RETR ".$filename."\r\n");

            while($this->connection) {
                $result = fgets($this->connection, 128);

                if($result) {
                    if(preg_match('/^150/', $result)) {
                        $this->log[] = trim($result);

                        if($this->sconnection) {
                            while($this->sconnection) {
                                $sresult = fgets($this->sconnection, 1024);
                                if($sresult) {
                                    $this->log[] = trim($sresult);
                                    $this->file_downloaded .= $sresult;
                                } else {
                                    break 1;
                                }
                            }
                        }
                    }

                    if(preg_match('/^226/', $result)) {
                        $this->log[] = trim($result);
                        break 1;
                    }

                    if(preg_match('/^5|4.+/', $result)) {
                        $this->log[] = trim($result);
                        break 1;
                    }
                } else {
                    break 1;
                }
            }

            // header("Content-Disposition: attachment; filename=" . urlencode($filename));
            // header("Content-Type: application/force-download");
            // header("Content-Type: application/octet-stream");
            // header("Content-Type: application/download");
            // header("Content-Description: File Transfer");
            // header("Content-Length: " . filesize($filename));
            // flush();
        }

        return $this;
    }

    public function dir() {
        return $this->pwd();
    }

    public function cdup() {
        if($this->connection && $this->user_logged_on) {
            fwrite($this->connection, "CDUP\r\n");

            while($this->connection) {
                $result = fgets($this->connection, 128);
                $this->log[] = trim($result);

                if(preg_match('/^2.+\"(.*)\"/', $result, $matches)) {
                    $this->path = $matches[1];
                    $this->log[] = trim($this->path);
                    break 1;
                }

                if(preg_match('/^4|5.+/', $result)) {
                    break 1;
                }
            }

            return $this->path;
        }
    }

    public function rein() {
        // REIN — Реинициализировать подключение.
        if($this->connection && $this->user_logged_on) {
            fwrite($this->connection, "REIN\r\n");
            $this->log[] = "REIN";

            while($this->connection) {
                $result = fgets($this->connection, 128);
                $this->log[] = trim($result);

                if(preg_match('/^0|1|2|3|4|5.+/', $result)) {
                    break 1;
                }
            }
        }
    }

    public function rename($from, $to) {
        if($this->connection && $this->user_logged_on) {
            fwrite($this->connection, "RNFR ".$from."\r\n");
            $this->log[] = "RNFR ".$from;

            while($this->connection) {
                $result = fgets($this->connection, 128);
                $this->log[] = trim($result);

                if(preg_match('/^0|1|2|3|4|5.+/', $result)) {
                    if(preg_match('/^350/', $result)) {
                        fwrite($this->connection, "RNTO ".$to."\r\n");
                        $this->log[] = "RNTO ".$to;
                    } else {
                        break 1;
                    }
                }
            }

            return trim($result);
        }
    }

    public function delete($filename) {
        if($this->connection && $this->user_logged_on) {
            fwrite($this->connection, "DELE ".$filename."\r\n");
            $this->log[] = "DELE ".$filename;

            while($this->connection) {
                $result = fgets($this->connection, 128);
                $this->log[] = trim($result);

                if(preg_match('/^0|1|2|3|4|5.+/', $result)) {
                    break 1;
                }
            }

            return $this->path;
        }
    }

    public function cwd($path) {
        return $this->cd($path);
    }

    public function cd($path) {
        if(!$path) {
            return false;
        }

        if($this->connection && $this->user_logged_on) {
            fwrite($this->connection, "CWD ".$path."\r\n");
            $this->log[] = "CWD ".$path;

            while($this->connection) {
                $result = fgets($this->connection, 128);

                $this->log[] = trim($result);

                if(preg_match('/^2.+/', $result, $matches)) {
                    $this->path = $path;
                    break 1;
                }

                if(preg_match('/^4|5.+/', $result)) {
                    break 1;
                }
            }
        }

        return $this;
    }

    public function mkd($pathname) {
        if($this->connection && $this->user_logged_on) {
            fwrite($this->connection, "MKD ".$pathname."\r\n");
            $this->log[] = "MKD ".$pathname;

            while($this->connection) {
                $result = fgets($this->connection, 128);
                $this->log[] = trim($result);

                if(preg_match('/^0|1|2|3|4|5.+/', $result)) {
                    break 1;
                }
            }
        }
    }

    public function pwd() {
        if($this->connection && $this->user_logged_on) {
            fwrite($this->connection, "PWD\r\n");
            while($this->connection) {
                $result = fgets($this->connection, 128);
                if(preg_match('/^257.+\"(.*)\"/', $result, $matches)) {
                    $this->path = $matches[1];
                    break 1;
                }
            }

            return $this->path;
        }
    }

    public function rmd($dirname) {
        if($this->connection && $this->user_logged_on) {
            fwrite($this->connection, "RMD ".$dirname."\r\n");
            $this->log[] = "RMD ".$dirname;

            while($this->connection) {
                $result = fgets($this->connection, 128);
                $this->log[] = trim($result);

                if(preg_match('/^0|1|2|3|4|5.+/', $result)) {
                    break 1;
                }
            }

            return $this->path;
        }
    }

    public function size($filename) {
        if($this->connection && $this->user_logged_on) {
            $filesize = '';
            fwrite($this->connection, "SIZE ".$filename."\r\n");
            $this->log[] = "SIZE ".$filename;

            while($this->connection) {
                $result = fgets($this->connection, 128);
                $this->log[] = trim($result);

                if(preg_match('/^213 (\d+)/', $result, $matches)) {
                    $filesize = $matches[1];
                    $this->log[] = $filesize;
                    break 1;
                }

                if(preg_match('/^0|1|2|3|4|5.+/', $result)) {
                    break 1;
                }
            }

            return $filesize;
        }
    }

    public function mdtm($filename) {
        if($this->connection && $this->user_logged_on) {
            $filedata = '';
            fwrite($this->connection, "MDTM ".$filename."\r\n");
            $this->log[] = "MDTM ".$filename;

            while($this->connection) {
                $result = fgets($this->connection, 128);
                $this->log[] = trim($result);

                if(preg_match('/^0|1|2|3|4|5.+/', $result)) {
                    if(preg_match('/^213\s(\d+)/', $result, $matches)) {
                        $filedata = $matches[1];
                        $this->log[] = $filedata;
                    }

                    break 1;
                }
            }

            return $filedata;
        }
    }

    public function quit() {
        if($this->connection && $this->user_logged_on) {
            fwrite($this->connection, "QUIT\r\n");
            $this->log[] = "QUIT";

            while ($this->connection) {
                $result = fgets($this->connection, 128);
                $this->log[] = trim($result);
                if(preg_match('/^2|4|5.+/', $result)) {
                    break 1;
                }
            }

            if($this->connection) {
                fclose($this->connection);
                $this->connection = false;
            }

            if($this->sconnection) {
                fclose($this->sconnection);
                $this->sconnection = false;
            }
        }
    }

    public function close() {
        if($this->connection && $this->user_logged_on) {
            fwrite($this->connection, "QUIT\r\n");
            $this->log[] = "QUIT";

            while ($this->connection) {
                $result = fgets($this->connection, 128);
                $this->log[] = trim($result);
                if(preg_match('/^2|4|5.+/', $result)) {
                    break 1;
                }
            }

            if($this->connection) {
                fclose($this->connection);
                $this->connection = false;
            }

            if($this->sconnection) {
                fclose($this->sconnection);
                $this->sconnection = false;
            }
        }
    }

    public function log() {
        return $this->log;
    }
}



$factory = new FileTransfer();

try {
    $conn = $factory->getConnection('ftp', 'breadftp', 'password', 'localhost');
    echo $conn->pwd() . "<br>\n";
    $conn->cd('/tmp');
    echo $conn->size('ftp_client_class.txt')."<br>\n";
    echo $conn->size('test_text2.txt')."<br>\n";
    echo $conn->mdtm('test_text2.txt')."<br>\n";

    echo $conn->rename('ftp_client_class.txt', 'ftp_client_class2.txt');
    $conn->cdup() . "<br>\n";
    $conn->mkd('/tmp2');
    $conn->rmd('/tmp2');

    // $conn->rein();

    // $conn->delete('ftp_client_class.txt');
    // $conn->cd('/tmp');

    // $conn->help() . "<br>\n";


    // echo $conn->getlist('-la')."<br>";
    // echo $conn->cd('/tmp')->upload('F:/www/vhosts/www.bread.local/test_text.txt', 'test_text2.txt')."<br>"; //
    // echo $conn->cd('/tmp')->download('test_text2.txt')->close()."<br>";

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


