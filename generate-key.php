<?php
use Storm\Security\Encrypt;

require_once "../../autoload.php";
echo "Key will be written to 'key' file, you have to manually initialise it with storm.";

$key = Encrypt::generateKey();
file_put_contents('key',$key);
