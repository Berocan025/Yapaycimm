/*
 * DG SPORTS - Admin Panel
 * Developer: DiziPortal.Com
 * Complete content management system
 */

// DiziPortal Admin Controller
const DiziPortalAdmin = {
    // Admin state
    state: {
        isLoggedIn: false,
        currentTab: 'matches',
        editMode: false,
        editingId: null,
        credentials: {
            username: 'admin',
            password: 'dgsports2024'
        }
    },

    // Initialize admin panel
    init() {
        console.log('🔐 DiziPortal Admin initializing...');
        this.setupAdminEvents();
        this.checkLoginStatus();
    },

    // Setup admin events
    setupAdminEvents() {
        // Admin login form
        const loginForm = document.getElementById('admin-login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', this.handleLogin.bind(this));
        }

        // Close login modal
        window.diziportalCloseLogin = () => {
            this.closeLoginModal();
        };

        // Close admin panel
        window.diziportalCloseAdmin = () => {
            this.closeAdminPanel();
        };

        // Admin tabs
        document.querySelectorAll('.admin-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                this.switchTab(tab.getAttribute('data-tab'));
            });
        });

        // Form submissions
        this.setupFormHandlers();
    },

    // Setup form handlers
    setupFormHandlers() {
        // Add match form
        const addMatchForm = document.getElementById('add-match-form');
        if (addMatchForm) {
            addMatchForm.addEventListener('submit', this.handleAddMatch.bind(this));
        }

        // Add channel form
        const addChannelForm = document.getElementById('add-channel-form');
        if (addChannelForm) {
            addChannelForm.addEventListener('submit', this.handleAddChannel.bind(this));
        }

        // Settings form
        const settingsForm = document.getElementById('settings-form');
        if (settingsForm) {
            settingsForm.addEventListener('submit', this.handleSaveSettings.bind(this));
        }
    },

    // Check login status
    checkLoginStatus() {
        const savedSession = localStorage.getItem('diziportal_admin_session');
        if (savedSession) {
            try {
                const session = JSON.parse(savedSession);
                if (session.expires > Date.now()) {
                    this.state.isLoggedIn = true;
                    console.log('✅ Admin session restored');
                }
            } catch (error) {
                console.warn('Invalid admin session:', error);
                localStorage.removeItem('diziportal_admin_session');
            }
        }
    },

    // Handle login
    handleLogin(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const username = formData.get('username');
        const password = formData.get('password');

        if (username === this.state.credentials.username && 
            password === this.state.credentials.password) {
            
            this.state.isLoggedIn = true;
            
            // Save session
            const session = {
                username: username,
                loginTime: Date.now(),
                expires: Date.now() + (24 * 60 * 60 * 1000) // 24 hours
            };
            localStorage.setItem('diziportal_admin_session', JSON.stringify(session));
            
            // Show admin panel
            this.closeLoginModal();
            this.showAdminPanel();
            
            DiziPortalApp.showMessage('Admin girişi başarılı', 'success');
        } else {
            DiziPortalApp.showMessage('Kullanıcı adı veya şifre hatalı', 'error');
        }
    },

    // Close login modal
    closeLoginModal() {
        const modal = document.getElementById('admin-login-modal');
        if (modal) {
            modal.classList.remove('active');
        }
    },

    // Show admin panel
    showAdminPanel() {
        if (!this.state.isLoggedIn) {
            DiziPortalApp.showAdminLogin();
            return;
        }

        const panel = document.getElementById('admin-panel');
        if (panel) {
            panel.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Load admin content
            this.loadAdminContent();
        }
    },

    // Close admin panel
    closeAdminPanel() {
        const panel = document.getElementById('admin-panel');
        if (panel) {
            panel.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    },

    // Switch admin tab
    switchTab(tabName) {
        this.state.currentTab = tabName;
        
        // Update tab buttons
        document.querySelectorAll('.admin-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
        
        // Update tab content
        document.querySelectorAll('.admin-tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(`${tabName}-tab`).classList.add('active');
        
        // Load tab-specific content
        this.loadTabContent(tabName);
    },

    // Load admin content
    loadAdminContent() {
        this.loadTabContent(this.state.currentTab);
        this.loadSettings();
    },

    // Load tab content
    loadTabContent(tabName) {
        switch (tabName) {
            case 'matches':
                this.loadMatchesList();
                break;
            case 'channels':
                this.loadChannelsList();
                break;
            case 'settings':
                this.loadSettings();
                break;
        }
    },

    // Load matches list
    loadMatchesList() {
        const container = document.getElementById('admin-matches-list');
        if (!container) return;

        const matches = DiziPortalApp.state.matches;
        
        if (matches.length === 0) {
            container.innerHTML = `
                <div class="admin-list-item">
                    <div class="item-info">
                        <div class="item-title">Henüz maç eklenmemiş</div>
                        <div class="item-subtitle">Yukarıdaki formu kullanarak maç ekleyebilirsiniz</div>
                    </div>
                </div>
            `;
            return;
        }

        container.innerHTML = matches.map(match => `
            <div class="admin-list-item">
                <div class="item-info">
                    <div class="item-title">${match.homeTeam} vs ${match.awayTeam}</div>
                    <div class="item-subtitle">${new Date(match.matchTime).toLocaleString('tr-TR')} - ${match.location}</div>
                    <div class="item-details">
                        <div class="item-detail">
                            <i class="fas fa-eye"></i>
                            ${DiziPortalApp.formatNumber(match.viewers)} izleyici
                        </div>
                        <div class="item-detail">
                            <i class="fas fa-${match.isLive ? 'play-circle' : 'clock'}"></i>
                            ${match.isLive ? 'CANLI' : 'YAKINDA'}
                        </div>
                        <div class="item-detail">
                            <i class="fas fa-link"></i>
                            ${this.truncateUrl(match.streamUrl)}
                        </div>
                    </div>
                </div>
                <div class="item-actions">
                    <button class="action-btn edit" onclick="diziportalEditMatch('${match.id}')" title="Düzenle">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete" onclick="diziportalDeleteMatch('${match.id}')" title="Sil">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');
    },

    // Load channels list
    loadChannelsList() {
        const container = document.getElementById('admin-channels-list');
        if (!container) return;

        const channels = DiziPortalApp.state.channels;
        
        if (channels.length === 0) {
            container.innerHTML = `
                <div class="admin-list-item">
                    <div class="item-info">
                        <div class="item-title">Henüz kanal eklenmemiş</div>
                        <div class="item-subtitle">Yukarıdaki formu kullanarak kanal ekleyebilirsiniz</div>
                    </div>
                </div>
            `;
            return;
        }

        container.innerHTML = channels.map(channel => `
            <div class="admin-list-item">
                <div class="item-info">
                    <div class="item-title">${channel.name}</div>
                    <div class="item-subtitle">${DiziPortalApp.getCategoryName(channel.category)}</div>
                    <div class="item-details">
                        <div class="item-detail">
                            <i class="fas fa-eye"></i>
                            ${DiziPortalApp.formatNumber(channel.viewers)} izleyici
                        </div>
                        <div class="item-detail">
                            <i class="fas fa-${channel.isLive ? 'play-circle' : 'stop-circle'}"></i>
                            ${channel.isLive ? 'CANLI' : 'KAPALI'}
                        </div>
                        <div class="item-detail">
                            <i class="fas fa-link"></i>
                            ${this.truncateUrl(channel.streamUrl)}
                        </div>
                    </div>
                </div>
                <div class="item-actions">
                    <button class="action-btn edit" onclick="diziportalEditChannel('${channel.id}')" title="Düzenle">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete" onclick="diziportalDeleteChannel('${channel.id}')" title="Sil">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');
    },

    // Load settings
    loadSettings() {
        const settings = DiziPortalApp.state.settings;
        
        // Update form fields
        const siteTitle = document.querySelector('input[name="siteTitle"]');
        const siteDescription = document.querySelector('textarea[name="siteDescription"]');
        const telegramUrl = document.getElementById('admin-telegram');
        const instagramUrl = document.getElementById('admin-instagram');
        const twitterUrl = document.getElementById('admin-twitter');
        const tiktokUrl = document.getElementById('admin-tiktok');

        if (siteTitle) siteTitle.value = settings.siteTitle;
        if (siteDescription) siteDescription.value = settings.siteDescription;
        if (telegramUrl) telegramUrl.value = settings.socialLinks.telegram;
        if (instagramUrl) instagramUrl.value = settings.socialLinks.instagram;
        if (twitterUrl) twitterUrl.value = settings.socialLinks.twitter;
        if (tiktokUrl) tiktokUrl.value = settings.socialLinks.tiktok;
    },

    // Handle add match
    handleAddMatch(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const matchData = {
            id: 'match_' + Date.now(),
            homeTeam: formData.get('homeTeam'),
            awayTeam: formData.get('awayTeam'),
            homeLogo: formData.get('homeLogo') || '',
            awayLogo: formData.get('awayLogo') || '',
            matchTime: formData.get('matchTime'),
            location: formData.get('location'),
            streamUrl: formData.get('streamUrl'),
            isLive: new Date(formData.get('matchTime')) <= new Date(),
            viewers: Math.floor(Math.random() * 50000) + 10000
        };

        if (this.state.editMode && this.state.editingId) {
            // Update existing match
            const index = DiziPortalApp.state.matches.findIndex(m => m.id === this.state.editingId);
            if (index !== -1) {
                matchData.id = this.state.editingId;
                matchData.viewers = DiziPortalApp.state.matches[index].viewers;
                DiziPortalApp.state.matches[index] = matchData;
                DiziPortalApp.showMessage('Maç güncellendi', 'success');
            }
            this.cancelEdit();
        } else {
            // Add new match
            DiziPortalApp.state.matches.push(matchData);
            DiziPortalApp.showMessage('Maç eklendi', 'success');
        }

        // Reset form
        e.target.reset();
        
        // Update displays
        DiziPortalApp.renderMatches();
        DiziPortalApp.updateStatistics();
        this.loadMatchesList();
        DiziPortalApp.saveData();
    },

    // Handle add channel
    handleAddChannel(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const channelData = {
            id: 'channel_' + Date.now(),
            name: formData.get('channelName'),
            category: formData.get('category'),
            logo: formData.get('channelLogo'),
            streamUrl: formData.get('streamUrl'),
            isLive: true,
            viewers: Math.floor(Math.random() * 35000) + 5000
        };

        if (this.state.editMode && this.state.editingId) {
            // Update existing channel
            const index = DiziPortalApp.state.channels.findIndex(c => c.id === this.state.editingId);
            if (index !== -1) {
                channelData.id = this.state.editingId;
                channelData.viewers = DiziPortalApp.state.channels[index].viewers;
                DiziPortalApp.state.channels[index] = channelData;
                DiziPortalApp.showMessage('Kanal güncellendi', 'success');
            }
            this.cancelEdit();
        } else {
            // Add new channel
            DiziPortalApp.state.channels.push(channelData);
            DiziPortalApp.showMessage('Kanal eklendi', 'success');
        }

        // Reset form
        e.target.reset();
        
        // Update displays
        DiziPortalApp.renderChannels();
        DiziPortalApp.updateStatistics();
        this.loadChannelsList();
        DiziPortalApp.saveData();
    },

    // Handle save settings
    handleSaveSettings(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        
        DiziPortalApp.state.settings.siteTitle = formData.get('siteTitle');
        DiziPortalApp.state.settings.siteDescription = formData.get('siteDescription');
        DiziPortalApp.state.settings.socialLinks.telegram = formData.get('telegramUrl');
        DiziPortalApp.state.settings.socialLinks.instagram = formData.get('instagramUrl');
        DiziPortalApp.state.settings.socialLinks.twitter = formData.get('twitterUrl');
        DiziPortalApp.state.settings.socialLinks.tiktok = formData.get('tiktokUrl');

        // Update social links
        DiziPortalApp.updateSocialLinks();
        
        // Save data
        DiziPortalApp.saveData();
        
        DiziPortalApp.showMessage('Ayarlar kaydedildi', 'success');
    },

    // Edit match
    editMatch(id) {
        const match = DiziPortalApp.state.matches.find(m => m.id === id);
        if (!match) return;

        this.state.editMode = true;
        this.state.editingId = id;

        // Switch to matches tab
        this.switchTab('matches');

        // Fill form with match data
        const form = document.getElementById('add-match-form');
        if (form) {
            form.querySelector('input[name="homeTeam"]').value = match.homeTeam;
            form.querySelector('input[name="awayTeam"]').value = match.awayTeam;
            form.querySelector('input[name="homeLogo"]').value = match.homeLogo;
            form.querySelector('input[name="awayLogo"]').value = match.awayLogo;
            form.querySelector('input[name="matchTime"]').value = new Date(match.matchTime).toISOString().slice(0, 16);
            form.querySelector('input[name="location"]').value = match.location;
            form.querySelector('input[name="streamUrl"]').value = match.streamUrl;
            
            // Update submit button
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Güncelle';
                submitBtn.classList.add('warning');
            }

            // Add cancel button
            this.addCancelButton(form);
        }

        DiziPortalApp.showMessage('Düzenleme modu aktif', 'info');
    },

    // Edit channel
    editChannel(id) {
        const channel = DiziPortalApp.state.channels.find(c => c.id === id);
        if (!channel) return;

        this.state.editMode = true;
        this.state.editingId = id;

        // Switch to channels tab
        this.switchTab('channels');

        // Fill form with channel data
        const form = document.getElementById('add-channel-form');
        if (form) {
            form.querySelector('input[name="channelName"]').value = channel.name;
            form.querySelector('select[name="category"]').value = channel.category;
            form.querySelector('input[name="channelLogo"]').value = channel.logo;
            form.querySelector('input[name="streamUrl"]').value = channel.streamUrl;
            
            // Update submit button
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Güncelle';
                submitBtn.classList.add('warning');
            }

            // Add cancel button
            this.addCancelButton(form);
        }

        DiziPortalApp.showMessage('Düzenleme modu aktif', 'info');
    },

    // Add cancel button
    addCancelButton(form) {
        // Remove existing cancel button
        const existingCancel = form.querySelector('.cancel-edit-btn');
        if (existingCancel) {
            existingCancel.remove();
        }

        const cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className = 'admin-btn secondary cancel-edit-btn';
        cancelBtn.innerHTML = '<i class="fas fa-times"></i> İptal';
        cancelBtn.onclick = () => this.cancelEdit();

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.parentNode.insertBefore(cancelBtn, submitBtn.nextSibling);
    },

    // Cancel edit mode
    cancelEdit() {
        this.state.editMode = false;
        this.state.editingId = null;

        // Reset forms
        const matchForm = document.getElementById('add-match-form');
        const channelForm = document.getElementById('add-channel-form');

        if (matchForm) {
            matchForm.reset();
            const submitBtn = matchForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-plus"></i> Maç Ekle';
                submitBtn.classList.remove('warning');
            }
        }

        if (channelForm) {
            channelForm.reset();
            const submitBtn = channelForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-plus"></i> Kanal Ekle';
                submitBtn.classList.remove('warning');
            }
        }

        // Remove cancel buttons
        document.querySelectorAll('.cancel-edit-btn').forEach(btn => btn.remove());

        DiziPortalApp.showMessage('Düzenleme iptal edildi', 'info');
    },

    // Delete match
    deleteMatch(id) {
        if (!confirm('Bu maçı silmek istediğinizden emin misiniz?')) {
            return;
        }

        const index = DiziPortalApp.state.matches.findIndex(m => m.id === id);
        if (index !== -1) {
            DiziPortalApp.state.matches.splice(index, 1);
            
            // Update displays
            DiziPortalApp.renderMatches();
            DiziPortalApp.updateStatistics();
            this.loadMatchesList();
            DiziPortalApp.saveData();
            
            DiziPortalApp.showMessage('Maç silindi', 'success');
        }
    },

    // Delete channel
    deleteChannel(id) {
        if (!confirm('Bu kanalı silmek istediğinizden emin misiniz?')) {
            return;
        }

        const index = DiziPortalApp.state.channels.findIndex(c => c.id === id);
        if (index !== -1) {
            DiziPortalApp.state.channels.splice(index, 1);
            
            // Update displays
            DiziPortalApp.renderChannels();
            DiziPortalApp.updateStatistics();
            this.loadChannelsList();
            DiziPortalApp.saveData();
            
            DiziPortalApp.showMessage('Kanal silindi', 'success');
        }
    },

    // Logout
    logout() {
        if (confirm('Çıkış yapmak istediğinizden emin misiniz?')) {
            this.state.isLoggedIn = false;
            localStorage.removeItem('diziportal_admin_session');
            this.closeAdminPanel();
            DiziPortalApp.showMessage('Çıkış yapıldı', 'info');
        }
    },

    // Utility functions
    truncateUrl(url) {
        if (url.length > 30) {
            return url.substring(0, 30) + '...';
        }
        return url;
    }
};

// Global admin functions
window.diziportalEditMatch = (id) => {
    DiziPortalAdmin.editMatch(id);
};

window.diziportalDeleteMatch = (id) => {
    DiziPortalAdmin.deleteMatch(id);
};

window.diziportalEditChannel = (id) => {
    DiziPortalAdmin.editChannel(id);
};

window.diziportalDeleteChannel = (id) => {
    DiziPortalAdmin.deleteChannel(id);
};

window.diziportalAdminLogout = () => {
    DiziPortalAdmin.logout();
};

// Override DiziPortalApp.showAdminLogin to use our admin
DiziPortalApp.showAdminLogin = function() {
    if (DiziPortalAdmin.state.isLoggedIn) {
        DiziPortalAdmin.showAdminPanel();
    } else {
        const loginModal = document.getElementById('admin-login-modal');
        if (loginModal) {
            loginModal.classList.add('active');
            
            // Focus on username field
            const usernameField = loginModal.querySelector('input[name="username"]');
            if (usernameField) {
                setTimeout(() => usernameField.focus(), 100);
            }
        }
    }
};

// Initialize admin when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    DiziPortalAdmin.init();
});

// Export for global access
window.DiziPortalAdmin = DiziPortalAdmin;