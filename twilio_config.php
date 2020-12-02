<?php

define('SID', '');
define('SIGNALWIREURL', '');
define('TOKEN', '');
define('FROM_NUMBER', '');
define('MINIMUM_REPLY_LIMIT', 10);
define('ADMIN_NUMBER', array('+15141111111', '+15141111111'));

// messages
$time = '2:00';
define('EVENT_MESSAGE', "Will you be participating in today's Mincha at $time? Please reply YES or NO");
define('CONFIRM_MESSAGE', "Mincha is confirmed for $time");
define('CANCEL_MESSAGE', "Unfortunately we only got {COUNT} confirmed, so no Mincha today");
define('NOTIFY_MESSAGE', "We now have {COUNT} responders. Please respond with YES if you will join Mincha Today at $time.");
define('NOT_MEMBER', "Your are not currently a member, if you would like to join Please send START");
define('NOT_YESNO_REPLY', "We did not understand your message, available replies are YES or NO if you have any other concern please contact the RABBI at 514-111-1111.");
define('CONFIRM_SUB', "Welcome back to the new Zman! Please reply START or STOP if want to signup to receive Mincha msgs for the minyan At 3675 everyday. \nIf you dont reply you will not be added. \nPS Anyone could signup by sending a msg with START to this number.\n\n^The Shames");
define('CONFIRMED_SUB_START', "Thank you! You're now added to the daily list. \nPlease reply CALL if you wish to get a reminder call 5 minutes before Mincha");
define('CONFIRMED_SUB_STOP', "Thank you! You have been removed from the daily msgs. You could always rejoin by sending START.");
define('CONFIRMED_SUB_CALL', "Thank you! Will call you!");

