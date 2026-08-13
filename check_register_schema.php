<?php
require 'appconfig.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$res = $db->query("DESCRIBE register");
while($row = $res->fetch_assoc()){
    if(strpos($row['Field'], 'addr_') === 0 || strpos($row['Field'], 'contact_') === 0) {
        echo $row['Field'] . " : " . $row['Type'] . "\n";
    }
}
