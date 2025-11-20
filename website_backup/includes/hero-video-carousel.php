<!-- 영상 캐러셀 히어로 섹션 -->
<section class="hero-video-carousel">
    <div class="hero-video-container">
        <!-- 비디오 요소들은 JS로 동적 생성 -->
    </div>
    
    <!-- 오버레이 및 콘텐츠 -->
    <div class="hero-overlay">
        <div class="hero-content">
            <h1 class="hero-title">IMPEX GLS</h1>
            <p class="hero-subtitle">Global Logistics Solutions</p>
        </div>
    </div>
    
    <!-- 비디오 컨트롤 (옵션) -->
    <div class="video-controls">
        <button class="control-btn play-pause" aria-label="Play/Pause">
            <svg class="icon-play" viewBox="0 0 24 24">
                <path d="M8 5v14l11-7z"/>
            </svg>
            <svg class="icon-pause" viewBox="0 0 24 24">
                <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
            </svg>
        </button>
        
        <div class="video-indicators">
            <span class="indicator active" data-index="0"></span>
            <span class="indicator" data-index="1"></span>
            <span class="indicator" data-index="2"></span>
            <span class="indicator" data-index="3"></span>
        </div>
        
        <button class="control-btn mute-toggle" aria-label="Mute/Unmute">
            <svg class="icon-volume" viewBox="0 0 24 24">
                <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02z"/>
            </svg>
        </button>
    </div>
    
    <!-- 로딩 인디케이터 -->
    <div class="video-loader">
        <div class="loader-spinner"></div>
    </div>
</section>

<style>
.hero-video-carousel {
    position: relative;
    width: 100%;
    height: 100vh;
    overflow: hidden;
    background-color: #000;
}

.hero-video-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        180deg,
        rgba(0, 0, 0, 0.3) 0%,
        rgba(0, 0, 0, 0.1) 50%,
        rgba(0, 0, 0, 0.5) 100%
    );
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
}

.hero-content {
    text-align: center;
    color: white;
    padding: 2rem;
}

.hero-title {
    font-size: clamp(3rem, 8vw, 6rem);
    font-weight: 700;
    margin-bottom: 1rem;
    letter-spacing: -0.02em;
}

.hero-subtitle {
    font-size: clamp(1.2rem, 3vw, 2rem);
    font-weight: 300;
    opacity: 0.9;
}

.video-controls {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 2rem;
    z-index: 2;
}

.control-btn {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.control-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

.control-btn svg {
    width: 1.5rem;
    height: 1.5rem;
    fill: currentColor;
}

.icon-pause {
    display: none;
}

.playing .icon-play {
    display: none;
}

.playing .icon-pause {
    display: block;
}

.video-indicators {
    display: flex;
    gap: 0.5rem;
}

.indicator {
    width: 3rem;
    height: 0.25rem;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 0.125rem;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.indicator.active {
    background: rgba(255, 255, 255, 0.5);
}

.indicator.active::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: white;
    animation: progress var(--duration, 15s) linear;
}

@keyframes progress {
    from { width: 0%; }
    to { width: 100%; }
}

.video-loader {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 3;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.loading .video-loader {
    opacity: 1;
}

.loader-spinner {
    width: 3rem;
    height: 3rem;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* 모바일 최적화 */
@media (max-width: 768px) {
    .hero-video-carousel {
        height: 100vh;
        height: calc(var(--vh, 1vh) * 100);
    }
    
    .video-controls {
        bottom: 1rem;
        gap: 1rem;
    }
    
    .control-btn {
        width: 2.5rem;
        height: 2.5rem;
    }
    
    .indicator {
        width: 2rem;
    }
}

/* 저사양 기기 대응 */
@media (prefers-reduced-motion: reduce) {
    .hero-video-container video {
        display: none;
    }
    
    .hero-video-carousel {
        background-image: url('/assets/images/hero-static.jpg');
        background-size: cover;
        background-position: center;
    }
}
</style>