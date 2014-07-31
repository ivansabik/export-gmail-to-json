#!/usr/bin/php -q
<?php

$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';

print 'Gmail user: ';
$user = trim(fgets(STDIN));
print 'Gmail password: ';
system('stty -echo');
$pass = trim(fgets(STDIN));
system('stty echo');
try {
    $inbox = imap_open($hostname, $user, $pass) or die('IMAP and GMail not working: ' . imap_last_error());
} catch (Exception $e) {
    print $e;
}

$emails = imap_search($inbox, 'ALL');

$mailexanders = array();
if ($emails) {
    foreach ($emails as $email_number) {
        $message = imap_fetchbody($inbox, $email_number, 1.1);
        $array = imap_fetch_overview($inbox, $email_number);
        var_dump($array[0]) . PHP_EOL;
    }
    imap_close($inbox);
} else {
    print 'No mails available' . PHP_EOL;
}
?>
