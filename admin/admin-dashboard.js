/*
 * DG SPORTS - Admin Dashboard JavaScript
 * Developer: DiziPortal.Com
 * Comprehensive Admin System with CRUD Operations
 */

// Global Variables
let currentEditingMatch = null;
let currentEditingChannel = null;
let searchTimeouts = {};

// Admin Dashboard Core
const AdminDashboard = {
    // Application State
    state: {
        isAuthenticated: false,
        currentUser: null,
        activeSection: 'dashboard',
        matches: [],
        channels: [],
        stats: {
            totalMatches: 0,
            liveMatches: 0,
            totalChannels: 0,
            totalViewers: 0
        },
        settings: {
            siteName: 'DG SPORTS',
            maintenanceMode: false,
            allowRegistrations: true
        }
    },

    // Default Credentials (for demo)
    credentials: {
        username: 'admin',
        password: 'dgsports2024'
    },

    // Initialize Admin Dashboard
    init() {
        console.log('🔐 Admin Dashboard initializing...');
        
        this.checkAuthentication();
        this.setupEventListeners();
        this.loadData();
        this.initializeCharts();
        this.setupMobileMenu();
        
        console.log('✅ Admin Dashboard initialized successfully');
    },

    // Authentication Methods
    checkAuthentication() {
        const session = localStorage.getItem('admin_session');
        if (session) {
            try {
                const sessionData = JSON.parse(session);
                if (sessionData.expires > Date.now()) {
                    this.authenticateUser(sessionData.username, sessionData.rememberMe);
                    return;
                }
            } catch (error) {
                console.warn('Invalid session data:', error);
            }
        }
        
        this.showLoginScreen();
    },

    showLoginScreen() {
        document.getElementById('admin-login-screen').style.display = 'flex';
        document.getElementById('admin-dashboard').style.display = 'none';
        
        // Focus on username input
        setTimeout(() => {
            const usernameInput = document.querySelector('input[name="username"]');
            if (usernameInput) usernameInput.focus();
        }, 100);
    },

    handleLogin(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const username = formData.get('username').trim();
        const password = formData.get('password');
        const remember = formData.get('remember') === 'on';
        
        // Clear previous errors
        this.clearLoginError();
        
        // Show loading
        const submitBtn = e.target.querySelector('.login-btn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<div class="spinner"></div> Giriş yapılıyor...';
        submitBtn.disabled = true;
        
        // Simulate authentication delay
        setTimeout(() => {
            if (username === this.credentials.username && password === this.credentials.password) {
                this.authenticateUser(username, remember);
            } else {
                this.showLoginError('Kullanıcı adı veya şifre hatalı!');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        }, 1000);
    },

    authenticateUser(username, remember) {
        this.state.isAuthenticated = true;
        this.state.currentUser = { username, role: 'admin' };
        
        // Save session
        const sessionData = {
            username,
            rememberMe: remember,
            expires: Date.now() + (remember ? 7 * 24 * 60 * 60 * 1000 : 24 * 60 * 60 * 1000)
        };
        localStorage.setItem('admin_session', JSON.stringify(sessionData));
        
        // Show dashboard
        this.showDashboard();
        this.updateUserInfo();
        this.loadData();
        this.showAlert('Başarıyla giriş yapıldı!', 'success');
    },

    showDashboard() {
        document.getElementById('admin-login-screen').style.display = 'none';
        document.getElementById('admin-dashboard').style.display = 'flex';
        
        // Initialize dashboard
        this.switchSection('dashboard');
        this.loadStats();
    },

    logout() {
        if (confirm('Çıkış yapmak istediğinizden emin misiniz?')) {
            localStorage.removeItem('admin_session');
            this.state.isAuthenticated = false;
            this.state.currentUser = null;
            this.showLoginScreen();
            this.showAlert('Başarıyla çıkış yapıldı!', 'info');
        }
    },

    showLoginError(message) {
        let errorDiv = document.querySelector('.login-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'login-error';
            document.querySelector('.login-form').appendChild(errorDiv);
        }
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    },

    clearLoginError() {
        const errorDiv = document.querySelector('.login-error');
        if (errorDiv) {
            errorDiv.style.display = 'none';
        }
    },

    // Event Listeners
    setupEventListeners() {
        // Login form
        const loginForm = document.getElementById('admin-login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', this.handleLogin.bind(this));
        }

        // Navigation
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const section = item.getAttribute('data-section');
                if (section) this.switchSection(section);
            });
        });

        // Logout
        const logoutBtn = document.querySelector('.logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', this.logout.bind(this));
        }

        // Modal controls
        document.querySelectorAll('.modal-close, .modal .close-btn, .cancel-btn').forEach(btn => {
            btn.addEventListener('click', this.closeModal.bind(this));
        });

        // Add buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('add-match-btn')) {
                this.showAddMatchModal();
            } else if (e.target.classList.contains('add-channel-btn')) {
                this.showAddChannelModal();
            } else if (e.target.classList.contains('edit-match-btn')) {
                const matchId = e.target.getAttribute('data-id');
                this.showEditMatchModal(matchId);
            } else if (e.target.classList.contains('edit-channel-btn')) {
                const channelId = e.target.getAttribute('data-id');
                this.showEditChannelModal(channelId);
            } else if (e.target.classList.contains('delete-match-btn')) {
                const matchId = e.target.getAttribute('data-id');
                this.deleteMatch(matchId);
            } else if (e.target.classList.contains('delete-channel-btn')) {
                const channelId = e.target.getAttribute('data-id');
                this.deleteChannel(channelId);
            }
        });

        // Form submissions
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'match-form') {
                e.preventDefault();
                this.handleMatchSubmit(e.target);
            } else if (e.target.id === 'channel-form') {
                e.preventDefault();
                this.handleChannelSubmit(e.target);
            }
        });

        // Search functionality
        document.addEventListener('input', (e) => {
            if (e.target.classList.contains('search-input')) {
                const table = e.target.getAttribute('data-table');
                this.handleSearch(e.target.value, table);
            }
        });

        // File uploads
        document.addEventListener('change', (e) => {
            if (e.target.type === 'file') {
                this.handleFileUpload(e.target);
            }
        });
    },

    setupMobileMenu() {
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 1024 && 
                    !sidebar.contains(e.target) && 
                    !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            });
        }
    },

    // Section Management
    switchSection(sectionName) {
        this.state.activeSection = sectionName;

        // Update navigation
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('data-section') === sectionName) {
                item.classList.add('active');
            }
        });

        // Update content sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });

        const targetSection = document.getElementById(`${sectionName}-section`);
        if (targetSection) {
            targetSection.classList.add('active');
        }

        // Update breadcrumb
        this.updateBreadcrumb(sectionName);

        // Load section specific data
        this.loadSectionData(sectionName);

        // Close mobile sidebar
        if (window.innerWidth <= 1024) {
            document.querySelector('.sidebar').classList.remove('active');
        }
    },

    updateBreadcrumb(sectionName) {
        const breadcrumb = document.querySelector('.breadcrumb');
        if (!breadcrumb) return;

        const sectionNames = {
            dashboard: 'Dashboard',
            matches: 'Canlı Maçlar',
            channels: '7/24 Kanallar',
            analytics: 'Analitik',
            users: 'Kullanıcılar',
            content: 'İçerik Yönetimi',
            settings: 'Ayarlar',
            backup: 'Yedekleme',
            logs: 'Sistem Logları'
        };

        breadcrumb.innerHTML = `
            <span>Admin</span>
            <span class="separator">›</span>
            <span>${sectionNames[sectionName] || sectionName}</span>
        `;
    },

    loadSectionData(sectionName) {
        switch (sectionName) {
            case 'dashboard':
                this.loadStats();
                this.updateCharts();
                break;
            case 'matches':
                this.loadMatches();
                break;
            case 'channels':
                this.loadChannels();
                break;
            case 'analytics':
                this.loadAnalytics();
                break;
        }
    },

    // Data Management
    loadData() {
        this.loadMatches();
        this.loadChannels();
        this.loadStats();
    },

    loadMatches() {
        // Load from localStorage or use defaults
        const savedMatches = localStorage.getItem('admin_matches');
        if (savedMatches) {
            this.state.matches = JSON.parse(savedMatches);
        } else {
            // Default matches
            this.state.matches = [
                {
                    id: 'match_' + Date.now(),
                    homeTeam: 'Galatasaray',
                    awayTeam: 'Fenerbahçe',
                    homeLogo: 'https://logoeps.com/wp-content/uploads/2013/03/galatasaray-vector-logo.png',
                    awayLogo: 'https://logoeps.com/wp-content/uploads/2013/03/fenerbahce-vector-logo.png',
                    matchTime: new Date(Date.now() + 2 * 60 * 60 * 1000).toISOString(),
                    location: 'Türk Telekom Stadyumu',
                    streamUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                    status: 'live',
                    viewers: Math.floor(Math.random() * 50000) + 15000,
                    createdAt: new Date().toISOString()
                },
                {
                    id: 'match_' + (Date.now() + 1),
                    homeTeam: 'Real Madrid',
                    awayTeam: 'Barcelona',
                    homeLogo: 'https://logoeps.com/wp-content/uploads/2013/03/real-madrid-vector-logo.png',
                    awayLogo: 'https://logoeps.com/wp-content/uploads/2013/03/barcelona-vector-logo.png',
                    matchTime: new Date(Date.now() + 4 * 60 * 60 * 1000).toISOString(),
                    location: 'Santiago Bernabéu',
                    streamUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
                    status: 'upcoming',
                    viewers: 0,
                    createdAt: new Date().toISOString()
                }
            ];
            this.saveMatches();
        }
        
        this.renderMatchesTable();
    },

    loadChannels() {
        // Load from localStorage or use defaults
        const savedChannels = localStorage.getItem('admin_channels');
        if (savedChannels) {
            this.state.channels = JSON.parse(savedChannels);
        } else {
            // Default channels
            this.state.channels = [
                {
                    id: 'channel_' + Date.now(),
                    name: 'beIN SPORTS 1 HD',
                    category: 'futbol',
                    logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/36/Bein_sports_1.png/512px-Bein_sports_1.png',
                    streamUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
                    status: 'active',
                    viewers: Math.floor(Math.random() * 35000) + 12000,
                    createdAt: new Date().toISOString()
                },
                {
                    id: 'channel_' + (Date.now() + 1),
                    name: 'TRT SPOR',
                    category: 'genel',
                    logo: 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/15/TRT_Spor_logo.png/512px-TRT_Spor_logo.png',
                    streamUrl: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4',
                    status: 'active',
                    viewers: Math.floor(Math.random() * 20000) + 8000,
                    createdAt: new Date().toISOString()
                }
            ];
            this.saveChannels();
        }
        
        this.renderChannelsTable();
    },

    loadStats() {
        const liveMatches = this.state.matches.filter(m => m.status === 'live').length;
        const activeChannels = this.state.channels.filter(c => c.status === 'active').length;
        const totalViewers = this.state.matches.reduce((sum, m) => sum + m.viewers, 0) +
                           this.state.channels.reduce((sum, c) => sum + c.viewers, 0);

        this.state.stats = {
            totalMatches: this.state.matches.length,
            liveMatches: liveMatches,
            totalChannels: this.state.channels.length,
            activeChannels: activeChannels,
            totalViewers: totalViewers
        };

        this.updateStatsDisplay();
    },

    // Save Methods
    saveMatches() {
        localStorage.setItem('admin_matches', JSON.stringify(this.state.matches));
        localStorage.setItem('diziportal_matches', JSON.stringify(this.state.matches));
    },

    saveChannels() {
        localStorage.setItem('admin_channels', JSON.stringify(this.state.channels));
        localStorage.setItem('diziportal_channels', JSON.stringify(this.state.channels));
    },

    // Render Methods
    renderMatchesTable() {
        const tbody = document.querySelector('#matches-table tbody');
        if (!tbody) return;

        if (this.state.matches.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center">
                        <div class="empty-state">
                            <i class="fas fa-futbol"></i>
                            <h3>Henüz maç eklenmemiş</h3>
                            <p>İlk maçınızı eklemek için "Maç Ekle" butonuna tıklayın.</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = this.state.matches.map(match => `
            <tr>
                <td>
                    <div class="team-display">
                        <img src="${match.homeLogo}" alt="${match.homeTeam}" class="team-logo-small" 
                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAiIGhlaWdodD0iMzAiIHZpZXdCb3g9IjAgMCAzMCAzMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMTUiIGN5PSIxNSIgcj0iMTUiIGZpbGw9IiNkYzI2MjYiLz4KPHN2ZyB3aWR0aD0iMTIiIGhlaWdodD0iMTIiIHZpZXdCb3g9IjAgMCAxMiAxMiIgZmlsbD0iI2ZmZmZmZiIgeD0iOSIgeT0iOSI+CjxwYXRoIGQ9Ik02IDFMNy41IDQuNUwxMSA1TDcuNSA3LjVMNiAxMUw0LjUgNy41TDEgNUw0LjUgNC41TDYgMVoiLz4KPC9zdmc+Cjwvc3ZnPgo='">
                        <span>${match.homeTeam}</span>
                        <span class="team-vs">VS</span>
                        <img src="${match.awayLogo}" alt="${match.awayTeam}" class="team-logo-small"
                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAiIGhlaWdodD0iMzAiIHZpZXdCb3g9IjAgMCAzMCAzMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMTUiIGN5PSIxNSIgcj0iMTUiIGZpbGw9IiNkYzI2MjYiLz4KPHN2ZyB3aWR0aD0iMTIiIGhlaWdodD0iMTIiIHZpZXdCb3g9IjAgMCAxMiAxMiIgZmlsbD0iI2ZmZmZmZiIgeD0iOSIgeT0iOSI+CjxwYXRoIGQ9Ik02IDFMNy41IDQuNUwxMSA1TDcuNSA3LjVMNiAxMUw0LjUgNy41TDEgNUw0LjUgNC41TDYgMVoiLz4KPC9zdmc+Cjwvc3ZnPgo='">
                        <span>${match.awayTeam}</span>
                    </div>
                </td>
                <td>${match.location}</td>
                <td>${new Date(match.matchTime).toLocaleString('tr-TR')}</td>
                <td><span class="status-badge ${match.status}">${this.getStatusText(match.status)}</span></td>
                <td>${this.formatNumber(match.viewers)}</td>
                <td>
                    <div class="table-actions-cell">
                        <button class="btn btn-sm btn-secondary edit-match-btn" data-id="${match.id}">
                            <i class="fas fa-edit"></i> Düzenle
                        </button>
                        <button class="btn btn-sm btn-danger delete-match-btn" data-id="${match.id}">
                            <i class="fas fa-trash"></i> Sil
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    renderChannelsTable() {
        const tbody = document.querySelector('#channels-table tbody');
        if (!tbody) return;

        if (this.state.channels.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center">
                        <div class="empty-state">
                            <i class="fas fa-tv"></i>
                            <h3>Henüz kanal eklenmemiş</h3>
                            <p>İlk kanalınızı eklemek için "Kanal Ekle" butonuna tıklayın.</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = this.state.channels.map(channel => `
            <tr>
                <td>
                    <div class="channel-display">
                        <img src="${channel.logo}" alt="${channel.name}" class="channel-logo-small"
                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAiIGhlaWdodD0iMzAiIHZpZXdCb3g9IjAgMCAzMCAzMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMwIiBoZWlnaHQ9IjMwIiByeD0iNCIgZmlsbD0iI2RjMjYyNiIvPgo8c3ZnIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE2IDE2IiBmaWxsPSIjZmZmZmZmIiB4PSI3IiB5PSI3Ij4KPHBhdGggZD0iTTggMkM0LjY5IDIgMiA0LjY5IDIgOHMyLjY5IDYgNiA2IDYtMi42OSA2LTZTMTEuMzEgMiA4IDJ6Ii8+Cjwvc3ZnPgo8L3N2Zz4K'">
                        <span>${channel.name}</span>
                    </div>
                </td>
                <td><span class="badge badge-${channel.category}">${this.getCategoryText(channel.category)}</span></td>
                <td><span class="status-badge ${channel.status}">${this.getStatusText(channel.status)}</span></td>
                <td>${this.formatNumber(channel.viewers)}</td>
                <td>
                    <div class="table-actions-cell">
                        <button class="btn btn-sm btn-secondary edit-channel-btn" data-id="${channel.id}">
                            <i class="fas fa-edit"></i> Düzenle
                        </button>
                        <button class="btn btn-sm btn-danger delete-channel-btn" data-id="${channel.id}">
                            <i class="fas fa-trash"></i> Sil
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    updateStatsDisplay() {
        const stats = this.state.stats;
        
        // Update stat cards
        this.updateStatCard('total-matches', stats.totalMatches);
        this.updateStatCard('live-matches', stats.liveMatches);
        this.updateStatCard('total-channels', stats.totalChannels);
        this.updateStatCard('total-viewers', this.formatNumber(stats.totalViewers));
    },

    updateStatCard(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    },

    // Modal Management
    showAddMatchModal() {
        currentEditingMatch = null;
        this.resetMatchForm();
        document.getElementById('match-modal-title').innerHTML = '<i class="fas fa-plus"></i> Yeni Maç Ekle';
        this.showModal('add-match-modal');
    },

    showEditMatchModal(matchId) {
        const match = this.state.matches.find(m => m.id === matchId);
        if (!match) {
            this.showAlert('Maç bulunamadı!', 'error');
            return;
        }

        currentEditingMatch = match;
        this.populateMatchForm(match);
        document.getElementById('match-modal-title').innerHTML = '<i class="fas fa-edit"></i> Maç Düzenle';
        this.showModal('add-match-modal');
    },

    showAddChannelModal() {
        currentEditingChannel = null;
        this.resetChannelForm();
        document.getElementById('channel-modal-title').innerHTML = '<i class="fas fa-plus"></i> Yeni Kanal Ekle';
        this.showModal('add-channel-modal');
    },

    showEditChannelModal(channelId) {
        const channel = this.state.channels.find(c => c.id === channelId);
        if (!channel) {
            this.showAlert('Kanal bulunamadı!', 'error');
            return;
        }

        currentEditingChannel = channel;
        this.populateChannelForm(channel);
        document.getElementById('channel-modal-title').innerHTML = '<i class="fas fa-edit"></i> Kanal Düzenle';
        this.showModal('add-channel-modal');
    },

    showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    },

    closeModal() {
        document.querySelectorAll('.modal').forEach(modal => {
            modal.classList.remove('active');
        });
        document.body.style.overflow = '';
        currentEditingMatch = null;
        currentEditingChannel = null;
    },

    // Form Management
    resetMatchForm() {
        const form = document.getElementById('match-form');
        if (form) {
            form.reset();
            this.clearFormErrors(form);
        }
    },

    resetChannelForm() {
        const form = document.getElementById('channel-form');
        if (form) {
            form.reset();
            this.clearFormErrors(form);
        }
    },

    populateMatchForm(match) {
        const form = document.getElementById('match-form');
        if (!form) return;

        form.homeTeam.value = match.homeTeam || '';
        form.awayTeam.value = match.awayTeam || '';
        form.homeLogo.value = match.homeLogo || '';
        form.awayLogo.value = match.awayLogo || '';
        form.matchTime.value = match.matchTime ? new Date(match.matchTime).toISOString().slice(0, 16) : '';
        form.location.value = match.location || '';
        form.streamUrl.value = match.streamUrl || '';
        form.status.value = match.status || 'upcoming';
    },

    populateChannelForm(channel) {
        const form = document.getElementById('channel-form');
        if (!form) return;

        form.channelName.value = channel.name || '';
        form.category.value = channel.category || 'genel';
        form.logo.value = channel.logo || '';
        form.streamUrl.value = channel.streamUrl || '';
        form.status.value = channel.status || 'active';
    },

    handleMatchSubmit(form) {
        if (!this.validateMatchForm(form)) return;

        const formData = new FormData(form);
        const matchData = {
            homeTeam: formData.get('homeTeam').trim(),
            awayTeam: formData.get('awayTeam').trim(),
            homeLogo: formData.get('homeLogo').trim(),
            awayLogo: formData.get('awayLogo').trim(),
            matchTime: formData.get('matchTime'),
            location: formData.get('location').trim(),
            streamUrl: formData.get('streamUrl').trim(),
            status: formData.get('status'),
            viewers: Math.floor(Math.random() * 50000) + 1000
        };

        if (currentEditingMatch) {
            // Update existing match
            const index = this.state.matches.findIndex(m => m.id === currentEditingMatch.id);
            if (index !== -1) {
                this.state.matches[index] = { ...currentEditingMatch, ...matchData };
                this.showAlert('Maç başarıyla güncellendi!', 'success');
            }
        } else {
            // Add new match
            const newMatch = {
                id: 'match_' + Date.now(),
                ...matchData,
                createdAt: new Date().toISOString()
            };
            this.state.matches.unshift(newMatch);
            this.showAlert('Yeni maç başarıyla eklendi!', 'success');
        }

        this.saveMatches();
        this.renderMatchesTable();
        this.loadStats();
        this.closeModal();
    },

    handleChannelSubmit(form) {
        if (!this.validateChannelForm(form)) return;

        const formData = new FormData(form);
        const channelData = {
            name: formData.get('channelName').trim(),
            category: formData.get('category'),
            logo: formData.get('logo').trim(),
            streamUrl: formData.get('streamUrl').trim(),
            status: formData.get('status'),
            viewers: Math.floor(Math.random() * 30000) + 1000
        };

        if (currentEditingChannel) {
            // Update existing channel
            const index = this.state.channels.findIndex(c => c.id === currentEditingChannel.id);
            if (index !== -1) {
                this.state.channels[index] = { ...currentEditingChannel, ...channelData };
                this.showAlert('Kanal başarıyla güncellendi!', 'success');
            }
        } else {
            // Add new channel
            const newChannel = {
                id: 'channel_' + Date.now(),
                ...channelData,
                createdAt: new Date().toISOString()
            };
            this.state.channels.unshift(newChannel);
            this.showAlert('Yeni kanal başarıyla eklendi!', 'success');
        }

        this.saveChannels();
        this.renderChannelsTable();
        this.loadStats();
        this.closeModal();
    },

    // Validation
    validateMatchForm(form) {
        this.clearFormErrors(form);
        let isValid = true;

        const required = ['homeTeam', 'awayTeam', 'matchTime', 'location', 'streamUrl'];
        required.forEach(field => {
            const input = form[field];
            if (!input.value.trim()) {
                this.showFieldError(input, 'Bu alan zorunludur');
                isValid = false;
            }
        });

        // Validate URLs
        const urls = ['homeLogo', 'awayLogo', 'streamUrl'];
        urls.forEach(field => {
            const input = form[field];
            if (input.value.trim() && !this.isValidUrl(input.value.trim())) {
                this.showFieldError(input, 'Geçerli bir URL giriniz');
                isValid = false;
            }
        });

        return isValid;
    },

    validateChannelForm(form) {
        this.clearFormErrors(form);
        let isValid = true;

        const required = ['channelName', 'streamUrl'];
        required.forEach(field => {
            const input = form[field];
            if (!input.value.trim()) {
                this.showFieldError(input, 'Bu alan zorunludur');
                isValid = false;
            }
        });

        // Validate URLs
        const urls = ['logo', 'streamUrl'];
        urls.forEach(field => {
            const input = form[field];
            if (input.value.trim() && !this.isValidUrl(input.value.trim())) {
                this.showFieldError(input, 'Geçerli bir URL giriniz');
                isValid = false;
            }
        });

        return isValid;
    },

    showFieldError(input, message) {
        input.classList.add('invalid');
        
        let errorEl = input.parentNode.querySelector('.form-error');
        if (!errorEl) {
            errorEl = document.createElement('div');
            errorEl.className = 'form-error';
            input.parentNode.appendChild(errorEl);
        }
        errorEl.textContent = message;
    },

    clearFormErrors(form) {
        form.querySelectorAll('.invalid').forEach(input => {
            input.classList.remove('invalid');
        });
        form.querySelectorAll('.form-error').forEach(error => {
            error.remove();
        });
    },

    // Delete Operations
    deleteMatch(matchId) {
        const match = this.state.matches.find(m => m.id === matchId);
        if (!match) return;

        if (confirm(`"${match.homeTeam} vs ${match.awayTeam}" maçını silmek istediğinizden emin misiniz?`)) {
            this.state.matches = this.state.matches.filter(m => m.id !== matchId);
            this.saveMatches();
            this.renderMatchesTable();
            this.loadStats();
            this.showAlert('Maç başarıyla silindi!', 'success');
        }
    },

    deleteChannel(channelId) {
        const channel = this.state.channels.find(c => c.id === channelId);
        if (!channel) return;

        if (confirm(`"${channel.name}" kanalını silmek istediğinizden emin misiniz?`)) {
            this.state.channels = this.state.channels.filter(c => c.id !== channelId);
            this.saveChannels();
            this.renderChannelsTable();
            this.loadStats();
            this.showAlert('Kanal başarıyla silindi!', 'success');
        }
    },

    // Search Functionality
    handleSearch(query, table) {
        clearTimeout(searchTimeouts[table]);
        searchTimeouts[table] = setTimeout(() => {
            this.performSearch(query, table);
        }, 300);
    },

    performSearch(query, table) {
        const searchTerm = query.toLowerCase().trim();
        
        if (table === 'matches') {
            const filteredMatches = searchTerm ? 
                this.state.matches.filter(match => 
                    match.homeTeam.toLowerCase().includes(searchTerm) ||
                    match.awayTeam.toLowerCase().includes(searchTerm) ||
                    match.location.toLowerCase().includes(searchTerm)
                ) : this.state.matches;
            
            this.renderFilteredMatches(filteredMatches);
        } else if (table === 'channels') {
            const filteredChannels = searchTerm ?
                this.state.channels.filter(channel =>
                    channel.name.toLowerCase().includes(searchTerm) ||
                    channel.category.toLowerCase().includes(searchTerm)
                ) : this.state.channels;
            
            this.renderFilteredChannels(filteredChannels);
        }
    },

    renderFilteredMatches(matches) {
        const tbody = document.querySelector('#matches-table tbody');
        if (!tbody) return;

        if (matches.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center">
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <h3>Arama sonucu bulunamadı</h3>
                            <p>Farklı anahtar kelimeler deneyin.</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        // Use the same rendering logic as renderMatchesTable but with filtered data
        tbody.innerHTML = matches.map(match => `
            <tr>
                <td>
                    <div class="team-display">
                        <img src="${match.homeLogo}" alt="${match.homeTeam}" class="team-logo-small" 
                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAiIGhlaWdodD0iMzAiIHZpZXdCb3g9IjAgMCAzMCAzMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMTUiIGN5PSIxNSIgcj0iMTUiIGZpbGw9IiNkYzI2MjYiLz4KPHN2ZyB3aWR0aD0iMTIiIGhlaWdodD0iMTIiIHZpZXdCb3g9IjAgMCAxMiAxMiIgZmlsbD0iI2ZmZmZmZiIgeD0iOSIgeT0iOSI+CjxwYXRoIGQ9Ik02IDFMNy41IDQuNUwxMSA1TDcuNSA3LjVMNiAxMUw0LjUgNy41TDEgNUw0LjUgNC41TDYgMVoiLz4KPC9zdmc+Cjwvc3ZnPgo='">
                        <span>${match.homeTeam}</span>
                        <span class="team-vs">VS</span>
                        <img src="${match.awayLogo}" alt="${match.awayTeam}" class="team-logo-small"
                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAiIGhlaWdodD0iMzAiIHZpZXdCb3g9IjAgMCAzMCAzMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMTUiIGN5PSIxNSIgcj0iMTUiIGZpbGw9IiNkYzI2MjYiLz4KPHN2ZyB3aWR0aD0iMTIiIGhlaWdodD0iMTIiIHZpZXdCb3g9IjAgMCAxMiAxMiIgZmlsbD0iI2ZmZmZmZiIgeD0iOSIgeT0iOSI+CjxwYXRoIGQ9Ik02IDFMNy41IDQuNUwxMSA1TDcuNSA3LjVMNiAxMUw0LjUgNy41TDEgNUw0LjUgNC41TDYgMVoiLz4KPC9zdmc+Cjwvc3ZnPgo='">
                        <span>${match.awayTeam}</span>
                    </div>
                </td>
                <td>${match.location}</td>
                <td>${new Date(match.matchTime).toLocaleString('tr-TR')}</td>
                <td><span class="status-badge ${match.status}">${this.getStatusText(match.status)}</span></td>
                <td>${this.formatNumber(match.viewers)}</td>
                <td>
                    <div class="table-actions-cell">
                        <button class="btn btn-sm btn-secondary edit-match-btn" data-id="${match.id}">
                            <i class="fas fa-edit"></i> Düzenle
                        </button>
                        <button class="btn btn-sm btn-danger delete-match-btn" data-id="${match.id}">
                            <i class="fas fa-trash"></i> Sil
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    renderFilteredChannels(channels) {
        const tbody = document.querySelector('#channels-table tbody');
        if (!tbody) return;

        if (channels.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center">
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <h3>Arama sonucu bulunamadı</h3>
                            <p>Farklı anahtar kelimeler deneyin.</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = channels.map(channel => `
            <tr>
                <td>
                    <div class="channel-display">
                        <img src="${channel.logo}" alt="${channel.name}" class="channel-logo-small"
                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAiIGhlaWdodD0iMzAiIHZpZXdCb3g9IjAgMCAzMCAzMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMwIiBoZWlnaHQ9IjMwIiByeD0iNCIgZmlsbD0iI2RjMjYyNiIvPgo8c3ZnIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE2IDE2IiBmaWxsPSIjZmZmZmZmIiB4PSI3IiB5PSI3Ij4KPHBhdGggZD0iTTggMkM0LjY5IDIgMiA0LjY5IDIgOHMyLjY5IDYgNiA2IDYtMi42OSA2LTZTMTEuMzEgMiA4IDJ6Ii8+Cjwvc3ZnPgo8L3N2Zz4K'">
                        <span>${channel.name}</span>
                    </div>
                </td>
                <td><span class="badge badge-${channel.category}">${this.getCategoryText(channel.category)}</span></td>
                <td><span class="status-badge ${channel.status}">${this.getStatusText(channel.status)}</span></td>
                <td>${this.formatNumber(channel.viewers)}</td>
                <td>
                    <div class="table-actions-cell">
                        <button class="btn btn-sm btn-secondary edit-channel-btn" data-id="${channel.id}">
                            <i class="fas fa-edit"></i> Düzenle
                        </button>
                        <button class="btn btn-sm btn-danger delete-channel-btn" data-id="${channel.id}">
                            <i class="fas fa-trash"></i> Sil
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    // File Upload
    handleFileUpload(input) {
        const file = input.files[0];
        if (!file) return;

        // Validate file type
        if (!file.type.startsWith('image/')) {
            this.showAlert('Lütfen geçerli bir resim dosyası seçiniz!', 'error');
            input.value = '';
            return;
        }

        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            this.showAlert('Dosya boyutu 5MB\'dan küçük olmalıdır!', 'error');
            input.value = '';
            return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = (e) => {
            this.showImagePreview(input, e.target.result);
        };
        reader.readAsDataURL(file);
    },

    showImagePreview(input, src) {
        let preview = input.parentNode.querySelector('.image-preview');
        if (!preview) {
            preview = document.createElement('div');
            preview.className = 'image-preview';
            input.parentNode.appendChild(preview);
        }
        
        preview.innerHTML = `<img src="${src}" alt="Preview">`;
    },

    // Charts (Placeholder for future implementation)
    initializeCharts() {
        // Initialize Chart.js charts here
        // This is a placeholder for future chart implementation
        console.log('📊 Charts will be initialized here');
    },

    updateCharts() {
        // Update chart data
        console.log('📊 Charts will be updated here');
    },

    loadAnalytics() {
        // Load analytics data
        console.log('📈 Analytics data will be loaded here');
    },

    // User Management
    updateUserInfo() {
        const userNameEl = document.querySelector('.admin-profile .profile-name');
        const avatarEl = document.querySelector('.admin-avatar');
        
        if (this.state.currentUser) {
            if (userNameEl) userNameEl.textContent = this.state.currentUser.username;
            if (avatarEl) avatarEl.textContent = this.state.currentUser.username.charAt(0).toUpperCase();
        }
    },

    // Utility Methods
    formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toString();
    },

    getStatusText(status) {
        const statusMap = {
            live: 'CANLI',
            upcoming: 'YAKINDA',
            ended: 'BİTTİ',
            active: 'AKTİF',
            inactive: 'PASİF'
        };
        return statusMap[status] || status;
    },

    getCategoryText(category) {
        const categoryMap = {
            futbol: 'Futbol',
            basketbol: 'Basketbol',
            voleybol: 'Voleybol',
            genel: 'Genel Spor',
            haber: 'Spor Haberleri'
        };
        return categoryMap[category] || category;
    },

    isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    },

    showAlert(message, type = 'info') {
        // Create alert element
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.innerHTML = `
            <i class="fas fa-${this.getAlertIcon(type)}"></i>
            <span>${message}</span>
        `;

        // Add to page
        const container = document.querySelector('.content-area');
        if (container) {
            container.insertBefore(alert, container.firstChild);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 5000);
        }
    },

    getAlertIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-triangle',
            warning: 'exclamation-circle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    AdminDashboard.init();
});

// Export for global access
window.AdminDashboard = AdminDashboard;

console.log('🎮 Admin Dashboard JS loaded - DiziPortal.Com');