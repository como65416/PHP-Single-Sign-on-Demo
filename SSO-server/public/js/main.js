// 取得可以登入的網站
async function getAvailableSites() {
  if (store.get('jwt_token') == null) {
    return [];
  }

  let response = await axios.get('/api/available-sites', {
      headers: {
        'Authorization': 'Bearer ' + store.get('jwt_token')
      }
    });

  return response.data;
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

// 取得登入狀態，如果沒有登入會回傳null
async function getLoginStatus() {
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

async function openSite(websiteUrl) {
  let response = await axios.get("/api/to-site", {
      params: {
        website_url: websiteUrl
      },
      headers: {
        'Authorization': 'Bearer ' + store.get('jwt_token')
      }
    });

  location.href = response.data.login_url;
}
