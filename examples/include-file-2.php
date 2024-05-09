<?php

global $mockrr;

$file   = __DIR__ . '/res/sample.txt';
$secret = file_get_contents($file);

if ($_POST['secret'] != trim($secret)) {
    $mockrr
        ->generate(['error'=> '403 Forbidden', 'message'=> "Secret does not match"])
        ->setStatus(403)
        ->print();
}
else{
    // Access granted
    file_put_contents($file, $_POST['new_secret']);

    $mockrr
        ->generate(['success'=> TRUE, 'message'=> "New secret saved."])
        ->setStatus(201)
        ->print();
}
