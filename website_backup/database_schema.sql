-- IMPEX GLS 웹사이트 데이터베이스 스키마
-- 데이터베이스 생성
CREATE DATABASE IF NOT EXISTS corporate_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE corporate_db;

-- 1. 메뉴 구조 테이블
CREATE TABLE menus (
    id INT PRIMARY KEY AUTO_INCREMENT,
    parent_id INT DEFAULT 0,
    title VARCHAR(100) NOT NULL,
    url VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. 페이지 메타 정보 테이블
CREATE TABLE page_meta (
    id INT PRIMARY KEY AUTO_INCREMENT,
    page_name VARCHAR(100) UNIQUE,
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords TEXT,
    og_image VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. 고객사 테이블
CREATE TABLE clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    name_en VARCHAR(100),
    logo_path VARCHAR(255),
    website VARCHAR(255),
    description TEXT,
    category VARCHAR(50),
    category_name VARCHAR(50),
    sort_order INT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. 인증서 테이블
CREATE TABLE certificates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    title_en VARCHAR(200),
    issuer VARCHAR(100),
    issue_date DATE,
    expiry_date DATE,
    certificate_number VARCHAR(100),
    image_path VARCHAR(255),
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. 유용한 링크 테이블
CREATE TABLE useful_links (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category VARCHAR(50),
    title VARCHAR(200) NOT NULL,
    url VARCHAR(500) NOT NULL,
    description TEXT,
    icon_path VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. 컨테이너 타입 테이블
CREATE TABLE container_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    type_code VARCHAR(20) NOT NULL,
    type_name VARCHAR(100) NOT NULL,
    external_length DECIMAL(10,2),
    external_width DECIMAL(10,2),
    external_height DECIMAL(10,2),
    internal_length DECIMAL(10,2),
    internal_width DECIMAL(10,2),
    internal_height DECIMAL(10,2),
    door_width DECIMAL(10,2),
    door_height DECIMAL(10,2),
    capacity_cbm DECIMAL(10,2),
    tare_weight DECIMAL(10,2),
    max_cargo_weight DECIMAL(10,2),
    image_path VARCHAR(255),
    description TEXT,
    features JSON,
    sort_order INT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. FAQ 테이블
CREATE TABLE faqs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category VARCHAR(50),
    question TEXT NOT NULL,
    question_en TEXT,
    answer TEXT NOT NULL,
    answer_en TEXT,
    view_count INT DEFAULT 0,
    sort_order INT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. 견적 요청 테이블
CREATE TABLE quote_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    request_type VARCHAR(50),
    company_name VARCHAR(200),
    contact_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    departure_country VARCHAR(100),
    departure_city VARCHAR(100),
    destination_country VARCHAR(100),
    destination_city VARCHAR(100),
    cargo_type VARCHAR(100),
    cargo_weight DECIMAL(10,2),
    cargo_volume DECIMAL(10,2),
    incoterms VARCHAR(20),
    expected_date DATE,
    message TEXT,
    attachments JSON,
    ip_address VARCHAR(45),
    status ENUM('pending', 'processing', 'quoted', 'closed') DEFAULT 'pending',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. 관리자 테이블
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    email VARCHAR(100),
    last_login DATETIME,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. 네트워크 위치 테이블
CREATE TABLE network_locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    location_type ENUM('headquarters', 'usa', 'global') NOT NULL,
    country_code VARCHAR(2),
    country_name VARCHAR(100),
    city VARCHAR(100),
    office_name VARCHAR(200),
    address TEXT,
    phone VARCHAR(50),
    fax VARCHAR(50),
    email VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    services JSON,
    operating_hours JSON,
    sort_order INT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. 관리자 활동 로그 테이블
CREATE TABLE admin_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT,
    action VARCHAR(100),
    table_name VARCHAR(50),
    record_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. 관리자 세션 테이블
CREATE TABLE admin_sessions (
    id VARCHAR(128) PRIMARY KEY,
    admin_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    last_activity INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 인덱스 추가
CREATE INDEX idx_client_category ON clients(category, sort_order);
CREATE INDEX idx_network_location_type ON network_locations(location_type);
CREATE INDEX idx_quote_status ON quote_requests(status, created_at);

-- 초기 관리자 계정 생성 (비밀번호: admin123)
INSERT INTO admins (username, password, name, email, is_active) VALUES 
('admin', '$2y$10$VpXBPNJZCvXhXPJhCiAFxOuYGqNOaFXqhCz7nE3UN0FdGx5mQyKqK', 'Administrator', 'admin@impexgls.com', 1);

-- 샘플 고객사 데이터
INSERT INTO clients (name, name_en, category, category_name, sort_order, is_active) VALUES
('삼성전자', 'Samsung Electronics', 'TECHNOLOGIES & ELECTRONICS', '기술 및 전자', 1, 1),
('LG전자', 'LG Electronics', 'TECHNOLOGIES & ELECTRONICS', '기술 및 전자', 2, 1),
('현대자동차', 'Hyundai Motor Company', 'AUTOMOTIVE & PARTS', '자동차 및 부품', 1, 1),
('SK하이닉스', 'SK Hynix', 'TECHNOLOGIES & ELECTRONICS', '기술 및 전자', 3, 1),
('포스코', 'POSCO', 'MACHINERY', '기계', 1, 1),
('한국항공우주산업', 'Korea Aerospace Industries', 'AEROSPACE & INDUSTRIAL', '항공우주 및 산업', 1, 1),
('아모레퍼시픽', 'Amorepacific', 'FOODS & COSMETICS', '식품 및 화장품', 1, 1),
('CJ제일제당', 'CJ CheilJedang', 'FOODS & COSMETICS', '식품 및 화장품', 2, 1),
('한화케미칼', 'Hanwha Chemical', 'MEDICAL & CHEMICAL', '의료 및 화학', 1, 1),
('두산중공업', 'Doosan Heavy Industries', 'MACHINERY', '기계', 2, 1);

-- 샘플 인증서 데이터
INSERT INTO certificates (title, title_en, issuer, issue_date, expiry_date, certificate_number, sort_order, is_active) VALUES
('ISO 9001:2015 품질경영시스템', 'ISO 9001:2015 Quality Management System', 'TÜV SÜD', '2023-01-15', '2026-01-14', 'QMS-2023-0001', 1, 1),
('ISO 14001:2015 환경경영시스템', 'ISO 14001:2015 Environmental Management System', 'TÜV SÜD', '2023-02-20', '2026-02-19', 'EMS-2023-0002', 2, 1),
('AEO 인증', 'AEO Certification', '관세청', '2022-12-01', '2025-11-30', 'AEO-2022-1234', 3, 1),
('TAPA FSR 인증', 'TAPA FSR Certification', 'TAPA EMEA', '2023-03-10', '2026-03-09', 'TAPA-2023-5678', 4, 1),
('GDP 인증', 'Good Distribution Practice', 'SGS', '2023-04-15', '2026-04-14', 'GDP-2023-9012', 5, 1);

-- 샘플 FAQ 데이터
INSERT INTO faqs (category, question, question_en, answer, answer_en, sort_order, is_active) VALUES
('일반', '견적은 어떻게 요청하나요?', 'How can I request a quote?', '홈페이지의 "Request a Quote" 메뉴를 통해 요청하시거나, 대표전화로 문의주시면 신속하게 견적을 제공해드립니다.', 'You can request a quote through the "Request a Quote" menu on our website or contact us via our main phone number for a quick quote.', 1, 1),
('운송', '항공운송과 해상운송의 차이점은 무엇인가요?', 'What is the difference between air and sea freight?', '항공운송은 빠르지만 비용이 높고, 해상운송은 시간이 오래 걸리지만 대량 화물에 경제적입니다.', 'Air freight is faster but more expensive, while sea freight takes longer but is more economical for large volumes.', 2, 1),
('통관', '통관에 필요한 서류는 무엇인가요?', 'What documents are required for customs clearance?', '상업송장, 포장명세서, 원산지증명서 등이 기본적으로 필요하며, 품목에 따라 추가 서류가 요구될 수 있습니다.', 'Commercial invoice, packing list, and certificate of origin are basically required. Additional documents may be required depending on the items.', 3, 1),
('창고', '창고 보관 서비스도 제공하나요?', 'Do you provide warehouse storage services?', '네, 전국 주요 거점에 창고를 보유하고 있으며, 장단기 보관 서비스를 제공합니다.', 'Yes, we have warehouses at major locations nationwide and provide short and long-term storage services.', 4, 1),
('기타', '화물 추적은 어떻게 하나요?', 'How can I track my shipment?', '고객님께 제공된 추적번호로 홈페이지에서 실시간 조회가 가능합니다.', 'You can track your shipment in real-time on our website using the tracking number provided to you.', 5, 1);

-- 샘플 유용한 링크 데이터
INSERT INTO useful_links (category, title, url, description, sort_order, is_active) VALUES
('정부기관', '관세청', 'https://www.customs.go.kr', '대한민국 관세청 공식 웹사이트', 1, 1),
('정부기관', '한국무역협회', 'https://www.kita.net', '무역정보 및 통계 제공', 2, 1),
('항공사', '대한항공 화물', 'https://cargo.koreanair.com', '대한항공 화물 서비스', 3, 1),
('항공사', '아시아나항공 화물', 'https://cargo.asiana.com', '아시아나항공 화물 서비스', 4, 1),
('선사', 'HMM', 'https://www.hmm21.com', '현대상선 화물 추적 및 스케줄', 5, 1),
('선사', 'MSC', 'https://www.msc.com', 'MSC 선사 서비스', 6, 1),
('국제기구', 'IATA', 'https://www.iata.org', '국제항공운송협회', 7, 1),
('국제기구', 'FIATA', 'https://fiata.org', '국제화물운송업자협회연맹', 8, 1);

-- 샘플 컨테이너 타입 데이터
INSERT INTO container_types (type_code, type_name, external_length, external_width, external_height, internal_length, internal_width, internal_height, door_width, door_height, capacity_cbm, tare_weight, max_cargo_weight, sort_order, is_active) VALUES
('20GP', '20ft General Purpose', 6.06, 2.44, 2.59, 5.90, 2.35, 2.39, 2.34, 2.28, 33.2, 2300, 28180, 1, 1),
('40GP', '40ft General Purpose', 12.19, 2.44, 2.59, 12.03, 2.35, 2.39, 2.34, 2.28, 67.7, 3750, 26730, 2, 1),
('40HC', '40ft High Cube', 12.19, 2.44, 2.90, 12.03, 2.35, 2.70, 2.34, 2.58, 76.4, 3900, 26580, 3, 1),
('20RF', '20ft Reefer', 6.06, 2.44, 2.59, 5.44, 2.29, 2.27, 2.29, 2.26, 28.3, 3080, 27400, 4, 1),
('40RF', '40ft Reefer', 12.19, 2.44, 2.59, 11.58, 2.29, 2.25, 2.29, 2.26, 59.3, 4480, 26000, 5, 1);

-- 샘플 네트워크 위치 데이터
INSERT INTO network_locations (location_type, country_code, country_name, city, office_name, address, phone, email, latitude, longitude, sort_order, is_active) VALUES
('headquarters', 'US', 'United States', 'Chicago', 'Chicago Corporate Headquarters', '2475 Touhy Avenue Suite 100 Elk Grove Village, IL 60007', '(630) 227-9300', 'hq@impexgls.com', 41.9742, -87.9073, 1, 1),
('usa', 'US', 'United States', 'Los Angeles', 'Los Angeles Branch', '123 Main St, Los Angeles, CA 90001', '(213) 123-4567', 'la@impexgls.com', 34.0522, -118.2437, 1, 1),
('usa', 'US', 'United States', 'New York', 'New York Branch', '456 Broadway, New York, NY 10013', '(212) 234-5678', 'ny@impexgls.com', 40.7128, -74.0060, 2, 1),
('global', 'KR', 'South Korea', 'Seoul', 'Seoul Office', '서울특별시 강남구 테헤란로 123', '+82-2-1234-5678', 'seoul@impexgls.com', 37.5665, 126.9780, 1, 1),
('global', 'CN', 'China', 'Shanghai', 'Shanghai Office', 'Pudong New Area, Shanghai', '+86-21-1234-5678', 'shanghai@impexgls.com', 31.2304, 121.4737, 2, 1),
('global', 'JP', 'Japan', 'Tokyo', 'Tokyo Office', 'Minato-ku, Tokyo', '+81-3-1234-5678', 'tokyo@impexgls.com', 35.6762, 139.6503, 3, 1);