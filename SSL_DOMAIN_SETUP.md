# SSL 및 도메인 연결 가이드

> IMPEXGLS 프로젝트에 SSL 인증서 적용 및 도메인 연결하기

## 목차
1. [사전 준비](#사전-준비)
2. [방법 A: Lightsail Load Balancer + SSL (권장)](#방법-a-lightsail-load-balancer--ssl-권장)
3. [방법 B: Nginx Reverse Proxy + Let's Encrypt](#방법-b-nginx-reverse-proxy--lets-encrypt)
4. [도메인 연결](#도메인-연결)
5. [HTTP to HTTPS 리다이렉트](#http-to-https-리다이렉트)
6. [SSL 인증서 갱신](#ssl-인증서-갱신)
7. [트러블슈팅](#트러블슈팅)

---

## 사전 준비

### 현재 상태 확인

```bash
# Lightsail SSH 접속
ssh -i ~/.ssh/lightsail-impexgls.pem ec2-user@YOUR_LIGHTSAIL_IP

# 컨테이너 상태 확인
cd ~/impexgls
docker-compose ps

# 웹사이트 접속 확인
curl -I http://YOUR_LIGHTSAIL_IP:8082
```

### 필요한 정보

- ✅ Lightsail 인스턴스 실행 중
- ✅ Docker 컨테이너 정상 작동 (`http://YOUR_IP:8082`)
- 📋 도메인 이름 (예: `impexgls.com`, `www.impexgls.com`)
- 📋 도메인 관리 권한 (DNS 설정 가능)

---

## 방법 A: Lightsail Load Balancer + SSL (권장)

> **장점**: 관리 간편, 자동 갱신, AWS 통합
> **단점**: 추가 비용 ($18/월)
> **추천**: 프로덕션 환경, 트래픽이 많은 경우

### 1. Lightsail Load Balancer 생성

**AWS Lightsail 콘솔에서:**

1. **Networking** → **Load balancers** 클릭
2. **Create load balancer** 클릭
3. 설정:
   - **위치**: Seoul (ap-northeast-2)
   - **이름**: `impexgls-lb`
   - **대상 인스턴스**: 현재 Lightsail 인스턴스 선택
   - **Health check path**: `/`

4. **Create load balancer** 클릭 (생성 3-5분 소요)

---

### 2. 인스턴스를 Load Balancer에 연결

**Load Balancer 설정:**

1. 생성된 Load Balancer 클릭
2. **Target** 탭
3. **Attach instance** 클릭
4. 현재 인스턴스 선택
5. **Health check port**: `8082` 입력
6. **Attach** 클릭

**Health Check 확인:**
- Status가 `Healthy`로 변경될 때까지 대기 (2-3분)

---

### 3. SSL 인증서 생성

**Load Balancer 설정:**

1. Load Balancer 페이지에서 **Inbound traffic** 탭
2. **Create certificate** 클릭
3. 설정:
   - **Primary domain**: `impexgls.com`
   - **Alternate domains**:
     - `www.impexgls.com`
     - (필요시 추가)
4. **Create** 클릭

**도메인 소유권 검증:**

1. CNAME 레코드가 표시됨
2. 도메인 DNS 관리 페이지로 이동 (가비아, AWS Route 53 등)
3. 표시된 CNAME 레코드 추가:

| 타입 | 이름 | 값 |
|------|------|-----|
| CNAME | `_xxxxx.impexgls.com` | `_xxxxx.acm-validations.aws.` |
| CNAME | `_xxxxx.www.impexgls.com` | `_xxxxx.acm-validations.aws.` |

4. DNS 전파 대기 (10분 ~ 1시간)
5. 인증서 상태가 `Valid`로 변경 확인

---

### 4. HTTPS 리스너 활성화

**Load Balancer 설정:**

1. **Inbound traffic** 탭
2. **HTTPS** 프로토콜 활성화:
   - **Protocol**: HTTPS
   - **Port**: 443
   - **Certificate**: 방금 생성한 인증서 선택
   - **Target port**: 8082
3. **Save** 클릭

**HTTP 리스너 설정:**
- **Protocol**: HTTP
- **Port**: 80
- **Target port**: 8082

---

### 5. 도메인 DNS A 레코드 설정

**도메인 DNS 관리 페이지에서:**

Load Balancer의 IP 주소를 확인하고 DNS A 레코드 추가:

```
타입: A
이름: @
값: [Load Balancer IP 또는 DNS]
TTL: 3600

타입: A
이름: www
값: [Load Balancer IP 또는 DNS]
TTL: 3600
```

**또는 CNAME 사용 (루트 도메인 제외):**

```
타입: CNAME
이름: www
값: [Load Balancer DNS 주소]
TTL: 3600
```

---

### 6. 접속 확인

DNS 전파 대기 (10분 ~ 1시간) 후:

```bash
# HTTP 접속 테스트
curl -I http://impexgls.com

# HTTPS 접속 테스트
curl -I https://impexgls.com

# 브라우저에서 접속
# https://impexgls.com
# https://www.impexgls.com
```

---

## 방법 B: Nginx Reverse Proxy + Let's Encrypt

> **장점**: 무료, 완전한 제어
> **단점**: 수동 설정 필요, 90일마다 갱신
> **추천**: 소규모 프로젝트, 비용 절감

### 1. 도메인 DNS 설정 (먼저 해야 함!)

**도메인 DNS 관리 페이지에서:**

```
타입: A
이름: @
값: [Lightsail 인스턴스 공인 IP]
TTL: 3600

타입: A
이름: www
값: [Lightsail 인스턴스 공인 IP]
TTL: 3600
```

**DNS 전파 확인:**
```bash
# 로컬에서 확인
nslookup impexgls.com
dig impexgls.com +short

# 전파될 때까지 대기 (10분 ~ 1시간)
```

---

### 2. Nginx 및 Certbot 설치

**Lightsail SSH 접속 후:**

```bash
# Nginx 설치
sudo yum install -y nginx

# Certbot 설치 (Let's Encrypt 클라이언트)
sudo yum install -y certbot python3-certbot-nginx

# Nginx 시작
sudo systemctl start nginx
sudo systemctl enable nginx

# Nginx 상태 확인
sudo systemctl status nginx
```

---

### 3. Nginx 설정 파일 생성

#### 임시 HTTP 설정 (인증서 발급용)

```bash
# Nginx 설정 파일 생성
sudo tee /etc/nginx/conf.d/impexgls.conf > /dev/null << 'EOF'
server {
    listen 80;
    server_name impexgls.com www.impexgls.com;

    # Let's Encrypt 검증용
    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }

    # 나머지는 Docker로 프록시
    location / {
        proxy_pass http://localhost:8082;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
EOF

# Certbot 디렉토리 생성
sudo mkdir -p /var/www/certbot

# Nginx 설정 테스트
sudo nginx -t

# Nginx 재시작
sudo systemctl restart nginx
```

---

### 4. Lightsail 방화벽 설정

**Lightsail 콘솔에서:**

| Application | Protocol | Port | Source |
|-------------|----------|------|--------|
| HTTP | TCP | 80 | 0.0.0.0/0 |
| HTTPS | TCP | 443 | 0.0.0.0/0 |
| Custom | TCP | 8082 | (삭제 또는 127.0.0.1만 허용) |

**8082 포트는 Nginx를 통해서만 접근하도록 제한 권장**

---

### 5. Let's Encrypt SSL 인증서 발급

```bash
# SSL 인증서 발급
sudo certbot --nginx -d impexgls.com -d www.impexgls.com

# 이메일 입력 (인증서 만료 알림용)
# 약관 동의: Y
# 이메일 수신 동의: N (선택)
```

**Certbot이 자동으로:**
- SSL 인증서 발급
- Nginx 설정 파일 업데이트 (HTTPS 추가)
- HTTP → HTTPS 리다이렉트 설정

---

### 6. Nginx 최종 설정 확인 및 수정

Certbot이 자동 생성한 설정을 확인하고 최적화:

```bash
# 설정 파일 확인
sudo cat /etc/nginx/conf.d/impexgls.conf
```

**수동으로 최적화된 설정 (선택사항):**

```bash
sudo tee /etc/nginx/conf.d/impexgls.conf > /dev/null << 'EOF'
# HTTP → HTTPS 리다이렉트
server {
    listen 80;
    server_name impexgls.com www.impexgls.com;

    # Let's Encrypt 검증용
    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }

    # 나머지는 HTTPS로 리다이렉트
    location / {
        return 301 https://$server_name$request_uri;
    }
}

# HTTPS 서버
server {
    listen 443 ssl http2;
    server_name impexgls.com www.impexgls.com;

    # SSL 인증서
    ssl_certificate /etc/letsencrypt/live/impexgls.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/impexgls.com/privkey.pem;

    # SSL 최적화
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;

    # HSTS (선택사항, 주의!)
    # add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # 보안 헤더
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # 클라이언트 최대 업로드 크기
    client_max_body_size 50M;

    # 정적 파일 캐싱 (선택사항)
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        proxy_pass http://localhost:8082;
        proxy_cache_valid 200 7d;
        expires 7d;
        add_header Cache-Control "public, immutable";
    }

    # 모든 요청을 Docker 컨테이너로 프록시
    location / {
        proxy_pass http://localhost:8082;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # WebSocket 지원 (필요시)
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
}
EOF

# 설정 테스트
sudo nginx -t

# Nginx 재시작
sudo systemctl restart nginx
```

---

### 7. 접속 확인

```bash
# HTTP 접속 (HTTPS로 리다이렉트되어야 함)
curl -I http://impexgls.com

# HTTPS 접속
curl -I https://impexgls.com

# SSL 인증서 확인
openssl s_client -connect impexgls.com:443 -servername impexgls.com < /dev/null

# 브라우저에서 접속
# https://impexgls.com
```

---

### 8. SSL 자동 갱신 설정

Let's Encrypt 인증서는 90일마다 갱신 필요. Certbot이 자동으로 Cron 설정.

```bash
# 자동 갱신 테스트
sudo certbot renew --dry-run

# Cron 설정 확인
sudo cat /etc/cron.d/certbot

# 또는 systemd timer 확인
sudo systemctl list-timers | grep certbot
```

**수동 갱신 (필요시):**
```bash
sudo certbot renew
sudo systemctl reload nginx
```

---

## 도메인 연결

### 1. DNS 레코드 종류

| 레코드 타입 | 사용 예시 | 설명 |
|-------------|----------|------|
| **A** | @ → IP | 루트 도메인을 IP로 연결 |
| **A** | www → IP | www 서브도메인을 IP로 연결 |
| **CNAME** | www → 루트 | www를 루트 도메인으로 리다이렉트 |
| **CNAME** | blog → 도메인 | 서브도메인 설정 |

### 2. 주요 DNS 제공업체별 설정

#### 가비아 (Gabia)

1. My가비아 → 도메인 → DNS 관리
2. 레코드 추가:
   ```
   타입: A
   호스트: @
   값/위치: YOUR_LIGHTSAIL_IP
   TTL: 3600
   ```

#### AWS Route 53

1. Route 53 → Hosted zones → 도메인 선택
2. Create record:
   ```
   Record name: (비워둠)
   Record type: A
   Value: YOUR_LIGHTSAIL_IP
   TTL: 300
   ```

#### Cloudflare

1. DNS → Add record:
   ```
   Type: A
   Name: @
   Content: YOUR_LIGHTSAIL_IP
   Proxy status: Proxied (선택사항)
   TTL: Auto
   ```

### 3. DNS 전파 확인

```bash
# nslookup
nslookup impexgls.com

# dig
dig impexgls.com +short

# 온라인 도구
# https://www.whatsmydns.net/
```

---

## HTTP to HTTPS 리다이렉트

### 방법 A: Lightsail Load Balancer 사용 시

**Load Balancer 설정에서 자동 처리됨**

### 방법 B: Nginx 사용 시

위의 Nginx 설정 참조 (자동 포함됨)

### 방법 C: PHP 코드에서 리다이렉트

```php
// website_backup/index.php 최상단 또는 config.php에 추가

<?php
// HTTPS 강제 리다이렉트
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    if (php_sapi_name() !== 'cli') {
        $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $redirect);
        exit();
    }
}
?>
```

---

## SSL 인증서 갱신

### Lightsail Load Balancer

**자동 갱신됨 - 작업 불필요**

### Let's Encrypt (Certbot)

#### 자동 갱신 확인

```bash
# 갱신 테스트
sudo certbot renew --dry-run

# Cron 또는 Timer 확인
sudo systemctl list-timers | grep certbot

# 수동 갱신
sudo certbot renew
sudo systemctl reload nginx
```

#### Cron 설정 (백업용)

```bash
# Crontab 편집
sudo crontab -e

# 매일 새벽 3시에 갱신 시도
0 3 * * * certbot renew --quiet --post-hook "systemctl reload nginx"
```

---

## 트러블슈팅

### 1. DNS가 전파되지 않아요

**확인:**
```bash
# DNS 조회
nslookup impexgls.com
dig impexgls.com +short

# DNS 전파 확인 (온라인)
# https://www.whatsmydns.net/
```

**해결:**
- DNS 레코드 설정 재확인
- TTL 시간 대기 (최대 48시간, 보통 1시간 이내)
- DNS 캐시 클리어: `sudo systemd-resolve --flush-caches` (로컬)

---

### 2. SSL 인증서 발급 실패

**증상:**
```
Certbot failed to authenticate some domains
```

**해결:**
```bash
# 도메인 DNS가 제대로 연결되었는지 확인
nslookup impexgls.com

# Nginx 80 포트 열려있는지 확인
sudo netstat -tlnp | grep :80

# Lightsail 방화벽 80 포트 확인

# 재시도
sudo certbot --nginx -d impexgls.com -d www.impexgls.com
```

---

### 3. HTTPS 접속 시 "ERR_SSL_PROTOCOL_ERROR"

**확인:**
```bash
# Nginx 상태 확인
sudo systemctl status nginx

# Nginx 에러 로그
sudo tail -50 /var/log/nginx/error.log

# SSL 인증서 경로 확인
sudo ls -l /etc/letsencrypt/live/impexgls.com/
```

**해결:**
```bash
# Nginx 설정 테스트
sudo nginx -t

# Nginx 재시작
sudo systemctl restart nginx

# 방화벽 443 포트 확인
sudo netstat -tlnp | grep :443
```

---

### 4. "Mixed Content" 경고

**증상:**
브라우저 콘솔에 "Mixed Content" 경고

**원인:**
HTTPS 페이지에서 HTTP 리소스 로딩

**해결:**

```php
// website_backup/config/config.php에 추가

// HTTPS 자동 감지
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://';
define('SITE_URL', $protocol . $_SERVER['HTTP_HOST']);

// 또는 하드코딩
define('SITE_URL', 'https://impexgls.com');
```

**HTML에서:**
```html
<!-- 절대 경로 사용 -->
<img src="https://impexgls.com/assets/images/logo.png">

<!-- 또는 프로토콜 상대 경로 -->
<img src="//impexgls.com/assets/images/logo.png">

<!-- 상대 경로 (권장) -->
<img src="/assets/images/logo.png">
```

---

### 5. www 붙은 주소와 없는 주소 통일

#### www → non-www 리다이렉트 (Nginx)

```nginx
server {
    listen 443 ssl http2;
    server_name www.impexgls.com;

    ssl_certificate /etc/letsencrypt/live/impexgls.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/impexgls.com/privkey.pem;

    return 301 https://impexgls.com$request_uri;
}

server {
    listen 443 ssl http2;
    server_name impexgls.com;

    # ... 나머지 설정
}
```

#### non-www → www 리다이렉트 (Nginx)

```nginx
server {
    listen 443 ssl http2;
    server_name impexgls.com;

    ssl_certificate /etc/letsencrypt/live/impexgls.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/impexgls.com/privkey.pem;

    return 301 https://www.impexgls.com$request_uri;
}

server {
    listen 443 ssl http2;
    server_name www.impexgls.com;

    # ... 나머지 설정
}
```

---

### 6. 인증서 갱신 실패

**증상:**
```
Attempting to renew cert (impexgls.com) from /etc/letsencrypt/renewal/impexgls.com.conf produced an unexpected error
```

**해결:**
```bash
# Nginx 80 포트 확인
sudo netstat -tlnp | grep :80

# Certbot 로그 확인
sudo tail -50 /var/log/letsencrypt/letsencrypt.log

# 수동 갱신 시도
sudo certbot renew --force-renewal

# Nginx 재시작
sudo systemctl restart nginx
```

---

## 보안 강화 체크리스트

- [ ] **HTTPS 강제 리다이렉트** 설정 완료
- [ ] **HSTS 헤더** 추가 (선택사항)
- [ ] **보안 헤더** 설정 (X-Frame-Options, X-Content-Type-Options 등)
- [ ] **SSL Labs 테스트** (https://www.ssllabs.com/ssltest/) A+ 등급
- [ ] **Mixed Content** 경고 해결
- [ ] **CSP (Content Security Policy)** 설정 (선택사항)
- [ ] **인증서 자동 갱신** 테스트 완료

---

## 성능 최적화

### 1. Nginx Gzip 압축

```nginx
# /etc/nginx/nginx.conf에 추가

http {
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript
               application/json application/javascript application/xml+rss
               application/rss+xml font/truetype font/opentype
               application/vnd.ms-fontobject image/svg+xml;
}
```

### 2. 정적 파일 캐싱

위의 Nginx 설정 참조 (이미 포함됨)

### 3. Cloudflare CDN 연동 (선택사항)

1. Cloudflare 계정 생성
2. 도메인 추가
3. Cloudflare의 네임서버로 변경
4. SSL/TLS 설정: **Full (strict)**
5. Speed → Optimization 설정

---

## 빠른 참조 (Cheat Sheet)

### Lightsail Load Balancer 방식

```bash
# 1. Load Balancer 생성 (Lightsail 콘솔)
# 2. SSL 인증서 생성 (Lightsail 콘솔)
# 3. DNS CNAME 레코드 추가 (도메인 관리)
# 4. DNS A 레코드 추가
# 5. 접속 확인
curl -I https://impexgls.com
```

### Let's Encrypt 방식

```bash
# 1. DNS A 레코드 추가 (도메인 관리)
# 2. Nginx 설치
sudo yum install -y nginx certbot python3-certbot-nginx

# 3. 임시 Nginx 설정
sudo tee /etc/nginx/conf.d/impexgls.conf > /dev/null << 'EOF'
server {
    listen 80;
    server_name impexgls.com www.impexgls.com;
    location / {
        proxy_pass http://localhost:8082;
    }
}
EOF

# 4. Nginx 시작
sudo systemctl start nginx
sudo systemctl enable nginx

# 5. SSL 인증서 발급
sudo certbot --nginx -d impexgls.com -d www.impexgls.com

# 6. 접속 확인
curl -I https://impexgls.com
```

---

## 문의 및 지원

**문서 버전**: 1.0
**최종 업데이트**: 2025-11-20
**작성자**: Claude Code Assistant

### 관련 문서
- [AWS_Lightsail_Docker 배포 방식.md](./AWS_Lightsail_Docker%20배포%20방식.md)
- [QUICKSTART.md](./QUICKSTART.md)
- [README_LOCAL_DOCKER.md](./README_LOCAL_DOCKER.md)

### 유용한 링크
- [Let's Encrypt 공식 문서](https://letsencrypt.org/docs/)
- [SSL Labs 테스트](https://www.ssllabs.com/ssltest/)
- [DNS 전파 확인](https://www.whatsmydns.net/)
- [AWS Lightsail 문서](https://lightsail.aws.amazon.com/ls/docs/)
