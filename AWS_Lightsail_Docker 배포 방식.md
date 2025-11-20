# AWS Lightsail Docker 배포 가이드

> DR Renewal 프로젝트를 AWS Lightsail에 Docker로 배포하고 유지보수하는 완벽 가이드

## 목차
1. [초기 배포](#초기-배포)
2. [일상적인 배포 워크플로우](#일상적인-배포-워크플로우)
3. [업로드 파일 관리](#업로드-파일-관리)
4. [데이터베이스 관리](#데이터베이스-관리)
5. [유지보수 및 모니터링](#유지보수-및-모니터링)
6. [트러블슈팅](#트러블슈팅)

---

## 초기 배포

### 1. Lightsail 인스턴스 생성

**AWS Lightsail 콘솔에서:**
- **OS**: Amazon Linux 2 (또는 Ubuntu 20.04/22.04)
- **플랜**: 최소 4GB RAM 권장
- **스토리지**: 60GB 이상 (업로드 파일 49GB 고려)
- **리전**: Seoul (ap-northeast-2)

### 2. SSH 접속 설정

```bash
# Lightsail 콘솔에서 SSH 키 다운로드
# 로컬에 저장: ~/.ssh/lightsail-dcrenewal.pem

# 키 권한 설정
chmod 400 ~/.ssh/lightsail-dcrenewal.pem

# SSH 접속 (Amazon Linux 2)
ssh -i ~/.ssh/lightsail-dcrenewal.pem ec2-user@YOUR_LIGHTSAIL_IP

# SSH 접속 (Ubuntu)
ssh -i ~/.ssh/lightsail-dcrenewal.pem ubuntu@YOUR_LIGHTSAIL_IP
```

### 3. Docker 설치

#### Amazon Linux 2:
```bash
sudo yum update -y
sudo yum install -y docker git
sudo service docker start
sudo usermod -aG docker ec2-user

# Docker Compose 설치
sudo curl -L "https://github.com/docker/compose/releases/download/v2.20.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# 재로그인 (Docker 그룹 적용)
exit
# 다시 SSH 접속
```

#### Ubuntu 20.04/22.04:
```bash
sudo apt update
sudo apt install -y git

# Docker 설치
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker ubuntu

# Docker Compose 설치
sudo curl -L "https://github.com/docker/compose/releases/download/v2.20.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# 재로그인
exit
# 다시 SSH 접속
```

### 4. 프로젝트 배포

```bash
# 저장소 클론
git clone https://YOUR_TOKEN@github.com/webdotcokr/dcrenewal.git
cd dcrenewal

# dbconfig.php 생성 (Docker 환경용)
cat > data/dbconfig.php << 'EOF'
<?php
if (!defined('_GNUBOARD_')) exit;

define('G5_MYSQL_HOST', getenv('G5_MYSQL_HOST') ?: 'db');
define('G5_MYSQL_USER', getenv('G5_MYSQL_USER') ?: 'drcody3651');
define('G5_MYSQL_PASSWORD', getenv('G5_MYSQL_PASSWORD') ?: 'Password321!');
define('G5_MYSQL_DB', getenv('G5_MYSQL_DB') ?: 'drcody3651');
define('G5_MYSQL_SET_MODE', true);

define('G5_TABLE_PREFIX', 'g5_');

$g5['write_prefix'] = G5_TABLE_PREFIX.'write_';
$g5['auth_table'] = G5_TABLE_PREFIX.'auth';
$g5['config_table'] = G5_TABLE_PREFIX.'config';
$g5['group_table'] = G5_TABLE_PREFIX.'group';
$g5['group_member_table'] = G5_TABLE_PREFIX.'group_member';
$g5['board_table'] = G5_TABLE_PREFIX.'board';
$g5['board_file_table'] = G5_TABLE_PREFIX.'board_file';
$g5['board_good_table'] = G5_TABLE_PREFIX.'board_good';
$g5['board_new_table'] = G5_TABLE_PREFIX.'board_new';
$g5['login_table'] = G5_TABLE_PREFIX.'login';
$g5['mail_table'] = G5_TABLE_PREFIX.'mail';
$g5['member_table'] = G5_TABLE_PREFIX.'member';
$g5['memo_table'] = G5_TABLE_PREFIX.'memo';
$g5['poll_table'] = G5_TABLE_PREFIX.'poll';
$g5['poll_etc_table'] = G5_TABLE_PREFIX.'poll_etc';
$g5['point_table'] = G5_TABLE_PREFIX.'point';
$g5['popular_table'] = G5_TABLE_PREFIX.'popular';
$g5['scrap_table'] = G5_TABLE_PREFIX.'scrap';
$g5['visit_table'] = G5_TABLE_PREFIX.'visit';
$g5['visit_sum_table'] = G5_TABLE_PREFIX.'visit_sum';
$g5['uniqid_table'] = G5_TABLE_PREFIX.'uniqid';
$g5['autosave_table'] = G5_TABLE_PREFIX.'autosave';
$g5['cert_history_table'] = G5_TABLE_PREFIX.'cert_history';
$g5['qa_config_table'] = G5_TABLE_PREFIX.'qa_config';
$g5['qa_content_table'] = G5_TABLE_PREFIX.'qa_content';
$g5['content_table'] = G5_TABLE_PREFIX.'content';
$g5['faq_table'] = G5_TABLE_PREFIX.'faq';
$g5['faq_master_table'] = G5_TABLE_PREFIX.'faq_master';
$g5['new_win_table'] = G5_TABLE_PREFIX.'new_win';
$g5['menu_table'] = G5_TABLE_PREFIX.'menu';

define('G5_USE_SHOP', true);
define('G5_SHOP_TABLE_PREFIX', 'g5_shop_');

$g5['g5_shop_default_table'] = G5_SHOP_TABLE_PREFIX.'default';
$g5['g5_shop_banner_table'] = G5_SHOP_TABLE_PREFIX.'banner';
$g5['g5_shop_cart_table'] = G5_SHOP_TABLE_PREFIX.'cart';
$g5['g5_shop_category_table'] = G5_SHOP_TABLE_PREFIX.'category';
$g5['g5_shop_event_table'] = G5_SHOP_TABLE_PREFIX.'event';
$g5['g5_shop_event_item_table'] = G5_SHOP_TABLE_PREFIX.'event_item';
$g5['g5_shop_item_table'] = G5_SHOP_TABLE_PREFIX.'item';
$g5['g5_shop_item_option_table'] = G5_SHOP_TABLE_PREFIX.'item_option';
$g5['g5_shop_item_use_table'] = G5_SHOP_TABLE_PREFIX.'item_use';
$g5['g5_shop_item_qa_table'] = G5_SHOP_TABLE_PREFIX.'item_qa';
$g5['g5_shop_item_relation_table'] = G5_SHOP_TABLE_PREFIX.'item_relation';
$g5['g5_shop_order_table'] = G5_SHOP_TABLE_PREFIX.'order';
$g5['g5_shop_order_delete_table'] = G5_SHOP_TABLE_PREFIX.'order_delete';
$g5['g5_shop_wish_table'] = G5_SHOP_TABLE_PREFIX.'wish';
$g5['g5_shop_coupon_table'] = G5_SHOP_TABLE_PREFIX.'coupon';
$g5['g5_shop_coupon_zone_table'] = G5_SHOP_TABLE_PREFIX.'coupon_zone';
$g5['g5_shop_coupon_log_table'] = G5_SHOP_TABLE_PREFIX.'coupon_log';
$g5['g5_shop_sendcost_table'] = G5_SHOP_TABLE_PREFIX.'sendcost';
$g5['g5_shop_personalpay_table'] = G5_SHOP_TABLE_PREFIX.'personalpay';
$g5['g5_shop_order_address_table'] = G5_SHOP_TABLE_PREFIX.'order_address';
$g5['g5_shop_item_stocksms_table'] = G5_SHOP_TABLE_PREFIX.'item_stocksms';
$g5['g5_shop_order_data_table'] = G5_SHOP_TABLE_PREFIX.'order_data';
$g5['g5_shop_inicis_log_table'] = G5_SHOP_TABLE_PREFIX.'inicis_log';
?>
EOF

chmod 644 data/dbconfig.php

# 배포 스크립트 실행
chmod +x deploy-lightsail.sh
./deploy-lightsail.sh
```

### 5. 방화벽 설정

**Lightsail 콘솔 → Networking 탭:**
- **HTTP (80)**: 0.0.0.0/0 허용
- **Custom TCP (8082)**: 0.0.0.0/0 허용 (웹 서버)
- **Custom TCP (8083)**: 선택적 (phpMyAdmin, 보안상 특정 IP만 허용 권장)

### 6. 접속 확인

```
http://YOUR_LIGHTSAIL_IP:8082
```

---

## 일상적인 배포 워크플로우

### 시나리오 1: 코드만 수정 (가장 일반적)

**로컬 (MacBook):**
```bash
cd /Applications/MAMP/htdocs/dcrenewal

# 테마, PHP 파일 등 수정 작업...

# 로컬 MAMP에서 테스트
# http://localhost:8888 접속 확인

# Git 커밋
git add .
git commit -m "Update theme layout"
git push origin main
```

**Lightsail (서버):**
```bash
cd ~/dcrenewal

# 최신 코드 가져오기
git pull origin main

# PHP/HTML 변경 시 웹 컨테이너만 재시작
docker-compose restart web

# 브라우저에서 확인
# http://YOUR_IP:8082
```

**배포 시간: 약 1분**

---

### 시나리오 2: Dockerfile 또는 PHP 패키지 변경

**로컬 (MacBook):**
```bash
# Dockerfile, docker-compose.yml 수정...

git add .
git commit -m "Update Docker configuration"
git push
```

**Lightsail (서버):**
```bash
cd ~/dcrenewal
git pull

# 이미지 재빌드 (--build 필수)
docker-compose up -d --build

# 컨테이너 상태 확인
docker-compose ps
```

**배포 시간: 약 3-5분**

---

### 시나리오 3: 긴급 핫픽스 (서버 직접 수정)

```bash
# Lightsail에서 직접 파일 수정
cd ~/dcrenewal
vi theme/webdot/skin/some_file.php

# 즉시 반영 (Git 커밋 없이)
docker-compose restart web

# 나중에 로컬에 반영
# 로컬에서: git pull 후 동일하게 수정하고 커밋
```

---

## 업로드 파일 관리

### data/file, data/editor 동기화

#### 방법 A: 백업 스크립트 사용 (권장)

**로컬 (MacBook):**
```bash
cd /Applications/MAMP/htdocs/dcrenewal

# 1. 업로드 파일 백업 (압축)
./backup-uploads.sh backup
# → uploads-backup/file_YYYYMMDD_HHMMSS.tar.gz
# → uploads-backup/editor_YYYYMMDD_HHMMSS.tar.gz 생성

# 2. Lightsail로 전송
scp uploads-backup/*.tar.gz ec2-user@YOUR_IP:~/dcrenewal/uploads-backup/
```

**Lightsail (서버):**
```bash
cd ~/dcrenewal

# 백업 파일 복원
./backup-uploads.sh restore
# 최신 백업으로 복원하시겠습니까? (y/N): y

# 권한 설정
chmod -R 777 data/file data/editor

# 웹 서버 재시작
docker-compose restart web
```

---

#### 방법 B: rsync 사용 (증분 동기화, 가장 빠름)

**로컬 (MacBook):**
```bash
# data/file 폴더 동기화 (변경된 파일만 전송)
rsync -avz --progress -e "ssh -i ~/.ssh/lightsail-dcrenewal.pem" \
  /Applications/MAMP/htdocs/dcrenewal/data/file/ \
  ec2-user@YOUR_IP:~/dcrenewal/data/file/

# data/editor 폴더 동기화
rsync -avz --progress -e "ssh -i ~/.ssh/lightsail-dcrenewal.pem" \
  /Applications/MAMP/htdocs/dcrenewal/data/editor/ \
  ec2-user@YOUR_IP:~/dcrenewal/data/editor/
```

**Lightsail (서버):**
```bash
# 권한 재설정
cd ~/dcrenewal
chmod -R 777 data/file data/editor
```

---

#### 방법 C: SFTP 클라이언트 사용

**FileZilla, Cyberduck, Transmit 등:**

```
호스트: YOUR_LIGHTSAIL_IP
프로토콜: SFTP
포트: 22
사용자: ec2-user (Amazon Linux) 또는 ubuntu (Ubuntu)
인증: SSH 키 파일 (~/.ssh/lightsail-dcrenewal.pem)

업로드 경로:
/home/ec2-user/dcrenewal/data/file/
/home/ec2-user/dcrenewal/data/editor/
```

**업로드 후 서버에서 권한 설정:**
```bash
cd ~/dcrenewal
chmod -R 777 data/file data/editor
docker-compose restart web
```

---

## 데이터베이스 관리

### DB 덤프 내보내기 (로컬 → 서버)

**로컬 (MacBook - MAMP):**
```bash
cd /Applications/MAMP/htdocs/dcrenewal

# 1. DB 덤프 생성
mysqldump -h localhost -P 8889 -u root -proot drcody3651 \
  > docker/mysql/init/drcody3651.sql

# 2. 압축
gzip docker/mysql/init/drcody3651.sql
# → drcody3651.sql.gz 생성

# 3. Git 커밋
git add docker/mysql/init/drcody3651.sql.gz
git commit -m "Update database schema"
git push
```

**Lightsail (서버):**
```bash
cd ~/dcrenewal
git pull

# ⚠️ 주의: 기존 DB 데이터가 모두 삭제됩니다!
docker-compose down -v

# DB 컨테이너 재생성 (init 폴더의 SQL 자동 실행)
docker-compose up -d --build

# 로그 확인 (DB 초기화 진행 상황)
docker-compose logs -f db
```

---

### DB 백업 (서버 → 로컬)

**Lightsail (서버):**
```bash
# 서버 DB 덤프
docker exec gnuboard_db mysqldump -udrcody3651 -pPassword321! drcody3651 \
  | gzip > ~/drcody3651_backup_$(date +%Y%m%d).sql.gz
```

**로컬 (MacBook):**
```bash
# 서버에서 로컬로 다운로드
scp -i ~/.ssh/lightsail-dcrenewal.pem \
  ec2-user@YOUR_IP:~/drcody3651_backup_*.sql.gz \
  ~/Downloads/
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
- 비밀번호: `gnuboard_root`

**또는:**
- 사용자: `drcody3651`
- 비밀번호: `Password321!`

**보안 강화:**
```bash
# Lightsail 방화벽에서 8083 포트를 특정 IP만 허용
# Networking 탭 → Custom TCP 8083 → Source: YOUR_OFFICE_IP/32
```

---

## 유지보수 및 모니터링

### 컨테이너 상태 확인

```bash
cd ~/dcrenewal

# 실행 중인 컨테이너 확인
docker-compose ps

# 출력 예시:
# NAME                  STATUS              PORTS
# gnuboard_web          Up 3 days           0.0.0.0:8082->80/tcp
# gnuboard_db           Up 3 days           0.0.0.0:3308->3306/tcp
# gnuboard_phpmyadmin   Up 3 days           0.0.0.0:8083->80/tcp
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
```

---

### 디스크 사용량 확인

```bash
# 전체 디스크 사용량
df -h

# 프로젝트 디렉토리 크기
du -sh ~/dcrenewal

# 업로드 파일 크기
du -sh ~/dcrenewal/data/file
du -sh ~/dcrenewal/data/editor

# Docker 볼륨 사용량
docker system df
```

---

### 정기 백업 (Cron 설정)

**매일 새벽 3시 DB 자동 백업:**
```bash
# Crontab 편집
crontab -e

# 추가:
0 3 * * * cd ~/dcrenewal && docker exec gnuboard_db mysqldump -udrcody3651 -pPassword321! drcody3651 | gzip > ~/backups/drcody3651_$(date +\%Y\%m\%d).sql.gz

# 백업 디렉토리 생성
mkdir -p ~/backups
```

---

### 컨테이너 재시작

```bash
cd ~/dcrenewal

# 웹 서버만 재시작 (코드 변경 시)
docker-compose restart web

# DB만 재시작
docker-compose restart db

# 모든 컨테이너 재시작
docker-compose restart

# 컨테이너 중지
docker-compose down

# 컨테이너 시작
docker-compose up -d
```

---

### 파일 권한 재설정

```bash
# Docker 컨테이너 내부에서 실행
docker exec -it gnuboard_web bash

# 컨테이너 내부에서:
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod -R 777 /var/www/html/data
exit
```

---

### Docker 이미지/캐시 정리

```bash
# 사용하지 않는 이미지 삭제
docker image prune -a

# 전체 시스템 정리 (주의!)
docker system prune -a

# 볼륨 포함 완전 정리 (데이터 삭제됨!)
docker system prune -a --volumes
```

---

## 트러블슈팅

### 1. 사이트가 안 열려요 (Connection refused)

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
```

---

### 2. DB 연결 오류 (Connect Error)

**원인 1: 호스트 설정 오류**
```bash
# data/dbconfig.php 확인
cat data/dbconfig.php | grep G5_MYSQL_HOST

# Docker 환경에서는 'db'여야 함
# 'localhost'로 되어 있으면 수정:
sed -i "s/'localhost'/'db'/g" data/dbconfig.php
docker-compose restart web
```

**원인 2: DB 컨테이너 미실행**
```bash
# DB 컨테이너 상태 확인
docker-compose ps db

# DB 로그 확인
docker-compose logs db

# DB 재시작
docker-compose restart db
```

**원인 3: DB 초기화 실패**
```bash
# DB 컨테이너 재생성
docker-compose down -v
docker-compose up -d --build

# 초기화 로그 확인
docker-compose logs -f db
# "MySQL init process done. Ready for start up." 메시지 대기
```

---

### 3. 업로드 파일이 안 보여요

```bash
# 1. 파일 존재 확인
ls -la ~/dcrenewal/data/file
ls -la ~/dcrenewal/data/editor

# 2. 권한 확인 및 재설정
chmod -R 777 ~/dcrenewal/data/file
chmod -R 777 ~/dcrenewal/data/editor

# 3. 웹 서버 재시작
docker-compose restart web
```

---

### 4. Git pull이 안 돼요 (conflict)

```bash
cd ~/dcrenewal

# 서버 로컬 변경사항 임시 저장
git stash

# 최신 코드 가져오기
git pull

# 임시 저장한 내용 복원 (선택사항)
git stash pop

# 또는 서버 변경사항 완전 무시하고 덮어쓰기
git fetch --all
git reset --hard origin/main
```

---

### 5. 컨테이너가 자꾸 재시작돼요

```bash
# 에러 로그 확인
docker-compose logs --tail=100

# PHP 메모리 부족 시 docker-compose.yml 수정
# web 서비스에 추가:
# deploy:
#   resources:
#     limits:
#       memory: 1G

# 재시작
docker-compose down
docker-compose up -d
```

---

### 6. 디스크 용량 부족

```bash
# 디스크 사용량 확인
df -h

# Docker 캐시 정리
docker system prune -a

# 오래된 백업 삭제
rm ~/backups/*_202401*.sql.gz

# 불필요한 로그 파일 삭제
docker-compose logs > /dev/null
```

---

### 7. SSL/HTTPS 적용하고 싶어요

**Let's Encrypt + Nginx Reverse Proxy 추가:**

```bash
# docker-compose.yml에 nginx 서비스 추가 필요
# 또는 Lightsail Load Balancer + SSL 인증서 사용
```

상세 가이드는 별도 문서 참고: `SSL-SETUP.md` (추후 작성)

---

## 빠른 참조 (Cheat Sheet)

### 일상적인 배포 (30초)
```bash
# 로컬
git add . && git commit -m "Update" && git push

# 서버
cd ~/dcrenewal && git pull && docker-compose restart web
```

### DB 업데이트 (5분)
```bash
# 로컬
mysqldump -h localhost -P 8889 -u root -proot drcody3651 | gzip > docker/mysql/init/drcody3651.sql.gz
git add docker/mysql/init/drcody3651.sql.gz && git commit -m "Update DB" && git push

# 서버
cd ~/dcrenewal && git pull && docker-compose down -v && docker-compose up -d --build
```

### 업로드 파일 동기화 (변경분만)
```bash
# 로컬
rsync -avz -e "ssh -i ~/.ssh/lightsail-dcrenewal.pem" \
  /Applications/MAMP/htdocs/dcrenewal/data/file/ \
  ec2-user@YOUR_IP:~/dcrenewal/data/file/
```

### 로그 확인
```bash
docker-compose logs -f web  # 실시간 웹 로그
docker-compose logs --tail=100 web  # 최근 100줄
```

### 권한 재설정
```bash
docker exec -it gnuboard_web chown -R www-data:www-data /var/www/html/data
chmod -R 777 ~/dcrenewal/data/file ~/dcrenewal/data/editor
```

---

## 환경 정보

### 로컬 개발 환경
- **도구**: MAMP
- **PHP**: 7.x
- **MySQL**: 5.7 (포트 8889)
- **웹 포트**: 8888
- **DB 호스트**: `localhost`

### 서버 환경 (Lightsail Docker)
- **OS**: Amazon Linux 2
- **Docker**: 최신 버전
- **Docker Compose**: v2.20.0+
- **PHP**: 5.6 (레거시 호환)
- **MySQL**: 5.7
- **웹 포트**: 8082
- **DB 포트**: 3308 (외부 접근)
- **phpMyAdmin 포트**: 8083
- **DB 호스트**: `db` (Docker 내부 네트워크)

### 주요 경로
```
로컬:  /Applications/MAMP/htdocs/dcrenewal
서버:  /home/ec2-user/dcrenewal
```

### Git 저장소
```
https://github.com/webdotcokr/dcrenewal.git
```

---

## 보안 체크리스트

- [ ] Lightsail 방화벽에서 불필요한 포트 차단
- [ ] phpMyAdmin(8083) 포트는 특정 IP만 허용
- [ ] SSH 키 파일 권한 400 설정
- [ ] DB 비밀번호 주기적 변경
- [ ] Git에 민감 정보 커밋 안 함 (data/dbconfig.php)
- [ ] 정기 백업 설정 (Cron)
- [ ] Docker 이미지 정기 업데이트
- [ ] 사용하지 않는 컨테이너/이미지 정리

---

## 성능 최적화 팁

1. **Lightsail 스냅샷 정기 생성** (주 1회)
   - Lightsail 콘솔 → Snapshots → Create snapshot

2. **Redis 캐시 추가** (선택사항)
   ```bash
   # docker-compose.yml에 redis 서비스 추가
   ```

3. **Nginx Reverse Proxy로 정적 파일 서빙**
   - CSS/JS/이미지는 Nginx로 직접 서빙
   - PHP는 Apache로 프록시

4. **CDN 사용**
   - CloudFlare 또는 AWS CloudFront 연동

---

## 문의 및 지원

**문서 버전**: 1.0
**최종 업데이트**: 2025-11-04
**작성자**: Claude Code Assistant

이 문서는 실제 배포 경험을 바탕으로 작성되었습니다.
