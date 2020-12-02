<?php
// Cron for 12:30 PM
error_reporting(0);
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);

// Get the PHP helper library from twilio.com/docs/php/install
require_once 'vendor/autoload.php'; // Loads the library

use SignalWire\Rest\Client;

include_once('db.php');
include_once('./twilio_config.php');

// Your Account Sid and Auth Token from twilio.com/user/account
$client = new Client(SID, TOKEN, array("signalwireSpaceUrl" => SIGNALWIREURL) );
$date = date('Y-m-d 00:00:00');

$statement = $connection->prepare("SELECT distinct m_to FROM send_sms JOIN reply ON reply.seid = send_sms.seid where reply.created_date >= '" . $date . "' AND reply.reply_msg = 'yes'");
$statement->execute();
$get_msgs = $statement->fetchAll(PDO::FETCH_ASSOC);

$get_msg_count = $statement->rowCount();

$statement = $connection->prepare("SELECT * FROM guest_count WHERE date = '".date('Y-m-d')."' ");
$statement->execute();
$guest_counter = $statement->fetch(PDO::FETCH_ASSOC);
$guest_count = $guest_counter['count'];

$total_join = $get_msg_count + $guest_count;

if($total_join >= MINIMUM_REPLY_LIMIT) {
    $statement = $connection->prepare("SELECT is_sent FROM is_confirm_sent WHERE date = '".date('Y-m-d')."' ");
    $statement->execute();
    $sms_sent = $statement->fetch(PDO::FETCH_ASSOC);
    $is_sms_sent = (int)$sms_sent['is_sent'];
    if($is_sms_sent == 0) {
        $statement = $connection->prepare("SELECT distinct m_to FROM send_sms WHERE date_created >= '$date'");
        $statement->execute();
        $all_members = $statement->fetchAll(PDO::FETCH_ASSOC);

         foreach ($all_members as $member) {
             $client->messages->create(
                   $member['m_to'],
                    array(
                        'from' => FROM_NUMBER,
                        'body' => CONFIRM_MESSAGE
                    )
            );
         }
         // send confirm sms to admin
         $member_name= array();
         foreach($get_msgs as $phone_number) {
            $statement = $connection->prepare("SELECT name FROM member WHERE phone = '".$phone_number['m_to']."' ");
            $statement->execute();
            $member_info = $statement->fetch(PDO::FETCH_ASSOC);
            $member_name[] = $member_info['name'];
         }
         $member_name = array_unique($member_name);
         $admin_msg = "Today's Respondents:"."\n";
         $admin_msg .= implode(',', $member_name);
        foreach (ADMIN_NUMBER as $adminNumber){
         $client->messages->create(
                   $adminNumber,
                    array(
                        'from' => FROM_NUMBER,
                        'body' => $admin_msg
                    )
            );
        }
    }
     
} else  {
    $statement = $connection->prepare("SELECT distinct m_to FROM send_sms WHERE date_created >= '$date'");
    $statement->execute();
    $all_members = $statement->fetchAll(PDO::FETCH_ASSOC);
    $message = str_replace('{COUNT}', $total_join, CANCEL_MESSAGE) ;
    //echo $message;exit;
     foreach ($all_members as $member) {
         $client->messages->create(
               $member['m_to'],
                array(
                    'from' => FROM_NUMBER,
                    'body' => $message,
                )
        );
     }
}
echo date('Y-m-d'). ' OK';

    
