<?php
error_reporting(1);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

include_once('db.php');

if (isset($_POST["counter"])) {
        $statement = $connection->prepare("SELECT * FROM guest_count WHERE date = '".date('Y-m-d')."' ");
        $statement->execute();
        $guest_counter = $statement->fetch(PDO::FETCH_ASSOC);
       
        if(empty($guest_counter)) {
            $statement = $connection->prepare("
                            INSERT INTO guest_count (date, count ) 
                            VALUES (:date, :count)
                    ");
            $result = $statement->execute(
                array(
                    ':date' => date("Y-m-d"),
                    ':count' => $_POST["counter"]
                )
            );
        } else {
            $statement = $connection->prepare(" UPDATE guest_count SET count = :count  WHERE date = '" . date('Y-m-d'). "' ");
            $result = $statement->execute(
                array(
                    ':count' => $_POST["counter"]
                    //':created_date' => date("Y-m-d H:i:s"),
                )
            );
        }

        if (!empty($result)) {

            echo $_POST["counter"] ."Guest Added saved successfully.";
        } else {
            echo "Data not saved try again.";
        }
    
    
}
