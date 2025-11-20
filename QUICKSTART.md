# IMPEXGLS Docker 로컬 환경 - 빠른 시작 가이드

## 현재 상태 ✅

모든 Docker 컨테이너가 정상 실행 중입니다!

| 서비스 | 상태 | 접속 URL |
|--------|------|---------|
| **웹사이트** | ✅ Running | http://localhost:8082 |
| **phpMyAdmin** | ✅ Running | http://localhost:8083 |
| **MySQL DB** | ✅ Running | localhost:3308 |

---

## 즉시 확인하기

### 1. 웹사이트 접속
```
브라우저에서: http://localhost:8082
```

### 2. phpMyAdmin 접속
```
URL: http://localhost:8083

로그인 정보:
- 서버: db
- 사용자: root
- 비밀번호: impexgls_root_password
```

### 3. 데이터베이스 정보
- **DB명**: corporate_db
- **테이블 수**: 14개
- **포트**: 3308

---

## 일반적인 작업

### 컨테이너 관리

```bash
# 컨테이너 상태 확인
docker-compose ps

# 컨테이너 중지
docker-compose down

# 컨테이너 시작
docker-compose up -d

# 로그 확인 (실시간)
docker-compose logs -f web
```

### 코드 수정 후 반영

```bash
# website_backup/ 폴더의 파일 수정
# 예: website_backup/index.php, website_backup/pages/about.php 등

# 웹 서버 재시작
docker-compose restart web

# 브라우저에서 확인
# http://localhost:8082
```

**참고**: 파일은 실시간 동기화되므로 대부분의 경우 재시작 없이 새로고침만 하면 됩니다.

---

## 데이터베이스 작업

### DB 백업

```bash
# 백업 파일 생성
docker exec impexgls_db mysqldump -uroot -pimpexgls_root_password corporate_db \
  | gzip > backup_$(date +%Y%m%d).sql.gz
```

### DB 복원

```bash
# 압축 해제 후 복원
gunzip < backup.sql.gz | docker exec -i impexgls_db mysql -uroot -pimpexgls_root_password corporate_db
```

---

## 트러블슈팅

### 웹사이트가 안 열려요

```bash
# 1. 컨테이너 상태 확인
docker-compose ps

# 2. 로그 확인
docker-compose logs web

# 3. 재시작
docker-compose restart web
```

### DB 연결 오류

```bash
# DB 컨테이너 재시작
docker-compose restart db

# 로그 확인
docker-compose logs db
```

### 포트 충돌

docker-compose.yml에서 포트 번호를 변경하세요:
```yaml
web:
  ports:
    - "8090:80"  # 8082 → 8090으로 변경
```

---

## 프로젝트 구조

```
impexgls/
├── Dockerfile                    # 웹 서버 이미지
├── docker-compose.yml            # 컨테이너 설정
├── website_backup/               # 웹사이트 소스 (여기서 개발)
│   ├── admin/                   # 관리자 페이지
│   ├── assets/                  # CSS, JS, 이미지
│   ├── config/                  # 설정 파일
│   ├── includes/                # 공통 함수
│   ├── pages/                   # 페이지 파일
│   └── index.php                # 메인 페이지
└── docker/
    └── mysql/
        └── init/
            └── corporate_db.sql  # DB 초기화 SQL
```

---

## 다음 단계

### AWS Lightsail 배포 준비

1. ✅ 로컬 Docker 환경 구축 완료
2. ⬜ Git 저장소 생성 및 Push
3. ⬜ AWS Lightsail 인스턴스 생성
4. ⬜ Lightsail에 Docker 배포
5. ⬜ SSL 인증서 적용 및 도메인 연결

자세한 배포 가이드는 다음 파일을 참조하세요:
- [AWS_Lightsail_Docker 배포 방식.md](./AWS_Lightsail_Docker%20배포%20방식.md) - 서버 배포
- [SSL_DOMAIN_SETUP.md](./SSL_DOMAIN_SETUP.md) - SSL 및 도메인 연결
- [README_LOCAL_DOCKER.md](./README_LOCAL_DOCKER.md) - 로컬 개발 환경

---

## 빠른 참조

```bash
# 시작
docker-compose up -d

# 중지
docker-compose down

# 재시작
docker-compose restart

# 로그 확인
docker-compose logs -f

# DB 백업
docker exec impexgls_db mysqldump -uroot -pimpexgls_root_password corporate_db > backup.sql
```

---

## 문의

문제가 발생하면 다음을 확인하세요:

1. **컨테이너 상태**: `docker-compose ps`
2. **로그 확인**: `docker-compose logs -f`
3. **전체 재시작**: `docker-compose restart`

**작성일**: 2025-11-20
