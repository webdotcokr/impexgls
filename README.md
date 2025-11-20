# IMPEXGLS Docker 배포 프로젝트

> PHP 기반 기업 홈페이지를 Docker로 로컬 개발 및 AWS Lightsail에 배포하는 완벽한 가이드

## 프로젝트 개요

IMPEXGLS 홈페이지를 AWS Lightsail LAMP 스택에서 Docker 기반 환경으로 마이그레이션하고, 로컬 개발 환경부터 프로덕션 배포까지 전체 워크플로우를 구축한 프로젝트입니다.

### 기술 스택

- **Frontend**: PHP 7.4, HTML5, CSS3, JavaScript, Tailwind CSS
- **Backend**: PHP 7.4, Apache 2.4
- **Database**: MySQL 5.7
- **Infrastructure**: Docker, Docker Compose
- **Deployment**: AWS Lightsail (Amazon Linux 2)
- **SSL**: Let's Encrypt 또는 AWS Certificate Manager

---

## 빠른 시작

### 1. 로컬 Docker 환경 실행

```bash
# 프로젝트 클론
git clone https://github.com/webdotcokr/impexgls.git
cd impexgls

# Docker 컨테이너 실행
docker-compose up -d --build

# 웹사이트 접속
# http://localhost:8082
```

**자세한 내용**: [QUICKSTART.md](./QUICKSTART.md)

---

### 2. AWS Lightsail 배포

```bash
# Lightsail SSH 접속
ssh -i ~/.ssh/lightsail-impexgls.pem ec2-user@YOUR_IP

# Docker 설치
sudo yum update -y
sudo yum install -y git docker
sudo systemctl start docker
sudo systemctl enable docker

# 프로젝트 클론 및 실행
git clone https://github.com/webdotcokr/impexgls.git
cd impexgls
docker-compose up -d --build
```

**자세한 내용**: [AWS_Lightsail_Docker 배포 방식.md](./AWS_Lightsail_Docker%20배포%20방식.md)

---

### 3. SSL 및 도메인 연결

```bash
# Nginx 설치
sudo yum install -y nginx certbot python3-certbot-nginx

# SSL 인증서 발급
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# HTTPS 접속
# https://yourdomain.com
```

**자세한 내용**: [SSL_DOMAIN_SETUP.md](./SSL_DOMAIN_SETUP.md)

---

## 문서 구조

### 📚 주요 문서

| 문서 | 설명 | 대상 |
|------|------|------|
| **[QUICKSTART.md](./QUICKSTART.md)** | 로컬 Docker 환경 빠른 시작 가이드 | 개발자 |
| **[README_LOCAL_DOCKER.md](./README_LOCAL_DOCKER.md)** | 로컬 Docker 환경 상세 가이드 | 개발자 |
| **[AWS_Lightsail_Docker 배포 방식.md](./AWS_Lightsail_Docker%20배포%20방식.md)** | AWS Lightsail 배포 완벽 가이드 | 운영자 |
| **[SSL_DOMAIN_SETUP.md](./SSL_DOMAIN_SETUP.md)** | SSL 인증서 및 도메인 연결 가이드 | 운영자 |

### 📖 읽는 순서

#### 개발자용
1. **[QUICKSTART.md](./QUICKSTART.md)** - 로컬 환경 빠른 구축
2. **[README_LOCAL_DOCKER.md](./README_LOCAL_DOCKER.md)** - 상세 개발 가이드
3. **[AWS_Lightsail_Docker 배포 방식.md](./AWS_Lightsail_Docker%20배포%20방식.md)** - 배포 방법 이해

#### 운영자용
1. **[AWS_Lightsail_Docker 배포 방식.md](./AWS_Lightsail_Docker%20배포%20방식.md)** - 서버 배포
2. **[SSL_DOMAIN_SETUP.md](./SSL_DOMAIN_SETUP.md)** - HTTPS 적용
3. **[README_LOCAL_DOCKER.md](./README_LOCAL_DOCKER.md)** - 로컬 테스트 환경

---

## 프로젝트 구조

