<?php
/**
 * Created by PhpStorm.
 * User: benja
 * Date: 22.11.16
 * Time: 11:31
 */

$paramsArray = [
    'file'      => 'file'
    ,'host'     => 'host'
    ,'port'     => 'port'
    ,'dbname'   => 'dbname'
    ,'user'     => 'user'
    ,'password' => 'password'
    ,'driver'   => 'driver'
];

unset($argv[0]);
foreach ($argv as $v) {
    $paramAr = explode('=', $v);
    if(!empty($paramAr[0])) {
        $$paramsArray[$paramAr[0]] = $paramAr[1];
    }
}

if (!empty($driver) && !empty($host) && !empty($port) && !empty($dbname) && !empty($user)) {
    pass:
    if(empty($password)) {
//        $password = readline('Enter your password for database gonnect: ');
        $password = '';
        echo 'Password is empty!!!';
        goto pass;
    } else {
        $dbh = new PDO($driver.':dbname='.$dbname.';host='.$host.';port='.$port, $user, $password);
        $query = $dbh->prepare('SELECT version()');
        $query->execute();
        echo $query->fetch(PDO::FETCH_ASSOC)['version']."\n";
        echo 'connect to db: true'."\n\n";
    }

    switch ($driver) {
        case 'pgsql':
        default:
            $dbconn = pg_connect('host='.$host.' port='.$port.' dbname='.$dbname.' user='.$user.' password='.$password);
            $fetchRow = 'pg_fetch_row';
            $escapeStr = 'pg_escape_string';
            $query = 'pg_query';
            break;
    }
}

if (!empty($file)) {
    $file = fopen($file, 'rb');
    $i = 1;
    while (($str2 = fgets($file, 4096)) !== false) {
        $str2 =  str_replace(["\r","\n"],'', $str2);
        $val = $escapeStr($str2);
        @$queryData = $fetchRow(pg_query('.$dbconn.', "select name, rating from hashes where name=\'".$val."\'"));
        if ($queryData[0]  !== $str2) {
            // $sql = "INSERT INTO hashes (name, md5, sha1, crypt, base64, flag, domain)
            $sql = "INSERT INTO hashes (name) VALUES ('" . $val . "')";
            @$query($dbconn, $sql);
            echo $i.') '.$str2."\n";
            ++$i;
        } else {
            $sql = "update hashes set rating = " . ((int)$queryData[1]+1) ." where name = '".$val."'";
            @$query($dbconn, $sql);
            echo $i.') '.$str2."\n";
            ++$i;
        }

    }
    if (!feof($file)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($file);
    pg_close($dbconn);
}
