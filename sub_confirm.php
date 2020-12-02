<?php
error_reporting(0);
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);

include_once('db.php');
include_once('./twilio_config.php');
// Get the PHP helper library from twilio.com/docs/php/install
require_once 'vendor/autoload.php'; // Loads the library
use SignalWire\Rest\Client;

// Your Account Sid and Auth Token from twilio.com/user/account

$client = new Client(SID, TOKEN, array("signalwireSpaceUrl" => SIGNALWIREURL) );
$statement = $connection->prepare("SELECT distinct phone,uid,modify_date FROM member");
$statement->execute();
$alluser = $statement->fetchAll(PDO::FETCH_ASSOC);

$updated_before ='2020-10-01' ;
foreach ($alluser as $allusers) {
    if (($allusers['modify_date'] === NULL) or (date('Y-m-d', strtotime($allusers['modify_date'])) < (date('Y-m-d', strtotime($updated_before))) ) ) {
        $statement = $connection->prepare("UPDATE member SET is_active = 'N', get_call = 'N' WHERE uid = '" . $allusers["uid"] . "' ");
        $result = $statement->execute();
        $number = $client->messages->create(
            $allusers['phone'],
            array(
                'from' => FROM_NUMBER,
                'body' => CONFIRM_SUB,
            )
        );
    }


}
echo date('Y-m-d'). ' OK';
