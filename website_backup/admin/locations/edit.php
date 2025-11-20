<?php
/**
 * 네트워크 위치 수정
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '위치 수정';

// ID 확인
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

// 위치 조회
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM network_locations WHERE id = ?");
    $stmt->execute([$id]);
    $location = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$location) {
        setAlert('error', '위치를 찾을 수 없습니다.');
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    error_log("Location error: " . $e->getMessage());
    setAlert('error', '데이터 조회에 실패했습니다.');
    header('Location: index.php');
    exit;
}

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
        header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $id);
        exit;
    }
    
    try {
        // 데이터 준비
        $location_type = $_POST['location_type'] ?? '';
        $office_name = trim($_POST['office_name'] ?? '');
        $country_code = trim($_POST['country_code'] ?? '');
        $country_name = trim($_POST['country_name'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $fax = trim($_POST['fax'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $latitude = $_POST['latitude'] ?? null;
        $longitude = $_POST['longitude'] ?? null;
        $google_map_link = trim($_POST['google_map_link'] ?? '');
        $business_hours = trim($_POST['business_hours'] ?? '');
        $services = trim($_POST['services'] ?? '');
        $sort_order = intval($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // 유효성 검사
        if (empty($location_type)) {
            throw new Exception('위치 타입을 선택해주세요.');
        }
        if (empty($office_name)) {
            throw new Exception('사무소명을 입력해주세요.');
        }
        if (empty($country_name)) {
            throw new Exception('국가명을 입력해주세요.');
        }
        if (empty($city)) {
            throw new Exception('도시명을 입력해주세요.');
        }
        
        // 이메일 검증
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('올바른 이메일 주소를 입력해주세요.');
        }
        
        // 웹사이트 URL 검증 및 정리
        if ($website && !filter_var($website, FILTER_VALIDATE_URL)) {
            if (!preg_match('/^https?:\/\//', $website)) {
                $website = 'https://' . $website;
            }
            if (!filter_var($website, FILTER_VALIDATE_URL)) {
                throw new Exception('올바른 웹사이트 주소를 입력해주세요.');
            }
        }
        
        // 좌표 검증
        if ($latitude !== null && $latitude !== '') {
            $latitude = floatval($latitude);
            if ($latitude < -90 || $latitude > 90) {
                throw new Exception('위도는 -90에서 90 사이의 값이어야 합니다.');
            }
        } else {
            $latitude = null;
        }
        
        if ($longitude !== null && $longitude !== '') {
            $longitude = floatval($longitude);
            if ($longitude < -180 || $longitude > 180) {
                throw new Exception('경도는 -180에서 180 사이의 값이어야 합니다.');
            }
        } else {
            $longitude = null;
        }
        
        // 업데이트
        $stmt = $pdo->prepare("
            UPDATE network_locations SET
                location_type = :location_type,
                office_name = :office_name,
                country_code = :country_code,
                country_name = :country_name,
                city = :city,
                address = :address,
                phone = :phone,
                fax = :fax,
                email = :email,
                website = :website,
                latitude = :latitude,
                longitude = :longitude,
                google_map_link = :google_map_link,
                business_hours = :business_hours,
                services = :services,
                sort_order = :sort_order,
                is_active = :is_active
            WHERE id = :id
        ");
        
        $stmt->execute([
            ':location_type' => $location_type,
            ':office_name' => $office_name,
            ':country_code' => $country_code,
            ':country_name' => $country_name,
            ':city' => $city,
            ':address' => $address,
            ':phone' => $phone ?: null,
            ':fax' => $fax ?: null,
            ':email' => $email ?: null,
            ':website' => $website ?: null,
            ':latitude' => $latitude,
            ':longitude' => $longitude,
            ':google_map_link' => $google_map_link ?: null,
            ':business_hours' => $business_hours ?: null,
            ':services' => $services ?: null,
            ':sort_order' => $sort_order,
            ':is_active' => $is_active,
            ':id' => $id
        ]);
        
        logAdminAction('update', 'network_locations', $id, 'Location updated: ' . $office_name);
        
        setAlert('success', '위치가 수정되었습니다.');
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        setAlert('error', $e->getMessage());
    }
}

// 위치 타입 정의
$location_types = [
    'headquarters' => '본사',
    'usa' => '미국 지사',
    'global' => '글로벌 네트워크'
];

// 국가 코드 목록 (주요 국가)
$countries = [
    'KR' => '대한민국',
    'US' => '미국',
    'CN' => '중국',
    'JP' => '일본',
    'VN' => '베트남',
    'TH' => '태국',
    'SG' => '싱가포르',
    'MY' => '말레이시아',
    'ID' => '인도네시아',
    'PH' => '필리핀',
    'IN' => '인도',
    'DE' => '독일',
    'GB' => '영국',
    'FR' => '프랑스',
    'IT' => '이탈리아',
    'ES' => '스페인',
    'NL' => '네덜란드',
    'BE' => '벨기에',
    'PL' => '폴란드',
    'CZ' => '체코',
    'AU' => '호주',
    'NZ' => '뉴질랜드',
    'CA' => '캐나다',
    'MX' => '멕시코',
    'BR' => '브라질',
    'AR' => '아르헨티나',
    'CL' => '칠레',
    'AE' => '아랍에미리트',
    'SA' => '사우디아라비아',
    'TR' => '터키',
    'EG' => '이집트',
    'ZA' => '남아프리카공화국'
];

$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<div class="max-w-4xl">
    <!-- 상단 버튼 -->
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">위치 수정</h1>
        <a href="index.php" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>목록으로
        </a>
    </div>
    
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
        
        <!-- 기본 정보 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">기본 정보</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- 위치 타입 -->
                <div>
                    <label for="location_type" class="block text-sm font-medium text-gray-700 mb-2">
                        위치 타입 <span class="text-red-500">*</span>
                    </label>
                    <select id="location_type" 
                            name="location_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                        <option value="">선택하세요</option>
                        <?php foreach ($location_types as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo $location['location_type'] == $value ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- 사무소명 -->
                <div>
                    <label for="office_name" class="block text-sm font-medium text-gray-700 mb-2">
                        사무소명 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="office_name" 
                           name="office_name" 
                           value="<?php echo e($location['office_name']); ?>"
                           placeholder="예: IMPEX GLS 본사"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <!-- 국가 코드 -->
                    <div>
                        <label for="country_code" class="block text-sm font-medium text-gray-700 mb-2">
                            국가 코드
                        </label>
                        <select id="country_code" 
                                name="country_code"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">직접 입력</option>
                            <?php foreach ($countries as $code => $name): ?>
                            <option value="<?php echo $code; ?>" 
                                    data-name="<?php echo $name; ?>"
                                    <?php echo $location['country_code'] == $code ? 'selected' : ''; ?>>
                                <?php echo $code; ?> - <?php echo $name; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- 국가명 -->
                    <div>
                        <label for="country_name" class="block text-sm font-medium text-gray-700 mb-2">
                            국가명 <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="country_name" 
                               name="country_name" 
                               value="<?php echo e($location['country_name']); ?>"
                               placeholder="예: 대한민국"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>
                </div>
                
                <!-- 도시 -->
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                        도시 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="city" 
                           name="city" 
                           value="<?php echo e($location['city']); ?>"
                           placeholder="예: 서울"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                
                <!-- 주소 -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                        주소
                    </label>
                    <textarea id="address" 
                              name="address" 
                              rows="2"
                              placeholder="상세 주소를 입력하세요"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($location['address']); ?></textarea>
                </div>
                
                <!-- 위치 정보 -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500">등록일</dt>
                            <dd class="mt-1 text-gray-900"><?php echo formatDate($location['created_at']); ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">현재 상태</dt>
                            <dd class="mt-1 text-gray-900">
                                <?php echo $location['is_active'] ? '<span class="text-green-600">활성</span>' : '<span class="text-gray-600">비활성</span>'; ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
        
        <!-- 연락처 정보 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">연락처 정보</h3>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <!-- 전화번호 -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            전화번호
                        </label>
                        <input type="text" 
                               id="phone" 
                               name="phone" 
                               value="<?php echo e($location['phone']); ?>"
                               placeholder="예: +82-2-1234-5678"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <!-- 팩스 -->
                    <div>
                        <label for="fax" class="block text-sm font-medium text-gray-700 mb-2">
                            팩스
                        </label>
                        <input type="text" 
                               id="fax" 
                               name="fax" 
                               value="<?php echo e($location['fax']); ?>"
                               placeholder="예: +82-2-1234-5679"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <!-- 이메일 -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        이메일
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="<?php echo e($location['email']); ?>"
                           placeholder="contact@example.com"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <!-- 웹사이트 -->
                <div>
                    <label for="website" class="block text-sm font-medium text-gray-700 mb-2">
                        웹사이트
                    </label>
                    <input type="text" 
                           id="website" 
                           name="website" 
                           value="<?php echo e($location['website'] ?? ''); ?>"
                           placeholder="https://www.example.com"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">http:// 또는 https://를 포함한 전체 URL을 입력하세요</p>
                </div>
                
                <!-- 영업시간 -->
                <div>
                    <label for="business_hours" class="block text-sm font-medium text-gray-700 mb-2">
                        영업시간
                    </label>
                    <input type="text" 
                           id="business_hours" 
                           name="business_hours" 
                           value="<?php echo e($location['business_hours']); ?>"
                           placeholder="예: 월-금 09:00-18:00"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>
        
        <!-- 위치 정보 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">위치 정보</h3>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <!-- 위도 -->
                    <div>
                        <label for="latitude" class="block text-sm font-medium text-gray-700 mb-2">
                            위도 (Latitude)
                        </label>
                        <input type="number" 
                               id="latitude" 
                               name="latitude" 
                               value="<?php echo $location['latitude']; ?>"
                               step="0.000001"
                               min="-90"
                               max="90"
                               placeholder="예: 37.5665"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <!-- 경도 -->
                    <div>
                        <label for="longitude" class="block text-sm font-medium text-gray-700 mb-2">
                            경도 (Longitude)
                        </label>
                        <input type="number" 
                               id="longitude" 
                               name="longitude" 
                               value="<?php echo $location['longitude']; ?>"
                               step="0.000001"
                               min="-180"
                               max="180"
                               placeholder="예: 126.9780"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <!-- 구글맵 링크 -->
                <div>
                    <label for="google_map_link" class="block text-sm font-medium text-gray-700 mb-2">
                        구글맵 링크
                    </label>
                    <input type="text" 
                           id="google_map_link" 
                           name="google_map_link" 
                           value="<?php echo e($location['google_map_link'] ?? ''); ?>"
                           placeholder="https://maps.google.com/..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">
                        구글맵에서 위치를 검색한 후 공유 링크를 복사하여 붙여넣으세요
                    </p>
                </div>
                
                <!-- 지도 미리보기 -->
                <div id="map-preview" class="<?php echo ($location['latitude'] && $location['longitude']) ? '' : 'hidden'; ?>">
                    <p class="text-sm font-medium text-gray-700 mb-2">위치 미리보기</p>
                    <div class="bg-gray-100 rounded-md p-4 text-center">
                        <a id="map-link" 
                           href="https://maps.google.com/?q=<?php echo $location['latitude']; ?>,<?php echo $location['longitude']; ?>" 
                           target="_blank" 
                           class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            구글맵에서 확인
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 추가 정보 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">추가 정보</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- 서비스 -->
                <div>
                    <label for="services" class="block text-sm font-medium text-gray-700 mb-2">
                        제공 서비스
                    </label>
                    <textarea id="services" 
                              name="services" 
                              rows="3"
                              placeholder="이 위치에서 제공하는 서비스를 설명하세요"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($location['services']); ?></textarea>
                </div>
                
                <!-- 정렬 순서 -->
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                        정렬 순서
                    </label>
                    <input type="number" 
                           id="sort_order" 
                           name="sort_order" 
                           value="<?php echo e($location['sort_order']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">숫자가 작을수록 먼저 표시됩니다</p>
                </div>
                
                <!-- 활성화 -->
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="is_active" 
                           name="is_active" 
                           value="1"
                           <?php echo $location['is_active'] ? 'checked' : ''; ?>
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        활성화 (체크하면 프론트엔드에 표시됩니다)
                    </label>
                </div>
            </div>
        </div>
        
        <!-- 저장 버튼 -->
        <div class="flex justify-end space-x-3">
            <a href="index.php" 
               class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 transition duration-200">
                취소
            </a>
            <button type="submit" 
                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                <i class="fas fa-save mr-2"></i>저장
            </button>
        </div>
    </form>
</div>

<script>
// 국가 선택 시 국가명 자동 입력
document.getElementById('country_code').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const countryName = selectedOption.getAttribute('data-name');
    if (countryName) {
        document.getElementById('country_name').value = countryName;
    }
});

// 좌표 입력 시 지도 미리보기 업데이트
function updateMapPreview() {
    const lat = document.getElementById('latitude').value;
    const lng = document.getElementById('longitude').value;
    const preview = document.getElementById('map-preview');
    const link = document.getElementById('map-link');
    
    if (lat && lng) {
        link.href = `https://maps.google.com/?q=${lat},${lng}`;
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
}

document.getElementById('latitude').addEventListener('input', updateMapPreview);
document.getElementById('longitude').addEventListener('input', updateMapPreview);

// 웹사이트 URL 자동 정리
document.getElementById('website').addEventListener('blur', function() {
    let url = this.value.trim();
    if (url && !url.match(/^https?:\/\//)) {
        this.value = 'https://' + url;
    }
});
</script>

<?php include '../includes/footer.php'; ?>