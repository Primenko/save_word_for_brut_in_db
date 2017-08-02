<?php
/**
 * Created by PhpStorm.
 * User: benja
 * Date: 22.11.16
 * Time: 11:31
 */
//6669689
$paramsArray = [
    'file'      => 'file'
    ,'host'     => 'host'
    ,'port'     => 'port'
    ,'dbname'   => 'dbname'
    ,'user'     => 'user'
    ,'password' => 'password'
    ,'driver'   => 'driver'
    ,'numstr'   => 'numstr'
];

unset($argv[0]);
foreach ($argv as $v) {
    $paramAr = explode('=', $v);
    if(!empty($paramAr[0])) {
        $$paramsArray[$paramAr[0]] = $paramAr[1];
    }
}

if (count($argv) < count($paramsArray)) {
    exit('Example: php /path/to/console_hash.php host=ip_address port=**** dbname=dbName user=user_db password=********* driver=pdo_driver numstr=* file=/path/to/test.txt'."\n");
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
            $escapeStr = 'pg_escape_string';
            break;
    }
}

if (!empty($file)) {
    $file = fopen($file, 'rb');
    $i = 1;
    $y = 1;
    while (($str2 = fgets($file, 4096)) !== false) {
        if (empty($numstr) || $numstr <= $i) {
            $str2 =  str_replace(["\r","\n"],'', $str2);
            $val = $escapeStr($str2);
            $query = $dbh->prepare("select name, rating from hashes where name='".$val."'");
            $query->execute();
            $data = $query->fetchAll(PDO::FETCH_ASSOC);

            if (!$data) {
                $query = $dbh->prepare("INSERT INTO hashes (name) VALUES ('" . $val . "')");
                $query->execute();
                echo $i.') '.$str2."      NEW (all new : ".$y.")  \n";
                ++$i;
                ++$y;
            } else {
                $query = $dbh->prepare("update hashes set rating = " . ((int)$data[0]['rating']+1) ." where name = '".$val."'");
                $query->execute();
                echo $i.') '.$str2."\n";
                ++$i;
            }
        } else {
            echo ++$i."\n";
        }
    }
    if (!feof($file)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($file);
    pg_close($dbconn);
    echo "\n".'All new added in db '.$y."\n";
}
