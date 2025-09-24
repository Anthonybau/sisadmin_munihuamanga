<?php
$options[ CURLOPT_SSL_VERIFYPEER] = false;
$options[CURLOPT_URL] = 'https://ww1.essalud.gob.pe';
//$options[CURLOPT_HTTPHEADER] =  $this->_httpheader);
$options[CURLOPT_TIMEOUT] =  30;
//$options[CURLOPT_MAXREDIRS] =  $this->_maxRedirects);
$options[CURLOPT_RETURNTRANSFER] =  true;
$options[CURLOPT_FOLLOWLOCATION] =  false;
$options[CURLOPT_HTTP_VERSION] =  CURL_HTTP_VERSION_1_1;
                       

//$options[CURLOPT_PORT] = 443;
$options[CURLOPT_FRESH_CONNECT] = true;
$options[CURLOPT_FOLLOWLOCATION] = false;
$options[CURLOPT_FAILONERROR] = true;
$options[CURLOPT_RETURNTRANSFER] = true; // curl_exec will not return true if you use this, it will instead return the request body
$options[CURLOPT_TIMEOUT] = 30;

// Preset $response var to false and output
$fb = "";
$response = "no existe";// don't quote booleans
echo '<p class="response1">'.$response.'</p>';

$curl = curl_init();
curl_setopt_array($curl, $options);
// If curl request returns a value, I set it to the var here. 
// If the file isn't found (server offline), the try/catch fails and var should stay as false.
$fb = curl_exec($curl);
curl_close($curl);

if($fb !== false) {
    echo '<p class="response2">'.$fb.'</p>';
    $response = $fb;
}

// If cURL was successful, $response should now be true, otherwise it will have stayed false.
echo '<p class="response3">'.$response.'</p>';