<?php
/**
 * 인증서 수정
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '인증서 수정';

// ID 확인
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

// 인증서 조회
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM certificates WHERE id = ?");
    $stmt->execute([$id]);
    $certificate = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$certificate) {
        setAlert('error', '인증서를 찾을 수 없습니다.');
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    error_log("Certificate error: " . $e->getMessage());
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
        $title = trim($_POST['title'] ?? '');
        $title_en = trim($_POST['title_en'] ?? '');
        $issuer = trim($_POST['issuer'] ?? '');
        $issue_date = $_POST['issue_date'] ?? null;
        $expiry_date = $_POST['expiry_date'] ?? null;
        $certificate_number = trim($_POST['certificate_number'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sort_order = intval($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // 유효성 검사
        if (empty($title)) {
            throw new Exception('인증서명을 입력해주세요.');
        }
        if (empty($issuer)) {
            throw new Exception('발급기관을 입력해주세요.');
        }
        
        // 날짜 유효성 검사
        if ($issue_date && $expiry_date && strtotime($issue_date) > strtotime($expiry_date)) {
            throw new Exception('만료일은 발급일보다 이후여야 합니다.');
        }
        
        // 이미지 업로드 처리
        $image_path = $certificate['image_path'];
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            // 기존 이미지 삭제
            if ($image_path && file_exists(PROJECT_ROOT . $image_path)) {
                unlink(PROJECT_ROOT . $image_path);
            }
            
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $upload_result = uploadFile($_FILES['image_file'], $allowed_types, '/uploads/certificates/');
            
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
            UPDATE certificates SET
                title = :title,
                title_en = :title_en,
                issuer = :issuer,
                issue_date = :issue_date,
                expiry_date = :expiry_date,
                certificate_number = :certificate_number,
                image_path = :image_path,
                description = :description,
                sort_order = :sort_order,
                is_active = :is_active
            WHERE id = :id
        ");
        
        $stmt->execute([
            ':title' => $title,
            ':title_en' => $title_en,
            ':issuer' => $issuer,
            ':issue_date' => $issue_date,
            ':expiry_date' => $expiry_date,
            ':certificate_number' => $certificate_number,
            ':image_path' => $image_path,
            ':description' => $description,
            ':sort_order' => $sort_order,
            ':is_active' => $is_active,
            ':id' => $id
        ]);
        
        logAdminAction('update', 'certificates', $id, 'Certificate updated: ' . $title);
        
        setAlert('success', '인증서가 수정되었습니다.');
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        setAlert('error', $e->getMessage());
    }
}

$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<div class="max-w-4xl">
    <!-- 상단 버튼 -->
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">인증서 수정</h1>
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
                <!-- 인증서명 (한국어) -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        인증서명 (한국어) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="<?php echo e($certificate['title']); ?>"
                           placeholder="예: ISO 9001:2015 품질경영시스템"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           required>
                </div>
                
                <!-- 인증서명 (영어) -->
                <div>
                    <label for="title_en" class="block text-sm font-medium text-gray-700 mb-2">
                        인증서명 (영어)
                    </label>
                    <input type="text" 
                           id="title_en" 
                           name="title_en" 
                           value="<?php echo e($certificate['title_en']); ?>"
                           placeholder="예: ISO 9001:2015 Quality Management System"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <!-- 발급기관 및 인증번호 -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="issuer" class="block text-sm font-medium text-gray-700 mb-2">
                            발급기관 <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="issuer" 
                               name="issuer" 
                               value="<?php echo e($certificate['issuer']); ?>"
                               placeholder="예: TÜV SÜD"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>
                    <div>
                        <label for="certificate_number" class="block text-sm font-medium text-gray-700 mb-2">
                            인증번호
                        </label>
                        <input type="text" 
                               id="certificate_number" 
                               name="certificate_number" 
                               value="<?php echo e($certificate['certificate_number']); ?>"
                               placeholder="예: QMS-2023-0001"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <!-- 발급일 및 만료일 -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="issue_date" class="block text-sm font-medium text-gray-700 mb-2">
                            발급일
                        </label>
                        <input type="date" 
                               id="issue_date" 
                               name="issue_date" 
                               value="<?php echo e($certificate['issue_date']); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="expiry_date" class="block text-sm font-medium text-gray-700 mb-2">
                            만료일
                        </label>
                        <input type="date" 
                               id="expiry_date" 
                               name="expiry_date" 
                               value="<?php echo e($certificate['expiry_date']); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-sm text-gray-500">무기한인 경우 비워두세요</p>
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
                              placeholder="인증서에 대한 간단한 설명"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e($certificate['description']); ?></textarea>
                </div>
                
                <!-- 인증서 정보 -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500">등록일</dt>
                            <dd class="mt-1 text-gray-900"><?php echo formatDate($certificate['created_at']); ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">유효 상태</dt>
                            <dd class="mt-1 text-gray-900">
                                <?php
                                if (!$certificate['expiry_date']) {
                                    echo '<span class="text-green-600">무기한</span>';
                                } elseif (strtotime($certificate['expiry_date']) < time()) {
                                    echo '<span class="text-red-600">만료됨</span>';
                                } else {
                                    $days_until_expiry = ceil((strtotime($certificate['expiry_date']) - time()) / 86400);
                                    if ($days_until_expiry <= 30) {
                                        echo '<span class="text-yellow-600">' . $days_until_expiry . '일 후 만료</span>';
                                    } else {
                                        echo '<span class="text-green-600">유효</span>';
                                    }
                                }
                                ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
        
        <!-- 이미지 업로드 -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">인증서 이미지</h3>
            </div>
            <div class="p-6">
                <!-- 현재 이미지 -->
                <?php if ($certificate['image_path'] && file_exists(PROJECT_ROOT . $certificate['image_path'])): ?>
                <div class="mb-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">현재 이미지</p>
                    <div class="flex items-start space-x-4">
                        <img src="<?php echo BASE_URL . $certificate['image_path']; ?>" 
                             alt="<?php echo e($certificate['title']); ?>"
                             class="max-w-xs rounded border">
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
                        JPG, PNG, GIF 형식 지원 (최대 5MB)
                    </p>
                </div>
                
                <!-- 이미지 미리보기 -->
                <div id="image-preview" class="mt-4 hidden">
                    <p class="text-sm font-medium text-gray-700 mb-2">미리보기</p>
                    <img id="preview-img" src="" alt="미리보기" class="max-w-xs rounded border">
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
                           value="<?php echo e($certificate['sort_order']); ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">숫자가 작을수록 먼저 표시됩니다</p>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="is_active" 
                           name="is_active" 
                           value="1"
                           <?php echo $certificate['is_active'] ? 'checked' : ''; ?>
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

// 날짜 유효성 검사
document.getElementById('issue_date').addEventListener('change', validateDates);
document.getElementById('expiry_date').addEventListener('change', validateDates);

function validateDates() {
    const issueDate = document.getElementById('issue_date').value;
    const expiryDate = document.getElementById('expiry_date').value;
    
    if (issueDate && expiryDate && issueDate > expiryDate) {
        document.getElementById('expiry_date').setCustomValidity('만료일은 발급일보다 이후여야 합니다.');
    } else {
        document.getElementById('expiry_date').setCustomValidity('');
    }
}
</script>

<?php include '../includes/footer.php'; ?>