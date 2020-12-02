<?php

error_reporting(1);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

include_once('db.php');
include_once('./twilio_config.php');
require_once 'vendor/autoload.php';

use SignalWire\Rest\Client;

$client = new Client(SID, TOKEN, array("signalwireSpaceUrl" => SIGNALWIREURL) );

if (isset($_POST["operation"])) {
    if ($_POST["action"] == "Add") {

        $statement = $connection->prepare("
			INSERT INTO member (name, email, phone, created_date) 
			VALUES (:name, :email, :phone, :created_date)
		");
        $result = $statement->execute(
                array(
                    ':name' => $_POST["name"],
                    ':email' => $_POST["email"],
                    ':phone' => $_POST["phone"],
                    ':created_date' => date("Y-m-d H:i:s"),
                )
        );

        if (!empty($result)) {

            echo 'Data Inserted Successfully';
        }
    }
    if ($_POST["action"] == "Update") {

        $statement = $connection->prepare(
                "UPDATE member 
			SET name = :name, email = :email, phone = :phone, modify_date = :modify_date , is_active = :status, get_call = :iscall 
			WHERE uid = :uid
			"
        );
        $result = $statement->execute(
                array(
                    ':name' => $_POST["name"],
                    ':email' => $_POST["email"],
                    ':phone' => $_POST["phone"],
                    ':status' => $_POST["status"],
                    ':iscall' => $_POST["get_call"],
                    ':modify_date' => date("Y-m-d H:i:s"),
                    ':uid' => $_POST["user_id"]
                )
        );
        if (!empty($result)) {
            echo 'Data Updated Successfully';
        }
    }

    if ($_POST["operation"] == "Send") {
        $check_status = $_POST['status'];
        try {
            if ($check_status == 'active') {  // For active user
                $stmt = $connection->prepare("SELECT * FROM member WHERE is_active = :active ");
                $stmt->execute(array(':active' => "Y"));
            } elseif ($check_status == 'inactive') {  // for In active User
                $stmt = $connection->prepare("SELECT * FROM member WHERE is_active =  :active ");
                $stmt->execute(array(':active' => "N"));
            } elseif ($check_status == 'get_call') {  // user who responed YES today
                $stmt = $connection->prepare("SELECT * FROM reply WHERE LOWER(`reply_msg`) = :msg  AND created_date >= :today");
                $stmt->execute(array(':msg' => strtolower("yes"), ':today' =>  date("Y-m-d") . ' 00:00:00'));
            } else {  // For all user 
                $stmt = $connection->prepare("SELECT * FROM member");
                $stmt->execute();
            }
        } catch (Exception $ex) {
            echo $ex->getMessage() . "<br />";
        }

        $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($all_users as $user) {
            try {
                if ($check_status == 'get_call') {
                    $phone_number = $user['m_from'];
                } else {
                    $phone_number = $user['phone'];
                }

                $twilio_msg = $client->messages->create(
                    $phone_number, array(
                    'from' => FROM_NUMBER,
                    'body' => $_POST["smstext"],
                        )
                );
                $statement = $connection->prepare("
                            INSERT INTO send_sms (sms_sid, body, m_to, m_from, date_created, date_modified) 
                            VALUES (:sms_sid, :body, :m_to, :m_from, :date_created, :date_modified)
                    ");
                $result = $statement->execute(
                        array(
                            ':sms_sid' => $twilio_msg->sid,
                            ':body' => $twilio_msg->body,
                            ':m_to' => $twilio_msg->to,
                            ':m_from' => $twilio_msg->from,
                            ':date_created' => date("Y-m-d H:i:s"),
                            ':date_modified' => date("Y-m-d H:i:s")
                        )
                );
            } catch (Exception $ex) {
                echo $ex->getMessage() . "<br />";
            }
        }
        echo "Message sent successfully.";
    }
}


if (isset($_POST['logout'])) {

    session_destroy();
    session_unset();

    header("Location: " . BASE_URL . "login.php");
}
