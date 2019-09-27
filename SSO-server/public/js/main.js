// 取得JWT內容
function parseJwt (token) {
  var base64Url = token.split('.')[1];
  var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
  var jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
      return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
  }).join(''));

  return JSON.parse(jsonPayload);
};

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

// 登入
function login() {
  let username = document.getElementById('username').value;
  let password = document.getElementById('password').value;

  axios.post('/api/login', {
    username: username,
    password: password
  })
  .then(function (response) {
    store.set('jwt_token', response.data.token);
    refreshUI();
  })
  .catch(function (error) {
    alert('Login Fail');
  });
}

// 登出
function logout() {
  store.remove('jwt_token');
  refreshUI();
}