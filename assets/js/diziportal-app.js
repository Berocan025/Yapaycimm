/**
 * DG SPORTS - Main Application JavaScript
 * Developer: DiziPortal.Com
 * Professional sports streaming platform
 */

// Main application object
const DGSports = {
    // Application state
    state: {
        isLoaded: false,
        currentPlayer: null,
        currentContent: null,
        viewerCounts: {},
        settings: {},
        matches: [],
        channels: []
    },

    // Initialize application
    init() {
        console.log('🚀 DG SPORTS initializing...');
        
        // Load data from PHP
        this.loadInitialData();
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Hide loading screen
        setTimeout(() => {
            this.hideLoadingScreen();
        }, 1500);
        
        // Start periodic updates
        this.startPeriodicUpdates();
        
        console.log('✅ DG SPORTS initialized successfully');
    },

    // Load initial data from PHP
    loadInitialData() {
        if (window.DGSportsData) {
            this.state.matches = window.DGSportsData.matches || [];
            this.state.channels = window.DGSportsData.channels || [];
            this.state.settings = window.DGSportsData.settings || {};
            
            console.log('📊 Data loaded:', {
                matches: this.state.matches.length,
                channels: this.state.channels.length,
                settings: Object.keys(this.state.settings).length
            });
        }
    },

    // Setup event listeners
    setupEventListeners() {
        // Navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const section = link.getAttribute('data-section');
                this.navigateToSection(section);
            });
        });

        // Mobile menu toggle
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        if (mobileToggle) {
            mobileToggle.addEventListener('click', this.toggleMobileMenu.bind(this));
        }

        // Match watch buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.match-watch-btn')) {
                e.preventDefault();
                const button = e.target.closest('.match-watch-btn');
                this.playMatch(button);
            }
        });

        // Channel watch buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.channel-watch-btn')) {
                e.preventDefault();
                const button = e.target.closest('.channel-watch-btn');
                this.playChannel(button);
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    this.scrollToElement(target);
                }
            });
        });

        // Scroll events
        window.addEventListener('scroll', this.handleScroll.bind(this));

        // Resize events
        window.addEventListener('resize', this.handleResize.bind(this));

        // Page visibility change
        document.addEventListener('visibilitychange', this.handleVisibilityChange.bind(this));
    },

    // Navigation
    navigateToSection(sectionId) {
        // Update active nav link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        
        const activeLink = document.querySelector(`[data-section="${sectionId}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
        
        // Scroll to section
        const target = document.getElementById(sectionId);
        if (target) {
            this.scrollToElement(target);
        }
    },

    // Smooth scroll to element
    scrollToElement(element) {
        const headerHeight = document.querySelector('.header').offsetHeight;
        const targetPosition = element.offsetTop - headerHeight - 20;
        
        window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
        });
    },

    // Toggle mobile menu
    toggleMobileMenu() {
        const navMenu = document.querySelector('.nav-menu');
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        
        if (navMenu && mobileToggle) {
            navMenu.classList.toggle('active');
            mobileToggle.classList.toggle('active');
        }
    },

    // Handle scroll events
    handleScroll() {
        const header = document.querySelector('.header');
        const scrolled = window.pageYOffset > 100;
        
        if (header) {
            header.classList.toggle('scrolled', scrolled);
        }
        
        // Update active section based on scroll position
        this.updateActiveSection();
        
        // Hide mobile menu on scroll
        const navMenu = document.querySelector('.nav-menu');
        if (navMenu && navMenu.classList.contains('active')) {
            this.toggleMobileMenu();
        }
    },

    // Update active section based on scroll position
    updateActiveSection() {
        const sections = document.querySelectorAll('section[id]');
        const scrollPos = window.pageYOffset + 200;
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            const sectionId = section.getAttribute('id');
            
            if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                // Update nav links
                document.querySelectorAll('.nav-link').forEach(link => {
                    link.classList.remove('active');
                });
                
                const activeLink = document.querySelector(`[data-section="${sectionId}"]`);
                if (activeLink) {
                    activeLink.classList.add('active');
                }
            }
        });
    },

    // Handle resize events
    handleResize() {
        // Close mobile menu on resize
        const navMenu = document.querySelector('.nav-menu');
        if (navMenu && navMenu.classList.contains('active') && window.innerWidth > 768) {
            this.toggleMobileMenu();
        }
        
        // Update player size if active
        if (this.state.currentPlayer) {
            this.updatePlayerSize();
        }
    },

    // Handle visibility change
    handleVisibilityChange() {
        if (document.hidden) {
            // Page is hidden
            this.pauseUpdates();
        } else {
            // Page is visible
            this.resumeUpdates();
        }
    },

    // Play match
    playMatch(button) {
        const streamUrl = button.getAttribute('data-stream-url');
        const matchTitle = button.getAttribute('data-match-title');
        const matchInfo = button.getAttribute('data-match-info');
        const viewers = button.getAttribute('data-viewers');
        
        if (!streamUrl) {
            this.showAlert('Yayın URL\'si bulunamadı', 'error');
            return;
        }
        
        console.log('🎬 Playing match:', matchTitle);
        
        this.showInlinePlayer({
            type: 'match',
            url: streamUrl,
            title: matchTitle,
            subtitle: matchInfo,
            viewers: viewers
        });
        
        // Log viewing activity
        this.logActivity('match_view', {
            title: matchTitle,
            url: streamUrl
        });
    },

    // Play channel
    playChannel(button) {
        const streamUrl = button.getAttribute('data-stream-url');
        const channelTitle = button.getAttribute('data-channel-title');
        const channelInfo = button.getAttribute('data-channel-info');
        const viewers = button.getAttribute('data-viewers');
        
        if (!streamUrl) {
            this.showAlert('Yayın URL\'si bulunamadı', 'error');
            return;
        }
        
        console.log('📺 Playing channel:', channelTitle);
        
        this.showInlinePlayer({
            type: 'channel',
            url: streamUrl,
            title: channelTitle,
            subtitle: channelInfo,
            viewers: viewers
        });
        
        // Log viewing activity
        this.logActivity('channel_view', {
            title: channelTitle,
            url: streamUrl
        });
    },

    // Show inline player
    showInlinePlayer(content) {
        const playerContainer = document.getElementById('inline-player-container');
        const playingTitle = document.getElementById('playing-title');
        const playingSubtitle = document.getElementById('playing-subtitle');
        const viewerCount = document.getElementById('inline-viewer-count');
        
        if (!playerContainer) {
            console.error('Player container not found');
            return;
        }
        
        // Update content info
        if (playingTitle) playingTitle.textContent = content.title;
        if (playingSubtitle) playingSubtitle.textContent = content.subtitle;
        if (viewerCount) viewerCount.textContent = this.formatNumber(content.viewers);
        
        // Show player container
        playerContainer.style.display = 'block';
        
        // Scroll to player
        setTimeout(() => {
            this.scrollToElement(playerContainer);
        }, 100);
        
        // Initialize player
        this.initializePlayer(content.url);
        
        // Store current content
        this.state.currentContent = content;
    },

    // Initialize video player
    initializePlayer(streamUrl) {
        const playerElement = document.getElementById('inline-player');
        const overlay = document.getElementById('player-overlay');
        
        if (!playerElement) {
            console.error('Player element not found');
            return;
        }
        
        // Show loading overlay
        if (overlay) overlay.style.display = 'flex';
        
        // Destroy existing player
        if (this.state.currentPlayer) {
            this.state.currentPlayer.destroy();
        }
        
        try {
            // Create new player
            this.state.currentPlayer = new Playerjs({
                id: 'inline-player',
                file: streamUrl,
                poster: '',
                autoplay: true,
                muted: false,
                preload: 'auto',
                controls: true,
                fluid: true,
                responsive: true,
                hlsjs: {
                    debug: false,
                    p2pConfig: {
                        logLevel: 'none'
                    }
                }
            });
            
            // Player event handlers
            this.state.currentPlayer.api('play', () => {
                console.log('▶️ Player started');
                if (overlay) overlay.style.display = 'none';
            });
            
            this.state.currentPlayer.api('error', (error) => {
                console.error('❌ Player error:', error);
                this.showAlert('Yayın yüklenirken bir hata oluştu', 'error');
                if (overlay) overlay.style.display = 'none';
            });
            
            this.state.currentPlayer.api('loaded', () => {
                console.log('✅ Player loaded');
                if (overlay) overlay.style.display = 'none';
            });
            
        } catch (error) {
            console.error('Player initialization error:', error);
            this.showAlert('Player başlatılamadı', 'error');
            if (overlay) overlay.style.display = 'none';
        }
    },

    // Close inline player
    closeInlinePlayer() {
        const playerContainer = document.getElementById('inline-player-container');
        
        if (playerContainer) {
            playerContainer.style.display = 'none';
        }
        
        // Destroy player
        if (this.state.currentPlayer) {
            this.state.currentPlayer.destroy();
            this.state.currentPlayer = null;
        }
        
        // Clear current content
        this.state.currentContent = null;
        
        console.log('⏹️ Player closed');
    },

    // Open fullscreen
    openFullscreen() {
        const playerElement = document.getElementById('inline-player');
        
        if (playerElement) {
            if (playerElement.requestFullscreen) {
                playerElement.requestFullscreen();
            } else if (playerElement.webkitRequestFullscreen) {
                playerElement.webkitRequestFullscreen();
            } else if (playerElement.msRequestFullscreen) {
                playerElement.msRequestFullscreen();
            }
        }
    },

    // Update player size
    updatePlayerSize() {
        if (this.state.currentPlayer) {
            // Player.js handles responsive sizing automatically
            console.log('🔄 Player size updated');
        }
    },

    // Show alert message
    showAlert(message, type = 'info') {
        // Create alert element
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `
            <div class="alert-content">
                <i class="fas fa-${this.getAlertIcon(type)}"></i>
                <span>${message}</span>
                <button class="alert-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        // Add to page
        document.body.appendChild(alert);
        
        // Show alert
        setTimeout(() => alert.classList.add('show'), 100);
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 300);
        }, 5000);
        
        // Close button
        alert.querySelector('.alert-close').addEventListener('click', () => {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 300);
        });
    },

    // Get alert icon
    getAlertIcon(type) {
        const icons = {
            'success': 'check-circle',
            'error': 'exclamation-triangle',
            'warning': 'exclamation-circle',
            'info': 'info-circle'
        };
        return icons[type] || 'info-circle';
    },

    // Format number with K/M suffix
    formatNumber(num) {
        const number = parseInt(num) || 0;
        if (number >= 1000000) {
            return (number / 1000000).toFixed(1) + 'M';
        } else if (number >= 1000) {
            return (number / 1000).toFixed(1) + 'K';
        }
        return number.toString();
    },

    // Hide loading screen
    hideLoadingScreen() {
        const loadingScreen = document.getElementById('loading-screen');
        if (loadingScreen) {
            loadingScreen.classList.add('hidden');
            setTimeout(() => {
                loadingScreen.style.display = 'none';
            }, 500);
        }
        this.state.isLoaded = true;
    },

    // Start periodic updates
    startPeriodicUpdates() {
        // Update viewer counts every 30 seconds
        this.viewerUpdateInterval = setInterval(() => {
            this.updateViewerCounts();
        }, 30000);
        
        // Update stats every 60 seconds
        this.statsUpdateInterval = setInterval(() => {
            this.updateStats();
        }, 60000);
    },

    // Pause updates
    pauseUpdates() {
        if (this.viewerUpdateInterval) {
            clearInterval(this.viewerUpdateInterval);
        }
        if (this.statsUpdateInterval) {
            clearInterval(this.statsUpdateInterval);
        }
    },

    // Resume updates
    resumeUpdates() {
        this.startPeriodicUpdates();
    },

    // Update viewer counts
    updateViewerCounts() {
        // Simulate realistic viewer count changes
        document.querySelectorAll('.match-viewers, .channel-viewers').forEach(element => {
            const currentText = element.textContent;
            const match = currentText.match(/(\d+(?:\.\d+)?[KM]?)/);
            
            if (match) {
                let currentCount = this.parseNumber(match[1]);
                // Random change between -5% to +10%
                const changePercent = (Math.random() * 0.15) - 0.05;
                let newCount = Math.max(50, Math.floor(currentCount * (1 + changePercent)));
                
                element.innerHTML = element.innerHTML.replace(
                    match[1], 
                    this.formatNumber(newCount)
                );
            }
        });
        
        // Update inline player viewer count
        const inlineViewer = document.getElementById('inline-viewer-count');
        if (inlineViewer && this.state.currentContent) {
            const current = this.parseNumber(inlineViewer.textContent);
            const change = Math.floor((Math.random() * 0.1 - 0.05) * current);
            const newCount = Math.max(50, current + change);
            inlineViewer.textContent = this.formatNumber(newCount);
        }
    },

    // Parse number from formatted string
    parseNumber(str) {
        if (str.includes('M')) {
            return parseFloat(str) * 1000000;
        } else if (str.includes('K')) {
            return parseFloat(str) * 1000;
        }
        return parseInt(str) || 0;
    },

    // Update statistics
    updateStats() {
        const liveMatchesCount = document.getElementById('live-matches-count');
        const channelsCount = document.getElementById('channels-count');
        const viewersCount = document.getElementById('viewers-count');
        
        // Small random variations in stats
        if (Math.random() < 0.3) { // 30% chance to update
            if (liveMatchesCount) {
                const current = parseInt(liveMatchesCount.textContent) || 0;
                const change = Math.random() > 0.5 ? 1 : -1;
                liveMatchesCount.textContent = Math.max(0, current + change);
            }
        }
        
        // Always update total viewers
        if (viewersCount) {
            let totalViewers = 0;
            document.querySelectorAll('.match-viewers, .channel-viewers').forEach(element => {
                const match = element.textContent.match(/(\d+(?:\.\d+)?[KM]?)/);
                if (match) {
                    totalViewers += this.parseNumber(match[1]);
                }
            });
            viewersCount.textContent = this.formatNumber(totalViewers);
        }
    },

    // Log activity
    logActivity(action, data = {}) {
        // In a real application, this would send data to the server
        console.log('📊 Activity logged:', action, data);
        
        // Store in localStorage for demo purposes
        const activities = JSON.parse(localStorage.getItem('dg_activities') || '[]');
        activities.push({
            action,
            data,
            timestamp: new Date().toISOString(),
            url: window.location.href,
            userAgent: navigator.userAgent
        });
        
        // Keep only last 100 activities
        if (activities.length > 100) {
            activities.splice(0, activities.length - 100);
        }
        
        localStorage.setItem('dg_activities', JSON.stringify(activities));
    }
};

// Global functions for HTML onclick handlers
window.closeInlinePlayer = function() {
    DGSports.closeInlinePlayer();
};

window.openFullscreen = function() {
    DGSports.openFullscreen();
};

// Initialize application when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    DGSports.init();
});

// Handle page unload
window.addEventListener('beforeunload', function() {
    if (DGSports.state.currentPlayer) {
        DGSports.state.currentPlayer.destroy();
    }
    DGSports.pauseUpdates();
});

// Export for potential use by other scripts
window.DGSports = DGSports;