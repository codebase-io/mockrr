<?php

$action = ucfirst($action ?? 'include');

http_response_code(200);
print sprintf("File %s was {$action}d", __FILE__);
