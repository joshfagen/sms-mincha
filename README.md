

1. Change db credentials in db.php with your server
2. set your https://signalwire.com/ credentials 

login to `/login.php` \
user: `admin` \
password: `admin` 


## Set up crons
Examples below, replace url and path to log
see https://crontab.guru/
 
```
00 12 * * 1-4 wget -qO- http://url.ca/send-sms.php >> /var/log/1.log 2>&1
00 13 * * 1-4 wget -qO- http://url.ca/notify.php >> /var/log/2.log 2>&1
50 13 * * 1-4 wget -qO- http://url.ca/confirm_cancel.php >> /var/log/3.log 2>&1
55 13 * * 1-4 wget -qO- http://url.ca/cron_do_call.php >> /var/log/4.log 2>&1
```