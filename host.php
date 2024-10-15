<?php
if (php_sapi_name() == "cli" || (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'curl') !== false)) {
    header('Content-Type: text/plain');
}
echo gethostbyaddr($_SERVER['REMOTE_ADDR']) . "\n";
?>