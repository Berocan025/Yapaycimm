/*
 * DG SPORTS - Core Application
 * Developer: DiziPortal.Com
 * Main application logic and initialization
 */

// DiziPortal Core Application
const DiziPortalApp = {
    // Application state
    state: {
        isLoaded: false,
        currentUser: null,
        isAdminLoggedIn: false,
        activeSection: 'hero',
        matches: [],
        channels: [],
        settings: {
            siteTitle: 'DG SPORTS',
            siteDescription: 'Kaliteli HD yayın ile tüm spor müsabakalarını canlı izleyin',
            socialLinks: {
                telegram: '',
                instagram: '',
                twitter: '',
                tiktok: ''
            }
        },
        viewerCounts: {
            total: 0,
            matches: {},
            channels: {}
        }
    },

    // Initialize application
    init() {
        console.log('🚀 DiziPortal DG SPORTS initializing...');
        
        // Load saved data
        this.loadLocalData();
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Initialize components
        this.initializeComponents();
        
        // Hide loading screen
        setTimeout(() => {
            this.hideLoadingScreen();
        }, 2000);
        
        // Start viewer count simulation
        this.startViewerCountSimulation();
        
        console.log('✅ DiziPortal DG SPORTS initialized successfully');
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

        // Admin login button
        const adminLoginBtn = document.getElementById('admin-login-btn');
        if (adminLoginBtn) {
            adminLoginBtn.addEventListener('click', this.showAdminLogin.bind(this));
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Window scroll events
        window.addEventListener('scroll', this.handleScroll.bind(this));

        // Window resize events
        window.addEventListener('resize', this.handleResize.bind(this));

        // Keyboard shortcuts
        document.addEventListener('keydown', this.handleKeyboard.bind(this));
    },

    // Initialize components
    initializeComponents() {
        // Load matches and channels
        this.loadMatches();
        this.loadChannels();
        
        // Update statistics
        this.updateStatistics();
        
        // Setup channel filters
        this.setupChannelFilters();
        
        // Initialize social links
        this.updateSocialLinks();
    },

    // Hide loading screen
    hideLoadingScreen() {
        const loadingScreen = document.getElementById('diziportal-loading');
        if (loadingScreen) {
            loadingScreen.classList.add('hidden');
            setTimeout(() => {
                loadingScreen.style.display = 'none';
            }, 500);
        }
        this.state.isLoaded = true;
    },

    // Navigation
    navigateToSection(sectionId) {
        this.state.activeSection = sectionId;
        
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
            const headerHeight = document.querySelector('.header').offsetHeight;
            const targetPosition = target.offsetTop - headerHeight - 20;
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
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
    },

    // Update active section based on scroll
    updateActiveSection() {
        const sections = ['hero', 'live-matches', 'channels', 'contact'];
        const headerHeight = document.querySelector('.header').offsetHeight;
        
        for (let section of sections) {
            const element = document.getElementById(section);
            if (element) {
                const rect = element.getBoundingClientRect();
                if (rect.top <= headerHeight + 50 && rect.bottom >= headerHeight + 50) {
                    if (this.state.activeSection !== section) {
                        this.state.activeSection = section;
                        
                        // Update nav links
                        document.querySelectorAll('.nav-link').forEach(link => {
                            link.classList.remove('active');
                        });
                        
                        const activeLink = document.querySelector(`[data-section="${section}"]`);
                        if (activeLink) {
                            activeLink.classList.add('active');
                        }
                    }
                    break;
                }
            }
        }
    },

    // Handle resize events
    handleResize() {
        // Close mobile menu on resize
        if (window.innerWidth > 768) {
            const navMenu = document.querySelector('.nav-menu');
            const mobileToggle = document.querySelector('.mobile-menu-toggle');
            
            if (navMenu) navMenu.classList.remove('active');
            if (mobileToggle) mobileToggle.classList.remove('active');
        }
    },

    // Handle keyboard shortcuts
    handleKeyboard(e) {
        // ESC key - close modals
        if (e.key === 'Escape') {
            this.closeAllModals();
        }
        
        // Admin shortcut: Ctrl+Shift+A
        if (e.ctrlKey && e.shiftKey && e.key === 'A') {
            e.preventDefault();
            this.showAdminLogin();
        }
    },

    // Close all modals
    closeAllModals() {
        const modals = document.querySelectorAll('.admin-panel, .admin-login-modal, .player-modal');
        modals.forEach(modal => {
            modal.classList.remove('active');
        });
    },

    // Show admin login
    showAdminLogin() {
        const loginModal = document.getElementById('admin-login-modal');
        if (loginModal) {
            loginModal.classList.add('active');
            
            // Focus on username field
            const usernameField = loginModal.querySelector('input[name="username"]');
            if (usernameField) {
                setTimeout(() => usernameField.focus(), 100);
            }
        }
    },

    // Load matches
    loadMatches() {
        // Load from localStorage or use defaults
        const savedMatches = localStorage.getItem('diziportal_matches');
        if (savedMatches) {
            this.state.matches = JSON.parse(savedMatches);
        } else {
            // Default matches for demo
            this.state.matches = [
                {
                    id: 'match1',
                    homeTeam: 'Galatasaray',
                    awayTeam: 'Fenerbahçe',
                    homeLogo: 'https://logoeps.com/wp-content/uploads/2013/03/galatasaray-vector-logo.png',
                    awayLogo: 'https://logoeps.com/wp-content/uploads/2013/03/fenerbahce-vector-logo.png',
                    matchTime: new Date(Date.now() + 2 * 60 * 60 * 1000).toISOString(),
                    location: 'Türk Telekom Stadyumu',
                    streamUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                    isLive: true,
                    viewers: Math.floor(Math.random() * 50000) + 15000
                },
                {
                    id: 'match2',
                    homeTeam: 'Real Madrid',
                    awayTeam: 'Barcelona',
                    homeLogo: 'https://logoeps.com/wp-content/uploads/2013/03/real-madrid-vector-logo.png',
                    awayLogo: 'https://logoeps.com/wp-content/uploads/2013/03/barcelona-vector-logo.png',
                    matchTime: new Date(Date.now() + 4 * 60 * 60 * 1000).toISOString(),
                    location: 'Santiago Bernabéu',
                    streamUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
                    isLive: true,
                    viewers: Math.floor(Math.random() * 80000) + 25000
                },
                {
                    id: 'match3',
                    homeTeam: 'Manchester United',
                    awayTeam: 'Liverpool',
                    homeLogo: 'https://logoeps.com/wp-content/uploads/2013/03/manchester-united-vector-logo.png',
                    awayLogo: 'https://logoeps.com/wp-content/uploads/2013/03/liverpool-vector-logo.png',
                    matchTime: new Date(Date.now() + 6 * 60 * 60 * 1000).toISOString(),
                    location: 'Old Trafford',
                    streamUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
                    isLive: false,
                    viewers: 0
                }
            ];
        }
        
        this.renderMatches();
    },

    // Load channels
    loadChannels() {
        // Load from localStorage or use defaults
        const savedChannels = localStorage.getItem('diziportal_channels');
        if (savedChannels) {
            this.state.channels = JSON.parse(savedChannels);
        } else {
            // Default channels for demo
            this.state.channels = [
                {
                    id: 'channel1',
                    name: 'beIN SPORTS 1 HD',
                    logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/36/Bein_sports_1.png/512px-Bein_sports_1.png',
                    category: 'futbol',
                    streamUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
                    isLive: true,
                    viewers: Math.floor(Math.random() * 35000) + 12000
                },
                {
                    id: 'channel2',
                    name: 'TRT SPOR',
                    logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/15/TRT_Spor_logo.png/512px-TRT_Spor_logo.png',
                    category: 'genel',
                    streamUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4',
                    isLive: true,
                    viewers: Math.floor(Math.random() * 20000) + 8000
                },
                {
                    id: 'channel3',
                    name: 'S SPORT',
                    logo: 'https://upload.wikimedia.org/wikipedia/tr/thumb/7/7f/S_Sport_logo.png/512px-S_Sport_logo.png',
                    category: 'futbol',
                    streamUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4',
                    isLive: true,
                    viewers: Math.floor(Math.random() * 25000) + 10000
                },
                {
                    id: 'channel4',
                    name: 'ESPN HD',
                    logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/2f/ESPN_wordmark.svg/512px-ESPN_wordmark.svg.png',
                    category: 'basketbol',
                    streamUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                    isLive: true,
                    viewers: Math.floor(Math.random() * 40000) + 15000
                },
                {
                    id: 'channel4',
                    name: 'TRT 3',
                    logo: 'https://upload.wikimedia.org/wikipedia/commons/9/94/TRT_3_logo.png',
                    category: 'genel',
                    streamUrl: 'https://example.com/trt-3.m3u8',
                    isLive: true,
                    viewers: Math.floor(Math.random() * 15000) + 3000
                }
            ];
        }
        
        this.renderChannels();
    },

    // Render matches
    renderMatches() {
        const container = document.getElementById('matches-container');
        if (!container) return;
        
        if (this.state.matches.length === 0) {
            container.innerHTML = `
                <div class="no-content">
                    <i class="fas fa-tv"></i>
                    <p>Henüz canlı maç bulunmuyor</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = this.state.matches.map(match => `
            <div class="match-card" onclick="diziportalPlayStream('${match.streamUrl}', '${match.homeTeam} vs ${match.awayTeam}', 'match', '${match.id}')">
                <div class="match-header">
                    <div class="match-time">
                        ${new Date(match.matchTime).toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })}
                    </div>
                    <div class="match-status">
                        ${match.isLive ? '<div class="live-indicator"></div><span>CANLI</span>' : '<span>YAKINDA</span>'}
                    </div>
                </div>
                <div class="match-teams">
                    <div class="team">
                        <img src="${match.homeLogo}" alt="${match.homeTeam}" class="team-logo" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTAiIGhlaWdodD0iNTAiIHZpZXdCb3g9IjAgMCA1MCA1MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjUiIGN5PSIyNSIgcj0iMjUiIGZpbGw9IiNkYzI2MjYiLz4KPHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4PSIxMyIgeT0iMTMiPgo8cGF0aCBkPSJNMTIgMkM2LjQ4IDIgMiA2LjQ4IDIgMTJzNC40OCAxMCAxMCAxMCAxMC00LjQ4IDEwLTEwUzE3LjUyIDIgMTIgMnoiIGZpbGw9IndoaXRlIi8+CjwvcGF0aD4KPC9zdmc+'" />
                        <span class="team-name">${match.homeTeam}</span>
                    </div>
                    <div class="vs">VS</div>
                    <div class="team">
                        <img src="${match.awayLogo}" alt="${match.awayTeam}" class="team-logo" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTAiIGhlaWdodD0iNTAiIHZpZXdCb3g9IjAgMCA1MCA1MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjUiIGN5PSIyNSIgcj0iMjUiIGZpbGw9IiNkYzI2MjYiLz4KPHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4PSIxMyIgeT0iMTMiPgo8cGF0aCBkPSJNMTIgMkM2LjQ4IDIgMiA2LjQ4IDIgMTJzNC40OCAxMCAxMCAxMCAxMC00LjQ4IDEwLTEwUzE3LjUyIDIgMTIgMnoiIGZpbGw9IndoaXRlIi8+CjwvcGF0aD4KPC9zdmc+'" />
                        <span class="team-name">${match.awayTeam}</span>
                    </div>
                </div>
                <div class="match-info">
                    <div class="match-location">
                        <i class="fas fa-map-marker-alt"></i>
                        ${match.location}
                    </div>
                    <div class="viewer-count">
                        <i class="fas fa-eye"></i>
                        ${this.formatNumber(match.viewers)} izleyici
                    </div>
                </div>
            </div>
        `).join('');
    },

    // Render channels
    renderChannels() {
        const container = document.getElementById('channels-container');
        if (!container) return;
        
        if (this.state.channels.length === 0) {
            container.innerHTML = `
                <div class="no-content">
                    <i class="fas fa-tv"></i>
                    <p>Henüz kanal bulunmuyor</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = this.state.channels.map(channel => `
            <div class="channel-card" data-category="${channel.category}" onclick="playInlineChannel('${channel.id}')">
                <img src="${channel.logo}" alt="${channel.name}" class="channel-logo" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiByeD0iMTIiIGZpbGw9IiNkYzI2MjYiLz4KPHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4PSIyMCIgeT0iMjAiPgo8cGF0aCBkPSJNMjAgNEMxMS4xNiA0IDQgMTEuMTYgNCAyMHM3LjE2IDE2IDE2IDE2IDE2LTcuMTYgMTYtMTZTMjguODQgNCAyMCA0eiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+'" />
                <div class="channel-name">${channel.name}</div>
                <div class="channel-category">${this.getCategoryName(channel.category)}</div>
                <div class="channel-status">
                    <div class="status-live">
                        <div class="live-indicator"></div>
                        CANLI
                    </div>
                    <div class="viewer-count">
                        <i class="fas fa-eye"></i>
                        ${this.formatNumber(channel.viewers)}
                    </div>
                </div>
            </div>
        `).join('');
    },

    // Setup channel filters
    setupChannelFilters() {
        const filterBtns = document.querySelectorAll('.filter-btn');
        
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Update active filter
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                // Filter channels
                const filter = btn.getAttribute('data-filter');
                this.filterChannels(filter);
            });
        });
    },

    // Filter channels
    filterChannels(category) {
        const channelCards = document.querySelectorAll('.channel-card');
        
        channelCards.forEach(card => {
            if (category === 'all' || card.getAttribute('data-category') === category) {
                card.style.display = 'block';
                card.classList.add('fade-in');
            } else {
                card.style.display = 'none';
                card.classList.remove('fade-in');
            }
        });
    },

    // Update statistics
    updateStatistics() {
        const totalViewers = this.state.matches.reduce((sum, match) => sum + match.viewers, 0) +
                           this.state.channels.reduce((sum, channel) => sum + channel.viewers, 0);
        
        const liveMatches = this.state.matches.filter(match => match.isLive).length;
        const totalChannels = this.state.channels.length;
        
        // Update hero stats
        this.animateNumber('total-viewers', totalViewers);
        this.animateNumber('live-matches-count', liveMatches);
        this.animateNumber('channels-count', totalChannels);
        
        this.state.viewerCounts.total = totalViewers;
    },

    // Animate numbers
    animateNumber(elementId, targetNumber) {
        const element = document.getElementById(elementId);
        if (!element) return;
        
        const startNumber = parseInt(element.textContent) || 0;
        const duration = 2000;
        const steps = 60;
        const increment = (targetNumber - startNumber) / steps;
        
        let currentNumber = startNumber;
        let step = 0;
        
        const timer = setInterval(() => {
            step++;
            currentNumber += increment;
            
            if (step >= steps) {
                currentNumber = targetNumber;
                clearInterval(timer);
            }
            
            element.textContent = this.formatNumber(Math.floor(currentNumber));
        }, duration / steps);
    },

    // Format numbers
    formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toString();
    },

    // Get category name
    getCategoryName(category) {
        const names = {
            'futbol': 'Futbol',
            'basketbol': 'Basketbol',
            'genel': 'Genel Spor'
        };
        return names[category] || 'Diğer';
    },

    // Update social links
    updateSocialLinks() {
        const links = this.state.settings.socialLinks;
        
        // Update contact section links
        const telegramLink = document.getElementById('telegram-link');
        const instagramLink = document.getElementById('instagram-link');
        const twitterLink = document.getElementById('twitter-link');
        const tiktokLink = document.getElementById('tiktok-link');
        
        if (telegramLink && links.telegram) telegramLink.href = links.telegram;
        if (instagramLink && links.instagram) instagramLink.href = links.instagram;
        if (twitterLink && links.twitter) twitterLink.href = links.twitter;
        if (tiktokLink && links.tiktok) tiktokLink.href = links.tiktok;
        
        // Update footer links
        const footerTelegram = document.getElementById('footer-telegram');
        const footerInstagram = document.getElementById('footer-instagram');
        const footerTwitter = document.getElementById('footer-twitter');
        const footerTiktok = document.getElementById('footer-tiktok');
        
        if (footerTelegram && links.telegram) footerTelegram.href = links.telegram;
        if (footerInstagram && links.instagram) footerInstagram.href = links.instagram;
        if (footerTwitter && links.twitter) footerTwitter.href = links.twitter;
        if (footerTiktok && links.tiktok) footerTiktok.href = links.tiktok;
    },

    // Start viewer count simulation
    startViewerCountSimulation() {
        setInterval(() => {
            // Update match viewers
            this.state.matches.forEach(match => {
                const variation = Math.floor(Math.random() * 2000) - 1000;
                match.viewers = Math.max(1000, match.viewers + variation);
                this.state.viewerCounts.matches[match.id] = match.viewers;
            });
            
            // Update channel viewers
            this.state.channels.forEach(channel => {
                const variation = Math.floor(Math.random() * 1000) - 500;
                channel.viewers = Math.max(500, channel.viewers + variation);
                this.state.viewerCounts.channels[channel.id] = channel.viewers;
            });
            
            // Re-render if needed
            if (this.state.isLoaded) {
                this.renderMatches();
                this.renderChannels();
                this.updateStatistics();
            }
        }, 30000); // Update every 30 seconds
    },

    // Load local data
    loadLocalData() {
        try {
            // Check if setup wizard was completed
            const setupCompleted = localStorage.getItem('setup_completed');
            if (setupCompleted === 'true') {
                const setupData = localStorage.getItem('dg_sports_setup');
                if (setupData) {
                    try {
                        const setup = JSON.parse(setupData);
                        
                        // Apply setup data
                        if (setup.siteSettings) {
                            this.state.settings = {
                                ...this.state.settings,
                                siteTitle: setup.siteSettings.siteTitle,
                                siteDescription: setup.siteSettings.siteDescription,
                                socialLinks: {
                                    telegram: setup.siteSettings.telegram || '',
                                    instagram: setup.siteSettings.instagram || '',
                                    twitter: setup.siteSettings.twitter || '',
                                    tiktok: setup.siteSettings.tiktok || ''
                                }
                            };
                            
                            // Update page title
                            document.title = setup.siteSettings.siteTitle;
                            
                            console.log('🚀 Setup data loaded successfully');
                        }
                        
                        // Clear setup data after use
                        localStorage.removeItem('dg_sports_setup');
                        
                    } catch (error) {
                        console.warn('Setup data parse error:', error);
                    }
                }
            }
            
            const savedSettings = localStorage.getItem('diziportal_settings');
            if (savedSettings) {
                this.state.settings = { ...this.state.settings, ...JSON.parse(savedSettings) };
            }
            
            const savedAdmin = localStorage.getItem('diziportal_admin_session');
            if (savedAdmin) {
                const session = JSON.parse(savedAdmin);
                if (session.expires > Date.now()) {
                    this.state.isAdminLoggedIn = true;
                }
            }
        } catch (error) {
            console.warn('DiziPortal: Error loading local data:', error);
        }
    },

    // Save data
    saveData() {
        try {
            localStorage.setItem('diziportal_matches', JSON.stringify(this.state.matches));
            localStorage.setItem('diziportal_channels', JSON.stringify(this.state.channels));
            localStorage.setItem('diziportal_settings', JSON.stringify(this.state.settings));
        } catch (error) {
            console.warn('DiziPortal: Error saving data:', error);
        }
    }
};

