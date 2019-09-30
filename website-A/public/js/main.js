// 取得JWT內容
function parseJwt(token) {
  var base64Url = token.split('.')[1];
  var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
  var jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
      return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
  }).join(''));

  return JSON.parse(jsonPayload);
};

// 取得現在是否為登入狀態
function getLoginStatus() {
  let jwt = store.get('jwt_token');
  if (jwt == null) {
    return null;
  }

  if (parseJwt(jwt).exp < (Date.now() / 1000)) {
    store.remove('jwt_token');
    return null;
  }

  return parseJwt(jwt);
}

// 轉至 SSO 登入頁面
async function toLoginPage() {
  let ssoLoginUrl = 'http://localhost:9011/login.html?';
  window.location.href = ssoLoginUrl + "redirect_url=" + encodeURIComponent(window.location.href);
}
