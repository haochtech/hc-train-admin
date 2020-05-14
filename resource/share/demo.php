<?php

include_once "wxBizDataCrypt.php";
$appid = "wx4f4bc4dec97d474b";
$sessionKey = "tiihtNczf5v6AKRyjwEUhQ==";
$encryptedData = "V5lOHNYztwn+MYv6VQLzvfDA4/TCMUADPVptwBDtKxu6ARpXmWDKN6KUncwdlUMEYOpTCJSMaAqSsZRrNy0HyPYs9tntEPi2Ia8mH/B92n4zmgOfq/ZLgdsiyajykxi+lvwZhg/50etxmE6ddfsFnQ==";
$iv = "MPCD0tusDHP7s4zgoGYE7w==";
$pc = new WXBizDataCrypt($appid, $sessionKey);
$errCode = $pc->decryptData($encryptedData, $iv, $data);
if ($errCode == 0) {
    print $data . "\n";
} else {
    print $errCode . "\n";
}