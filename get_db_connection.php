<?php
// based on the example given on gitlab
require 'db_secrets.php';

function get_db_connection(): ?PDO
{

    $data_source_name = DB_DRIVER.":host=".DB_HOST.";dbname=".DB_NAME;

    try {
        return new PDO($data_source_name, DB_USER, DB_PASSWORD);
    } catch (PDOException $e) {
        echo "couldn't get a handle on the database ".$e."\n";
        return NULL;
    }
}
