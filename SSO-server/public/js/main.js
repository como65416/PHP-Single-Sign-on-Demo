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