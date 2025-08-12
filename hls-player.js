/**
 * DiziPortal.Com - Advanced HLS Player Module with CORS Protection
 * Developer: DiziPortal.Com Development Team
 * Version: 2.0 - Professional Sports Streaming Solution with Anti-DevTools
 */

class DiziPortalHLSPlayer {
    constructor(videoElement, options = {}) {
        this.video = videoElement;
        this.hls = null;
        this.currentUrl = null;
        this.corsProxyList = [
            'https://cors-anywhere.herokuapp.com/',
            'https://api.allorigins.win/raw?url=',
            'https://corsproxy.io/?',
            'https://thingproxy.freeboard.io/fetch/'
        ];
        this.currentProxyIndex = 0;
        this.options = {
            debug: false,
            enableWorker: true,
            lowLatencyMode: true,
            backBufferLength: 90,
            maxBufferLength: 30,
            maxMaxBufferLength: 600,
            maxBufferSize: 60 * 1000 * 1000,
            maxBufferHole: 0.5,
            highBufferWatchdogPeriod: 2,
            nudgeOffset: 0.1,
            nudgeMaxRetry: 3,
            maxFragLookUpTolerance: 0.25,
            liveSyncDurationCount: 3,
            liveMaxLatencyDurationCount: 10,
            liveDurationInfinity: false,
            enableSoftwareAES: true,
            manifestLoadingTimeOut: 15000,
            manifestLoadingMaxRetry: 3,
            manifestLoadingRetryDelay: 2000,
            levelLoadingTimeOut: 15000,
            levelLoadingMaxRetry: 6,
            levelLoadingRetryDelay: 2000,
            fragLoadingTimeOut: 25000,
            fragLoadingMaxRetry: 8,
            fragLoadingRetryDelay: 2000,
            startFragPrefetch: true,
            testBandwidth: true,
            progressive: false,
            xhrSetup: (xhr, url) => {
                xhr.setRequestHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
                xhr.setRequestHeader('Referer', 'https://diziportal.com/');
                xhr.setRequestHeader('Origin', 'https://diziportal.com');
            },
            ...options
        };
        
        console.log('🚀 DiziPortal.Com HLS Player v2.0 initializing...');
        this.diziportalInit();
    }

    diziportalInit() {
        // Check if HLS.js is supported
        if (this.diziportalIsHLSJSSupported()) {
            console.log('✅ DiziPortal.Com: HLS.js is supported');
            this.diziportalInitHLSJS();
        } else if (this.diziportalIsNativeHLSSupported()) {
            console.log('📱 DiziPortal.Com: Native HLS is supported');
            this.diziportalUseNativeHLS();
        } else {
            console.error('❌ DiziPortal.Com: HLS is not supported in this browser');
            this.diziportalShowError('Bu tarayıcı HLS video formatını desteklemiyor');
        }

        this.diziportalSetupEventListeners();
    }

    diziportalIsHLSJSSupported() {
        return typeof Hls !== 'undefined' && Hls.isSupported();
    }

    diziportalIsNativeHLSSupported() {
        return this.video.canPlayType('application/vnd.apple.mpegurl') !== '';
    }

    diziportalInitHLSJS() {
        try {
            this.hls = new Hls(this.options);
            
            // Setup HLS.js event listeners
            this.hls.on(Hls.Events.MEDIA_ATTACHED, () => {
                console.log('📺 DiziPortal.Com: HLS media attached');
            });

            this.hls.on(Hls.Events.MANIFEST_PARSED, (event, data) => {
                console.log('📋 DiziPortal.Com: HLS manifest parsed, found ' + data.levels.length + ' quality levels');
                this.diziportalOnManifestParsed(data);
            });

            this.hls.on(Hls.Events.LEVEL_SWITCHED, (event, data) => {
                console.log('🔄 DiziPortal.Com: Quality level switched to ' + data.level);
            });

            this.hls.on(Hls.Events.FRAG_LOADED, (event, data) => {
                // Fragment loaded successfully
            });

            this.hls.on(Hls.Events.ERROR, (event, data) => {
                this.diziportalHandleHLSError(data);
            });

            // Attach video element
            this.hls.attachMedia(this.video);
            
        } catch (error) {
            console.error('❌ DiziPortal.Com: Error initializing HLS.js', error);
            this.diziportalShowError('HLS player başlatılamadı');
        }
    }

    diziportalUseNativeHLS() {
        this.video.addEventListener('loadstart', () => {
            console.log('📱 DiziPortal.Com: Native HLS loading started');
        });

        this.video.addEventListener('canplay', () => {
            console.log('✅ DiziPortal.Com: Native HLS can play');
        });

        this.video.addEventListener('error', (e) => {
            console.error('❌ DiziPortal.Com: Native HLS error', e);
            this.diziportalShowError('Video yükleme hatası');
        });
    }

