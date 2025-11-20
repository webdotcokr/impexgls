<?php
/**
 * 컨테이너 타입 수정
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '컨테이너 타입 수정';

// ID 확인
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

// 컨테이너 조회
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM container_types WHERE id = ?");
    $stmt->execute([$id]);
    $container = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$container) {
        setAlert('error', '컨테이너 타입을 찾을 수 없습니다.');
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    error_log("Container error: " . $e->getMessage());
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
        $name = trim($_POST['name'] ?? '');
        $name_ko = trim($_POST['name_ko'] ?? '');
        $category = $_POST['category'] ?? '';
        $size_feet = intval($_POST['size_feet'] ?? 0);
        $max_weight_kg = intval($_POST['max_weight_kg'] ?? 0);
        $tare_weight_kg = intval($_POST['tare_weight_kg'] ?? 0);
        $volume_cbm = floatval($_POST['volume_cbm'] ?? 0);
        $internal_length_m = floatval($_POST['internal_length_m'] ?? 0);
        $internal_width_m = floatval($_POST['internal_width_m'] ?? 0);
        $internal_height_m = floatval($_POST['internal_height_m'] ?? 0);
        $door_width_m = floatval($_POST['door_width_m'] ?? 0);
        $door_height_m = floatval($_POST['door_height_m'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $features = trim($_POST['features'] ?? '');
        $suitable_for = trim($_POST['suitable_for'] ?? '');
        $sort_order = intval($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // 유효성 검사
        if (empty($name)) {
            throw new Exception('컨테이너 타입명을 입력해주세요.');
        }
        if (empty($category)) {
            throw new Exception('카테고리를 선택해주세요.');
        }
        if ($size_feet <= 0) {
            throw new Exception('컨테이너 크기를 입력해주세요.');
        }
        
        // 이미지 업로드 처리
        $image_path = $container['image_path'];
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            // 기존 이미지 삭제
            if ($image_path && file_exists(PROJECT_ROOT . $image_path)) {
                unlink(PROJECT_ROOT . $image_path);
            }
            
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
            $upload_result = uploadFile($_FILES['image_file'], $allowed_types, '/uploads/containers/');
            
            if ($upload_result['success']) {
                $image_path = $upload_result['path'];
            } else {
                throw new Exception($upload_result['message']);
            }
        }
        
        // 이미지 삭제 처리
        if (isset($_POST['delete_image']) && $_POST['delete_image'] === '1') {
            if ($image_path && file_exists(PROJECT_ROOT . $image_path)) {
                unlink(PROJECT_ROOT . $image_path);
            }
            $image_path = null;
        }
        
        // 업데이트
        $stmt = $pdo->prepare("
            UPDATE container_types SET
                name = :name,
                name_ko = :name_ko,
                category = :category,
                size_feet = :size_feet,
                max_weight_kg = :max_weight_kg,
                tare_weight_kg = :tare_weight_kg,
                volume_cbm = :volume_cbm,
                internal_length_m = :internal_length_m,
                internal_width_m = :internal_width_m,
                internal_height_m = :internal_height_m,
                door_width_m = :door_width_m,
                door_height_m = :door_height_m,
                image_path = :image_path,
                description = :description,
                features = :features,
                suitable_for = :suitable_for,
                sort_order = :sort_order,
                is_active = :is_active
            WHERE id = :id
        ");
        
        $stmt->execute([
            ':name' => $name,
            ':name_ko' => $name_ko ?: null,
            ':category' => $category,
            ':size_feet' => $size_feet,
            ':max_weight_kg' => $max_weight_kg ?: null,
            ':tare_weight_kg' => $tare_weight_kg ?: null,
            ':volume_cbm' => $volume_cbm ?: null,
            ':internal_length_m' => $internal_length_m ?: null,
            ':internal_width_m' => $internal_width_m ?: null,
            ':internal_height_m' => $internal_height_m ?: null,
            ':door_width_m' => $door_width_m ?: null,
            ':door_height_m' => $door_height_m ?: null,
            ':image_path' => $image_path,
            ':description' => $description ?: null,
            ':features' => $features ?: null,
            ':suitable_for' => $suitable_for ?: null,
            ':sort_order' => $sort_order,
            ':is_active' => $is_active,
            ':id' => $id
        ]);
        
        logAdminAction('update', 'container_types', $id, 'Container type updated: ' . $name);
        
        setAlert('success', '컨테이너 타입이 수정되었습니다.');
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        setAlert('error', $e->getMessage());
    }
}

// 카테고리 정의
$categories = [
    'Dry' => 'Dry Container (일반 컨테이너)',
    'Reefer' => 'Reefer Container (냉동/냉장 컨테이너)',
    'Special' => 'Special Container (특수 컨테이너)'
];

// 일반적인 컨테이너 크기
$common_sizes = [
    20 => "20' (Twenty-foot)",
    40 => "40' (Forty-foot)",
    45 => "45' (Forty-five-foot)"
];

$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<div class="max-w-4xl">
    <!-- 상단 버튼 -->
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">컨테이너 타입 수정</h1>
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
                <div class="grid grid-cols-2 gap-4">
                    <!-- 컨테이너 타입명 (영어) -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            컨테이너 타입명 (영어) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="<?php echo e($container['name']); ?>"
                               placeholder="예: 20' Standard Dry"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>
                    
                    <!-- 컨테이너 타입명 (한글) -->
                    <div>
                        <label for="name_ko" class="block text-sm font-medium text-gray-700 mb-2">
                            컨테이너 타입명 (한글)
                        </label>
                        <input type="text" 
                               id="name_ko" 
                               name="name_ko" 
                               value="<?php echo e($container['name_ko']); ?>"
                               placeholder="예: 20피트 표준 드라이"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
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
                            <option value="<?php echo $value; ?>" <?php echo $container['category'] == $value ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- 크기 -->
                    <div>
                        <label for="size_feet" class="block text-sm font-medium text-gray-700 mb-2">
                            크기 (feet) <span class="text-red-500">*</span>
                        </label>
                        <select id="size_feet" 
                                name="size_feet"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                            <option value="">선택하세요</option>
                            <?php foreach ($common_sizes as $size => $label): ?>
                            <option value="<?php echo $size; ?>" <?php echo $container['size_feet'] == $size ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                            <?php endforeach; ?>
                            <option value="10" <?php echo $container['size_feet'] == '10' ? 'selected' : ''; ?>>10' (Ten-foot)</option>
                            <option value="30" <?php echo $container['size_feet'] == '30' ? 'selected' : ''; ?>>30' (Thirty-foot)</option>
                            <?php if (!in_array($container['size_feet'], [10, 20, 30, 40, 45])): ?>
                            <option value="<?php echo $container['size_feet']; ?>" selected>
                                <?php echo $container['size_feet']; ?>' (Custom)
                            </option>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <!-- 설명 -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        설명
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              placeholder="컨테이너에 대한 간단한 설명"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($container['description']); ?></textarea>
                </div>
                
                <!-- 컨테이너 정보 -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500">등록일</dt>
                            <dd class="mt-1 text-gray-900"><?php echo formatDate($container['created_at']); ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">현재 상태</dt>
                            <dd class="mt-1 text-gray-900">
                                <?php echo $container['is_active'] ? '<span class="text-green-600">활성</span>' : '<span class="text-gray-600">비활성</span>'; ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
        
        <!-- 사양 정보 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">사양 정보</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- 무게 정보 -->
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label for="max_weight_kg" class="block text-sm font-medium text-gray-700 mb-2">
                            최대 적재 중량 (kg)
                        </label>
                        <input type="number" 
                               id="max_weight_kg" 
                               name="max_weight_kg" 
                               value="<?php echo $container['max_weight_kg']; ?>"
                               placeholder="예: 28200"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="tare_weight_kg" class="block text-sm font-medium text-gray-700 mb-2">
                            컨테이너 자중 (kg)
                        </label>
                        <input type="number" 
                               id="tare_weight_kg" 
                               name="tare_weight_kg" 
                               value="<?php echo $container['tare_weight_kg']; ?>"
                               placeholder="예: 2300"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="volume_cbm" class="block text-sm font-medium text-gray-700 mb-2">
                            용적 (CBM)
                        </label>
                        <input type="number" 
                               id="volume_cbm" 
                               name="volume_cbm" 
                               value="<?php echo $container['volume_cbm']; ?>"
                               step="0.1"
                               placeholder="예: 33.2"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <!-- 내부 치수 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">내부 치수 (m)</label>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <input type="number" 
                                   name="internal_length_m" 
                                   value="<?php echo $container['internal_length_m']; ?>"
                                   step="0.01"
                                   placeholder="길이 (예: 5.90)"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <input type="number" 
                                   name="internal_width_m" 
                                   value="<?php echo $container['internal_width_m']; ?>"
                                   step="0.01"
                                   placeholder="너비 (예: 2.35)"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <input type="number" 
                                   name="internal_height_m" 
                                   value="<?php echo $container['internal_height_m']; ?>"
                                   step="0.01"
                                   placeholder="높이 (예: 2.39)"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
                
                <!-- 문 치수 -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">문 크기 (m)</label>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <input type="number" 
                                   name="door_width_m" 
                                   value="<?php echo $container['door_width_m']; ?>"
                                   step="0.01"
                                   placeholder="너비 (예: 2.34)"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <input type="number" 
                                   name="door_height_m" 
                                   value="<?php echo $container['door_height_m']; ?>"
                                   step="0.01"
                                   placeholder="높이 (예: 2.28)"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
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
                <!-- 특징 -->
                <div>
                    <label for="features" class="block text-sm font-medium text-gray-700 mb-2">
                        주요 특징
                    </label>
                    <textarea id="features" 
                              name="features" 
                              rows="3"
                              placeholder="예: 방수, 통풍구 장착, 바닥 강화 등"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($container['features']); ?></textarea>
                    <p class="mt-1 text-sm text-gray-500">줄바꿈으로 여러 특징을 구분해주세요</p>
                </div>
                
                <!-- 적합한 화물 -->
                <div>
                    <label for="suitable_for" class="block text-sm font-medium text-gray-700 mb-2">
                        적합한 화물
                    </label>
                    <textarea id="suitable_for" 
                              name="suitable_for" 
                              rows="3"
                              placeholder="예: 일반 화물, 팔레트 화물, 박스 화물 등"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($container['suitable_for']); ?></textarea>
                </div>
                
                <!-- 현재 이미지 -->
                <?php if ($container['image_path'] && file_exists(PROJECT_ROOT . $container['image_path'])): ?>
                <div class="mb-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">현재 이미지</p>
                    <div class="flex items-start space-x-4">
                        <div class="bg-gray-100 p-4 rounded">
                            <img src="<?php echo BASE_URL . $container['image_path']; ?>" 
                                 alt="<?php echo e($container['name']); ?>"
                                 class="max-h-32">
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="delete_image" 
                                   name="delete_image" 
                                   value="1"
                                   class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                            <label for="delete_image" class="ml-2 block text-sm text-red-600">
                                이미지 삭제
                            </label>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- 새 이미지 업로드 -->
                <div>
                    <label for="image_file" class="block text-sm font-medium text-gray-700 mb-2">
                        새 이미지 업로드
                    </label>
                    <input type="file" 
                           id="image_file" 
                           name="image_file" 
                           accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">
                        JPG, PNG, GIF, SVG 형식 지원 (최대 5MB)<br>
                        권장 크기: 400x300px 이상
                    </p>
                </div>
                
                <!-- 이미지 미리보기 -->
                <div id="image-preview" class="hidden">
                    <p class="text-sm font-medium text-gray-700 mb-2">미리보기</p>
                    <div class="bg-gray-100 rounded-md p-4">
                        <img id="preview-img" src="" alt="미리보기" class="max-h-48 mx-auto">
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
                           value="<?php echo e($container['sort_order']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">숫자가 작을수록 먼저 표시됩니다</p>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="is_active" 
                           name="is_active" 
                           value="1"
                           <?php echo $container['is_active'] ? 'checked' : ''; ?>
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
// 이미지 미리보기
document.getElementById('image_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('image-preview');
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

// 내부 치수로 용적 자동 계산
function calculateVolume() {
    const length = parseFloat(document.querySelector('[name="internal_length_m"]').value) || 0;
    const width = parseFloat(document.querySelector('[name="internal_width_m"]').value) || 0;
    const height = parseFloat(document.querySelector('[name="internal_height_m"]').value) || 0;
    
    if (length > 0 && width > 0 && height > 0) {
        const volume = (length * width * height).toFixed(1);
        document.getElementById('volume_cbm').value = volume;
    }
}

document.querySelector('[name="internal_length_m"]').addEventListener('input', calculateVolume);
document.querySelector('[name="internal_width_m"]').addEventListener('input', calculateVolume);
document.querySelector('[name="internal_height_m"]').addEventListener('input', calculateVolume);
</script>

<?php include '../includes/footer.php'; ?>