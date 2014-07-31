#!/usr/bin/php -q
<?php

# Dyamic prompt for config (inbox, all, number, fields, one/many files, etc)
# Create dir to export
# Mongo
# Progress bar, print currently writing file

$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';

# Prompt Gmail login credentials
print 'Gmail user: ';
$user = trim(fgets(STDIN));
print 'Gmail password: ';
system('stty -echo');
$pass = trim(fgets(STDIN));
system('stty echo');
print PHP_EOL;

try {
    $inbox = imap_open($hostname, $user, $pass) or die('IMAP / GMail not working: ' . imap_last_error());
} catch (Exception $e) {
    print 'Perhaps IMAP for PHP missing? sudo apt-get install php5-imap' . PHP_EOL . $e;
}

$emails = imap_search($inbox, 'ALL');

if (!file_exists('./json')) {
    mkdir('./json', 0777, true);
}

# Get mails data
if ($emails) {
    $total = count($emails);
    $remaining = $total;
    foreach ($emails as $email_number) {
        $message =  quoted_printable_decode(imap_fetchbody($inbox, $email_number, 1.1));
        if($message == '') {
            $message =  quoted_printable_decode(imap_fetchbody($inbox, $email_number, 1));
        }

        $head = imap_fetch_overview($inbox, $email_number);
	$head = $head[0];
        $date = $head->date;
        $from = $head->from;
        $subject = $head->subject;
        $message_id = $head->message_id;
        $uid = $head->uid;
	$email = array(
	    'message_id' => $message_id,
	    'uid' => $uid,
	    'date' => $date,
	    'subject' => $subject,
	    'from' => $from,
	    'message' => $message,
	);
        $per_remaining = sprintf ("%.2f", (1 - $remaining/$total) * 100);
        print 'Exporting ' . $uid . ' of ' . $total . ' -> '.  $per_remaining .'%' . PHP_EOL;
	$json_email = json_encode($email);
        file_put_contents('./json/' . $uid . '_' . urlencode($message_id) . '.json', $json_email);
        $remaining--;

    }
    imap_close($inbox);
} else {
    print 'No mails available' . PHP_EOL;
}
?>