    diziportalLoadSource(url, title = '') {
        if (!url) {
            console.error('❌ DiziPortal.Com: No URL provided');
            return false;
        }

        // Clean and validate URL
        url = this.diziportalProcessURL(url);
        
        if (!this.diziportalValidateURL(url)) {
            console.error('❌ DiziPortal.Com: Invalid URL format');
            this.diziportalShowError('Geçersiz video URL formatı');
            return false;
        }

        this.currentUrl = url;
        console.log('🔄 DiziPortal.Com: Loading source:', url);

        try {
            if (this.hls) {
                // Using HLS.js
                this.hls.loadSource(url);
                console.log('📺 DiziPortal.Com: HLS.js source loaded');
            } else {
                // Using native HLS
                this.video.src = url;
                console.log('📱 DiziPortal.Com: Native HLS source set');
            }

            // Set video attributes for better streaming
            this.video.setAttribute('crossorigin', 'anonymous');
            this.video.setAttribute('preload', 'metadata');
            
            // Try to play
            this.diziportalAttemptPlay();
            
            return true;
        } catch (error) {
            console.error('❌ DiziPortal.Com: Error loading source', error);
            this.diziportalShowError('Video kaynağı yüklenemedi');
            return false;
        }
    }

    diziportalProcessURL(url) {
        // Clean whitespace
        url = url.trim();
        
        // Always try CORS proxy for better compatibility
        return this.diziportalApplyCORSProxy(url);
    }

    diziportalValidateURL(url) {
        try {
            const urlObj = new URL(url);
            const validProtocols = ['http:', 'https:'];
            const validExtensions = ['.m3u8', '.mp4', '.webm', '.ogg'];
            
            if (!validProtocols.includes(urlObj.protocol)) {
                return false;
            }
            
            // Check if URL ends with valid extension or contains m3u8
            const pathname = urlObj.pathname.toLowerCase();
            const hasValidExt = validExtensions.some(ext => pathname.includes(ext));
            const hasM3U8 = url.toLowerCase().includes('m3u8');
            
            return hasValidExt || hasM3U8;
        } catch (error) {
            return false;
        }
    }

    diziportalNeedsCORSProxy(url) {
        // List of domains that typically need CORS proxy
        const corsProblematicDomains = [
            'example.com',
            'test.com'
            // Add more domains as needed
        ];
        
        try {
            const urlObj = new URL(url);
            return corsProblematicDomains.some(domain => urlObj.hostname.includes(domain));
        } catch (error) {
            return false;
        }
    }

    diziportalApplyCORSProxy(url) {
        // Skip proxy if URL is already proxied or is a data URL
        if (url.includes('cors-anywhere') || url.includes('allorigins') || 
            url.includes('corsproxy') || url.includes('thingproxy') || 
            url.startsWith('data:') || url.startsWith('blob:')) {
            return url;
        }

        // Try different CORS proxies for better reliability
        const proxyUrl = this.corsProxyList[this.currentProxyIndex] + encodeURIComponent(url);
        console.log(`🔄 DiziPortal.Com: Using CORS proxy (${this.currentProxyIndex + 1}/${this.corsProxyList.length}):`, proxyUrl);
        return proxyUrl;
    }

    diziportalTryNextProxy(originalUrl) {
        this.currentProxyIndex = (this.currentProxyIndex + 1) % this.corsProxyList.length;
        console.log(`🔄 DiziPortal.Com: Trying next CORS proxy (${this.currentProxyIndex + 1}/${this.corsProxyList.length})`);
        return this.diziportalApplyCORSProxy(originalUrl);
    }

    diziportalAttemptPlay() {
        const playPromise = this.video.play();
        
        if (playPromise !== undefined) {
            playPromise
                .then(() => {
                    console.log('▶️ DiziPortal.Com: Video playback started');
                })
                .catch((error) => {
                    console.log('⏸️ DiziPortal.Com: Autoplay prevented, user interaction required');
                    // Autoplay was prevented, this is normal
                });
        }
    }

    diziportalOnManifestParsed(data) {
        // Automatically select the best quality level
        if (this.hls && data.levels.length > 0) {
            // Start with automatic quality selection
            this.hls.currentLevel = -1;
            console.log('🎯 DiziPortal.Com: Auto quality selection enabled');
        }
    }

