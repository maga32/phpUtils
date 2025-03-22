<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Content-Encoding, Accept');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PATCH, PUT, DELETE');
header("Content-Type: application/javascript");
header("X-Content-Type-Options: nosniff");

/* ------------------------------설정부 시작------------------------------- */
// 요청시작/요청대상 사이트 화이트리스트 사용여부
$useFromWhitelist = true;
$useToWhitelist = true;

// 요청시작/요청대상 사이트 허용목록(["http://example.com", ..., "https://example.com"])
$fromUrlWhitelist = [];
$toUrlWhitelist = [];

// 요청시작/요청대상 사이트 외부공급여부
$useExternalWhitelist = true;
$externalWhitelistUrl = "https://notion-api.splitbee.io/v1/page/1bd8de6c33428070b74df50f15f00ab2";

// 외부공급데이터 가공
if($useExternalWhitelist) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $externalWhitelistUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    curl_close($ch);

    $fromUrlData = json_decode($response, true)["1bd8de6c-3342-80fd-a5ef-ebb1e4365e05"]["collection"]["data"];
    $toUrlData = json_decode($response, true)["1bd8de6c-3342-800b-8fd6-d94a5af673e1"]["collection"]["data"];
    foreach($fromUrlData as $url) { array_push($fromUrlWhitelist, $url["fromUrl"][0][0]); }
    foreach($toUrlData as $url) { array_push($toUrlWhitelist, $url["toUrl"][0][0]); }
}
/* ------------------------------설정부 끝------------------------------- */

// 클라이언트가 보낸 URL 가져오기
$callback = isset($_GET['callback']) ? $_GET['callback'] : 'callbackFunction';
$target = isset($_GET['jsonpTargetUrl']) ? urldecode($_GET['jsonpTargetUrl']) : null;
$method = isset($_GET['jsonpTargetMethod']) ? strtoupper($_GET['jsonpTargetMethod']) : 'GET';

// URL 유효성 검사
if(!filter_var($target, FILTER_VALIDATE_URL)) {
    echo $callback . '({"data": "error: Invalid URL"});';
    exit;
}

// 요청시작 사이트 허용목록 확인
$fromUrlAllowed = true;
if($useFromWhitelist) {
    $fromUrlAllowed = false;
    $referer = $_SERVER['HTTP_REFERER'] ?? '';  // 'http://example.com:8080/page.html' - 조작가능
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';    // 'http://example.com:8080' - 브라우저 자동설정

    foreach($fromUrlWhitelist as $allowed) {
        if(strpos($referer, $allowed) === 0 || strpos($origin, $allowed) === 0) {
            $fromUrlAllowed = true;
            break;
        }
    }
}

// 요청대상 사이트 허용목록 확인
$toUrlAllowed = true;
if($useToWhitelist) {
    $toUrlHost = parse_url($target)['scheme'].'://'.parse_url($target)['host'] ?? '';
    $toUrlAllowed = in_array($toUrlHost, $toUrlWhitelist, true);
}

// 화이트리스트 사용시 적용
if(!$fromUrlAllowed || !$toUrlAllowed) {
    echo $callback . '({"data": "error: Access denied"});';
    exit;
}

// 대상 URL에서 데이터 가져오기
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $target);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
curl_close($ch);

echo $callback . '(' . json_encode(["data" => $response]) . ');';

?>
