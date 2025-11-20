// 영상 캐러셀 클래스
class VideoCarousel {
    constructor(container, options = {}) {
        this.container = container;
        this.videos = options.videos || [];
        this.currentIndex = 0;
        this.isPlaying = true;
        this.preloadCount = options.preloadCount || 2;
        this.fadeTransition = options.fadeTransition || true;
        this.transitionDuration = options.transitionDuration || 1000;
        
        this.init();
    }
    
    init() {
        this.createVideoElements();
        this.preloadVideos();
        this.setupEventListeners();
        this.play();
    }
    
    createVideoElements() {
        // 두 개의 비디오 엘리먼트 생성 (크로스페이드용)
        this.videoA = this.createVideoElement();
        this.videoB = this.createVideoElement();
        
        this.container.appendChild(this.videoA);
        this.container.appendChild(this.videoB);
        
        this.activeVideo = this.videoA;
        this.inactiveVideo = this.videoB;
    }
    
    createVideoElement() {
        const video = document.createElement('video');
        video.muted = true;
        video.playsInline = true;
        video.setAttribute('webkit-playsinline', '');
        video.setAttribute('playsinline', '');
        video.setAttribute('autoplay', '');
        video.setAttribute('muted', ''); // 속성으로도 추가
        video.style.position = 'absolute';
        video.style.width = '100%';
        video.style.height = '100%';
        video.style.objectFit = 'cover';
        video.style.opacity = '0';
        video.style.transition = `opacity ${this.transitionDuration}ms ease-in-out`;
        
        return video;
    }
    
    preloadVideos() {
        // 다음 영상들 미리 로드
        for (let i = 0; i < Math.min(this.preloadCount, this.videos.length); i++) {
            const index = (this.currentIndex + i) % this.videos.length;
            this.preloadVideo(this.videos[index]);
        }
    }
    
    preloadVideo(videoConfig) {
        // 적응형 비트레이트 선택
        const source = this.selectOptimalSource(videoConfig.sources);
        
        // 프리로드
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = 'video';
        link.href = source.src;
        document.head.appendChild(link);
    }
    
    selectOptimalSource(sources) {
        // 네트워크 속도 기반 소스 선택
        const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        const effectiveType = connection ? connection.effectiveType : '4g';
        
        // 화면 크기 고려
        const screenWidth = window.innerWidth * window.devicePixelRatio;
        
        let selectedSource;
        
        if (effectiveType === 'slow-2g' || effectiveType === '2g') {
            selectedSource = sources.find(s => s.quality === '480p') || sources[0];
        } else if (effectiveType === '3g' || screenWidth < 1280) {
            selectedSource = sources.find(s => s.quality === '720p') || sources[0];
        } else {
            selectedSource = sources.find(s => s.quality === '1080p') || sources[0];
        }
        
        return selectedSource;
    }
    
    async play() {
        if (!this.isPlaying || this.videos.length === 0) return;
        
        const currentVideo = this.videos[this.currentIndex];
        const source = this.selectOptimalSource(currentVideo.sources);
        
        // 새 영상 로드
        this.activeVideo.src = source.src;
        
        try {
            // 로드 대기
            await this.activeVideo.load();
            
            // 페이드 인
            this.activeVideo.style.opacity = '1';
            this.inactiveVideo.style.opacity = '0';
            
            // 재생 시작
            await this.activeVideo.play();
            
            // 영상 종료 이벤트 리스너
            this.activeVideo.onended = () => {
                this.next();
            };
            
            // 다음 영상 프리로드
            const nextIndex = (this.currentIndex + 1) % this.videos.length;
            this.preloadVideo(this.videos[nextIndex]);
            
        } catch (error) {
            console.error('Video play error:', error);
            
            // 모바일 자동재생 실패 대응
            if (error.name === 'NotAllowedError') {
                // 사용자 상호작용 대기
                console.log('Autoplay blocked, waiting for user interaction');
                
                // 음소거 상태 재확인
                this.activeVideo.muted = true;
                this.activeVideo.setAttribute('muted', '');
                
                // 클릭 이벤트로 재생 시도
                const playOnClick = async () => {
                    try {
                        await this.activeVideo.play();
                        document.removeEventListener('click', playOnClick);
                        document.removeEventListener('touchstart', playOnClick);
                    } catch (e) {
                        console.error('Play failed even with user interaction:', e);
                    }
                };
                
                document.addEventListener('click', playOnClick, { once: true });
                document.addEventListener('touchstart', playOnClick, { once: true });
                
                // 정적 이미지 표시 옵션
                this.showPoster();
            } else {
                // 다른 에러: 다음 영상으로
                this.next();
            }
        }
    }
    
