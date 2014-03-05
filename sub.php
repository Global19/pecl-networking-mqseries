<?php

$cfg = array();
$cfg['ESB_ADDRESS']                = '';
$cfg['ESB_CHANNEL']                = '';
$cfg['ESB_QUEUE_MANAGER']          = '';
$cfg['ESB_QUEUE_NAME']             = '';
$cfg['ESB_TOPIC_STRING']           = '';


$connectionOptions = array(
    'Version' => MQSERIES_MQCNO_VERSION_2,
    'Options' => MQSERIES_MQCNO_STANDARD_BINDING | 64,
    'MQCD' => array(
        'ChannelName' => $cfg['ESB_CHANNEL'],
        'ConnectionName' => $cfg['ESB_ADDRESS'],
        'TransportType' => MQSERIES_MQXPT_TCP
    )
);

mqseries_connx($cfg['ESB_QUEUE_MANAGER'], $connectionOptions, $connection, $completionCode, $reason);
if ($completionCode !== MQSERIES_MQCC_OK) {
    die("Connx CompCode : {$completionCode} Reason : {$reason} Text : " . mqseries_strerror($reason));
}


const MQSO_CREATE = 2;
const MQSO_MANAGED = 32;
const MQSO_NON_DURABLE = 0;
const MQSO_FAIL_IF_QUIESCING = 8192;

$subDesc = array(
//    'ObjectName' => $cfg['ESB_QUEUE_NAME'],
    'ObjectString' => $cfg['ESB_TOPIC_STRING'],
    'Options' => MQSO_CREATE | MQSO_MANAGED | MQSO_NON_DURABLE | MQSO_FAIL_IF_QUIESCING,
);

mqseries_sub($connection, $subDesc, $queue, $sub, $completionCode, $reason);
if ($completionCode !== MQSERIES_MQCC_OK) {
    die("Connx CompCode : {$completionCode} Reason : {$reason} Text : " . mqseries_strerror($reason));
}

const MQGMO_CONVERT = 16384;
$gmo = array(
    'Options' => MQSERIES_MQGMO_WAIT,
    'WaitInterval' => 100,
);

function inquireQname($connection, $queue, &$qname)
{
    mqseries_inq($connection, $queue, 1, array(MQSERIES_MQCA_Q_MGR_NAME), 0, $int_attr, 48, $char_attr, $completionCode, $reason);
    if ($completionCode !== MQSERIES_MQCC_OK) {
        printf("MQINQ failed with Condition code %d and Reason %d\n", $completionCode, $reason);
        $qname = 'unknown queue';
    }
    return;
}

inquireQname($connection, $queue, $qname);
echo 'WAITING ', PHP_EOL;
do {
    $messageFilter = array(
    );
    $bytesLength = null;
    mqseries_get($connection, $queue, $messageFilter, $gmo, $bytesLength, $messageContent, $data_length, $completionCode, $reason);
    var_dump($messageContent);die();
} while($completionCode !== MQSERIES_MQCC_OK);

die('FIN');
