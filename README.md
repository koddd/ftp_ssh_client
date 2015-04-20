#File transfer library
##Usage

```php
$factory = new FileTransfer();
```

```php
try {
    $conn = $factory->getConnection('ftp', 'user', 'pass', 'hostname.com');
    echo $conn->pwd() . "<br>\n";
    echo $conn->upload('test_text.txt')."<br>";
    // print_r($factory->exec('ls -al'));
    echo "<pre>".print_r($factory->log(), true)."</pre>";
} catch (Exception $e) {
    echo $e->getMessage();
}
```

```php
try {
	$conn = $factory->getConnection('ssh', 'user', 'pass', 'hostname.com', 2222);
	$conn->cd('/var/www')->download('dump.tar.gz')->close();
} catch(Exception $e) {
	echo $e->getMessage();
}
```

##Results

Results of entering functions will return this log

After wrong login or passwords

```php
Array
(
    [0] => 220-FileZilla Server version 0.9.41 beta
    [1] => 220-written by Tim Kosse (Tim.Kosse@gmx.de)
    [2] => 220 Please visit http://sourceforge.net/projects/filezilla/
    [3] => 530 Please log in with USER and PASS first.
    [4] => ======================================
    [5] => USER user1
    [6] => 331 Password required for user1
    [7] => PASS pass
    [8] => 530 Login or password incorrect!
    [9] => ======================================
)
```

After correct operations of user login

```php
Array
(
    [0] => 220-FileZilla Server version 0.9.41 beta
    [1] => 220-written by Tim Kosse (Tim.Kosse@gmx.de)
    [2] => 220 Please visit http://sourceforge.net/projects/filezilla/
    [3] => 530 Please log in with USER and PASS first.
    [4] => ======================================
    [5] => USER user
    [6] => 331 Password required for user
    [7] => PASS pass
    [8] => 230 Logged on
    [9] => CLNT HandMade (UTF-8)
    [10] => 200 Don't care
    [11] => OPTS UTF8 ON
    [12] => 200 UTF8 mode enabled
    [13] => ======================================
    [14] => 214-The following commands are recognized:
    [15] => USER   PASS   QUIT   CWD    PWD    PORT   PASV   TYPE
    [16] => LIST   REST   CDUP   RETR   STOR   SIZE   DELE   RMD
    [17] => MKD    RNFR   RNTO   ABOR   SYST   NOOP   APPE   NLST
    [18] => MDTM   XPWD   XCUP   XMKD   XRMD   NOP    EPSV   EPRT
    [19] => AUTH   ADAT   PBSZ   PROT   FEAT   MODE   OPTS   HELP
    [20] => ALLO   MLST   MLSD   SITE   P@SW   STRU   CLNT   MFMT
    [21] => HASH
    [22] => 214 Have a nice day.
    [23] => 227 Entering Passive Mode (127,0,0,1,195,169)
    [24] => 200 Type set to A
    [25] => 
    [26] => LIST -la
    [27] => 150 Connection accepted
    [28] => -rw-r--r-- 1 ftp ftp            166 Jul 29  2013 .gitignore
    [29] => -rw-r--r-- 1 ftp ftp            553 Sep 26  2014 .htaccess
    [30] => -rw-r--r-- 1 ftp ftp             49 Jul 29  2013 .travis.yml
    [31] => drwxr-xr-x 1 ftp ftp              0 Sep 26  2014 application
    [32] => drwxr-xr-x 1 ftp ftp              0 Apr 04 18:50 css
    [33] => -rw-r--r-- 1 ftp ftp          13511 Apr 20 11:57 file_transfer_lib.php
    [34] => -rw-r--r-- 1 ftp ftp           1541 Sep 26  2014 Git Bash.lnk
    [35] => drwxr-xr-x 1 ftp ftp              0 Mar 24 14:35 images
    [36] => -rw-r--r-- 1 ftp ftp           6357 Jul 29  2013 index.php
    [37] => drwxr-xr-x 1 ftp ftp              0 Apr 01 21:33 js
    [38] => drwxr-xr-x 1 ftp ftp              0 Sep 26  2014 system
    [39] => drwxr-xr-x 1 ftp ftp              0 Apr 19 20:22 tmp
    [40] => 226 Transfer OK
    [41] => CWD /tmp
    [42] => 250 CWD successful. "/tmp" is current directory.
    [43] => 227 Entering Passive Mode (127,0,0,1,195,171)
    [44] => 200 Type set to I
    [45] => STOR test_text2.txt
    [46] => 550 Permission denied
    [47] => CWD /tmp
    [48] => 250 CWD successful. "/tmp" is current directory.
    [49] => 200 Type set to I
    [50] => 227 Entering Passive Mode (127,0,0,1,195,173)
    [51] => RETR test_text2.txt
    [52] => 150 Connection accepted
    [53] => Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut consequat vehicula ullamcorper. Aenean porttitor, turpis non commodo tincidunt, leo leo placerat augue, rutrum elementum magna enim a libero. Morbi aliquam sed nulla a tincidunt. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Duis sodales ex turpis, quis fermentum erat blandit ut. Proin nec mauris molestie, pellentesque metus ut, bibendum risus. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Quisque imperdiet vestibulum magna ac consequat. Quisque condimentum dapibus rutrum. In elementum nisl lacinia, gravida magna non, pulvinar lacus.
    [54] => Sed non consectetur ex, in aliquam sem. Aenean non sagittis lorem. Etiam luctus ex a ex faucibus, id cursus massa pellentesque. Cras sagittis, risus quis placerat fermentum, urna dolor efficitur leo, imperdiet vestibulum nibh sapien vitae urna. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Donec bibendum efficitur porttitor. Nulla id porta justo. Quisque dictum enim ut sagittis maximus. Praesent id mauris pulvinar, pharetra orci ac, ultrices nunc. Vivamus ultrices dui ut sodales tristique. Aliquam id augue egestas, scelerisque libero eu, sagittis mauris. Vestibulum sodales at velit egestas dignissim.
    [55] => Morbi interdum vestibulum ipsum. Aliquam ut convallis nulla. Morbi quis faucibus purus, vitae vulputate augue. Proin id lobortis diam, blandit consectetur purus. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nullam congue ultricies urna. In hac habitasse platea dictumst. Sed in elit a nisi dignissim pulvinar nec nec dolor. Maecenas maximus, tellus ut mattis elementum, ante magna tempus nibh, id feugiat odio neque eget lectus. Praesent nulla augue, dapibus a varius nec, imperdiet ut arcu. Aliquam id dolor ante.
    [56] => Suspendisse auctor, lectus in lacinia molestie, lectus lorem tempus massa, quis laoreet massa orci mollis libero. Quisque quis tortor fermentum purus dictum congue. Aenean congue, velit in fringilla iaculis, dui nisl varius augue, vitae porta magna sem ac neque. Vestibulum vitae augue gravida, lacinia turpis vitae, elementum sapien. In vehicula tincidunt sapien, ac facilisis tortor molestie sed. Nam eget nisi varius, placerat dolor ut, vehicula ligula. Ut sed varius dolor, sit amet condimentum magna. Duis sodales euismod libero, id vestibulum nunc porttitor quis. Morbi commodo ultrices odio. Vivamus congue porttitor tortor ut interdum.
    [57] => 226 Transfer OK
    [58] => QUIT
    [59] => 221 Goodbye
)
```