```
impexgls/
├── Dockerfile                          # 웹 서버 이미지 정의
├── docker-compose.yml                  # 컨테이너 오케스트레이션
├── .dockerignore                       # Docker 빌드 제외 파일
├── .gitignore                          # Git 제외 파일
│
├── website_backup/                     # 웹사이트 소스 코드
│   ├── admin/                         # 관리자 페이지
│   │   ├── login.php                 # 관리자 로그인
│   │   ├── dashboard.php             # 대시보드
│   │   └── ...
│   ├── assets/                        # 정적 파일
│   │   ├── css/                      # 스타일시트
│   │   ├── js/                       # JavaScript
│   │   ├── images/                   # 이미지
│   │   └── uploads/                  # 업로드 파일
│   ├── config/                        # 설정 파일
│   │   ├── config.php                # 전역 설정
│   │   ├── db-config.php             # DB 설정 (Docker 지원)
│   │   └── meta-config.php           # SEO 메타 설정
│   ├── includes/                      # 공통 함수
│   │   └── functions.php             # 공통 함수 라이브러리
│   ├── pages/                         # 페이지 파일
│   │   ├── about.php                 # 회사 소개
│   │   ├── services.php              # 서비스
│   │   └── ...
│   └── index.php                      # 메인 페이지
│
├── docker/                             # Docker 관련 파일
│   └── mysql/
│       └── init/
│           └── corporate_db.sql       # DB 초기화 SQL
│
└── docs/                               # 문서
    ├── README.md                      # 이 파일
    ├── QUICKSTART.md                  # 빠른 시작
    ├── README_LOCAL_DOCKER.md         # 로컬 Docker 가이드
    ├── AWS_Lightsail_Docker 배포 방식.md  # AWS 배포 가이드
    └── SSL_DOMAIN_SETUP.md            # SSL 설정 가이드
```

---

## 주요 기능

### 웹사이트 기능
- ✅ 반응형 디자인 (모바일, 태블릿, 데스크톱)
- ✅ 다국어 지원 (한국어, 영어)
- ✅ 관리자 페이지
  - 뉴스/공지사항 관리
  - 고객 관리
  - FAQ 관리
  - 인증서 관리
  - 견적 요청 관리
- ✅ SEO 최적화
- ✅ 파일 업로드 기능

### 인프라 기능
- ✅ Docker 기반 컨테이너화
- ✅ 로컬 개발 환경 = 프로덕션 환경
- ✅ phpMyAdmin 통합 (DB 관리)
- ✅ 자동 DB 초기화
- ✅ 환경 변수 기반 설정

---

## 환경 정보

### 로컬 개발 환경

| 항목 | 값 |
|------|-----|
| **OS** | macOS |
| **도구** | Docker Desktop |
| **PHP** | 7.4 |
| **MySQL** | 5.7 |
| **웹 포트** | 8082 |
| **DB 포트** | 3308 |
| **phpMyAdmin 포트** | 8083 |

### 서버 환경 (AWS Lightsail)

| 항목 | 값 |
|------|-----|
| **OS** | Amazon Linux 2 |
| **Docker** | 최신 버전 |
| **Docker Compose** | v2.20.0+ |
| **PHP** | 7.4 |
| **MySQL** | 5.7 |
| **웹 포트** | 8082 → 80/443 (Nginx) |
| **DB 호스트** | db (Docker 내부 네트워크) |

---

## 데이터베이스

### 테이블 목록 (14개)

- `admins` - 관리자 계정
- `admin_logs` - 관리자 활동 로그
- `admin_sessions` - 관리자 세션
- `certificates` - 인증서 정보
- `client_categories` - 고객 카테고리
- `clients` - 고객 정보
- `container_types` - 컨테이너 타입
- `faqs` - FAQ
- `menus` - 메뉴 구조
- `network_locations` - 네트워크 위치
- `news_posts` - 뉴스/공지사항
- `page_meta` - 페이지 메타 정보
- `quote_requests` - 견적 요청
- `useful_links` - 유용한 링크

---

## 배포 워크플로우

### 일상적인 배포 (코드 수정)

```bash
# 1. 로컬에서 개발 및 테스트
cd /Users/kimjunha/Desktop/impexgls
vi website_backup/index.php
docker-compose restart web

# 2. Git 커밋 및 Push
git add .
git commit -m "Update homepage"
git push origin main

# 3. 서버에서 Pull 및 재시작
ssh -i ~/.ssh/lightsail-impexgls.pem ec2-user@YOUR_IP
cd ~/impexgls
git pull origin main
docker-compose restart web
```

**배포 시간: 약 30초 ~ 1분**

---

## 트러블슈팅

### 자주 발생하는 문제

| 문제 | 해결 방법 |
|------|----------|
| **컨테이너가 시작 안 됨** | `docker-compose logs` 확인 |
| **DB 연결 오류** | `docker-compose restart db` |
| **포트 충돌** | docker-compose.yml에서 포트 변경 |
| **SSL 인증서 발급 실패** | DNS 설정 확인, 80/443 포트 열림 확인 |
| **Git pull 충돌** | `git stash && git pull && git stash pop` |

**자세한 내용**: 각 문서의 트러블슈팅 섹션 참조

---

## 보안 체크리스트

