/**
 * sendJsonp
 * @param bypassUrl 우회를 요청할 사이트 주소입니다. <주소>/util/ajax.php 혹은 <주소>/util/ajax 입니다.
 * @param tragetUrl 우회할 대상 사이트의 주소입니다.
 * @param callbackFunc 콜백으로 실행할 함수입니다. function 함수(response){} 의 response에 응답값이 담깁니다.
 * @param method 요청할 method입니다. 지정하지 않으면 get 방식으로 전달됩니다.
 */
const sendJsonp = (bypassUrl, tragetUrl, callbackFunc, method="GET") => {
  const callbackName = "jsonpCallback_" + new Date().getTime()

  const removeCallback = () => {
    document.getElementById(callbackName).remove()
    delete window[callbackName]
  }

  // 동적으로 script 태그 추가하여 JSONP 요청 수행
  const script = document.createElement("script")
  script.src = `${bypassUrl}?jsonpTargetUrl=${encodeURIComponent(tragetUrl)}&callback=${callbackName}&jsonpTargetMethod=${method}`
  script.id = callbackName
  script.onerror = () => {
    callbackFunc("요청실패")
    removeCallback()
  }

  // 콜백 함수 정의
  window[callbackName] = (response) => {
    try {
      callbackFunc(JSON.parse(response.data))
    } catch(e) {
      callbackFunc(response.data)
    }
    removeCallback()
  }

  document.body.appendChild(script)
}