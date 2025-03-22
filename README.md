# php Utils :: php를 사용한 api서비스

###
###
## 0. 왜 php인가?
- 서버가 필요한 작업에서 여러 호스팅 업체들이 APM(Apache, Php, MySql)을 무료, 혹은 저렴한 가격에 제공하고 있어 부담이 없습니다.
- 기본적으로 php는 backend언어지만 서버사이드렌더링(SSR)을 통해 웹페이지의 기능 역시 할 수 있습니다.
- 운영중 실시간으로 코드의 수정이 가능하고 수정된 코드가 바로 적용됩니다.
- java, javascript와 비슷한 코드 작성법으로 구현이 간단하며 러닝커브가 낮습니다.


## 1. 지원중인 서비스
### 1.1. CORS Bypass
- 서버를 통해 직접 cURL을 요청하여 CORS(Cross-Origin Resource Sharing) block 현상을 우회하여 응답값을 return 해줍니다.

#### 1.1.1. ajax방식 : [/util/ajax.php](/html/util/ajax.php)
> - 파일의 설정부에 요청시작사이트, 요청대상사이트의 허용목록, 화이트리스트 사용여부, 사이트 외부공급여부 등을 설정합니다.
> - ajax를 사용하여 <호스팅주소>/util/ajax.php?ajaxTargetUrl=<대상url> 로 요청을 보내면 응답을 보내줍니다.
> - 대상url은 encodeURIComponent(대상url)를 통해 변환 후 사용해야합니다. 
> - Apache 1.3 이상에서 .htaccess의 내용 적용시 링크에서 ".php"를 제외하고 사용 가능합니다.
> - POST, GET 등의 ajax방식에 따라 서버에도 같은 방식으로 cURL을 요청합니다. 


#### 1.1.2. jsonp방식 : [/util/jsonp.php](/html/util/jsonp.php)
> - 파일의 설정부에 요청시작사이트, 요청대상사이트의 허용목록, 화이트리스트 사용여부, 사이트 외부공급여부 등을 설정합니다.
> - jsonp를 사용하여 <호스팅주소>/util/jsonp.php?jsonpTargetUrl=<대상url>&callback=<콜백함수명>&jsonpTargetMethod=<요청메소드> 로 요청을 보내면 콜백함수로 응답값을 보내줍니다. 
> - 직접 사용시 대상url은 encodeURIComponent(대상url)를 통해 변환 후 사용해야합니다.
> - Apache 1.3 이상에서 .htaccess의 내용 적용시 링크에서 ".php"를 제외하고 사용 가능합니다.
> - POST, GET 등의 ajax방식에 따라 서버에도 같은 방식으로 cURL을 요청합니다. 
- jsonp를 사용하기 쉽도록 [jsonp.js](/html/js/jsonp.js) 와 [jsonp.min.js](/html/js/jsonp.min.js) 안에 sendJsonp(bypassUrl, tragetUrl, callbackFunc, method="GET") 함수를 작성해놓았습니다.
```
// 아래와 같은 형식으로 사용가능.
const callbackTest = (response) => {
  console.log(response)
}

sendJsonp("http://example.com/jsonp.php", "http://target.com", callbackTest, "POST")
```