// Global functions for DiziPortal
window.DiziPortalApp = DiziPortalApp;

// Refresh matches function
window.diziportalRefreshMatches = function() {
    const refreshBtn = document.querySelector('.refresh-btn');
    if (refreshBtn) {
        refreshBtn.classList.add('loading');
        refreshBtn.innerHTML = '<i class="fas fa-sync-alt spinning"></i> Yenileniyor...';
    }
    
    setTimeout(() => {
        DiziPortalApp.loadMatches();
        DiziPortalApp.updateStatistics();
        
        if (refreshBtn) {
            refreshBtn.classList.remove('loading');
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Yenile';
        }
        
        // Show success message
        DiziPortalApp.showMessage('Canlı maçlar güncellendi', 'success');
    }, 1500);
};

// Show message function
DiziPortalApp.showMessage = function(message, type = 'info') {
    const messageEl = document.createElement('div');
    messageEl.className = `message ${type}`;
    messageEl.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}-circle"></i>
        ${message}
    `;
    
    document.body.appendChild(messageEl);
    
    // Position the message
    messageEl.style.position = 'fixed';
    messageEl.style.top = '100px';
    messageEl.style.right = '20px';
    messageEl.style.zIndex = '10000';
    messageEl.style.animation = 'slideIn 0.3s ease-out';
    
    // Remove after 3 seconds
    setTimeout(() => {
        messageEl.style.animation = 'fadeOut 0.3s ease-out';
        setTimeout(() => {
            document.body.removeChild(messageEl);
        }, 300);
    }, 3000);
};

// Inline Player Functions
function playInlineMatch(matchId) {
    const match = DiziPortalApp.state.matches.find(m => m.id === matchId);
    if (!match) return;
    
    // Show inline player
    const inlinePlayerContainer = document.getElementById('inline-player-container');
    if (inlinePlayerContainer) {
        inlinePlayerContainer.style.display = 'block';
        
        // Scroll to player
        inlinePlayerContainer.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
        
        // Update player info
        document.getElementById('playing-title').textContent = `${match.homeTeam} vs ${match.awayTeam}`;
        document.getElementById('playing-subtitle').textContent = `${match.location} - ${new Date(match.matchTime).toLocaleString('tr-TR')}`;
        document.getElementById('inline-viewer-count').textContent = DiziPortalApp.formatNumber(match.viewers);
        
        // Show match info
        const matchInfo = document.getElementById('match-info-inline');
        if (matchInfo) {
            matchInfo.style.display = 'block';
            document.getElementById('home-team-logo-inline').src = match.homeLogo;
            document.getElementById('home-team-name-inline').textContent = match.homeTeam;
            document.getElementById('away-team-logo-inline').src = match.awayLogo;
            document.getElementById('away-team-name-inline').textContent = match.awayTeam;
            document.getElementById('match-time-inline').textContent = new Date(match.matchTime).toLocaleString('tr-TR');
            document.getElementById('match-location-inline').textContent = match.location;
        }
        
        // Initialize player
        setTimeout(() => {
            initInlinePlayer(match.streamUrl, match.homeTeam + ' vs ' + match.awayTeam);
        }, 500);
    }
}

function playInlineChannel(channelId) {
    const channel = DiziPortalApp.state.channels.find(c => c.id === channelId);
    if (!channel) return;
    
    // Show inline player
    const inlinePlayerContainer = document.getElementById('inline-player-container');
    if (inlinePlayerContainer) {
        inlinePlayerContainer.style.display = 'block';
        
        // Scroll to player
        inlinePlayerContainer.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
        
        // Update player info
        document.getElementById('playing-title').textContent = channel.name;
        document.getElementById('playing-subtitle').textContent = `${DiziPortalApp.getCategoryName(channel.category)} - 7/24 Canlı Yayın`;
        document.getElementById('inline-viewer-count').textContent = DiziPortalApp.formatNumber(channel.viewers);
        
        // Hide match info for channels
        const matchInfo = document.getElementById('match-info-inline');
        if (matchInfo) {
            matchInfo.style.display = 'none';
        }
        
        // Initialize player
        setTimeout(() => {
            initInlinePlayer(channel.streamUrl, channel.name);
        }, 500);
    }
}

function initInlinePlayer(streamUrl, title) {
    const playerContainer = document.getElementById('inline-diziportal-player');
    const overlay = document.getElementById('player-overlay');
    
    if (!playerContainer) return;
    
    // Show loading overlay
    overlay.classList.remove('hidden');
    
    try {
        // Process stream URL (same as in player.js)
        const processedUrl = processStreamUrlForPlayer(streamUrl);
        
        // Initialize PlayerJS
        if (typeof Playerjs !== 'undefined') {
            const player = new Playerjs({
                id: 'inline-diziportal-player',
                file: processedUrl,
                poster: '',
                default_quality: 'HD',
                auto: true,
                flashplayer: 'https://cdn.playerjs.com/latest/playerjs.swf',
                hlsplayer: 'https://cdn.playerjs.com/latest/playerjs.hls.min.js'
            });
            
            // Hide overlay after player loads
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 2000);
            
            console.log('🎬 Inline PlayerJS initialized:', title);
        } else {
            // Fallback to HTML5 video
            playerContainer.innerHTML = `
                <video controls autoplay style="width: 100%; height: 100%;">
                    <source src="${processedUrl}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            `;
            
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 1000);
            
            console.log('📺 Inline HTML5 video initialized:', title);
        }
        
    } catch (error) {
        console.error('❌ Inline player error:', error);
        overlay.classList.add('hidden');
        DiziPortalApp.showMessage('Yayın başlatılamadı', 'error');
    }
}

