<!-- Quick Menu (플로팅 버튼) -->
<div class="floating-buttons fixed bottom-6 right-6 z-40 flex flex-col gap-3">
    <!-- Contact 버튼 (quick1.svg) -->
    <!-- <a href="<?php echo BASE_URL; ?>/pages/support/request-quote.php" 
       class="floating-quick-btn transition-transform hover:scale-110" 
       style="box-shadow: 0px 4px 10px 0px rgba(0, 0, 0, 0.07);"
       title="Contact Us">
        <img src="<?php echo BASE_URL; ?>/assets/images/quick1.svg" alt="Contact" width="56" height="56">
    </a> -->
    
    <!-- KakaoTalk 버튼 (quick2.svg) -->
    <a href="https://pf.kakao.com/_xdQenn" 
       target="_blank"
       class="floating-quick-btn transition-transform hover:scale-110" 
       style="box-shadow: 0px 4px 10px 0px rgba(0, 0, 0, 0.07);"
       title="KakaoTalk Channel">
        <img src="<?php echo BASE_URL; ?>/assets/images/quick2.svg" alt="KakaoTalk" width="56" height="56">
    </a>
    
    <!-- Top 버튼 (quick3.svg) -->
    <button class="floating-top-btn transition-transform hover:scale-110" 
            style="box-shadow: 0px 4px 10px 0px rgba(0, 0, 0, 0.07);"
            onclick="window.scrollTo({top: 0, behavior: 'smooth'})"
            title="Back to Top">
        <img src="<?php echo BASE_URL; ?>/assets/images/quick3.svg" alt="Top" width="56" height="56">
    </button>
</div>

<style>
/* Quick Menu 스타일 */
.floating-buttons {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.floating-quick-btn,
.floating-top-btn {
    display: inline-block;
    border-radius: 50%;
    overflow: hidden;
}

/* Top 버튼 표시/숨김 */
.floating-top-btn {
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.floating-top-btn.show {
    opacity: 1;
    visibility: visible;
}

/* 모바일 반응형 */
@media (max-width: 768px) {
    .floating-buttons {
        bottom: 20px;
        right: 10px;
        gap: 0.25rem;
    }
    
    .floating-quick-btn img,
    .floating-top-btn img {
        width: 48px;
        height: 48px;
    }
}
</style>

<script>
// Top 버튼 표시/숨김 처리
document.addEventListener('DOMContentLoaded', function() {
    const topBtn = document.querySelector('.floating-top-btn');
    
    if (topBtn) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                topBtn.classList.add('show');
            } else {
                topBtn.classList.remove('show');
            }
        });
    }
});
</script>