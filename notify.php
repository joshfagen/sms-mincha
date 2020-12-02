<?php
// cron for 12:15 PM 
error_reporting(E_ALL);
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);

// Get the PHP helper library from twilio.com/docs/php/install
require_once 'vendor/autoload.php'; // Loads the library
use SignalWire\Rest\Client;
include_once('db.php');
include_once('./twilio_config.php');

// Your Account Sid and Auth Token from twilio.com/user/account
$client = new Client(SID, TOKEN, array("signalwireSpaceUrl" => SIGNALWIREURL) );
$date = date('Y-m-d') . ' 00:00:00';

$statement = $connection->prepare("SELECT distinct seid FROM reply where reply_msg = 'yes' and created_date >= '$date'");
$statement->execute();
$count_yes = $statement->rowCount();

$statement = $connection->prepare("SELECT * FROM guest_count WHERE date = '".date('Y-m-d')."' ");
$statement->execute();
$guest_counter = $statement->fetch(PDO::FETCH_ASSOC);
$guest_count = $guest_counter['count'];

$total_join = ($count_yes + $guest_count);

if ( $total_join < MINIMUM_REPLY_LIMIT) {
    $statement = $connection->prepare("SELECT distinct m_to FROM send_sms LEFT JOIN reply ON reply.seid = send_sms.seid where send_sms.date_created >= '" . $date . "' AND reply.rid IS NULL ");
    $statement->execute();
    $pending_reply = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($pending_reply as $phone) {
       $message = str_replace('{COUNT}', $total_join, NOTIFY_MESSAGE) ;
       if(!empty($phone['m_to'])) {
            $response = $client->messages->create(
                 $phone['m_to'],
                 array(
                     'from' => FROM_NUMBER,
                     'body' => $message
                 )
             );
       }

       
    }

}

echo date('Y-m-d'). ' OK';

