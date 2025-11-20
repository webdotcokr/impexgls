# AWS Lightsail Docker 배포 가이드

> IMPEXGLS 프로젝트를 AWS Lightsail에 Docker로 배포하고 유지보수하는 완벽 가이드

## 목차
1. [사전 준비 (로컬 환경)](#사전-준비-로컬-환경)
2. [초기 배포](#초기-배포)
3. [일상적인 배포 워크플로우](#일상적인-배포-워크플로우)
4. [데이터베이스 관리](#데이터베이스-관리)
5. [유지보수 및 모니터링](#유지보수-및-모니터링)
6. [트러블슈팅](#트러블슈팅)
7. [SSL 및 도메인 연결](#ssl-및-도메인-연결)

---

## 사전 준비 (로컬 환경)

### 1. 로컬 Docker 환경 구축 완료 확인

배포 전에 **반드시** 로컬에서 Docker 환경이 정상 작동하는지 확인하세요.

```bash
# 로컬에서 확인
cd /Users/kimjunha/Desktop/impexgls

# Docker 컨테이너 실행 확인
docker-compose ps

# 웹사이트 접속 테스트
curl -I http://localhost:8082
```

**참고 문서:**
- [QUICKSTART.md](./QUICKSTART.md) - 로컬 Docker 빠른 시작
- [README_LOCAL_DOCKER.md](./README_LOCAL_DOCKER.md) - 로컬 Docker 상세 가이드

---

### 2. GitHub 저장소 준비

#### Git 저장소 초기화 및 Push

```bash
cd /Users/kimjunha/Desktop/impexgls

# Git 저장소 초기화 (아직 안 했으면)
git init

# .gitignore 파일 생성
cat > .gitignore << 'EOF'
.DS_Store
*.log
.env
website_backup/admin/uploads/*
website_backup/assets/uploads/*
EOF

# 모든 파일 추가
git add .

# 첫 커밋
git commit -m "Initial commit: IMPEXGLS Docker environment"

# GitHub 저장소 생성 후 연결
git remote add origin https://github.com/webdotcokr/impexgls.git

# Push
git branch -M main
git push -u origin main
```

#### Private 저장소용 Personal Access Token 생성 (선택사항)

GitHub에서 토큰 생성:
1. GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. **Generate new token** 클릭
3. **repo** 권한 선택
4. 토큰 복사 (한 번만 표시됨!)

---

## 초기 배포

### 1. Lightsail 인스턴스 생성

**AWS Lightsail 콘솔에서:**
- **OS**: Amazon Linux 2023 또는 Amazon Linux 2
- **플랜**: 최소 2GB RAM (4GB 권장)
- **스토리지**: 40GB 이상
- **리전**: Seoul (ap-northeast-2)

**SSH 키 다운로드:**
- Lightsail 콘솔에서 SSH 키 다운로드
- 로컬에 저장: `~/.ssh/lightsail-impexgls.pem`

---

### 2. SSH 접속 설정 (로컬 맥북)

```bash
# SSH 키 권한 설정
chmod 400 ~/.ssh/lightsail-impexgls.pem

# SSH 접속 테스트 (YOUR_LIGHTSAIL_IP를 실제 IP로 변경)
ssh -i ~/.ssh/lightsail-impexgls.pem ec2-user@YOUR_LIGHTSAIL_IP

# SSH 접속 간소화 (선택사항)
cat >> ~/.ssh/config << 'EOF'
Host impexgls
    HostName YOUR_LIGHTSAIL_IP
    User ec2-user
    IdentityFile ~/.ssh/lightsail-impexgls.pem
EOF

# 이후 간단히 접속
ssh impexgls
```

---

### 3. Docker 및 Git 설치 (Lightsail 서버)

SSH 접속 후 다음 명령어들을 **순서대로** 실행:

#### 시스템 업데이트 및 패키지 설치

```bash
# 시스템 업데이트
sudo yum update -y

# Git과 Docker 설치
sudo yum install -y git docker

# 설치 확인
git --version
docker --version
```

#### Docker 서비스 시작 및 설정

```bash
# Docker 서비스 시작
sudo systemctl start docker

# Docker 상태 확인
sudo systemctl status docker
# Active: active (running) 확인

# Docker 자동 시작 설정 (재부팅 시에도 자동 실행)
sudo systemctl enable docker

# ec2-user를 docker 그룹에 추가 (sudo 없이 docker 명령 실행)
sudo usermod -aG docker ec2-user

# 현재 그룹 확인
groups
```

#### Docker Compose 설치

```bash
# Docker Compose v2.20.0 다운로드
sudo curl -L "https://github.com/docker/compose/releases/download/v2.20.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose

# 실행 권한 부여
sudo chmod +x /usr/local/bin/docker-compose

# 버전 확인
docker-compose --version
# docker-compose version 2.20.0
```

#### SSH 재접속 (필수!)

Docker 그룹 권한이 적용되려면 **반드시 재접속**해야 합니다.

```bash
# 현재 SSH 세션 종료
exit

# 다시 접속
ssh -i ~/.ssh/lightsail-impexgls.pem ec2-user@YOUR_LIGHTSAIL_IP

# Docker 그룹 적용 확인
groups
# 출력: ec2-user adm wheel systemd-journal docker

# sudo 없이 docker 명령 테스트
docker ps
# CONTAINER ID   IMAGE     COMMAND   CREATED   STATUS    PORTS     NAMES
```

---

### 4. 프로젝트 클론 및 배포

#### Public 저장소 클론

```bash
# Git 저장소 클론
git clone https://github.com/webdotcokr/impexgls.git

# 디렉토리 이동
cd impexgls

# 파일 확인
ls -la
```

#### Private 저장소 클론 (Personal Access Token 사용)

```bash
# 토큰을 사용하여 클론 (YOUR_GITHUB_TOKEN을 실제 토큰으로 변경)
git clone https://YOUR_GITHUB_TOKEN@github.com/webdotcokr/impexgls.git

# 디렉토리 이동
cd impexgls

# 파일 확인
ls -la
```

#### Docker 컨테이너 실행

```bash
# impexgls 디렉토리에서 실행
cd ~/impexgls

# docker-compose.yml 파일 확인
cat docker-compose.yml

# Docker 이미지 빌드 및 컨테이너 시작
docker-compose up -d --build

# 빌드 진행 상황 확인 (3-5분 소요)
# PHP 확장 설치, Apache 설정, MySQL 초기화 등이 진행됩니다.
```

#### 컨테이너 상태 확인

```bash
# 컨테이너 상태 확인
docker-compose ps

# 출력 예시:
# NAME                  IMAGE                          COMMAND                  STATUS          PORTS
# impexgls_web          impexgls-web                   "docker-php-entrypoi…"   Up 2 minutes    0.0.0.0:8082->80/tcp
# impexgls_db           mysql:5.7                      "docker-entrypoint.s…"   Up 2 minutes    0.0.0.0:3308->3306/tcp
# impexgls_phpmyadmin   phpmyadmin/phpmyadmin:latest   "/docker-entrypoint.…"   Up 2 minutes    0.0.0.0:8083->80/tcp
```

#### DB 초기화 확인

```bash
# DB 로그 확인 (DB 초기화 완료 대기)
docker-compose logs -f db

# 다음 메시지가 나오면 초기화 완료:
# impexgls_db  | mysqld: ready for connections.

# Ctrl + C로 로그 보기 종료

# DB 테이블 생성 확인
docker exec impexgls_db mysql -uroot -pimpexgls_root_password -e "USE corporate_db; SHOW TABLES;"

# 14개 테이블이 표시되어야 함
```

---

### 5. 방화벽 설정

**AWS Lightsail 콘솔에서:**

1. 인스턴스 선택
2. **Networking** 탭 클릭
3. **Firewall** 섹션에서 규칙 추가:

| Application | Protocol | Port | Restrict to IP | 용도 |
|-------------|----------|------|----------------|------|
| SSH | TCP | 22 | ✓ (본인 IP만) | SSH 접속 |
| Custom | TCP | 8082 | ✗ (모든 IP) | 웹사이트 |
| Custom | TCP | 8083 | ✓ (본인 IP만) | phpMyAdmin |

**규칙 추가 방법:**
- **Add rule** 클릭
- **Application**: Custom 선택
- **Protocol**: TCP
- **Port**: 8082
- **Restrict to IP address**: 체크 해제 (모든 접속 허용)

**보안 권장 사항:**
- SSH(22): 본인 IP만 허용
- phpMyAdmin(8083): 본인 IP만 허용 또는 컨테이너 중지

---

### 6. 접속 확인

#### 웹사이트 접속

브라우저에서:
```
http://YOUR_LIGHTSAIL_IP:8082
```

#### phpMyAdmin 접속 (선택사항)

브라우저에서:
```
http://YOUR_LIGHTSAIL_IP:8083

로그인 정보:
- 서버: db
- 사용자: root
- 비밀번호: impexgls_root_password
```

#### 명령줄에서 확인

```bash
# 웹사이트 응답 확인
curl -I http://YOUR_LIGHTSAIL_IP:8082

# 출력:
# HTTP/1.1 200 OK
# Content-Type: text/html; charset=UTF-8
```

---

## 일상적인 배포 워크플로우

### 시나리오 1: 코드만 수정 (가장 일반적)

#### 로컬 (MacBook)

```bash
# 프로젝트 디렉토리로 이동
cd /Users/kimjunha/Desktop/impexgls

# 파일 수정 (예: website_backup/index.php, website_backup/pages/about.php 등)
vi website_backup/index.php

# 로컬에서 테스트
docker-compose restart web
# 브라우저에서 http://localhost:8082 확인

# Git 커밋 및 Push
git add .
git commit -m "Update homepage layout"
git push origin main
```

#### Lightsail (서버)

```bash
# SSH 접속
ssh -i ~/.ssh/lightsail-impexgls.pem ec2-user@YOUR_LIGHTSAIL_IP

# 프로젝트 디렉토리로 이동
cd ~/impexgls

# 최신 코드 가져오기
git pull origin main

# 웹 컨테이너만 재시작 (PHP/HTML 변경 시)
docker-compose restart web

# 브라우저에서 확인
# http://YOUR_IP:8082
```

**배포 시간: 약 30초 ~ 1분**

---

### 시나리오 2: Dockerfile 또는 docker-compose.yml 변경

#### 로컬 (MacBook)

```bash
# Dockerfile 또는 docker-compose.yml 수정
vi Dockerfile

# 로컬에서 테스트
docker-compose up -d --build

# Git 커밋 및 Push
git add .
git commit -m "Update Docker configuration"
git push origin main
```

#### Lightsail (서버)

```bash
cd ~/impexgls

# 최신 코드 가져오기
git pull origin main

# 이미지 재빌드 (--build 필수)
docker-compose up -d --build

# 컨테이너 상태 확인
docker-compose ps

# 로그 확인
docker-compose logs -f web
```

**배포 시간: 약 3-5분**

---

### 시나리오 3: 긴급 핫픽스 (서버 직접 수정)

```bash
# Lightsail SSH 접속
ssh -i ~/.ssh/lightsail-impexgls.pem ec2-user@YOUR_LIGHTSAIL_IP

# 프로젝트 디렉토리로 이동
cd ~/impexgls

# 파일 직접 수정
vi website_backup/index.php

# 즉시 반영 (Git 커밋 없이)
docker-compose restart web

# 브라우저에서 확인

# ⚠️ 나중에 로컬에 반영 필수!
# 로컬에서: git pull 후 동일하게 수정하고 커밋
```

---

## 데이터베이스 관리

### DB 백업 (서버 → 로컬)

#### Lightsail (서버)

```bash
# SSH 접속
ssh -i ~/.ssh/lightsail-impexgls.pem ec2-user@YOUR_LIGHTSAIL_IP

# 백업 디렉토리 생성
mkdir -p ~/backups

# DB 덤프 및 압축
docker exec impexgls_db mysqldump -uroot -pimpexgls_root_password corporate_db \
  | gzip > ~/backups/corporate_db_backup_$(date +%Y%m%d_%H%M%S).sql.gz

# 백업 파일 확인
ls -lh ~/backups/
```

#### 로컬 (MacBook)

```bash
# 서버에서 로컬로 다운로드
scp -i ~/.ssh/lightsail-impexgls.pem \
  ec2-user@YOUR_IP:~/backups/corporate_db_backup_*.sql.gz \
  ~/Downloads/

# 다운로드 확인
ls -lh ~/Downloads/corporate_db_backup_*.sql.gz
```

---

### DB 복원 (로컬 → 서버)

#### 로컬 (MacBook)

```bash
cd /Users/kimjunha/Desktop/impexgls

# 로컬 DB 덤프 생성 (필요시)
docker exec impexgls_db mysqldump -uroot -pimpexgls_root_password corporate_db \
  > docker/mysql/init/corporate_db.sql

# Git에 커밋
git add docker/mysql/init/corporate_db.sql
git commit -m "Update database schema"
git push origin main
```

#### Lightsail (서버)

```bash
cd ~/impexgls

# 최신 코드 가져오기
git pull origin main

# ⚠️ 주의: 기존 DB 데이터가 모두 삭제됩니다!
docker-compose down -v

# DB 컨테이너 재생성 (init 폴더의 SQL 자동 실행)
docker-compose up -d --build

# 로그 확인 (DB 초기화 진행 상황)
docker-compose logs -f db

# "mysqld: ready for connections" 메시지 확인
```

---

### DB 직접 복원 (SQL 파일 업로드)

```bash
# 로컬에서 서버로 SQL 파일 업로드
scp -i ~/.ssh/lightsail-impexgls.pem \
  backup.sql.gz \
  ec2-user@YOUR_IP:~/

# 서버에서 복원
ssh -i ~/.ssh/lightsail-impexgls.pem ec2-user@YOUR_IP

# SQL 파일 압축 해제 및 복원
gunzip < ~/backup.sql.gz | docker exec -i impexgls_db mysql -uroot -pimpexgls_root_password corporate_db

# 복원 확인
docker exec impexgls_db mysql -uroot -pimpexgls_root_password -e "USE corporate_db; SHOW TABLES;"
```

---

### phpMyAdmin 사용

**접속:**
```
http://YOUR_LIGHTSAIL_IP:8083
```

**로그인 정보:**
- 서버: `db`
- 사용자: `root`
- 비밀번호: `impexgls_root_password`

**보안 강화:**
```bash
# Lightsail 방화벽에서 8083 포트를 본인 IP만 허용
# Networking 탭 → Custom TCP 8083 → Restrict to IP: YOUR_IP/32
```

---

## 유지보수 및 모니터링

### 컨테이너 상태 확인

```bash
cd ~/impexgls

# 실행 중인 컨테이너 확인
docker-compose ps

# 컨테이너 리소스 사용량 확인
docker stats

# 특정 컨테이너 상세 정보
docker inspect impexgls_web
```

---

### 로그 확인

```bash
# 웹 서버 로그 실시간 확인
docker-compose logs -f web

# DB 로그 확인
docker-compose logs -f db

# 모든 컨테이너 로그
docker-compose logs -f

# 최근 100줄만 확인
docker-compose logs --tail=100 web

# 특정 시간대 로그 확인
docker-compose logs --since 30m web
```

---

### 디스크 사용량 확인

```bash
# 전체 디스크 사용량
df -h

# 프로젝트 디렉토리 크기
du -sh ~/impexgls

# 업로드 파일 크기
du -sh ~/impexgls/website_backup/admin/uploads
du -sh ~/impexgls/website_backup/assets/uploads

# Docker 볼륨 사용량
docker system df

# 상세 정보
docker system df -v
```

---

### 정기 백업 (Cron 설정)

#### 매일 새벽 3시 DB 자동 백업

```bash
# 백업 디렉토리 생성
mkdir -p ~/backups

# Crontab 편집
crontab -e

# 다음 라인 추가 (i 키로 입력 모드, ESC → :wq 로 저장)
0 3 * * * docker exec impexgls_db mysqldump -uroot -pimpexgls_root_password corporate_db | gzip > ~/backups/corporate_db_$(date +\%Y\%m\%d).sql.gz

# Cron 설정 확인
crontab -l
```

#### 오래된 백업 자동 삭제 (30일 이상)

```bash
# Crontab 편집
crontab -e

# 매일 새벽 4시에 30일 이상 된 백업 삭제
0 4 * * * find ~/backups -name "corporate_db_*.sql.gz" -mtime +30 -delete
```

---

### 컨테이너 재시작

```bash
cd ~/impexgls

# 웹 서버만 재시작 (코드 변경 시)
docker-compose restart web

# DB만 재시작
docker-compose restart db

# phpMyAdmin만 재시작
docker-compose restart phpmyadmin

# 모든 컨테이너 재시작
docker-compose restart

# 컨테이너 중지
docker-compose down

# 컨테이너 시작
docker-compose up -d

# 완전 재시작 (DB 데이터 유지)
docker-compose down && docker-compose up -d
```

---

### 파일 권한 재설정

```bash
# 컨테이너 내부에서 실행
docker exec -it impexgls_web bash

# 컨테이너 내부에서:
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 777 /var/www/html/admin/uploads
chmod -R 777 /var/www/html/assets/uploads
exit

# 또는 외부에서 한 줄로 실행
docker exec impexgls_web chown -R www-data:www-data /var/www/html
docker exec impexgls_web chmod -R 755 /var/www/html
docker exec impexgls_web chmod -R 777 /var/www/html/admin/uploads /var/www/html/assets/uploads
```

---

### Docker 이미지/캐시 정리

```bash
# 사용하지 않는 이미지 삭제
docker image prune -a

# 전체 시스템 정리 (주의!)
docker system prune -a

# 볼륨 포함 완전 정리 (⚠️ DB 데이터 삭제됨!)
docker system prune -a --volumes

# 디스크 사용량 확인 후 정리
docker system df
docker system prune -a --volumes
```

---

## 트러블슈팅

### 1. Git clone 시 "command not found"

**증상:**
```bash
git clone https://github.com/webdotcokr/impexgls.git
-bash: git: command not found
```

**해결:**
```bash
# Git 설치
sudo yum install -y git

# 설치 확인
git --version
```

---

### 2. Docker 명령 시 "command not found"

**증상:**
```bash
docker --version
sudo: docker: command not found
```

**해결:**
```bash
# Docker 설치
sudo yum install -y docker

# Docker 서비스 시작
sudo systemctl start docker
sudo systemctl enable docker

# Docker 상태 확인
sudo systemctl status docker
```

---

### 3. Docker daemon 연결 오류

**증상:**
```bash
docker ps
Cannot connect to the Docker daemon at unix:///var/run/docker.sock. Is the docker daemon running?
```

**해결:**
```bash
# Docker 서비스 시작
sudo systemctl start docker

# Docker 상태 확인
sudo systemctl status docker

# ec2-user를 docker 그룹에 추가
sudo usermod -aG docker ec2-user

# SSH 재접속 필수!
exit
ssh -i ~/.ssh/lightsail-impexgls.pem ec2-user@YOUR_IP

# 테스트
docker ps
```

---

### 4. docker-compose "no configuration file provided"

**증상:**
```bash
docker-compose up -d
no configuration file provided: not found
```

**해결:**
```bash
# 현재 디렉토리 확인
pwd

# impexgls 디렉토리로 이동
cd ~/impexgls

# docker-compose.yml 파일 존재 확인
ls -l docker-compose.yml

# 파일이 없으면 Git에서 다시 받기
git pull origin main

# 또는 저장소 재클론
cd ~
rm -rf impexgls
git clone https://github.com/webdotcokr/impexgls.git
cd impexgls
```

---

### 5. DB 초기화 실패 (collation 오류)

**증상:**
```bash
docker-compose logs db
ERROR 1273 (HY000) at line 30: Unknown collation: 'utf8mb4_uca1400_ai_ci'
```

**원인:**
MariaDB용 collation이 MySQL 5.7에서 지원되지 않음

**해결:**
```bash
# SQL 파일 수정
cd ~/impexgls
sed -i 's/utf8mb4_uca1400_ai_ci/utf8mb4_unicode_ci/g' docker/mysql/init/corporate_db.sql

# DB 재생성
docker-compose down -v
docker-compose up -d --build

# 로그 확인
docker-compose logs -f db
```

---

### 6. 웹사이트가 안 열려요 (Connection refused)

**체크리스트:**

```bash
# 1. 컨테이너 상태 확인
docker-compose ps
# → STATUS가 "Up"인지 확인

# 2. 웹 컨테이너 로그 확인
docker-compose logs web

# 3. 방화벽 포트 확인
# Lightsail 콘솔 → Networking → 8082 포트 열려있는지 확인

# 4. 웹 서버 재시작
docker-compose restart web

# 5. curl로 로컬 테스트
curl -I http://localhost:8082
```

---

### 7. DB 연결 오류 (Connect Error)

**원인 1: 호스트 설정 오류**
```bash
# DB 설정 파일 확인
cat website_backup/config/db-config.php | grep DB_HOST

# Docker 환경에서는 'db'여야 함
# 'localhost'로 되어 있으면 환경 변수 확인

# docker-compose.yml 환경 변수 확인
cat docker-compose.yml | grep -A5 environment
```

**원인 2: DB 컨테이너 미실행**
```bash
# DB 컨테이너 상태 확인
docker-compose ps db

# DB 로그 확인
docker-compose logs db

# DB 재시작
docker-compose restart db

# DB 준비 완료 확인
docker-compose logs db | grep "ready for connections"
```

**원인 3: DB 초기화 실패**
```bash
# DB 컨테이너 재생성
docker-compose down -v
docker-compose up -d --build

# 초기화 로그 확인
docker-compose logs -f db
# "mysqld: ready for connections" 메시지 대기

# 테이블 생성 확인
docker exec impexgls_db mysql -uroot -pimpexgls_root_password -e "USE corporate_db; SHOW TABLES;"
```

---

### 8. Git pull 충돌 (conflict)

```bash
cd ~/impexgls

# 서버 로컬 변경사항 임시 저장
git stash

# 최신 코드 가져오기
git pull origin main

# 임시 저장한 내용 복원 (선택사항)
git stash pop

# 또는 서버 변경사항 완전 무시하고 덮어쓰기
git fetch --all
git reset --hard origin/main
```

---

### 9. 컨테이너가 자꾸 재시작돼요

```bash
# 에러 로그 확인
docker-compose logs --tail=100

# 특정 컨테이너 로그 확인
docker-compose logs --tail=100 web

# 컨테이너 상태 확인
docker-compose ps

# 완전 재시작
docker-compose down
docker-compose up -d --build
```

---

### 10. 디스크 용량 부족

```bash
# 디스크 사용량 확인
df -h

# Docker 캐시 정리
docker system prune -a

# 오래된 백업 삭제
rm ~/backups/*_202401*.sql.gz

# Docker 로그 정리
docker-compose logs > /dev/null

# 사용하지 않는 볼륨 삭제 (주의!)
docker volume prune
```

---

## 빠른 참조 (Cheat Sheet)

### 초기 설치 (한 번만)

```bash
# 로컬: SSH 키 설정
chmod 400 ~/.ssh/lightsail-impexgls.pem

# 서버: Docker 및 Git 설치
ssh -i ~/.ssh/lightsail-impexgls.pem ec2-user@YOUR_IP
sudo yum update -y
sudo yum install -y git docker
sudo systemctl start docker
sudo systemctl enable docker
sudo usermod -aG docker ec2-user
sudo curl -L "https://github.com/docker/compose/releases/download/v2.20.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
exit

# 재접속 후 프로젝트 클론
ssh -i ~/.ssh/lightsail-impexgls.pem ec2-user@YOUR_IP
git clone https://github.com/webdotcokr/impexgls.git
cd impexgls
docker-compose up -d --build
```

---

### 일상적인 배포 (30초)

```bash
# 로컬: 코드 수정 및 Push
cd /Users/kimjunha/Desktop/impexgls
git add . && git commit -m "Update" && git push origin main

# 서버: Pull 및 재시작
ssh -i ~/.ssh/lightsail-impexgls.pem ec2-user@YOUR_IP
cd ~/impexgls && git pull origin main && docker-compose restart web
```

---

### DB 백업

```bash
# 서버에서 백업
docker exec impexgls_db mysqldump -uroot -pimpexgls_root_password corporate_db | gzip > ~/backups/corporate_db_$(date +%Y%m%d).sql.gz

# 로컬로 다운로드
scp -i ~/.ssh/lightsail-impexgls.pem ec2-user@YOUR_IP:~/backups/corporate_db_*.sql.gz ~/Downloads/
```

---

### 로그 확인

```bash
docker-compose logs -f web  # 실시간 웹 로그
docker-compose logs --tail=100 web  # 최근 100줄
docker-compose logs --since 30m web  # 최근 30분
```

---

### 권한 재설정

```bash
docker exec impexgls_web chown -R www-data:www-data /var/www/html
docker exec impexgls_web chmod -R 755 /var/www/html
docker exec impexgls_web chmod -R 777 /var/www/html/admin/uploads /var/www/html/assets/uploads
```

---

## 환경 정보

### 로컬 개발 환경
- **OS**: macOS
- **도구**: Docker Desktop
- **PHP**: 7.4
- **MySQL**: 5.7
- **웹 포트**: 8082
- **DB 포트**: 3308
- **phpMyAdmin 포트**: 8083
- **프로젝트 경로**: /Users/kimjunha/Desktop/impexgls

### 서버 환경 (Lightsail Docker)
- **OS**: Amazon Linux 2
- **Docker**: 최신 버전
- **Docker Compose**: v2.20.0+
- **PHP**: 7.4
- **MySQL**: 5.7
- **웹 포트**: 8082
- **DB 포트**: 3308 (외부 접근)
- **phpMyAdmin 포트**: 8083
- **DB 호스트**: `db` (Docker 내부 네트워크)
- **프로젝트 경로**: /home/ec2-user/impexgls

### Git 저장소
```
https://github.com/webdotcokr/impexgls.git
```

---

## 보안 체크리스트

- [ ] Lightsail 방화벽에서 SSH(22) 포트는 본인 IP만 허용
- [ ] phpMyAdmin(8083) 포트는 본인 IP만 허용 또는 사용 안 함
- [ ] SSH 키 파일 권한 400 설정 (`chmod 400`)
- [ ] DB 비밀번호를 기본값에서 변경
- [ ] Git에 민감 정보 커밋 안 함 (.gitignore 설정)
- [ ] 정기 백업 설정 (Cron)
- [ ] Docker 이미지 정기 업데이트
- [ ] 사용하지 않는 컨테이너/이미지 정리
- [ ] Lightsail 스냅샷 정기 생성 (주 1회)

---

## 성능 최적화 팁

1. **Lightsail 스냅샷 정기 생성** (주 1회)
   - Lightsail 콘솔 → Snapshots → Create snapshot
   - 복원 시 빠른 롤백 가능

2. **리소스 모니터링**
   ```bash
   # 실시간 리소스 사용량
   docker stats

   # 서버 전체 리소스
   top
   htop
   ```

3. **로그 로테이션**
   ```bash
   # Docker 로그 크기 제한 (docker-compose.yml에 추가)
   logging:
     driver: "json-file"
     options:
       max-size: "10m"
       max-file: "3"
   ```

4. **CDN 사용** (선택사항)
   - CloudFlare 또는 AWS CloudFront 연동
   - 정적 파일(이미지, CSS, JS) 캐싱

---

## SSL 및 도메인 연결

HTTP(8082 포트)로 정상 접속 확인 후, HTTPS 및 도메인을 연결하세요.

### 📖 상세 가이드

자세한 SSL 인증서 적용 및 도메인 연결 방법은 별도 문서를 참조하세요:

**👉 [SSL_DOMAIN_SETUP.md](./SSL_DOMAIN_SETUP.md)**

### 두 가지 방법

#### 방법 A: Lightsail Load Balancer + SSL (권장)
- **장점**: 관리 간편, 자동 갱신, AWS 통합
- **단점**: 추가 비용 ($18/월)
- **추천**: 프로덕션 환경

#### 방법 B: Nginx + Let's Encrypt
- **장점**: 무료, 완전한 제어
- **단점**: 수동 설정, 90일마다 갱신
- **추천**: 소규모 프로젝트, 비용 절감

### 빠른 시작 (Let's Encrypt)

```bash
# 1. 도메인 DNS A 레코드 추가 (먼저!)
# 타입: A, 이름: @, 값: YOUR_LIGHTSAIL_IP

# 2. Nginx 및 Certbot 설치
sudo yum install -y nginx certbot python3-certbot-nginx

# 3. 기본 Nginx 설정
sudo tee /etc/nginx/conf.d/impexgls.conf > /dev/null << 'EOF'
server {
    listen 80;
    server_name impexgls.com www.impexgls.com;
    location / {
        proxy_pass http://localhost:8082;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
EOF

# 4. Nginx 시작
sudo systemctl start nginx
sudo systemctl enable nginx

# 5. Lightsail 방화벽에서 80, 443 포트 열기

# 6. SSL 인증서 발급
sudo certbot --nginx -d impexgls.com -d www.impexgls.com

# 7. 접속 확인
curl -I https://impexgls.com
```

**자세한 내용은 [SSL_DOMAIN_SETUP.md](./SSL_DOMAIN_SETUP.md)를 참조하세요.**
