<?php
/**
 * 문의 상세 보기
 */

require_once '../includes/auth.php';
require_once '../includes/functions.php';

$page_title = '문의 상세';

// ID 확인
$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: index.php');
    exit;
}

// 노트 저장 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_notes'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        setAlert('error', '잘못된 요청입니다.');
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("
                UPDATE quote_requests 
                SET admin_notes = :notes, 
                    status = :status,
                    updated_at = NOW() 
                WHERE id = :id
            ");
            $stmt->execute([
                ':notes' => $_POST['admin_notes'],
                ':status' => $_POST['status'],
                ':id' => $id
            ]);
            
            logAdminAction('update', 'quote_requests', $id, 'Quote updated with notes');
            setAlert('success', '저장되었습니다.');
        } catch (Exception $e) {
            setAlert('error', '저장에 실패했습니다.');
        }
    }
    
    header('Location: view.php?id=' . $id);
    exit;
}

// 데이터 조회
try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM quote_requests WHERE id = ?");
    $stmt->execute([$id]);
    $quote = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$quote) {
        setAlert('error', '문의를 찾을 수 없습니다.');
        header('Location: index.php');
        exit;
    }
    
    // 첨부 파일 파싱
    $attachments = json_decode($quote['attachments'] ?? '[]', true);
    
} catch (Exception $e) {
    error_log("Quote view error: " . $e->getMessage());
    setAlert('error', '데이터 조회에 실패했습니다.');
    header('Location: index.php');
    exit;
}

// 상태 옵션
$status_options = [
    'pending' => ['label' => '대기', 'color' => 'yellow'],
    'processing' => ['label' => '처리중', 'color' => 'blue'],
    'quoted' => ['label' => '견적완료', 'color' => 'green'],
    'closed' => ['label' => '종료', 'color' => 'gray']
];

$csrf_token = generateCSRFToken();

include '../includes/header.php';
?>

