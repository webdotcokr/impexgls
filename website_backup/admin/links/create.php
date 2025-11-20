<?php
/**
 * 유용한 링크 등록
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '새 링크 등록';

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
        $category = $_POST['category'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sort_order = intval($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // 유효성 검사
        if (empty($category)) {
            throw new Exception('카테고리를 선택해주세요.');
        }
        if (empty($title)) {
            throw new Exception('사이트명을 입력해주세요.');
        }
        if (empty($url)) {
            throw new Exception('URL을 입력해주세요.');
        }
        
        // URL 검증 및 정리
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            // http:// 또는 https://가 없으면 추가
            if (!preg_match('/^https?:\/\//', $url)) {
                $url = 'https://' . $url;
            }
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new Exception('올바른 URL을 입력해주세요.');
            }
        }
        
        // 아이콘 업로드 처리
        $icon_path = null;
        if (isset($_FILES['icon_file']) && $_FILES['icon_file']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'ico'];
            $upload_result = uploadFile($_FILES['icon_file'], $allowed_types, '/uploads/links/');
            
            if ($upload_result['success']) {
                $icon_path = $upload_result['path'];
            } else {
                throw new Exception($upload_result['message']);
            }
        }
        
        // 삽입
        $stmt = $pdo->prepare("
            INSERT INTO useful_links (
                category, title, url, description, icon_path,
                sort_order, is_active, created_at
            ) VALUES (
                :category, :title, :url, :description, :icon_path,
                :sort_order, :is_active, NOW()
            )
        ");
        
        $stmt->execute([
            ':category' => $category,
            ':title' => $title,
            ':url' => $url,
            ':description' => $description,
            ':icon_path' => $icon_path,
            ':sort_order' => $sort_order,
            ':is_active' => $is_active
        ]);
        
        $link_id = $pdo->lastInsertId();
        logAdminAction('create', 'useful_links', $link_id, 'Link created: ' . $title);
        
        setAlert('success', '링크가 등록되었습니다.');
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        setAlert('error', $e->getMessage());
    }
}

// 카테고리 목록
$categories = [
    '정부기관' => '정부기관',
    '항공사' => '항공사',
    '선사' => '선사',
    '국제기구' => '국제기구',
    '물류협회' => '물류협회',
    '유관기관' => '유관기관',
    '기타' => '기타'
];

// 카테고리별 다음 순서 번호 가져오기
$next_orders = [];
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT category, MAX(sort_order) as max_order FROM useful_links GROUP BY category");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $next_orders[$row['category']] = $row['max_order'] + 10;
    }
} catch (Exception $e) {
    // 기본값 사용
}

$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<div class="max-w-4xl">
    <!-- 상단 버튼 -->
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">새 링크 등록</h1>
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
                        <option value="<?php echo $value; ?>" <?php echo ($_POST['category'] ?? '') == $value ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- 사이트명 -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        사이트명 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="<?php echo e($_POST['title'] ?? ''); ?>"
                           placeholder="예: 관세청"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                
                <!-- URL -->
                <div>
                    <label for="url" class="block text-sm font-medium text-gray-700 mb-2">
                        URL <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="url" 
                           name="url" 
                           value="<?php echo e($_POST['url'] ?? ''); ?>"
                           placeholder="https://www.customs.go.kr"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
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
                              placeholder="사이트에 대한 간단한 설명"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($_POST['description'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- 아이콘 업로드 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">아이콘</h3>
            </div>
            <div class="p-6">
                <div>
                    <label for="icon_file" class="block text-sm font-medium text-gray-700 mb-2">
                        아이콘 파일
                    </label>
                    <input type="file" 
                           id="icon_file" 
                           name="icon_file" 
                           accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">
                        JPG, PNG, GIF, SVG, ICO 형식 지원 (최대 2MB)<br>
                        권장 크기: 32x32px 또는 64x64px
                    </p>
                </div>
                
                <!-- 아이콘 미리보기 -->
                <div id="icon-preview" class="mt-4 hidden">
                    <p class="text-sm font-medium text-gray-700 mb-2">미리보기</p>
                    <div class="flex items-center space-x-4">
                        <div class="bg-gray-100 p-2 rounded">
                            <img id="preview-img" src="" alt="미리보기" class="h-8 w-8 object-contain">
                        </div>
                        <div class="text-sm text-gray-500">
                            실제 크기로 표시됩니다
                        </div>
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
                           value="<?php echo e($_POST['sort_order'] ?? '10'); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">
                        숫자가 작을수록 먼저 표시됩니다
                        <span id="next-order-hint"></span>
                    </p>
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
// 카테고리별 다음 순서 번호
const nextOrders = <?php echo json_encode($next_orders); ?>;

// 카테고리 선택 시 순서 힌트 업데이트
document.getElementById('category').addEventListener('change', function() {
    const category = this.value;
    const hint = document.getElementById('next-order-hint');
    const orderInput = document.getElementById('sort_order');
    
    if (category && nextOrders[category]) {
        hint.textContent = ` (${category} 카테고리의 다음 번호: ${nextOrders[category]})`;
        orderInput.value = nextOrders[category];
    } else {
        hint.textContent = '';
        orderInput.value = '10';
    }
});

// 아이콘 미리보기
document.getElementById('icon_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('icon-preview');
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

// URL 자동 정리
document.getElementById('url').addEventListener('blur', function() {
    let url = this.value.trim();
    if (url && !url.match(/^https?:\/\//)) {
        this.value = 'https://' + url;
    }
});
</script>

<?php include '../includes/footer.php'; ?>