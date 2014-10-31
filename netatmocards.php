<?php
/* 
* Script Antoine KLEIN à partir du travail de @Cyril Lopez 
* Pour affichage des informations Netatmo sur Pebble - CARDS
* Netatmo API
*/ 
// **************************** PARTIE A PERSONNALISER

// Nombre de minutes pour raffraichir les informations affichées
$refresh_frequency = 300;

// Indiquez les informations après avoir créer une application sur http://dev.netatmo.com/dev/createapp
$app_id = '[APP_ID_NETATMO]';
$app_secret = '[APP_SECRET_NETATMO]';

// **************************** VERIFICATION IDENTIFICATION

if ($_GET['mail'] != "" & $_GET['pass'] != "") 
{	
	// RECUPERTATION IDENTIFICATION
	$username = $_GET['mail'];
	$password = $_GET['pass'];
	
} else {
	echo 'Vous devez saisir les informations MAIL et PASS';
}

if  ($username != '' & $password != '') 
{
// **************************** DEBUT PAGE *****************************************

// **************************** CONNECTION API
$token_url = "https://api.netatmo.net/oauth2/token";
$postdata = http_build_query(
        array(
            'grant_type' => "password",
            'client_id' => $app_id,
            'client_secret' => $app_secret,
            'username' => $username,
            'password' => $password,
            'scope' => 'read_station read_thermostat write_thermostat'
    )
);

$opts = array('http' =>
	array(
		'method'  => 'POST',
		'header'  => 'Content-type: application/x-www-form-urlencoded',
		'content' => $postdata
	)
);

// Récupération des données via l'Api Netatmo
function getSSLPage($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSLVERSION,3); 
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

getSSLPage($token_url);

$context  = stream_context_create($opts);
$response = file_get_contents($token_url, false, $context);
$params = null;
$params = json_decode($response, true);
$api_url = "https://api.netatmo.net/api/getuser?access_token=" . $params['access_token']."&app_type=app_thermostat";
$requete = @file_get_contents($api_url);

// Création de(s) l'url(s)
$api_url_stationmeteo = "https://api.netatmo.net/api/devicelist?access_token=" .$params['access_token'];
$api_url_user = "https://api.netatmo.net/api/getuser?access_token=" . $params['access_token']."&app_type=app_thermostat";
$api_url_thermostat = "https://api.netatmo.net/api/devicelist?access_token=" .  $params['access_token']."&app_type=app_thermostat";

$data_info = json_decode(file_get_contents($api_url_stationmeteo, false, $context), true);
$data_therm = json_decode(file_get_contents($api_url_thermostat, false, $context), true);

// **************************** FONCTIONS

// Battery level INDORR
Function NABatteryLevelIndoorModule($data)
{
    if ( $data >= 5640 ) 
	{ 
		return "Pleine";
	} else {
		if ( $data >= 5280 )
		{ 
			return "Haute"; 
		} else {
			if ( $data >= 4920 )
			{
				return "Moyenne";
			} else {
				if ( $data >= 4560 )
				{
					return "Bassse";
				} else {
					return "Très bassse";
				}
			}
		}
	}
}

// Battery level OUTDOOR
Function NABatteryLevelModule($data)
{
    if ( $data >= 5500 ) 
	{ 
		return "Pleine";
	} else {
		if ( $data >= 5000 )
		{ 
			return "Haute"; 
		} else {
			if ( $data >= 4500 )
			{
				return "Moyenne";
			} else {
				if ( $data >= 4000 )
				{
					return "Basse";
				} else {
					return "Très basse";
				}
			}
		}
	}
}

// Battery level thermostat
Function NABatteryLevelThermostat($data)
{
    if ( $data >= 4100 ) 
	{ 
		return "Pleine";
	} else {
		if ( $data >= 3600 )
		{ 
			return "Haute"; 
		} else {
			if ( $data >= 3300 )
			{
				return "Moyenne";
			} else {
				if ( $data >= 3000 )
				{
					return "Basse";
				} else {
					return "Très basse";
				}
			}
		}
	}
}

// rf_status
Function NARadioRssiTreshold($data)
{
    if ( $data >= 90 ) 
	{ 
		return "Signal mauvais";
	} else {
		if ( $data >= 80 )
		{ 
			return "Signal de qualité moyenne"; 
		} else {
			if ( $data >= 70 )
			{
				return "Signal bon";
			} else {
				return "Signal fort";
			}
		}
	}
}

// wifi_status
Function NAWifiRssiThreshold($data)
{
	if ( $data >= 86 )
	{ 
		return "Signal mauvais"; 
	} else {
		if ( $data >= 71 )
		{
			return "<Signal de qualité moyenne";
		} else {
			return "Signal bon";
		}
	}
}

// Orentiation
Function NAorientation($data)
{
	if ( $data == 1 ) { return "Paysage"; }
	if ( $data == 2 ) { return "Portait"; }
}

// ETAT
Function NAetat($data)
{
	if ( $data == 0 ) { return "X"; }
	if ( $data == 100 ) { return "!"; }
}

// **************************** RECUPERATION DES DONNEES

//INFO-INT
$name_int = $data_info['body']['devices'][0]['module_name'];
$mac_int = $data_info['body']['devices'][0]['_id'];
$type_int = $data_info['body']['devices'][0]['type'];
$temp_int = $data_info['body']['devices'][0]['dashboard_data']['Temperature'];
$hum_int = $data_info['body']['devices'][0]['dashboard_data']['Humidity'];
$noise_int = $data_info['body']['devices'][0]['dashboard_data']['Noise'];
$pres_int = $data_info['body']['devices'][0]['dashboard_data']['Pressure'];
$presabsolue_int = $data_info['body']['devices'][0]['dashboard_data']['AbsolutePressure'];
$co2_int = $data_info['body']['devices'][0]['dashboard_data']['CO2'];
$rain_int = $data_info['body']['devices'][0]['dashboard_data']['rain'];
$mintemp_int = $data_info['body']['devices'][0]['dashboard_data']['min_temp'];
$maxtemp_int = $data_info['body']['devices'][0]['dashboard_data']['max_temp'];
$datemintemp_int = $data_info['body']['devices'][0]['dashboard_data']['date_min_temp'];
$datemaxtemp_int = $data_info['body']['devices'][0]['dashboard_data']['date_max_temp'];
$firmware_int = $data_info['body']['devices'][0]['firmware'];
$wifi_int = $data_info['body']['devices'][0]['wifi_status'];
$refmod1_int = $data_info['body']['devices'][0]['modules'][1];
$refmod2_int = $data_info['body']['devices'][0]['modules'][2];
$refmod3_int = $data_info['body']['devices'][0]['modules'][3];

//INFO-EXT
$name_ext = $data_info['body']['modules'][0]['module_name'];
$mac_ext = $data_info['body']['modules'][0]['_id'];
$type_ext = $data_info['body']['modules'][0]['type'];
$temp_ext = $data_info['body']['modules'][0]['dashboard_data']['Temperature'];
$hum_ext = $data_info['body']['modules'][0]['dashboard_data']['Humidity'];
$mintemp_ext = $data_info['body']['modules'][0]['dashboard_data']['min_temp'];
$maxtemp_ext = $data_info['body']['modules'][0]['dashboard_data']['max_temp'];
$datemintemp_ext = $data_info['body']['modules'][0]['dashboard_data']['date_min_temp'];
$datemaxtemp_ext = $data_info['body']['modules'][0]['dashboard_data']['date_max_temp'];
$battery_ext = $data_info['body']['modules'][0]['battery_vp'];
$statusrf_ext = $data_info['body']['modules'][0]['rf_status'];
$firmware_ext = $data_info['body']['modules'][0]['firmware'];

//INFO_MOD1
if ( $refmod1_int <> "" ) {
	$name_mod1 = $data_info['body']['modules'][1]['module_name'];
	$mac_mod1 = $data_info['body']['modules'][1]['_id'];
	$type_mod1 = $data_info['body']['modules'][1]['type'];
	$temp_mod1 = $data_info['body']['modules'][1]['dashboard_data']['Temperature'];
	$hum_mod1 = $data_info['body']['modules'][1]['dashboard_data']['Humidity'];
	$noise_mod1 = $data_info['body']['modules'][1]['dashboard_data']['Noise'];
	$pres_mod1 = $data_info['body']['modules'][1]['dashboard_data']['Pressure'];
	$co2_mod1 = $data_info['body']['modules'][1]['dashboard_data']['CO2'];
	$mintemp_mod1 = $data_info['body']['modules'][1]['dashboard_data']['min_temp'];
	$maxtemp_mod1 = $data_info['body']['modules'][1]['dashboard_data']['max_temp'];
	$datemintemp_mod1 = $data_info['body']['modules'][1]['dashboard_data']['date_min_temp'];
	$datemaxtemp_mod1 = $data_info['body']['modules'][1]['dashboard_data']['date_max_temp'];
	$battery_mod1 = $data_info['body']['modules'][1]['battery_vp'];
	$statusrf_mod1 = $data_info['body']['modules'][1]['rf_status'];
	$firmware_mod1 = $data_info['body']['modules'][1]['firmware'];
}

//INFO_MOD2
if ( $refmod2_int <> "" ) {
	$name_mod2 = $data_info['body']['modules'][2]['module_name'];
	$mac_mod2 = $data_info['body']['modules'][2]['_id'];
	$type_mod2 = $data_info['body']['modules'][2]['type'];
	$temp_mod2 = $data_info['body']['modules'][2]['dashboard_data']['Temperature'];
	$hum_mod2 = $data_info['body']['modules'][2]['dashboard_data']['Humidity'];
	$noise_mod2 = $data_info['body']['modules'][2]['dashboard_data']['Noise'];
	$pres_mod2 = $data_info['body']['modules'][2]['dashboard_data']['Pressure'];
	$co2_mod2 = $data_info['body']['modules'][2]['dashboard_data']['CO2'];
	$mintemp_mod2 = $data_info['body']['modules'][2]['dashboard_data']['min_temp'];
	$maxtemp_mod2 = $data_info['body']['modules'][2]['dashboard_data']['max_temp'];
	$datemintemp_mod2 = $data_info['body']['modules'][2]['dashboard_data']['date_min_temp'];
	$datemaxtemp_mod2 = $data_info['body']['modules'][2]['dashboard_data']['date_max_temp'];
	$battery_mod2 = $data_info['body']['modules'][2]['battery_vp'];
	$statusrf_mod2 = $data_info['body']['modules'][2]['rf_status'];
	$firmware_mod2 = $data_info['body']['modules'][2]['firmware'];
}

//INFO_MOD3
if ( $refmod3_int <> "" ) {
	$name_mod3 = $data_info['body']['modules'][3]['module_name'];
	$mac_mod3 = $data_info['body']['modules'][3]['_id'];
	$type_mod3 = $data_info['body']['modules'][3]['type'];
	$temp_mod3 = $data_info['body']['modules'][3]['dashboard_data']['Temperature'];
	$hum_mod3 = $data_info['body']['modules'][3]['dashboard_data']['Humidity'];
	$noise_mod3 = $data_info['body']['modules'][3]['dashboard_data']['Noise'];
	$pres_mod3 = $data_info['body']['modules'][3]['dashboard_data']['Pressure'];
	$co2_mod3 = $data_info['body']['modules'][3]['dashboard_data']['CO2'];
	$mintemp_mod3 = $data_info['body']['modules'][3]['dashboard_data']['min_temp'];
	$maxtemp_mod3 = $data_info['body']['modules'][3]['dashboard_data']['max_temp'];
	$datemintemp_mod3 = $data_info['body']['modules'][3]['dashboard_data']['date_min_temp'];
	$datemaxtemp_mod3 = $data_info['body']['modules'][3]['dashboard_data']['date_max_temp'];
	$battery_mod3 = $data_info['body']['modules'][3]['battery_vp'];
	$statusrf_mod3 = $data_info['body']['modules'][3]['rf_status'];
	$firmware_mod3 = $data_info['body']['modules'][3]['firmware'];
}

// Thermostat
$name_therm = $data_therm['body']['modules'][0]['module_name'];
$mac_therm = $data_therm['body']['modules'][0]['_id'];
$type_therm = $data_therm['body']['modules'][0]['type'];
$temp_therm = $data_therm['body']['modules'][0]['dashboard_data']['Temperature'];
$mintemp_therm = $data_therm['body']['modules'][0]['dashboard_data']['min_temp'];
$maxtemp_therm = $data_therm['body']['modules'][0]['dashboard_data']['max_temp'];
$datemintemp_therm = $data_therm['body']['modules'][0]['dashboard_data']['date_min_temp'];
$datemaxtemp_therm = $data_therm['body']['modules'][0]['dashboard_data']['date_max_temp'];
$battery_therm = $data_therm['body']['modules'][0]['battery_vp'];
$statusrf_therm = $data_therm['body']['modules'][0]['rf_status'];
$firmware_therm = $data_therm['body']['modules'][0]['firmware'];
$orientation_therm = $data_therm['body']['modules'][0]['therm_orientation'];
$etat_therm = $data_therm['body']['modules'][0]['therm_relay_cmd'];

// Relai
$name_relai = $data_therm['body']['devices'][0]['station_name'];
$mac_relai = $data_therm['body']['devices'][0]['_id'];
$type_relai = $data_therm['body']['devices'][0]['type'];
$firmware_relai = $data_therm['body']['devices'][0]['firmware'];
$wifi_relai = $data_therm['body']['devices'][0]['wifi_status'];
$refmod1_relai = $data_therm['body']['devices'][0]['modules'][0];
$refmod2_relai = $data_therm['body']['devices'][0]['modules'][1];
$refmod3_relai = $data_therm['body']['devices'][0]['modules'][2];
$refmac_relai = $data_therm['body']['devices'][0]['house_model']['link_station']['mac'];
$refext_relai = $data_therm['body']['devices'][0]['house_model']['link_station']['ext'];
$reftemp_relai = $data_therm['body']['devices'][0]['house_model']['link_station']['Temperature'];

// **************************** AFFICHAGE DES INFORMATIONS POUR LA PEBBLE

echo '{"content":"Ti/e : '.$temp_int.'/'.$temp_ext.'\nT1/2 '.$temp_mod1.'/'.$temp_mod2.'\nT therm '.$temp_therm.'- '.NAetat($etat_therm).'","refresh_frequency":'.$refresh_frequency.'}';
}
?>
