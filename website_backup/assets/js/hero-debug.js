// 디버깅용 - 프로그레스바 상태 확인
function debugProgressBars() {
    const progressBars = document.querySelectorAll('.video-progress-item');
    console.log('=== Progress Bar Status ===');
    progressBars.forEach((bar, index) => {
        const isActive = bar.classList.contains('active');
        const fill = bar.querySelector('.video-progress-fill');
        const transform = fill.style.transform;
        console.log(`Bar ${index + 1}: Active=${isActive}, Transform=${transform}`);
    });
}

// 현재 비디오 인덱스 확인
function debugCurrentVideo() {
    console.log('Current Video Index:', window.currentVideoIndex || 0);
}

// 1초마다 상태 출력 (개발 중에만 사용)
// setInterval(() => {
//     debugProgressBars();
//     debugCurrentVideo();
// }, 1000);