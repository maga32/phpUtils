<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Content-Encoding, Accept');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PATCH, PUT, DELETE');
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// OPTIONS 요청 처리 (CORS Preflight)
if($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
$target = isset($_GET['ajaxTargetUrl']) ? urldecode($_GET['ajaxTargetUrl']) : '';

// URL 유효성 검사
if(!filter_var($target, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid URL"]);
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
    header('Content-Type: application/json');
    echo json_encode(["data" => "error: Access denied"]);
    exit;
}

// HTTP 메서드 가져오기
$method = $_SERVER['REQUEST_METHOD'];

// 요청 데이터 가져오기
$data = file_get_contents("php://input");

// cURL 요청 설정
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $target);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

// 데이터가 있으면 추가
if(!empty($data)) {
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
}

// 요청 실행
$response = curl_exec($ch);
curl_close($ch);

// json응답의 경우 json화 시도
try {
    $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    header("Content-Type: application/json");
    echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo $response;
}

?>
