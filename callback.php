<?php
// callback file for SMS reply TEST GIT
error_reporting(0);
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);;

include('db.php');
// Get the PHP helper library from twilio.com/docs/php/install
require_once 'vendor/autoload.php'; // Loads the library
use SignalWire\Rest\Client;
use SignalWire\LaML;

include_once('./twilio_config.php');

$client = new Client(SID, TOKEN, array("signalwireSpaceUrl" => SIGNALWIREURL) );
$from = $_REQUEST['From'];// '+919510212332';
$body = trim(strtolower($_REQUEST['Body']));
$to = $_REQUEST['To'];// '+15005550006';

//$content = http_build_query($_REQUEST);
//file_put_contents('./log.txt', $content,FILE_APPEND);
//exit;

if ($body != '') {
    $statement = $connection->prepare("SELECT * FROM member WHERE phone = '" . $from . "' ORDER BY 1 DESC LIMIT 1");
    $statement->execute();
    $member = $statement->fetch(PDO::FETCH_ASSOC);

    $statement = $connection->prepare("SELECT * FROM send_sms WHERE m_to = '" . $_REQUEST['From'] . "' ORDER BY 1 DESC LIMIT 1");
    $statement->execute();
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    $reply_arr = array('yes', 'no');
    $sub_reply_arr = array('start', 'stop');

    if ($body == 'start') {
        if (!empty($member)) {
            //already member want to confirm
            $statement = $connection->prepare(" UPDATE member SET is_active = 'Y', modify_date = '" . date("Y-m-d H:i:s") . "' WHERE uid = '" . $member["uid"] . "' ");
            $result = $statement->execute();
        } else {

            $statement = $connection->prepare("INSERT INTO member (phone, get_call,is_active, created_date) VALUES (:phone, :get_call, :is_active, :created_date) ");
            $result = $statement->execute(
                array(
                    ':phone' => $from,
                    ':get_call' => 'N',
                    ':is_active' => 'Y',
                    ':created_date' => date("Y-m-d H:i:s"),
                )
            );
        }

        if (!empty($result)) {
            $reply_text = CONFIRMED_SUB_START;
        }

    } elseif ($body == 'stop' and !empty($member)) {
        $statement = $connection->prepare(" UPDATE member SET is_active = 'N', modify_date = '" . date("Y-m-d H:i:s") . "' WHERE uid = '" . $member["uid"] . "' ");
        $result = $statement->execute();
        if (!empty($result)) {
            $reply_text = CONFIRMED_SUB_STOP;
        }

    } elseif ($body == 'call' and !empty($member)) {
        $statement = $connection->prepare(" UPDATE member SET get_call = 'Y', modify_date = '" . date("Y-m-d H:i:s") . "' WHERE uid = '" . $member["uid"] . "' ");
        $result = $statement->execute();
        if (!empty($result)) {
            $reply_text = CONFIRMED_SUB_CALL;
        }

    } elseif ($body == 'status' or  $body == 'stats'){
        $date = date('Y-m-d') . ' 00:00:00';

        $statement = $connection->prepare("SELECT distinct seid FROM reply where reply_msg = 'yes' and created_date >= '$date'");
        $statement->execute();
        $count_yes = $statement->rowCount();

        $statement = $connection->prepare("SELECT * FROM guest_count WHERE date = '".date('Y-m-d')."' ");
        $statement->execute();
        $guest_counter = $statement->fetch(PDO::FETCH_ASSOC);
        $guest_count = $guest_counter['count'];

        $total_join = ($count_yes + $guest_count);
        $reply_text = str_replace('{COUNT}', $total_join, NOTIFY_MESSAGE) ;

    } elseif (in_array($body, $reply_arr) and !empty($member)) {

        $statement = $connection->prepare("SELECT * FROM reply WHERE seid = '" . $user["seid"] . "' ORDER BY 1 DESC LIMIT 1");
        $statement->execute();
        $reply = $statement->fetch(PDO::FETCH_ASSOC);
        if (empty($reply)) {
            $statement = $connection->prepare(" INSERT INTO reply (seid, m_from,reply_msg, created_date) VALUES (:seid, :m_from,:reply_msg, :created_date) ");
            $result = $statement->execute(
                array(
                    ':seid' => $user["seid"],
                    ':reply_msg' => $body,
                    ':m_from' => $from,
                    ':created_date' => date("Y-m-d H:i:s"),
                )
            );
        } else {
            $statement = $connection->prepare(" UPDATE reply SET reply_msg = :reply_msg  WHERE seid = '" . $user["seid"] . "' ");
            $result = $statement->execute(
                array(
                    ':reply_msg' => $body
                    //':created_date' => date("Y-m-d H:i:s"),
                )
            );
        }

        // check if 10 reply go then inform all members
        $date = date('Y-m-d 00:00:00');

        $statement = $connection->prepare("SELECT distinct m_to FROM send_sms JOIN reply ON reply.seid = send_sms.seid where reply.created_date >= '" . $date . "' AND reply.reply_msg = 'yes'");
        $statement->execute();
        $get_msgs = $statement->fetchAll(PDO::FETCH_ASSOC);
        $get_msg_count = $statement->rowCount();

        $statement = $connection->prepare("SELECT * FROM guest_count WHERE date = '" . date('Y-m-d') . "' ");
        $statement->execute();
        $guest_counter = $statement->fetch(PDO::FETCH_ASSOC);
        $guest_count = $guest_counter['count'];

        $statement = $connection->prepare("SELECT is_sent FROM is_confirm_sent WHERE date = '" . date('Y-m-d') . "' ");
        $statement->execute();
        $guest_counter = $statement->fetch(PDO::FETCH_ASSOC);
        $is_sms_sent = (int)$guest_counter['is_sent'];

        $total_join = $get_msg_count + $guest_count;

        if ($total_join >= MINIMUM_REPLY_LIMIT && $is_sms_sent == 0) {
            $statement = $connection->prepare("
                            INSERT INTO is_confirm_sent (date, is_sent ) 
                            VALUES (:date, :is_sent)
                    ");
            $result = $statement->execute(
                array(
                    ':date' => date("Y-m-d"),
                    ':is_sent' => 1
                )
            );
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
            // send sms to admin 
            $member_name = array();
            foreach ($get_msgs as $phone_number) {
                $statement = $connection->prepare("SELECT name FROM member WHERE phone = '$phone_number' ");
                $statement->execute();
                $member_info = $statement->fetch(PDO::FETCH_ASSOC);
                $member_name[] = $member_info['name'];
            }
            $member_name = array_unique($member_name);
            $admin_msg = "Today's Respondents:" . "\n";
            $admin_msg .= implode(',', $member_name);
                        
            foreach (ADMIN_NUMBER as $adminNumber ){
            $client->messages->create(
                $adminNumber,
                array(
                    'from' => FROM_NUMBER,
                    'body' => $admin_msg
                )
            );
            }
        }
        

    } elseif (empty($member)) {
        $reply_text = NOT_MEMBER;
    } else {
        $reply_text = NOT_YESNO_REPLY;

        //someone not a member and not start
        $message = $_REQUEST['From'] . ":" . $_REQUEST['Body'];
        foreach (ADMIN_NUMBER as $adminNumber){
        $client->messages->create(
            $adminNumber,
            array(
                'from' => FROM_NUMBER,
                'body' => $message,
            )
        );
        }
    }
//  response with empty
    $response = new LaML;
    if (isset($reply_text)){
        $response->message($reply_text);
    }
    echo $response;
}
