# IMPEX Global Logistics Services Website

IMPEX GLS 기업 웹사이트 프로젝트입니다.

## 프로젝트 구조

```
corporate-website/
├── admin/              # 관리자 페이지
├── assets/             # CSS, JS, 이미지 등 정적 파일
├── components/         # 재사용 가능한 컴포넌트
├── config/             # 설정 파일
├── database/           # 데이터베이스 스키마 및 마이그레이션
├── includes/           # 공통 include 파일 (header, footer 등)
├── pages/              # 웹사이트 페이지들
│   ├── about/          # 회사소개
│   ├── service/        # 서비스
│   ├── networks/       # 네트워크
│   ├── resources/      # 자료실
│   ├── support/        # 고객지원
│   └── policies/       # 정책 페이지
└── index.php           # 메인 페이지
```

## 설치 방법

1. 프로젝트 클론
```bash
git clone https://github.com/webdotcokr/impex_gls.git
cd impex_gls
```

2. 데이터베이스 설정
   - MySQL 데이터베이스 생성
   - `database_schema.sql` 파일 실행
   - `database/` 폴더 내의 추가 SQL 파일들 실행

3. 설정 파일 생성
```bash
cp config/db-config.example.php config/db-config.php
```
   - `config/db-config.php` 파일을 열어 데이터베이스 정보 입력

4. 웹서버 설정
   - DocumentRoot를 프로젝트 폴더로 설정
   - PHP 7.4 이상 필요

## 관리자 페이지

- URL: `/admin`
- 기본 계정: admin / admin123
- 첫 로그인 후 반드시 비밀번호 변경

## 주요 기능

### 프론트엔드
- 반응형 디자인
- 다국어 지원 (한국어/영어)
- 서비스 소개
- 네트워크 위치 정보
- 문의하기 기능
- FAQ
- 리소스 (Incoterms, Container Types)

### 관리자
- 사이트 설정 관리
- 문의 관리
- 뉴스 관리
- FAQ 관리
- 인증서 관리
- 클라이언트 관리
- 네트워크 위치 관리
- 유용한 링크 관리

## 기술 스택

- PHP 7.4+
- MySQL 5.7+
- Tailwind CSS
- JavaScript (Vanilla)
- Font Awesome Icons

## 라이선스

Copyright © 2024 IMPEX Global Logistics Services. All rights reserved.