- [ ] SSH 키 파일 권한 400 설정
- [ ] Lightsail 방화벽에서 불필요한 포트 차단
- [ ] phpMyAdmin 포트는 본인 IP만 허용
- [ ] DB 비밀번호 기본값에서 변경
- [ ] HTTPS 강제 리다이렉트 설정
- [ ] 정기 백업 설정 (Cron)
- [ ] Git에 민감 정보 커밋 안 함 (.gitignore)
- [ ] Docker 이미지 정기 업데이트
- [ ] Lightsail 스냅샷 정기 생성

---

## 성능 최적화

### 권장 사항

1. **Nginx Reverse Proxy**
   - 정적 파일 캐싱
   - Gzip 압축
   - HTTP/2 활성화

2. **CDN 사용**
   - Cloudflare
   - AWS CloudFront

3. **데이터베이스 최적화**
   - 쿼리 최적화
   - 인덱스 추가
   - 정기 OPTIMIZE TABLE

4. **리소스 모니터링**
   - `docker stats` - 컨테이너 리소스 사용량
   - `top` / `htop` - 서버 전체 리소스

---

## 백업 전략

### 자동 백업 (Cron)

```bash
# 매일 새벽 3시 DB 백업
0 3 * * * docker exec impexgls_db mysqldump -uroot -pimpexgls_root_password corporate_db | gzip > ~/backups/corporate_db_$(date +\%Y\%m\%d).sql.gz

# 30일 이상 된 백업 자동 삭제
0 4 * * * find ~/backups -name "corporate_db_*.sql.gz" -mtime +30 -delete
```

### Lightsail 스냅샷

- **주기**: 주 1회
- **방법**: Lightsail 콘솔 → Snapshots → Create snapshot
- **보관**: 최근 4주

---

## 비용 예상 (AWS Lightsail)

| 항목 | 월 비용 |
|------|---------|
| **인스턴스** (2GB RAM, 60GB SSD) | ~$10 |
| **Load Balancer** (선택사항) | $18 |
| **스냅샷** (60GB × 4주) | ~$2 |
| **데이터 전송** (500GB 포함) | $0 |
| **총계 (Load Balancer 제외)** | **~$12** |
| **총계 (Load Balancer 포함)** | **~$30** |

**참고**:
- Let's Encrypt 사용 시 Load Balancer 불필요 (무료)
- 실제 비용은 사용량에 따라 변동

---

## 개발 로드맵

### 완료됨 ✅
- [x] 로컬 Docker 환경 구축
- [x] AWS Lightsail 배포
- [x] SSL 인증서 적용 가이드
- [x] 전체 문서화

### 진행 중 🔄
- [ ] CI/CD 파이프라인 구축 (GitHub Actions)
- [ ] Cloudflare CDN 연동
- [ ] 모니터링 시스템 구축 (Prometheus + Grafana)

### 계획 중 📋
- [ ] Redis 캐시 추가
- [ ] Elasticsearch 로그 분석
- [ ] 자동화된 백업 시스템
- [ ] 다중 컨테이너 환경 (웹 서버 스케일링)

---

## 기여하기

프로젝트 개선 아이디어나 버그 리포트는 GitHub Issues를 통해 제출해주세요.

### 개발 가이드라인

1. 새 브랜치 생성: `git checkout -b feature/new-feature`
2. 변경사항 커밋: `git commit -m "Add new feature"`
3. 브랜치 Push: `git push origin feature/new-feature`
4. Pull Request 생성

---

## 라이선스

이 프로젝트는 내부 사용을 위한 비공개 프로젝트입니다.

---

## 문의 및 지원

**프로젝트 관리자**: IMPEX GLS IT Team
**문서 버전**: 1.0
**최종 업데이트**: 2025-11-20
**작성자**: Claude Code Assistant

### 관련 링크

- [AWS Lightsail 공식 문서](https://lightsail.aws.amazon.com/ls/docs/)
- [Docker 공식 문서](https://docs.docker.com/)
- [Let's Encrypt 공식 문서](https://letsencrypt.org/docs/)
- [PHP 공식 문서](https://www.php.net/docs.php)

---

## 빠른 참조

```bash
# 로컬 개발
docker-compose up -d              # 시작
docker-compose down               # 중지
docker-compose restart web        # 웹 서버 재시작
docker-compose logs -f web        # 로그 확인

# 서버 배포
git pull origin main              # 코드 업데이트
docker-compose restart web        # 재시작
docker-compose ps                 # 상태 확인

# DB 백업
docker exec impexgls_db mysqldump -uroot -pimpexgls_root_password corporate_db | gzip > backup.sql.gz

# SSL 인증서 갱신
sudo certbot renew
sudo systemctl reload nginx
```

---

**이 프로젝트는 실제 배포 경험을 바탕으로 작성된 완전한 프로덕션 가이드입니다.**
