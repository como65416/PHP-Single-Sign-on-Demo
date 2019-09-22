// 取得現在是否為登入狀態
function isLogin() {
  let jwt = store.get('jwt_token');
  if (jwt == null) {
    return false;
  }

  if (parseJwt(jwt).exp < (Date.now() / 1000)) {
    store.remove('jwt_token');
    return false;
  }

  return true;
}

// 取得JWT內容
function parseJwt (token) {
  var base64Url = token.split('.')[1];
  var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
  var jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
      return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
  }).join(''));

  return JSON.parse(jsonPayload);
};

// 登出
function logout() {
  store.remove('jwt_token');
  refreshUI();
}