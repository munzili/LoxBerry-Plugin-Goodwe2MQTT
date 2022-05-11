<?php
require_once "loxberry_system.php";
require_once "loxberry_json.php";
require_once "loxberry_io.php";
require_once "loxberry_log.php";
require_once "phpMQTT/phpMQTT.php";
error_reporting(E_ALL);

$cfg = new LBJSON("$lbpconfigdir/config.json");

if(!file_exists($lbpdatadir . "/sensors.json"))
{
    exec("python3 $lbpbindir/fetchInverterData.py {$cfg->InverterIP} $lbpdatadir", null, $result_code);    
    
    if($result_code != 0)
    {
        notify( $lbpplugindir, "cron-python", "The python 'sensors data' import script returned an error: $result_code", true);
        exit 1;
    }
}

exec("python3 $lbpbindir/fetchInverterData.py {$cfg->InverterIP} $lbpdatadir", null, $result_code);

if($result_code != 0)
{
    notify( $lbpplugindir, "cron-python", "The python 'inverter data' import script returned an error: $result_code", true);
    exit 1;
}

if(!file_exists($lbpdatadir . "/data.json") || !file_exists($lbpdatadir . "/sensors.json"))
{
    notify( $lbpplugindir, "cron-php", "No data and sensors file found", true);
    exit 1;
}

// Get the MQTT Gateway connection details from LoxBerry
$creds = mqtt_connectiondetails();

// MQTT requires a unique client id
$client_id = uniqid(gethostname()."_client");

$dataFile = file_get_contents($lbpdatadir . "/data.json");
$datas = json_decode($dataFile, true);

$sensorsFile = file_get_contents($lbpdatadir . "/sensors.json");
$sensors = json_decode($sensorsFile, true);

$mqtt = new Bluerhinos\phpMQTT($creds['brokerhost'],  $creds['brokerport'], $client_id);

if( $mqtt->connect(true, NULL, $creds['brokeruser'], $creds['brokerpass'] ) ) {
    foreach($sensors as $sensorInfo) {
        preg_match("/id_='([a-z0-9_]+)'/i", $sensorInfo, $match);
        $sensorName = $match[1];

        if(isset($datas[$sensorName]))
        {
            $mqtt->publish("goodwe/$sensorName", $datas[$sensorName], 0, 1);
        }
    }
    $mqtt->close();
} else {
    echo "MQTT connection failed";
}

unlink($lbpdatadir . "/data.json");