<div class="max-w-6xl mx-auto">
    <!-- 상단 버튼 -->
    <div class="mb-4 flex justify-between items-center">
        <a href="index.php" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>목록으로
        </a>
        <div class="flex space-x-2">
            <button onclick="window.print()" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                <i class="fas fa-print mr-2"></i>인쇄
            </button>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 좌측: 문의 정보 -->
        <div class="lg:col-span-2 space-y-6">
            <!-- 기본 정보 -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">문의 정보</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">접수번호</dt>
                            <dd class="mt-1 text-sm text-gray-900">#<?php echo str_pad($quote['id'], 6, '0', STR_PAD_LEFT); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">접수일시</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo formatDate($quote['created_at']); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">문의 유형</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($quote['request_type'] ?: '일반문의'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">현재 상태</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                           bg-<?php echo $status_options[$quote['status']]['color']; ?>-100 
                                           text-<?php echo $status_options[$quote['status']]['color']; ?>-800">
                                    <?php echo $status_options[$quote['status']]['label']; ?>
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
            
            <!-- 고객 정보 -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">고객 정보</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">회사명</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($quote['company_name'] ?: '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">담당자명</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($quote['contact_name'] ?? $quote['full_name'] ?? '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">이메일</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a href="mailto:<?php echo e($quote['email'] ?? $quote['email_address'] ?? ''); ?>" class="text-blue-600 hover:underline">
                                    <?php echo e($quote['email'] ?? $quote['email_address'] ?? '-'); ?>
                                </a>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">전화번호</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php if ($quote['phone']): ?>
                                <a href="tel:<?php echo e($quote['phone'] ?? $quote['telephone_number'] ?? ''); ?>" class="text-blue-600 hover:underline">
                                    <?php echo e($quote['phone'] ?? $quote['telephone_number'] ?? '-'); ?>
                                </a>
                                <?php else: ?>
                                -
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">IP 주소</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($quote['ip_address'] ?: '-'); ?></dd>
                        </div>
                    </dl>
                </div>
            </div>
            
            <!-- 화물 정보 -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">화물 정보</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">출발지</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo e($quote['departure_country'] ?: '-'); ?>
                                <?php if ($quote['departure_city']): ?>
                                    <br><span class="text-gray-500"><?php echo e($quote['departure_city']); ?></span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">도착지</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo e($quote['destination_country'] ?: '-'); ?>
                                <?php if ($quote['destination_city']): ?>
                                    <br><span class="text-gray-500"><?php echo e($quote['destination_city']); ?></span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">화물 종류</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($quote['cargo_type'] ?: '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">인코텀즈</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($quote['incoterms'] ?: '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">중량</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo $quote['cargo_weight'] ? number_format($quote['cargo_weight'], 2) . ' kg' : '-'; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">부피</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo $quote['cargo_volume'] ? number_format($quote['cargo_volume'], 2) . ' m³' : '-'; ?>
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">희망 운송일</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo $quote['expected_date'] ? formatDate($quote['expected_date'], 'Y-m-d') : '-'; ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
            
            <!-- 메시지 -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">문의 내용</h3>
                </div>
                <div class="p-6">
                    <div class="prose max-w-none">
                        <?php echo nl2br(e($quote['message'] ?: '내용 없음')); ?>
                    </div>
                </div>
            </div>
            
            <!-- 첨부 파일 -->
            <?php if (!empty($attachments)): ?>
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">첨부 파일</h3>
                </div>
                <div class="p-6">
                    <ul class="space-y-2">
                        <?php foreach ($attachments as $file): ?>
                        <li class="flex items-center justify-between py-2 border-b last:border-0">
                            <div class="flex items-center">
                                <i class="fas fa-file text-gray-400 mr-2"></i>
                                <span class="text-sm text-gray-900"><?php echo e($file['name'] ?? 'Unknown'); ?></span>
                                <?php if (isset($file['size'])): ?>
                                <span class="text-sm text-gray-500 ml-2">
                                    (<?php echo formatFileSize($file['size']); ?>)
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php if (isset($file['path']) && file_exists(PROJECT_ROOT . $file['path'])): ?>
                            <a href="<?php echo BASE_URL . $file['path']; ?>" 
                               target="_blank"
                               class="text-blue-600 hover:text-blue-800 text-sm">
                                <i class="fas fa-download mr-1"></i>다운로드
                            </a>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- 우측: 관리자 메모 -->
        <div class="lg:col-span-1">
            <form method="POST" action="" class="bg-white rounded-lg shadow sticky top-6">
                <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token); ?>">
                
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">관리자 메모</h3>
                </div>
                
                <div class="p-6 space-y-4">
                    <!-- 상태 변경 -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            상태 변경
                        </label>
                        <select id="status" 
                                name="status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php foreach ($status_options as $value => $option): ?>
                            <option value="<?php echo $value; ?>" 
                                    <?php echo $quote['status'] == $value ? 'selected' : ''; ?>>
                                <?php echo $option['label']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- 관리자 노트 -->
                    <div>
                        <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-2">
                            메모
                        </label>
                        <textarea id="admin_notes" 
                                  name="admin_notes" 
                                  rows="10"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="처리 내용, 고객 응대 기록 등을 입력하세요..."><?php echo e($quote['admin_notes'] ?? ''); ?></textarea>
                    </div>
                    
                    <!-- 마지막 수정 -->
                    <?php if ($quote['updated_at'] && $quote['updated_at'] != $quote['created_at']): ?>
                    <div class="text-sm text-gray-500">
                        마지막 수정: <?php echo formatDate($quote['updated_at']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- 저장 버튼 -->
                    <button type="submit" 
                            name="save_notes"
                            class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200">
                        <i class="fas fa-save mr-2"></i>저장
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 인쇄용 스타일 -->
<style>
@media print {
    .sidebar, header, .header-spacer { display: none !important; }
    main { margin: 0 !important; }
    .no-print { display: none !important; }
    .shadow { box-shadow: none !important; }
    .bg-gray-100 { background: white !important; }
}
</style>

<?php include '../includes/footer.php'; ?>