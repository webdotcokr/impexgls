# IMPEXGLS 로컬 Docker 환경 가이드

## 프로젝트 구조

```
impexgls/
├── Dockerfile                          # 웹 서버 이미지 정의
├── docker-compose.yml                  # 컨테이너 오케스트레이션
├── .dockerignore                       # Docker 빌드 제외 파일
├── website_backup/                     # 웹사이트 소스 파일
│   ├── admin/                         # 관리자 페이지
│   ├── assets/                        # 정적 파일 (CSS, JS, 이미지)
│   ├── config/                        # 설정 파일
│   │   └── db-config.php             # DB 설정 (Docker 환경 지원)
│   ├── includes/                      # 공통 함수
│   └── pages/                         # 페이지 파일
├── docker/
│   └── mysql/
│       └── init/
│           └── corporate_db.sql       # DB 초기화 SQL
└── README_LOCAL_DOCKER.md             # 이 파일
```

---

## 로컬 Docker 환경 구성

### 서비스 구성

| 서비스 | 컨테이너명 | 포트 | 설명 |
|--------|-----------|------|------|
| **web** | impexgls_web | 8082 | PHP 7.4 + Apache 웹 서버 |
| **db** | impexgls_db | 3308 | MySQL 5.7 데이터베이스 |
| **phpmyadmin** | impexgls_phpmyadmin | 8083 | phpMyAdmin 관리 도구 |

### 데이터베이스 정보

- **데이터베이스명**: corporate_db
- **Root 비밀번호**: impexgls_root_password
- **사용자명**: impexgls_user
- **사용자 비밀번호**: impexgls_password

---

## 시작하기

### 1. Docker 컨테이너 실행

```bash
# 프로젝트 디렉토리로 이동
cd /Users/kimjunha/Desktop/impexgls

# Docker 컨테이너 빌드 및 실행
docker-compose up -d --build

# 컨테이너 상태 확인
docker-compose ps
```

### 2. 서비스 접속

#### 웹사이트
```
http://localhost:8082
```

#### phpMyAdmin (DB 관리)
```
http://localhost:8083

로그인 정보:
- 서버: db
- 사용자: root
- 비밀번호: impexgls_root_password
```

---

## 일상적인 개발 워크플로우

### 코드 수정 후 반영

```bash
# website_backup/ 폴더의 파일 수정
# 예: website_backup/index.php 수정

# 웹 컨테이너 재시작 (즉시 반영)
docker-compose restart web

# 브라우저에서 확인
# http://localhost:8082
```

**Note**: `website_backup/` 폴더는 컨테이너와 볼륨으로 연결되어 있어, 파일 수정 시 즉시 반영됩니다.

---

## Docker 컨테이너 관리

### 컨테이너 시작/중지

```bash
# 모든 컨테이너 시작
docker-compose up -d

# 모든 컨테이너 중지
docker-compose down

# 컨테이너 재시작
docker-compose restart

# 특정 컨테이너만 재시작
docker-compose restart web
docker-compose restart db
```

### 로그 확인

```bash
# 웹 서버 로그 (실시간)
docker-compose logs -f web

# DB 로그
docker-compose logs -f db

# 모든 컨테이너 로그
docker-compose logs -f

# 최근 100줄만 확인
docker-compose logs --tail=100 web
```

### 컨테이너 내부 접속

```bash
# 웹 서버 컨테이너 접속
docker exec -it impexgls_web bash

# MySQL 컨테이너 접속
docker exec -it impexgls_db bash

# MySQL CLI 직접 접속
docker exec -it impexgls_db mysql -uroot -pimpexgls_root_password corporate_db
```

---

## 데이터베이스 관리

### DB 덤프 생성

```bash
# DB 전체 백업
docker exec impexgls_db mysqldump -uroot -pimpexgls_root_password corporate_db \
  > backup_$(date +%Y%m%d_%H%M%S).sql

# 압축해서 백업
docker exec impexgls_db mysqldump -uroot -pimpexgls_root_password corporate_db \
  | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz
```

### DB 복원

```bash
# SQL 파일로 복원
docker exec -i impexgls_db mysql -uroot -pimpexgls_root_password corporate_db \
  < backup.sql

# 압축 파일 복원
gunzip < backup.sql.gz | docker exec -i impexgls_db mysql -uroot -pimpexgls_root_password corporate_db
```

### DB 초기화 (완전 재생성)

```bash
# ⚠️ 주의: 기존 데이터 모두 삭제됨!
docker-compose down -v

# 컨테이너 재생성 (init 폴더의 SQL 자동 실행)
docker-compose up -d --build

# 로그 확인 (초기화 진행 상황)
docker-compose logs -f db
```

