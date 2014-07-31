#!/usr/bin/php -q
<?php

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
    print 'Perhaps IMAP for PHP missing?' . PHP_EOL . $e;
}
$emails = imap_search($inbox, 'ALL');


# General wizard
$outputdir = './json';
$many_files = 'Y';
$write_mongo = false;


print 'Output directory (default is '. $outputdir .' ): ';
$response = trim(fgets(STDIN));
if($response != '')
    $outputdir = $response;

print 'Output many files (default is Y): ';
$response = trim(fgets(STDIN));
if($response != '')
    $many_files = $response;
if($many_files == 'N' || $many_files == 'n')
    $mails_array = array();
else
    $mails_array = false;

print 'Write to MongoDB (default is N): ';
$response = trim(fgets(STDIN));
if($response != '')
    $write_mongo = $response;

#  MongoDB wizard
if($write_mongo) {
    print 'MongoDB connection string: ';
    $user = trim(fgets(STDIN));
    print 'MongoDB login: ';
    $user = trim(fgets(STDIN));
    print 'MongoDB database: ';
    $user = trim(fgets(STDIN));
    print 'MongoDB collection: ';
    $user = trim(fgets(STDIN));
    print 'Gmail user: ';
    $user = trim(fgets(STDIN));
}


# Make dir if non-existent
if (!file_exists($outputdir)) {
    mkdir($outputdir, 0777, true);
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
        if($mails_array) {
            $mails_array[] = $json_email;
        }
        else {
            file_put_contents($outputdir . '/'. $uid . '_' . urlencode($message_id) . '.json', $json_email);
        }
        $remaining--;
    }
    imap_close($inbox);
    if($mails_array) {
        file_put_contents($outputdirr . '/' . $user . '.json', json_encode($mails_array));
    }
} else {
    print 'No mails available' . PHP_EOL;
}
?>
