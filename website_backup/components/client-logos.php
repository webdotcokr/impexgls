<?php
// 클라이언트 로고 컴포넌트
$client_logos = [
    ['name' => 'Samsung', 'logo' => 'samsung.png', 'url' => '#'],
    ['name' => 'LG', 'logo' => 'lg.png', 'url' => '#'],
    ['name' => 'Hyundai', 'logo' => 'hyundai.png', 'url' => '#'],
    ['name' => 'Doosan', 'logo' => 'doosan.png', 'url' => '#'],
    ['name' => 'POSCO', 'logo' => 'posco.png', 'url' => '#'],
    ['name' => 'SK', 'logo' => 'sk.png', 'url' => '#'],
    ['name' => 'CJ', 'logo' => 'cj.png', 'url' => '#'],
    ['name' => 'Hanwha', 'logo' => 'hanwha.png', 'url' => '#'],
    ['name' => 'Lotte', 'logo' => 'lotte.png', 'url' => '#'],
    ['name' => 'GS', 'logo' => 'gs.png', 'url' => '#'],
    ['name' => 'Kumho', 'logo' => 'kumho.png', 'url' => '#'],
    ['name' => 'KT', 'logo' => 'kt.png', 'url' => '#'],
    ['name' => 'KB', 'logo' => 'kb.png', 'url' => '#'],
    ['name' => 'Shinhan', 'logo' => 'shinhan.png', 'url' => '#'],
    ['name' => 'Woori', 'logo' => 'woori.png', 'url' => '#'],
    ['name' => 'Hana', 'logo' => 'hana.png', 'url' => '#']
];
?>

<div class="client-logos-container">
    <h3 class="text-center text-white text-2xl mb-8 font-semibold">CLIENTS</h3>
    
    <div class="client-logos-slider overflow-hidden">
        <div class="client-logos-track flex">
            <?php for($i = 0; $i < 2; $i++): // 무한 스크롤을 위해 2번 반복 ?>
            <div class="client-logos-group flex">
                <?php foreach($client_logos as $logo): ?>
                <div class="client-logo-item px-6 py-4">
                    <img src="<?php echo BASE_URL; ?>/assets/images/placeholder/client-<?php echo strtolower($logo['name']); ?>.png" 
                         alt="<?php echo $logo['name']; ?>" 
                         class="h-12 opacity-70 hover:opacity-100 transition-opacity filter grayscale hover:grayscale-0"
                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjYwIiB2aWV3Qm94PSIwIDAgMTIwIDYwIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxMjAiIGhlaWdodD0iNjAiIGZpbGw9IiNmMGYwZjAiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSI+TE9HTzwvdGV4dD48L3N2Zz4=';">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</div>

<style>
.client-logos-slider {
    position: relative;
    width: 100%;
}

.client-logos-track {
    animation: scroll-logos 30s linear infinite;
}

.client-logos-group {
    display: flex;
    min-width: max-content;
}

@keyframes scroll-logos {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}

.client-logos-track:hover {
    animation-play-state: paused;
}

@media (max-width: 768px) {
    .client-logo-item img {
        height: 2.5rem;
    }
}
</style>