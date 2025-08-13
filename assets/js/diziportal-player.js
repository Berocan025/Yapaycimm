/*
 * DG SPORTS - PlayerJS Integration
 * Developer: DiziPortal.Com
 * Advanced video player with multi-format support and CORS handling
 */

// DiziPortal Player Controller
const DiziPortalPlayer = {
    // Player state
    state: {
        currentPlayer: null,
        isPlaying: false,
        currentStream: null,
        currentTitle: '',
        currentType: '', // 'match' or 'channel'
        currentId: '',
        viewerUpdateInterval: null,
        errorRetryAttempts: 0,
        maxRetryAttempts: 3,
        streamFormats: ['m3u8', 'ts', 'mp4', 'mkv', 'avi', 'webm']
    },

    // Initialize player
    init() {
        console.log('🎬 DiziPortal Player initializing...');
        this.setupPlayerEvents();
        this.detectPlayerJSAvailability();
    },

    // Detect PlayerJS availability
    detectPlayerJSAvailability() {
        if (typeof Playerjs !== 'undefined') {
            console.log('✅ PlayerJS loaded successfully');
            this.isPlayerJSReady = true;
        } else {
            console.warn('⚠️ PlayerJS not detected, loading fallback...');
            this.loadPlayerJSFallback();
        }
    },

    // Load PlayerJS fallback
    loadPlayerJSFallback() {
        const script = document.createElement('script');
        script.src = 'https://cdn.plrjs.com/player/watermark/player.js';
        script.onload = () => {
            console.log('✅ PlayerJS fallback loaded');
            this.isPlayerJSReady = true;
        };
        script.onerror = () => {
            console.error('❌ Failed to load PlayerJS, using HTML5 fallback');
            this.useHTML5Fallback = true;
        };
        document.head.appendChild(script);
    },

    // Setup player events
    setupPlayerEvents() {
        // Close player event
        window.diziportalClosePlayer = () => {
            this.closePlayer();
        };

        // Play stream event
        window.diziportalPlayStream = (streamUrl, title, type, id) => {
            this.playStream(streamUrl, title, type, id);
        };

        // Handle escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.state.isPlaying) {
                this.closePlayer();
            }
        });

        // Handle fullscreen changes
        document.addEventListener('fullscreenchange', () => {
            this.handleFullscreenChange();
        });
    },

    // Play stream
    async playStream(streamUrl, title, type, id) {
        try {
            console.log(`🎬 Playing stream: ${title}`);
            
            // Validate and process stream URL
            const processedUrl = this.processStreamUrl(streamUrl);
            if (!processedUrl) {
                throw new Error('Invalid stream URL format');
            }

            // Update state
            this.state.currentStream = processedUrl;
            this.state.currentTitle = title;
            this.state.currentType = type;
            this.state.currentId = id;
            this.state.errorRetryAttempts = 0;

            // Show player modal
            this.showPlayerModal();

            // Update player title and match info
            this.updatePlayerInfo(title, type, id);

            // Initialize player
            await this.initializePlayer(processedUrl);

            // Start viewer count updates
            this.startViewerCountUpdates();

            // Track analytics
            this.trackPlayEvent(type, id, title);

        } catch (error) {
            console.error('❌ Error playing stream:', error);
            this.handlePlayerError(error);
        }
    },

    // Process stream URL
    processStreamUrl(url) {
        if (!url || typeof url !== 'string') {
            return null;
        }

        // Clean URL
        url = url.trim();

        // Add protocol if missing
        if (url.startsWith('//')) {
            url = 'https:' + url;
        } else if (!url.startsWith('http')) {
            url = 'https://' + url;
        }

        // Detect stream format
        const format = this.detectStreamFormat(url);
        console.log(`📡 Detected stream format: ${format}`);

        // Add CORS proxy if needed
        if (this.needsCorsProxy(url)) {
            url = this.addCorsProxy(url);
        }

        return {
            url: url,
            format: format,
            original: arguments[0]
        };
    },

    // Detect stream format
    detectStreamFormat(url) {
        const urlLower = url.toLowerCase();
        
        if (urlLower.includes('.m3u8') || urlLower.includes('playlist')) {
            return 'hls';
        } else if (urlLower.includes('.ts')) {
            return 'ts';
        } else if (urlLower.includes('.mp4')) {
            return 'mp4';
        } else if (urlLower.includes('.mkv')) {
            return 'mkv';
        } else if (urlLower.includes('.avi')) {
            return 'avi';
        } else if (urlLower.includes('.webm')) {
            return 'webm';
        } else if (urlLower.includes('youtube.com') || urlLower.includes('youtu.be')) {
            return 'youtube';
        } else if (urlLower.includes('twitch.tv')) {
            return 'twitch';
        }
        
        return 'auto';
    },

    // Check if CORS proxy is needed
    needsCorsProxy(url) {
        const currentDomain = window.location.hostname;
        try {
            const urlObj = new URL(url);
            const streamDomain = urlObj.hostname;
            
            // Skip proxy for same domain, localhost, or known CORS-enabled domains
            if (streamDomain === currentDomain || 
                streamDomain === 'localhost' || 
                streamDomain.includes('127.0.0.1') ||
                this.isKnownCorsEnabledDomain(streamDomain)) {
                return false;
            }
            
            return true;
        } catch (error) {
            return false;
        }
    },

    // Check if domain is known to be CORS-enabled
    isKnownCorsEnabledDomain(domain) {
        const corsEnabledDomains = [
            'cdn.plrjs.com',
            'playerjs.com',
            'cdnjs.cloudflare.com',
            'jsdelivr.net',
            'unpkg.com'
        ];
        
        return corsEnabledDomains.some(corseDomain => 
            domain.includes(corseDomain)
        );
    },

    // Add CORS proxy
    addCorsProxy(url) {
        // Multiple CORS proxy options for reliability
        const proxies = [
            'https://api.allorigins.win/raw?url=',
            'https://cors-anywhere.herokuapp.com/',
            'https://thingproxy.freeboard.io/fetch/'
        ];
        
        // Use first available proxy
        const proxy = proxies[0];
        return proxy + encodeURIComponent(url);
    },

    // Show player modal
    showPlayerModal() {
        const modal = document.getElementById('player-modal');
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            this.state.isPlaying = true;
        }
    },

    // Close player
    closePlayer() {
        // Destroy current player
        if (this.state.currentPlayer) {
            try {
                if (typeof this.state.currentPlayer.api === 'function') {
                    this.state.currentPlayer.api('pause');
                    this.state.currentPlayer.api('remove');
                }
            } catch (error) {
                console.warn('Error destroying player:', error);
            }
            this.state.currentPlayer = null;
        }

        // Hide modal
        const modal = document.getElementById('player-modal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Clear intervals
        if (this.state.viewerUpdateInterval) {
            clearInterval(this.state.viewerUpdateInterval);
            this.state.viewerUpdateInterval = null;
        }

        // Reset state
        this.state.isPlaying = false;
        this.state.currentStream = null;
        this.state.currentTitle = '';
        this.state.currentType = '';
        this.state.currentId = '';
    },

    // Update player info
    updatePlayerInfo(title, type, id) {
        // Update title
        const titleElement = document.getElementById('player-title');
        if (titleElement) {
            titleElement.textContent = title;
        }

        // Show/hide match details
        const matchDetails = document.getElementById('match-details');
        if (matchDetails) {
            if (type === 'match') {
                this.updateMatchDetails(id);
                matchDetails.style.display = 'block';
            } else {
                matchDetails.style.display = 'none';
            }
        }
    },

    // Update match details
    updateMatchDetails(matchId) {
        const match = DiziPortalApp.state.matches.find(m => m.id === matchId);
        if (!match) return;

        // Update team logos and names
        const homeTeamLogo = document.getElementById('home-team-logo');
        const homeTeamName = document.getElementById('home-team-name');
        const awayTeamLogo = document.getElementById('away-team-logo');
        const awayTeamName = document.getElementById('away-team-name');
        const matchTime = document.getElementById('match-time');
        const matchLocation = document.getElementById('match-location');

        if (homeTeamLogo) homeTeamLogo.src = match.homeLogo;
        if (homeTeamName) homeTeamName.textContent = match.homeTeam;
        if (awayTeamLogo) awayTeamLogo.src = match.awayLogo;
        if (awayTeamName) awayTeamName.textContent = match.awayTeam;
        if (matchTime) matchTime.textContent = new Date(match.matchTime).toLocaleString('tr-TR');
        if (matchLocation) matchLocation.textContent = match.location;
    },

    // Initialize player
    async initializePlayer(streamData) {
        const playerContainer = document.getElementById('diziportal-player');
        if (!playerContainer) {
            throw new Error('Player container not found');
        }

        // Clear container
        playerContainer.innerHTML = '';

        try {
            if (this.isPlayerJSReady && typeof Playerjs !== 'undefined') {
                await this.initializePlayerJS(playerContainer, streamData);
            } else {
                await this.initializeHTML5Player(playerContainer, streamData);
            }
        } catch (error) {
            console.error('Primary player failed, trying fallback:', error);
            await this.initializeHTML5Player(playerContainer, streamData);
        }
    },

    // Initialize PlayerJS
    async initializePlayerJS(container, streamData) {
        const config = {
            id: container.id,
            file: streamData.url,
            width: '100%',
            height: '100%',
            autoplay: true,
            muted: false,
            controls: true,
            responsive: true,
            aspectratio: '16:9',
            stretching: 'uniform',
            primaryColor: '#dc2626',
            logo: '',
            abouttext: 'DG SPORTS - DiziPortal.Com',
            aboutlink: 'https://diziportal.com',
            crossorigin: 'anonymous',
            preload: 'auto',
            hlsjs: {
                debug: false,
                enableWorker: true,
                lowLatencyMode: true,
                backBufferLength: 90
            }
        };

        // Format-specific configurations
        if (streamData.format === 'hls') {
            config.hlsjs.enableWorker = true;
            config.hlsjs.lowLatencyMode = true;
            config.type = 'hls';
        } else if (streamData.format === 'ts') {
            config.type = 'ts';
        } else if (streamData.format === 'mp4') {
            config.type = 'mp4';
        }

        // Create player
        this.state.currentPlayer = new Playerjs(config);

        // Setup event handlers
        this.setupPlayerJSEvents();
    },

    // Setup PlayerJS events
    setupPlayerJSEvents() {
        if (!this.state.currentPlayer) return;

        try {
            // Player ready
            this.state.currentPlayer.api('on', 'ready', () => {
                console.log('✅ Player ready');
                this.handlePlayerReady();
            });

            // Player error
            this.state.currentPlayer.api('on', 'error', (error) => {
                console.error('❌ Player error:', error);
                this.handlePlayerError(error);
            });

            // Player play
            this.state.currentPlayer.api('on', 'play', () => {
                console.log('▶️ Player started');
                this.handlePlayerPlay();
            });

            // Player pause
            this.state.currentPlayer.api('on', 'pause', () => {
                console.log('⏸️ Player paused');
                this.handlePlayerPause();
            });

            // Player ended
            this.state.currentPlayer.api('on', 'end', () => {
                console.log('🏁 Player ended');
                this.handlePlayerEnd();
            });
        } catch (error) {
            console.warn('Error setting up PlayerJS events:', error);
        }
    },

    // Initialize HTML5 player (fallback)
    async initializeHTML5Player(container, streamData) {
        const video = document.createElement('video');
        video.controls = true;
        video.autoplay = true;
        video.muted = false;
        video.preload = 'auto';
        video.style.width = '100%';
        video.style.height = '100%';
        video.crossOrigin = 'anonymous';

        // Add source
        const source = document.createElement('source');
        source.src = streamData.url;
        
        // Set type based on format
        if (streamData.format === 'hls') {
            source.type = 'application/x-mpegURL';
        } else if (streamData.format === 'mp4') {
            source.type = 'video/mp4';
        } else if (streamData.format === 'webm') {
            source.type = 'video/webm';
        }

        video.appendChild(source);
        container.appendChild(video);

        // Setup HTML5 events
        this.setupHTML5Events(video);

        // Try to load HLS.js for HLS streams
        if (streamData.format === 'hls') {
            await this.loadHLSJS(video, streamData.url);
        }

        this.state.currentPlayer = video;
    },

    // Load HLS.js library
    async loadHLSJS(video, url) {
        if (typeof Hls !== 'undefined') {
            this.initializeHLS(video, url);
            return;
        }

        try {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/hls.js@latest';
            script.onload = () => {
                this.initializeHLS(video, url);
            };
            document.head.appendChild(script);
        } catch (error) {
            console.warn('Failed to load HLS.js:', error);
        }
    },

    // Initialize HLS
    initializeHLS(video, url) {
        if (typeof Hls === 'undefined') return;

        if (Hls.isSupported()) {
            const hls = new Hls({
                enableWorker: true,
                lowLatencyMode: true,
                backBufferLength: 90,
                maxBufferLength: 30,
                maxMaxBufferLength: 60,
                startLevel: -1,
                capLevelToPlayerSize: true
            });

            hls.loadSource(url);
            hls.attachMedia(video);

            hls.on(Hls.Events.MANIFEST_PARSED, () => {
                console.log('✅ HLS manifest parsed');
                video.play().catch(e => console.warn('Autoplay failed:', e));
            });

            hls.on(Hls.Events.ERROR, (event, data) => {
                console.error('HLS Error:', data);
                if (data.fatal) {
                    this.handlePlayerError(data);
                }
            });

            this.state.currentHLS = hls;
        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
            // Native HLS support (Safari)
            video.src = url;
        }
    },

    // Setup HTML5 events
    setupHTML5Events(video) {
        video.addEventListener('loadstart', () => {
            console.log('📺 Video loading started');
        });

        video.addEventListener('canplay', () => {
            console.log('✅ Video can play');
            this.handlePlayerReady();
        });

        video.addEventListener('play', () => {
            this.handlePlayerPlay();
        });

        video.addEventListener('pause', () => {
            this.handlePlayerPause();
        });

        video.addEventListener('ended', () => {
            this.handlePlayerEnd();
        });

        video.addEventListener('error', (e) => {
            this.handlePlayerError(e);
        });
    },

    // Handle player ready
    handlePlayerReady() {
        DiziPortalApp.showMessage('Yayın başlatıldı', 'success');
    },

    // Handle player play
    handlePlayerPlay() {
        // Update UI
    },

    // Handle player pause
    handlePlayerPause() {
        // Update UI
    },

    // Handle player end
    handlePlayerEnd() {
        DiziPortalApp.showMessage('Yayın sona erdi', 'info');
    },

    // Handle player error
    handlePlayerError(error) {
        console.error('Player error:', error);
        
        this.state.errorRetryAttempts++;
        
        if (this.state.errorRetryAttempts < this.state.maxRetryAttempts) {
            console.log(`Retrying... (${this.state.errorRetryAttempts}/${this.state.maxRetryAttempts})`);
            
            setTimeout(() => {
                if (this.state.currentStream) {
                    this.initializePlayer(this.state.currentStream);
                }
            }, 2000);
        } else {
            DiziPortalApp.showMessage('Yayın yüklenirken hata oluştu. Lütfen daha sonra tekrar deneyin.', 'error');
        }
    },

    // Start viewer count updates
    startViewerCountUpdates() {
        if (this.state.viewerUpdateInterval) {
            clearInterval(this.state.viewerUpdateInterval);
        }

        this.state.viewerUpdateInterval = setInterval(() => {
            this.updateViewerCount();
        }, 5000);

        // Initial update
        this.updateViewerCount();
    },

    // Update viewer count
    updateViewerCount() {
        if (!this.state.currentType || !this.state.currentId) return;

        const viewerElement = document.getElementById('current-viewers');
        if (!viewerElement) return;

        let viewers = 0;
        
        if (this.state.currentType === 'match') {
            const match = DiziPortalApp.state.matches.find(m => m.id === this.state.currentId);
            if (match) {
                viewers = match.viewers;
            }
        } else if (this.state.currentType === 'channel') {
            const channel = DiziPortalApp.state.channels.find(c => c.id === this.state.currentId);
            if (channel) {
                viewers = channel.viewers;
            }
        }

        viewerElement.textContent = DiziPortalApp.formatNumber(viewers);
    },

    // Handle fullscreen changes
    handleFullscreenChange() {
        const isFullscreen = document.fullscreenElement !== null;
        
        if (isFullscreen) {
            console.log('📺 Entered fullscreen');
        } else {
            console.log('📺 Exited fullscreen');
        }
    },

    // Track play event
    trackPlayEvent(type, id, title) {
        try {
            // Analytics tracking
            if (typeof gtag !== 'undefined') {
                gtag('event', 'video_play', {
                    video_title: title,
                    video_type: type,
                    video_id: id
                });
            }
            
            console.log(`📊 Tracked play event: ${type} - ${title}`);
        } catch (error) {
            console.warn('Analytics tracking failed:', error);
        }
    }
};

// Initialize player when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    DiziPortalPlayer.init();
});

// Export for global access
window.DiziPortalPlayer = DiziPortalPlayer;