function processStreamUrlForPlayer(url) {
    if (!url) return '';
    
    // If it's already a processed URL, return as is
    if (url.includes('commondatastorage.googleapis.com')) {
        return url;
    }
    
    // Add CORS proxy if needed
    const corsProxies = [
        'https://api.allorigins.win/raw?url=',
        'https://cors-anywhere.herokuapp.com/',
        'https://thingproxy.freeboard.io/fetch/'
    ];
    
    // Try first proxy
    return corsProxies[0] + encodeURIComponent(url);
}

function closeInlinePlayer() {
    const inlinePlayerContainer = document.getElementById('inline-player-container');
    if (inlinePlayerContainer) {
        inlinePlayerContainer.style.display = 'none';
        
        // Clear player content
        const playerContainer = document.getElementById('inline-diziportal-player');
        if (playerContainer) {
            playerContainer.innerHTML = '';
        }
        
        console.log('❌ Inline player closed');
    }
}

function openFullscreen() {
    const playerWrapper = document.querySelector('.inline-player-wrapper');
    if (playerWrapper) {
        if (playerWrapper.requestFullscreen) {
            playerWrapper.requestFullscreen();
        } else if (playerWrapper.webkitRequestFullscreen) {
            playerWrapper.webkitRequestFullscreen();
        } else if (playerWrapper.msRequestFullscreen) {
            playerWrapper.msRequestFullscreen();
        }
    }
}

// Make functions globally available
window.playInlineMatch = playInlineMatch;
window.playInlineChannel = playInlineChannel;
window.closeInlinePlayer = closeInlinePlayer;
window.openFullscreen = openFullscreen;

// Make DiziPortalApp globally available
window.DiziPortalApp = DiziPortalApp;

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DiziPortalApp;
}