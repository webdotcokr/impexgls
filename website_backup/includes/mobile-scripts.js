// 모바일 최적화 스크립트

document.addEventListener('DOMContentLoaded', function() {
    // 모바일 터치 최적화
    if ('ontouchstart' in window) {
        document.body.classList.add('touch-device');
    }
    
    // 뷰포트 높이 보정 (모바일 브라우저 주소창 대응)
    function setViewportHeight() {
        const vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }
    
    setViewportHeight();
    window.addEventListener('resize', setViewportHeight);
    
    // 스와이프 제스처 지원
    let touchStartX = 0;
    let touchEndX = 0;
    
    function handleSwipe() {
        const swipeThreshold = 50;
        const diff = touchEndX - touchStartX;
        
        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
                // 오른쪽 스와이프
                const mobileMenu = document.querySelector('.mobile-menu');
                const mobileMenuOverlay = document.querySelector('.mobile-menu-overlay');
                if (mobileMenu && mobileMenu.style.transform !== 'translateX(-100%)') {
                    mobileMenu.style.transform = 'translateX(-100%)';
                    if (mobileMenuOverlay) {
                        mobileMenuOverlay.classList.add('hidden');
                    }
                    document.body.style.overflow = '';
                }
            }
        }
    }
    
    document.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].screenX;
    });
    
    document.addEventListener('touchend', e => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });
    
    // 이미지 지연 로딩
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // 모바일 테이블 스크롤 표시
    const tables = document.querySelectorAll('.responsive-table');
    tables.forEach(table => {
        const wrapper = table.parentElement;
        if (table.scrollWidth > wrapper.clientWidth) {
            wrapper.classList.add('has-scroll');
        }
    });
    
    // 폼 입력 최적화
    const inputs = document.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            if (window.innerWidth < 768) {
                setTimeout(() => {
                    this.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
            }
        });
    });
    
    // 모바일 브라우저 감지
    function detectMobileBrowser() {
        const userAgent = navigator.userAgent || navigator.vendor || window.opera;
        if (/android/i.test(userAgent)) {
            document.body.classList.add('android');
        }
        if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
            document.body.classList.add('ios');
        }
    }
    
    detectMobileBrowser();
    
    // 가로/세로 방향 변경 감지
    function handleOrientationChange() {
        const orientation = window.innerWidth > window.innerHeight ? 'landscape' : 'portrait';
        document.body.setAttribute('data-orientation', orientation);
    }
    
    handleOrientationChange();
    window.addEventListener('orientationchange', handleOrientationChange);
    window.addEventListener('resize', handleOrientationChange);
});

// 유틸리티 함수
function isMobile() {
    return window.innerWidth < 768;
}

function isTablet() {
    return window.innerWidth >= 768 && window.innerWidth < 1024;
}

function isDesktop() {
    return window.innerWidth >= 1024;
}