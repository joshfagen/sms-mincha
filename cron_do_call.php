<?php
include('db.php');
include_once('./twilio_config.php');
// Get the PHP helper library from twilio.com/docs/php/install
require_once 'vendor/autoload.php'; // Loads the library
use SignalWire\Rest\Client;

// Your Account Sid and Auth Token from twilio.com/user/account
$client = new Client(SID, TOKEN, array("signalwireSpaceUrl" => SIGNALWIREURL) );
//$client = new Client(SID_TEST, TOKEN_TEST);

// Get data
$query = "SELECT * FROM `member` WHERE `get_call` = 'Y'";
$statement = $connection->prepare($query);
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
$date = date('Y-m-d') . ' 00:00:00';
foreach ($result as $row) {
    try {
        $query2 = "SELECT * FROM `reply` WHERE `m_from` = '".$row['phone']."' AND reply_msg = 'yes' and created_date >= '$date' ";
        $statement2 = $connection->prepare($query2);
        $statement2->execute();
        
        $result2 = $statement2->fetch(PDO::FETCH_ASSOC);
        if($result2) {
            $to_call = $row['phone'];
            $makecall = $client->calls->create(
                    $to_call, // To
                    FROM_NUMBER, // From
                    array('url' => BASE_URL ."voice.xml")
            );
        }
    } catch (Exception $ex) {
        echo $ex->getMessage() . "<br />";
    }
}
echo date('Y-m-d'). ' OK';
//print($makecall->sid);