---

## 트러블슈팅

### 1. 웹사이트가 안 열려요

```bash
# 컨테이너 상태 확인
docker-compose ps

# 웹 서버 로그 확인
docker-compose logs web

# 웹 서버 재시작
docker-compose restart web
```

### 2. DB 연결 오류

```bash
# DB 컨테이너 상태 확인
docker-compose ps db

# DB 로그 확인
docker-compose logs db

# DB가 준비될 때까지 대기 (20초 정도 소요)
docker-compose logs -f db | grep "ready for connections"

# DB 재시작
docker-compose restart db
```

### 3. 포트 충돌 (이미 사용 중)

```bash
# 포트 사용 중인 프로세스 확인
lsof -i :8082
lsof -i :3308
lsof -i :8083

# docker-compose.yml에서 포트 변경
# 예: 8082 → 8090
```

**docker-compose.yml 수정 예시:**
```yaml
web:
  ports:
    - "8090:80"  # 8082 → 8090으로 변경
```

### 4. 파일 권한 문제

```bash
# 컨테이너 내부에서 권한 재설정
docker exec -it impexgls_web bash
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 777 /var/www/html/admin/uploads
exit

# 웹 서버 재시작
docker-compose restart web
```

### 5. 디스크 용량 부족

```bash
# Docker 이미지/컨테이너 정리
docker system prune -a

# 사용하지 않는 볼륨 삭제 (⚠️ 주의: DB 데이터 삭제될 수 있음)
docker volume prune
```

---

## 환경 변수 설정

### DB 연결 정보 수정

**docker-compose.yml** 파일에서 환경 변수를 수정할 수 있습니다:

```yaml
web:
  environment:
    - DB_HOST=db
    - DB_NAME=corporate_db
    - DB_USER=root
    - DB_PASS=impexgls_root_password

db:
  environment:
    MYSQL_ROOT_PASSWORD: impexgls_root_password
    MYSQL_DATABASE: corporate_db
    MYSQL_USER: impexgls_user
    MYSQL_PASSWORD: impexgls_password
```

**website_backup/config/db-config.php**는 자동으로 환경 변수를 읽습니다:

```php
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'corporate_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'OvIZQ5TyCQN/');
```

---

## 성능 최적화

### PHP 설정 커스터마이징

**Dockerfile**의 PHP 설정을 수정할 수 있습니다:

```dockerfile
RUN { \
    echo 'upload_max_filesize = 50M'; \
    echo 'post_max_size = 50M'; \
    echo 'max_execution_time = 300'; \
    echo 'memory_limit = 256M'; \
    echo 'display_errors = On'; \
    echo 'error_reporting = E_ALL'; \
} > /usr/local/etc/php/conf.d/custom.ini
```

수정 후:
```bash
docker-compose up -d --build
```

---

## AWS Lightsail 배포 준비

로컬 Docker 환경이 정상 작동하면, 다음 단계로 진행하세요:

1. **AWS Lightsail 인스턴스 생성** (Amazon Linux 2)
2. **Docker & Git 설치**
3. **프로젝트 Git 저장소 생성 및 Push**
4. **Lightsail에서 Git Clone 및 Docker 실행**

자세한 내용은 [AWS_Lightsail_Docker 배포 방식.md](./AWS_Lightsail_Docker%20배포%20방식.md)를 참조하세요.

---

## 빠른 참조 (Cheat Sheet)

```bash
# 컨테이너 시작
docker-compose up -d

# 컨테이너 중지
docker-compose down

# 로그 확인
docker-compose logs -f web

# 웹 서버 재시작
docker-compose restart web

# DB 백업
docker exec impexgls_db mysqldump -uroot -pimpexgls_root_password corporate_db \
  | gzip > backup_$(date +%Y%m%d).sql.gz

# 컨테이너 상태 확인
docker-compose ps

# 완전 재시작 (DB 데이터 유지)
docker-compose restart

# 완전 재시작 (DB 데이터 삭제 후 초기화)
docker-compose down -v && docker-compose up -d --build
```

---

## 문의 및 지원

**문서 버전**: 1.0
**최종 업데이트**: 2025-11-20
**작성자**: Claude Code Assistant

---

## 다음 단계

✅ **완료**: 로컬 Docker 환경 구축
⬜ **다음**: Git 저장소 생성 및 코드 Push
⬜ **다음**: AWS Lightsail 인스턴스 생성
⬜ **다음**: Lightsail에 Docker 배포