    diziportalHandleHLSError(data) {
        console.error('❌ DiziPortal.Com: HLS Error:', data);
        
        switch (data.type) {
            case Hls.ErrorTypes.NETWORK_ERROR:
                console.log('🌐 DiziPortal.Com: Network error, attempting recovery...');
                if (data.details === Hls.ErrorDetails.MANIFEST_LOAD_ERROR && this.currentProxyIndex < this.corsProxyList.length - 1) {
                    console.log('🔄 DiziPortal.Com: Trying next CORS proxy...');
                    const originalUrl = this.currentUrl.split(this.corsProxyList[this.currentProxyIndex])[1];
                    if (originalUrl) {
                        const decodedUrl = decodeURIComponent(originalUrl);
                        const nextProxyUrl = this.diziportalTryNextProxy(decodedUrl);
                        this.diziportalLoadSource(nextProxyUrl);
                        return;
                    }
                }
                this.diziportalRecoverNetworkError();
                break;
                
            case Hls.ErrorTypes.MEDIA_ERROR:
                console.log('📺 DiziPortal.Com: Media error, attempting recovery...');
                this.diziportalRecoverMediaError();
                break;
                
            default:
                if (data.fatal) {
                    console.error('💥 DiziPortal.Com: Fatal error, destroying player');
                    this.diziportalShowError('Yayın bağlantısında sorun oluştu. Lütfen daha sonra tekrar deneyin.');
                    // Don't destroy, just show error
                }
                break;
        }
    }

    diziportalRecoverNetworkError() {
        if (this.hls) {
            this.hls.startLoad();
        }
    }

    diziportalRecoverMediaError() {
        if (this.hls) {
            this.hls.recoverMediaError();
        }
    }

    diziportalSetupEventListeners() {
        this.video.addEventListener('loadstart', () => {
            console.log('🎬 DiziPortal.Com: Video loading started');
        });

        this.video.addEventListener('loadedmetadata', () => {
            console.log('📊 DiziPortal.Com: Video metadata loaded');
        });

        this.video.addEventListener('canplay', () => {
            console.log('✅ DiziPortal.Com: Video can play');
        });

        this.video.addEventListener('playing', () => {
            console.log('▶️ DiziPortal.Com: Video is playing');
        });

        this.video.addEventListener('pause', () => {
            console.log('⏸️ DiziPortal.Com: Video paused');
        });

        this.video.addEventListener('ended', () => {
            console.log('🏁 DiziPortal.Com: Video ended');
        });

        this.video.addEventListener('error', (e) => {
            console.error('❌ DiziPortal.Com: Video element error:', e);
            this.diziportalShowError('Video oynatma hatası');
        });

        this.video.addEventListener('waiting', () => {
            console.log('⏳ DiziPortal.Com: Video buffering...');
        });

        this.video.addEventListener('timeupdate', () => {
            // Handle time updates if needed
        });
    }

    diziportalShowError(message) {
        // Create error overlay
        const errorOverlay = document.createElement('div');
        errorOverlay.className = 'diziportal-video-error';
        errorOverlay.innerHTML = `
            <div class="diziportal-error-content">
                <div class="diziportal-error-icon">⚠️</div>
                <div class="diziportal-error-message">${message}</div>
                <div class="diziportal-error-footer">DiziPortal.Com - Box Sports</div>
            </div>
        `;
        
        // Style the error overlay
        errorOverlay.style.cssText = `
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            z-index: 100;
        `;
        
        // Add to video container
        const container = this.video.parentElement;
        if (container) {
            container.style.position = 'relative';
            container.appendChild(errorOverlay);
            
            // Remove error after 5 seconds
            setTimeout(() => {
                if (errorOverlay.parentElement) {
                    errorOverlay.parentElement.removeChild(errorOverlay);
                }
            }, 5000);
        }
    }

    diziportalDestroy() {
        console.log('🗑️ DiziPortal.Com: Destroying HLS player');
        
        if (this.hls) {
            this.hls.destroy();
            this.hls = null;
        }
        
        this.video.src = '';
        this.currentUrl = null;
    }

    diziportalGetQualityLevels() {
        if (this.hls && this.hls.levels) {
            return this.hls.levels.map((level, index) => ({
                index: index,
                height: level.height,
                width: level.width,
                bitrate: level.bitrate,
                name: `${level.height}p (${Math.round(level.bitrate / 1000)}k)`
            }));
        }
        return [];
    }

    diziportalSetQualityLevel(levelIndex) {
        if (this.hls) {
            this.hls.currentLevel = levelIndex;
            console.log(`🎯 DiziPortal.Com: Quality level set to ${levelIndex}`);
        }
    }

    diziportalGetCurrentQuality() {
        if (this.hls) {
            return this.hls.currentLevel;
        }
        return -1;
    }

    diziportalIsLive() {
        if (this.hls && this.hls.levels && this.hls.levels[0]) {
            return this.hls.levels[0].details && this.hls.levels[0].details.live;
        }
        return false;
    }

    diziportalSeekToLive() {
        if (this.diziportalIsLive() && this.video.duration) {
            this.video.currentTime = this.video.duration;
            console.log('🔴 DiziPortal.Com: Seeking to live position');
        }
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DiziPortalHLSPlayer;
}

// Global variable for browser usage
window.DiziPortalHLSPlayer = DiziPortalHLSPlayer;

console.log(`
🎬 DiziPortal.Com HLS Player Module Loaded
🚀 Advanced Sports Streaming Technology
⚡ Version 1.0 - Professional Grade
`);