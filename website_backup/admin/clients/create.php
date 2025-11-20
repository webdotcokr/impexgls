<?php
/**
 * 클라이언트 등록
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '새 클라이언트 등록';

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    try {
        $pdo = getDBConnection();
        
        // 데이터 준비
        $name = trim($_POST['name'] ?? '');
        $name_en = trim($_POST['name_en'] ?? '');
        $category = $_POST['category'] ?? '';
        $category_name = trim($_POST['category_name'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sort_order = intval($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // 유효성 검사
        if (empty($name)) {
            throw new Exception('회사명을 입력해주세요.');
        }
        if (empty($category)) {
            throw new Exception('카테고리를 선택해주세요.');
        }
        
        // 웹사이트 URL 검증 및 정리
        if ($website && !filter_var($website, FILTER_VALIDATE_URL)) {
            // http:// 또는 https://가 없으면 추가
            if (!preg_match('/^https?:\/\//', $website)) {
                $website = 'https://' . $website;
            }
            if (!filter_var($website, FILTER_VALIDATE_URL)) {
                throw new Exception('올바른 웹사이트 주소를 입력해주세요.');
            }
        }
        
        // 로고 업로드 처리
        $logo_path = null;
        if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
            $upload_result = uploadFile($_FILES['logo_file'], $allowed_types, '/uploads/clients/');
            
            if ($upload_result['success']) {
                $logo_path = $upload_result['path'];
            } else {
                throw new Exception($upload_result['message']);
            }
        }
        
        // 삽입
        $stmt = $pdo->prepare("
            INSERT INTO clients (
                name, name_en, logo_path, website, description,
                category, category_name, sort_order, is_active, created_at
            ) VALUES (
                :name, :name_en, :logo_path, :website, :description,
                :category, :category_name, :sort_order, :is_active, NOW()
            )
        ");
        
        $stmt->execute([
            ':name' => $name,
            ':name_en' => $name_en,
            ':logo_path' => $logo_path,
            ':website' => $website,
            ':description' => $description,
            ':category' => $category,
            ':category_name' => $category_name,
            ':sort_order' => $sort_order,
            ':is_active' => $is_active
        ]);
        
        $client_id = $pdo->lastInsertId();
        logAdminAction('create', 'clients', $client_id, 'Client created: ' . $name);
        
        setAlert('success', '클라이언트가 등록되었습니다.');
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        setAlert('error', $e->getMessage());
    }
}

// 카테고리 목록
$categories = [
    'TECHNOLOGIES & ELECTRONICS' => '기술 및 전자',
    'AUTOMOTIVE & PARTS' => '자동차 및 부품',
    'MACHINERY' => '기계',
    'AEROSPACE & INDUSTRIAL' => '항공우주 및 산업',
    'FOODS & COSMETICS' => '식품 및 화장품',
    'MEDICAL & CHEMICAL' => '의료 및 화학',
    'TEXTILES & FASHION' => '섬유 및 패션',
    'CONSTRUCTION & MATERIALS' => '건설 및 자재',
    'ENERGY & RESOURCES' => '에너지 및 자원',
    'OTHERS' => '기타'
];

// 다음 순서 번호 가져오기
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT MAX(sort_order) FROM clients");
    $next_order = $stmt->fetchColumn() + 10;
} catch (Exception $e) {
    $next_order = 10;
}

$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<div class="max-w-4xl">
    <!-- 상단 버튼 -->
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">새 클라이언트 등록</h1>
        <a href="index.php" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>목록으로
        </a>
    </div>
    
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
        
        <!-- 기본 정보 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">기본 정보</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- 회사명 (한국어) -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        회사명 (한국어) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="<?php echo e($_POST['name'] ?? ''); ?>"
                           placeholder="예: 삼성전자"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                
                <!-- 회사명 (영어) -->
                <div>
                    <label for="name_en" class="block text-sm font-medium text-gray-700 mb-2">
                        회사명 (영어)
                    </label>
                    <input type="text" 
                           id="name_en" 
                           name="name_en" 
                           value="<?php echo e($_POST['name_en'] ?? ''); ?>"
                           placeholder="예: Samsung Electronics"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <!-- 카테고리 -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                        카테고리 <span class="text-red-500">*</span>
                    </label>
                    <select id="category" 
                            name="category"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required>
                        <option value="">선택하세요</option>
                        <?php foreach ($categories as $value => $label): ?>
                        <option value="<?php echo $value; ?>" 
                                data-name="<?php echo $label; ?>"
                                <?php echo ($_POST['category'] ?? '') == $value ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" id="category_name" name="category_name" value="<?php echo e($_POST['category_name'] ?? ''); ?>">
                </div>
                
                <!-- 웹사이트 -->
                <div>
                    <label for="website" class="block text-sm font-medium text-gray-700 mb-2">
                        웹사이트
                    </label>
                    <input type="text" 
                           id="website" 
                           name="website" 
                           value="<?php echo e($_POST['website'] ?? ''); ?>"
                           placeholder="https://www.example.com"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">http:// 또는 https://를 포함한 전체 URL을 입력하세요</p>
                </div>
                
                <!-- 설명 -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        설명
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              placeholder="회사에 대한 간단한 설명"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($_POST['description'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- 로고 업로드 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">회사 로고</h3>
            </div>
            <div class="p-6">
                <div>
                    <label for="logo_file" class="block text-sm font-medium text-gray-700 mb-2">
                        로고 파일
                    </label>
                    <input type="file" 
                           id="logo_file" 
                           name="logo_file" 
                           accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">
                        JPG, PNG, GIF, SVG 형식 지원 (최대 5MB)<br>
                        권장 크기: 200x100px (가로형 로고)
                    </p>
                </div>
                
                <!-- 로고 미리보기 -->
                <div id="logo-preview" class="mt-4 hidden">
                    <p class="text-sm font-medium text-gray-700 mb-2">미리보기</p>
                    <div class="bg-gray-100 p-4 rounded">
                        <img id="preview-img" src="" alt="미리보기" class="max-h-20 mx-auto">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 게시 옵션 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">게시 옵션</h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                        정렬 순서
                    </label>
                    <input type="number" 
                           id="sort_order" 
                           name="sort_order" 
                           value="<?php echo e($_POST['sort_order'] ?? $next_order); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">숫자가 작을수록 먼저 표시됩니다</p>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="is_active" 
                           name="is_active" 
                           value="1"
                           <?php echo ($_POST['is_active'] ?? '1') ? 'checked' : ''; ?>
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
// 카테고리 선택 시 한글명 자동 입력
document.getElementById('category').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const categoryName = selectedOption.getAttribute('data-name') || '';
    document.getElementById('category_name').value = categoryName;
});

// 로고 미리보기
document.getElementById('logo_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('logo-preview');
    const previewImg = document.getElementById('preview-img');
    
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
    }
});
</script>

<?php include '../includes/footer.php'; ?>