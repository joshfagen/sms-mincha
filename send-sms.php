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
$statement = $connection->prepare("SELECT distinct phone,uid FROM member WHERE is_active = 'Y'");
$statement->execute();
$alluser = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($alluser as $allusers) {
    $number = $client->messages->create(
        $allusers['phone'],
        array(
            'from' => FROM_NUMBER,
            'body' => EVENT_MESSAGE,
        )
    );
    try {

        $statement = $connection->prepare("
			INSERT INTO send_sms (uid, sms_sid, body, m_to, m_from, date_created, date_modified) 
			VALUES (:uid,:sms_sid,:body, :m_to, :m_from, :date_created, :date_modified)
		");
        $result = $statement->execute(
            array(
                ':uid' => $allusers['uid'],
                ':sms_sid' => $number->sid,
                ':body' => $number->body,
                ':m_to' => $number->to,
                ':m_from' => $number->from,
                ':date_created' => date("Y-m-d H:i:s"),
                ':date_modified' => date("Y-m-d H:i:s")
            )
        );

    } catch (Exception $e) {
        echo 'Message: ' . $e->getMessage();
    }

}

echo date('Y-m-d'). ' OK';

