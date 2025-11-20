<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once 'auth-check.php';

$quote_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$quote_id) {
    echo "<p class='text-red-600'>Invalid quote ID.</p>";
    exit;
}

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM quote_requests WHERE id = :id");
    $stmt->execute([':id' => $quote_id]);
    $quote = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$quote) {
        echo "<p class='text-red-600'>Quote not found.</p>";
        exit;
    }
} catch (PDOException $e) {
    echo "<p class='text-red-600'>Error loading quote details.</p>";
    exit;
}
?>

<div class="space-y-4">
    <!-- 회사 정보 -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="font-bold text-lg mb-3">Company Information</h3>
        <div class="space-y-2">
            <div class="detail-row">
                <span class="detail-label">Company Name:</span>
                <span><?php echo htmlspecialchars($quote['company_name']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Contact Person:</span>
                <span><?php echo htmlspecialchars($quote['contact_person']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span><a href="mailto:<?php echo htmlspecialchars($quote['email']); ?>" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($quote['email']); ?></a></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Phone:</span>
                <span><a href="tel:<?php echo htmlspecialchars($quote['phone']); ?>" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($quote['phone']); ?></a></span>
            </div>
        </div>
    </div>
    
    <!-- 서비스 정보 -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="font-bold text-lg mb-3">Service Requirements</h3>
        <div class="space-y-2">
            <div class="detail-row">
                <span class="detail-label">Service Type:</span>
                <span><?php echo htmlspecialchars($quote['service_type']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Origin Country:</span>
                <span><?php echo htmlspecialchars($quote['origin_country']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Destination Country:</span>
                <span><?php echo htmlspecialchars($quote['destination_country']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Expected Date:</span>
                <span><?php echo $quote['expected_date'] ? date('M d, Y', strtotime($quote['expected_date'])) : 'Not specified'; ?></span>
            </div>
        </div>
    </div>
    
    <!-- 화물 정보 -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="font-bold text-lg mb-3">Cargo Information</h3>
        <div class="space-y-2">
            <div class="detail-row">
                <span class="detail-label">Cargo Type:</span>
                <span><?php echo htmlspecialchars($quote['cargo_type']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Weight:</span>
                <span><?php echo $quote['cargo_weight'] ? htmlspecialchars($quote['cargo_weight']) . ' kg' : 'Not specified'; ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Volume:</span>
                <span><?php echo $quote['cargo_volume'] ? htmlspecialchars($quote['cargo_volume']) . ' m³' : 'Not specified'; ?></span>
            </div>
        </div>
    </div>
    
    <!-- 추가 정보 -->
    <?php if ($quote['additional_info']): ?>
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="font-bold text-lg mb-3">Additional Information</h3>
        <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($quote['additional_info']); ?></p>
    </div>
    <?php endif; ?>
    
    <!-- 상태 정보 -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="font-bold text-lg mb-3">Status Information</h3>
        <div class="space-y-2">
            <div class="detail-row">
                <span class="detail-label">Current Status:</span>
                <span>
                    <span class="status-badge status-<?php echo $quote['status']; ?>">
                        <?php echo ucfirst($quote['status']); ?>
                    </span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Submitted Date:</span>
                <span><?php echo date('M d, Y H:i', strtotime($quote['created_at'])); ?></span>
            </div>
        </div>
    </div>
</div>