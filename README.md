# PHP Single Sign-on Demo

這是一個用PHP寫的 Single Sign-on 範例 (因為 Single Sign-on 這個概念的實作可以有很多種，這邊只以一種為範本，可以依據自己需求做變形)。

## 啟動

這邊以docker做示範，需先安裝docker 並輸入指令啟動：

```
docker-compose up
```

## 網站

| 網址 | 說明 |
| --- | --- |
| http://localhost:9011 | SSO 站 |
| http://localhost:9012 | 子服務站 WebsiteA |

## 運作流程

#### 從 SSO站 登入並到 WebsiteA：

![](./README_attachments/login-sequenceDiagram-1.png)

<details>
  <summary>Mermaid source code</summary>

  ```
  sequenceDiagram
    participant SSO站
    participant WebsiteA
    SSO站->>SSO站: 1. 在SSO站登入，SSO站紀錄本身的登入狀態
    SSO站->>WebsiteA: 2. 點擊 Website 連結時，連結附上驗證用的 token 轉向 WebsiteA
    WebsiteA->>SSO站: 3. WebsiteA 透過後端API詢問 SSO站 token 是否有效
    SSO站->>WebsiteA: 4. SSO站回覆token是否正確，如果正確附上使用者資訊
    WebsiteA->>WebsiteA: 5. 如果token是有效的，WebsiteA記住本身的登入狀態 
  ```
</details>
