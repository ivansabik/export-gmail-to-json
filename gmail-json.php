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
    $inbox = imap_open($hostname, $user, $pass) or die('IMAP / Gmail not working: ' . imap_last_error());
} catch (Exception $e) {
    print 'Perhaps IMAP for PHP missing?' . PHP_EOL . $e;
}
$emails = imap_search($inbox, 'ALL');

# General wizard
$outputdir = './gmail-backup';
$write_mongo = null;
print 'Output directory (default is '. $outputdir . '): ';
$response = trim(fgets(STDIN));
if($response != '') {
    $outputdir = $response;
}
print 'Write to MongoDB (default is N): ';
$response = trim(fgets(STDIN));
if($response != '')
    $write_mongo = $response;

#  MongoDB wizard
$mogo_url = 'localhost:27017';
$mongo_user = '';
$mongo_password = '';
$mongo_dbname = 'gmail';
$mongo_colname = 'backup';

if($write_mongo) { 
    print 'MongoDB url (default is '. $mogo_url . '): ';
    trim(fgets(STDIN));
    print 'MongoDB user: (default is '. $mongo_user . '): ';
    trim(fgets(STDIN));
    print 'MongoDB password: (default is '. $mongo_password . '): ';
    trim(fgets(STDIN));
    print 'MongoDB database: (default is '. $mongo_dbname . '): ';
    trim(fgets(STDIN));
    print 'MongoDB collection: (default is '. $mongo_colname . '): ';
    trim(fgets(STDIN));
}

# Make dir if non-existent
if (!file_exists($outputdir)) {
    mkdir($outputdir, 0777, true);
}

# Iterate email data
if ($emails) {
    $total = count($emails);
    $remaining = $total;

    if($write_mongo) {
        $mongodb = new Mongo($mogo_url);
        $mongodb_db = $mongodb->$mongo_dbname;
        $mongodb_col = $mongodb_db->$mongo_colname;
    }
   

    foreach ($emails as $email_number) {
        # Fetch body
        $message =  quoted_printable_decode(imap_fetchbody($inbox, $email_number, 1.1));
        if($message == '') {
            $message =  quoted_printable_decode(imap_fetchbody($inbox, $email_number, 1));
        }
        # Fetch date, from, subject and message id
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
        # Output progress
        $per_remaining = sprintf ("%.2f", $remaining/$total * 100);
        print 'Processing message ' . $message_id . ' remaining '.  $per_remaining .'%' . PHP_EOL;
        $json_email = json_encode($email);
        # Export to file and mongo
        file_put_contents($outputdir . '/'. $uid . '_' . urlencode($message_id) . '.json', $json_email);
        if($write_mongo) {
	    $mongodb_col->save($email);
        }
        $remaining--;
    }
    imap_close($inbox);
    # MongoDB indexing and output statistics
    if($write_mongo) {
        print 'Imported records: ';
    }
} else {
    print 'No mails available' . PHP_EOL;
}
?>