    next() {
        this.currentIndex = (this.currentIndex + 1) % this.videos.length;
        
        // 비디오 요소 스왑
        [this.activeVideo, this.inactiveVideo] = [this.inactiveVideo, this.activeVideo];
        
        this.play();
    }
    
    pause() {
        this.isPlaying = false;
        this.activeVideo.pause();
    }
    
    resume() {
        this.isPlaying = true;
        this.activeVideo.play();
    }
    
    showPoster() {
        // 비디오 재생 실패시 포스터 이미지 표시
        if (this.videos[this.currentIndex].poster) {
            this.activeVideo.poster = this.videos[this.currentIndex].poster;
        }
        // 재생 버튼 표시
        const playButton = document.createElement('button');
        playButton.className = 'video-play-overlay';
        playButton.innerHTML = '▶';
        playButton.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80px;
            height: 80px;
            background: rgba(0,0,0,0.7);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 30px;
            cursor: pointer;
            z-index: 10;
        `;
        playButton.onclick = () => {
            this.activeVideo.play();
            playButton.remove();
        };
        this.container.appendChild(playButton);
    }
    
    setupEventListeners() {
        // 페이지 가시성 변경 시 일시정지/재생
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pause();
            } else {
                this.resume();
            }
        });
        
        // 저전력 모드 감지
        if ('getBattery' in navigator) {
            navigator.getBattery().then(battery => {
                battery.addEventListener('levelchange', () => {
                    if (battery.level < 0.2 && !battery.charging) {
                        this.pause();
                    }
                });
            });
        }
    }
}

// 사용 예시
const heroVideoCarousel = new VideoCarousel(
    document.querySelector('.hero-video-container'),
    {
        videos: [
            {
                id: 'video1',
                sources: [
                    { src: '/assets/videos/hero-1-1080p.mp4', quality: '1080p', type: 'video/mp4' },
                    { src: '/assets/videos/hero-1-720p.mp4', quality: '720p', type: 'video/mp4' },
                    { src: '/assets/videos/hero-1-480p.mp4', quality: '480p', type: 'video/mp4' }
                ],
                poster: '/assets/images/hero-1-poster.jpg',
                duration: 15 // seconds
            },
            {
                id: 'video2',
                sources: [
                    { src: '/assets/videos/hero-2-1080p.mp4', quality: '1080p', type: 'video/mp4' },
                    { src: '/assets/videos/hero-2-720p.mp4', quality: '720p', type: 'video/mp4' },
                    { src: '/assets/videos/hero-2-480p.mp4', quality: '480p', type: 'video/mp4' }
                ],
                poster: '/assets/images/hero-2-poster.jpg',
                duration: 12
            },
            {
                id: 'video3',
                sources: [
                    { src: '/assets/videos/hero-3-1080p.mp4', quality: '1080p', type: 'video/mp4' },
                    { src: '/assets/videos/hero-3-720p.mp4', quality: '720p', type: 'video/mp4' },
                    { src: '/assets/videos/hero-3-480p.mp4', quality: '480p', type: 'video/mp4' }
                ],
                poster: '/assets/images/hero-3-poster.jpg',
                duration: 18
            },
            {
                id: 'video4',
                sources: [
                    { src: '/assets/videos/hero-4-1080p.mp4', quality: '1080p', type: 'video/mp4' },
                    { src: '/assets/videos/hero-4-720p.mp4', quality: '720p', type: 'video/mp4' },
                    { src: '/assets/videos/hero-4-480p.mp4', quality: '480p', type: 'video/mp4' }
                ],
                poster: '/assets/images/hero-4-poster.jpg',
                duration: 20
            }
        ],
        preloadCount: 2,
        fadeTransition: true,
        transitionDuration: 1000
    }
);