/*
 * DG SPORTS Admin Dashboard JavaScript
 * Developer: DiziPortal.Com
 * Professional Admin Management System
 */

// Admin Dashboard Controller
const AdminDashboard = {
    // Dashboard state
    state: {
        isAuthenticated: false,
        currentSection: 'dashboard',
        adminUser: null,
        dataRefreshInterval: null,
        charts: {},
        notifications: []
    },

    // Credentials (In production, use secure backend authentication)
    credentials: {
        username: 'admin',
        password: 'dgsports2024'
    },

    // Initialize dashboard
    init() {
        console.log('🔐 Admin Dashboard initializing...');
        this.checkAuthentication();
        this.setupEventListeners();
        this.initializeCharts();
    },

    // Check authentication status
    checkAuthentication() {
        const savedAuth = localStorage.getItem('admin_session');
        if (savedAuth) {
            try {
                const session = JSON.parse(savedAuth);
                if (session.expires > Date.now()) {
                    this.state.isAuthenticated = true;
                    this.state.adminUser = session.user;
                    this.showDashboard();
                    return;
                }
            } catch (error) {
                console.warn('Invalid session data');
            }
        }
        this.showLoginScreen();
    },

    // Setup event listeners
    setupEventListeners() {
        // Login form
        const loginForm = document.getElementById('admin-login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', this.handleLogin.bind(this));
        }

        // Password toggle
        const passwordToggle = document.querySelector('.toggle-password');
        if (passwordToggle) {
            passwordToggle.addEventListener('click', this.togglePasswordVisibility);
        }

        // Navigation links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const section = link.getAttribute('data-section');
                this.switchSection(section);
            });
        });

        // Mobile menu toggle
        const menuToggle = document.querySelector('.menu-toggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', this.toggleSidebar);
        }

        // Modal handlers
        this.setupModalHandlers();

        // Form handlers
        this.setupFormHandlers();

        // Global keyboard shortcuts
        document.addEventListener('keydown', this.handleKeyboardShortcuts.bind(this));
    },

    // Handle login
    handleLogin(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const username = formData.get('username');
        const password = formData.get('password');
        const remember = formData.get('remember');

        // Add loading state
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Authenticating...';
        submitBtn.disabled = true;

        // Simulate authentication delay
        setTimeout(() => {
            if (username === this.credentials.username && password === this.credentials.password) {
                this.authenticateUser(username, remember);
            } else {
                this.showLoginError('Invalid credentials. Please try again.');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        }, 1500);
    },

    // Authenticate user
    authenticateUser(username, remember) {
        const expiryTime = remember ? 
            Date.now() + (30 * 24 * 60 * 60 * 1000) : // 30 days
            Date.now() + (8 * 60 * 60 * 1000); // 8 hours

        const session = {
            user: username,
            loginTime: Date.now(),
            expires: expiryTime,
            remember: !!remember
        };

        localStorage.setItem('admin_session', JSON.stringify(session));
        
        this.state.isAuthenticated = true;
        this.state.adminUser = username;
        
        this.showDashboard();
        this.loadDashboardData();
        
        console.log('✅ Admin authenticated successfully');
    },

    // Show login error
    showLoginError(message) {
        // Remove existing error
        const existingError = document.querySelector('.login-error');
        if (existingError) {
            existingError.remove();
        }

        // Create error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'login-error';
        errorDiv.style.cssText = `
            background: rgba(220, 38, 38, 0.1);
            border: 1px solid rgba(220, 38, 38, 0.3);
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        `;
        errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;

        // Insert before login form
        const loginForm = document.getElementById('admin-login-form');
        loginForm.parentNode.insertBefore(errorDiv, loginForm);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 5000);
    },

    // Toggle password visibility
    togglePasswordVisibility() {
        const passwordInput = document.querySelector('input[name="password"]');
        const toggleIcon = document.querySelector('.toggle-password i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    },

    // Show login screen
    showLoginScreen() {
        document.getElementById('admin-login-screen').style.display = 'flex';
        document.getElementById('admin-dashboard').style.display = 'none';
    },

    // Show dashboard
    showDashboard() {
        document.getElementById('admin-login-screen').style.display = 'none';
        document.getElementById('admin-dashboard').style.display = 'flex';
        
        // Update admin name
        const adminNameEl = document.getElementById('admin-name');
        if (adminNameEl) {
            adminNameEl.textContent = this.state.adminUser || 'Administrator';
        }
    },

    // Switch section
    switchSection(sectionName) {
        // Update navigation
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelector(`[data-section="${sectionName}"]`).parentElement.classList.add('active');

        // Update content sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });
        document.getElementById(`${sectionName}-section`).classList.add('active');

        // Update breadcrumb
        document.getElementById('current-section').textContent = this.getSectionTitle(sectionName);
        
        this.state.currentSection = sectionName;
        
        // Load section-specific data
        this.loadSectionData(sectionName);
    },

    // Get section title
    getSectionTitle(section) {
        const titles = {
            dashboard: 'Dashboard',
            matches: 'Live Matches',
            channels: '24/7 Channels',
            analytics: 'Analytics',
            users: 'User Management',
            content: 'Content Manager',
            settings: 'Settings',
            backup: 'Backup & Restore',
            logs: 'System Logs'
        };
        return titles[section] || section;
    },

    // Toggle sidebar
    toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('active');
    },

    // Load dashboard data
    loadDashboardData() {
        // Load stats
        this.updateStatistics();
        
        // Load recent activity
        this.loadRecentActivity();
        
        // Update charts
        this.updateCharts();
        
        // Start real-time updates
        this.startDataRefresh();
    },

    // Load section data
    loadSectionData(section) {
        switch (section) {
            case 'matches':
                this.loadMatchesData();
                break;
            case 'channels':
                this.loadChannelsData();
                break;
            case 'analytics':
                this.loadAnalyticsData();
                break;
            case 'settings':
                this.loadSettingsData();
                break;
            default:
                break;
        }
    },

    // Update statistics
    updateStatistics() {
        // Get data from main app if available
        if (typeof DiziPortalApp !== 'undefined') {
            const totalViewers = DiziPortalApp.state.matches.reduce((sum, match) => sum + match.viewers, 0) +
                               DiziPortalApp.state.channels.reduce((sum, channel) => sum + channel.viewers, 0);
            
            const liveMatches = DiziPortalApp.state.matches.filter(match => match.isLive).length;
            const activeChannels = DiziPortalApp.state.channels.filter(channel => channel.isLive).length;

            // Update stat cards
            this.animateStatNumber('total-viewers-stat', totalViewers);
            this.animateStatNumber('live-matches-stat', liveMatches);
            this.animateStatNumber('active-channels-stat', activeChannels);

            // Update sidebar badges
            document.getElementById('matches-count').textContent = liveMatches;
            document.getElementById('channels-count').textContent = activeChannels;
        } else {
            // Use dummy data
            this.animateStatNumber('total-viewers-stat', Math.floor(Math.random() * 100000) + 50000);
            this.animateStatNumber('live-matches-stat', Math.floor(Math.random() * 10) + 5);
            this.animateStatNumber('active-channels-stat', Math.floor(Math.random() * 20) + 10);
        }
    },

    // Animate stat numbers
    animateStatNumber(elementId, targetNumber) {
        const element = document.getElementById(elementId);
        if (!element) return;

        const startNumber = parseInt(element.textContent.replace(/[,\.]/g, '')) || 0;
        const duration = 1500;
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
        return num.toLocaleString();
    },

    // Load recent activity
    loadRecentActivity() {
        const activities = [
            {
                icon: 'fa-plus-circle',
                text: 'New match added: Galatasaray vs Fenerbahçe',
                time: '2 minutes ago',
                type: 'success'
            },
            {
                icon: 'fa-tv',
                text: 'Channel "beIN Sports 1" status updated',
                time: '5 minutes ago',
                type: 'info'
            },
            {
                icon: 'fa-users',
                text: '1,234 new viewers joined',
                time: '10 minutes ago',
                type: 'success'
            },
            {
                icon: 'fa-exclamation-triangle',
                text: 'High server load detected',
                time: '15 minutes ago',
                type: 'warning'
            },
            {
                icon: 'fa-cog',
                text: 'System settings updated',
                time: '1 hour ago',
                type: 'info'
            }
        ];

        const container = document.getElementById('recent-activity-list');
        if (container) {
            container.innerHTML = activities.map(activity => `
                <div class="activity-item ${activity.type}">
                    <div class="activity-icon">
                        <i class="fas ${activity.icon}"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-text">${activity.text}</div>
                        <div class="activity-time">${activity.time}</div>
                    </div>
                </div>
            `).join('');
        }
    },

    // Initialize charts
    initializeCharts() {
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js not loaded');
            return;
        }

        // Set default chart colors
        Chart.defaults.color = '#9ca3af';
        Chart.defaults.borderColor = 'rgba(107, 114, 128, 0.2)';

        this.initViewerChart();
        this.initContentChart();
    },

    // Initialize viewer chart
    initViewerChart() {
        const ctx = document.getElementById('viewerChart');
        if (!ctx) return;

        this.state.charts.viewer = new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.generateTimeLabels(24),
                datasets: [{
                    label: 'Total Viewers',
                    data: this.generateViewerData(24),
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(107, 114, 128, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(107, 114, 128, 0.1)'
                        }
                    }
                }
            }
        });
    },

    // Initialize content chart
    initContentChart() {
        const ctx = document.getElementById('contentChart');
        if (!ctx) return;

        this.state.charts.content = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Live Matches', 'Football Channels', 'Basketball Channels', 'General Sports'],
                datasets: [{
                    data: [35, 25, 20, 20],
                    backgroundColor: [
                        '#dc2626',
                        '#991b1b',
                        '#b91c1c',
                        '#7f1d1d'
                    ],
                    borderWidth: 2,
                    borderColor: '#0a0a0a'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    },

    // Generate time labels
    generateTimeLabels(hours) {
        const labels = [];
        const now = new Date();
        
        for (let i = hours - 1; i >= 0; i--) {
            const time = new Date(now.getTime() - (i * 60 * 60 * 1000));
            labels.push(time.getHours().toString().padStart(2, '0') + ':00');
        }
        
        return labels;
    },

    // Generate viewer data
    generateViewerData(points) {
        const data = [];
        let baseValue = 50000;
        
        for (let i = 0; i < points; i++) {
            baseValue += (Math.random() - 0.5) * 10000;
            baseValue = Math.max(20000, baseValue);
            data.push(Math.floor(baseValue));
        }
        
        return data;
    },

    // Update charts
    updateCharts() {
        if (this.state.charts.viewer) {
            // Update viewer chart with new data
            this.state.charts.viewer.data.datasets[0].data = this.generateViewerData(24);
            this.state.charts.viewer.update();
        }
    },

    // Setup modal handlers
    setupModalHandlers() {
        // Close modal buttons
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const modal = e.target.closest('.modal');
                this.closeModal(modal.id);
            });
        });

        // Close modal on background click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal(modal.id);
                }
            });
        });
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
    },

    // Handle keyboard shortcuts
    handleKeyboardShortcuts(e) {
        if (e.ctrlKey || e.metaKey) {
            switch (e.key) {
                case '1':
                    e.preventDefault();
                    this.switchSection('dashboard');
                    break;
                case '2':
                    e.preventDefault();
                    this.switchSection('matches');
                    break;
                case '3':
                    e.preventDefault();
                    this.switchSection('channels');
                    break;
                case 'r':
                    e.preventDefault();
                    this.refreshData();
                    break;
            }
        }
        
        if (e.key === 'Escape') {
            // Close any open modals
            document.querySelectorAll('.modal.active').forEach(modal => {
                this.closeModal(modal.id);
            });
        }
    },

    // Load matches data
    loadMatchesData() {
        const tbody = document.getElementById('matches-table-body');
        if (!tbody) return;

        let matches = [];
        if (typeof DiziPortalApp !== 'undefined') {
            matches = DiziPortalApp.state.matches;
        }

        if (matches.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; color: #6b7280; padding: 2rem;">
                        <i class="fas fa-futbol" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        No matches found. <a href="#" onclick="showAddMatchModal()" style="color: #dc2626;">Add your first match</a>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = matches.map(match => `
            <tr>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <img src="${match.homeLogo}" alt="${match.homeTeam}" style="width: 24px; height: 24px; border-radius: 50%;" onerror="this.style.display='none'">
                        <span>vs</span>
                        <img src="${match.awayLogo}" alt="${match.awayTeam}" style="width: 24px; height: 24px; border-radius: 50%;" onerror="this.style.display='none'">
                    </div>
                </td>
                <td>
                    <div>
                        <div style="font-weight: 600;">${match.homeTeam} vs ${match.awayTeam}</div>
                        <div style="font-size: 0.8rem; color: #9ca3af;">${match.location}</div>
                    </div>
                </td>
                <td>${new Date(match.matchTime).toLocaleString('tr-TR')}</td>
                <td>${match.location}</td>
                <td>
                    <span class="status-badge ${match.isLive ? 'live' : 'upcoming'}">
                        ${match.isLive ? '🔴 LIVE' : '⏰ Upcoming'}
                    </span>
                </td>
                <td>${this.formatNumber(match.viewers)}</td>
                <td>
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="action-btn" onclick="editMatch('${match.id}')" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn" onclick="deleteMatch('${match.id}')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button class="action-btn" onclick="viewMatch('${match.id}')" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    // Load channels data
    loadChannelsData() {
        const tbody = document.getElementById('channels-table-body');
        if (!tbody) return;

        let channels = [];
        if (typeof DiziPortalApp !== 'undefined') {
            channels = DiziPortalApp.state.channels;
        }

        if (channels.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; color: #6b7280; padding: 2rem;">
                        <i class="fas fa-tv" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        No channels found. <a href="#" onclick="showAddChannelModal()" style="color: #dc2626;">Add your first channel</a>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = channels.map(channel => `
            <tr>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <img src="${channel.logo}" alt="${channel.name}" style="width: 32px; height: 32px; border-radius: 6px;" onerror="this.style.display='none'">
                        <span style="font-weight: 500;">${channel.name}</span>
                    </div>
                </td>
                <td>
                    <span class="category-badge ${channel.category}">
                        ${this.getCategoryName(channel.category)}
                    </span>
                </td>
                <td>
                    <span class="status-badge ${channel.isLive ? 'live' : 'offline'}">
                        ${channel.isLive ? '🟢 Live' : '🔴 Offline'}
                    </span>
                </td>
                <td>${this.formatNumber(channel.viewers)}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <div class="quality-bar">
                            <div class="quality-fill" style="width: ${Math.floor(Math.random() * 30) + 70}%"></div>
                        </div>
                        <span style="font-size: 0.8rem;">${Math.floor(Math.random() * 30) + 70}%</span>
                    </div>
                </td>
                <td>
                    <span style="color: #10b981; font-weight: 500;">
                        ${(Math.random() * 2 + 98).toFixed(1)}%
                    </span>
                </td>
                <td>
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="action-btn" onclick="editChannel('${channel.id}')" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn" onclick="deleteChannel('${channel.id}')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button class="action-btn" onclick="viewChannel('${channel.id}')" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    // Get category name
    getCategoryName(category) {
        const names = {
            'futbol': 'Football',
            'basketbol': 'Basketball',
            'genel': 'General Sports'
        };
        return names[category] || category;
    },

    // Handle add match
    handleAddMatch(e) {
        e.preventDefault();
        // Implementation would be here
        console.log('Add match form submitted');
        this.closeModal('add-match-modal');
    },

    // Handle add channel
    handleAddChannel(e) {
        e.preventDefault();
        // Implementation would be here
        console.log('Add channel form submitted');
        this.closeModal('add-channel-modal');
    },

    // Show modal
    showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    },

    // Close modal
    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    },

    // Start data refresh
    startDataRefresh() {
        // Refresh data every 30 seconds
        this.state.dataRefreshInterval = setInterval(() => {
            this.updateStatistics();
            this.updateCharts();
        }, 30000);
    },

    // Refresh data manually
    refreshData() {
        console.log('🔄 Refreshing data...');
        this.updateStatistics();
        this.updateCharts();
        this.loadSectionData(this.state.currentSection);
        
        // Show refresh notification
        this.showNotification('Data refreshed successfully', 'success');
    },

    // Show notification
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}-circle"></i>
            <span>${message}</span>
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: rgba(26, 26, 26, 0.95);
            border: 1px solid ${type === 'success' ? '#10b981' : type === 'error' ? '#dc2626' : '#3b82f6'};
            color: ${type === 'success' ? '#10b981' : type === 'error' ? '#dc2626' : '#3b82f6'};
            padding: 1rem;
            border-radius: 8px;
            backdrop-filter: blur(10px);
            z-index: 10001;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    },

    // Logout
    logout() {
        localStorage.removeItem('admin_session');
        this.state.isAuthenticated = false;
        this.state.adminUser = null;
        
        if (this.state.dataRefreshInterval) {
            clearInterval(this.state.dataRefreshInterval);
        }
        
        this.showLoginScreen();
        console.log('👋 Admin logged out');
    }
};

// Global functions
window.AdminDashboard = AdminDashboard;

window.showAddMatchModal = () => {
    AdminDashboard.showModal('add-match-modal');
};

window.showAddChannelModal = () => {
    AdminDashboard.showModal('add-channel-modal');
};

window.closeModal = (modalId) => {
    AdminDashboard.closeModal(modalId);
};

window.refreshData = () => {
    AdminDashboard.refreshData();
};

window.viewSite = () => {
    window.open('../index.html', '_blank');
};

window.adminLogout = () => {
    if (confirm('Are you sure you want to logout?')) {
        AdminDashboard.logout();
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    AdminDashboard.init();
});

// Add necessary CSS for dynamic elements
const additionalCSS = `
.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-badge.live {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.status-badge.upcoming {
    background: rgba(245, 158, 11, 0.2);
    color: #f59e0b;
}

.status-badge.offline {
    background: rgba(107, 114, 128, 0.2);
    color: #6b7280;
}

.category-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    background: rgba(220, 38, 38, 0.2);
    color: #dc2626;
}

.quality-bar {
    width: 60px;
    height: 4px;
    background: rgba(107, 114, 128, 0.3);
    border-radius: 2px;
    overflow: hidden;
}

.quality-fill {
    height: 100%;
    background: #10b981;
    border-radius: 2px;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid rgba(107, 114, 128, 0.1);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.activity-item.success .activity-icon {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.activity-item.warning .activity-icon {
    background: rgba(245, 158, 11, 0.2);
    color: #f59e0b;
}

.activity-item.info .activity-icon {
    background: rgba(59, 130, 246, 0.2);
    color: #3b82f6;
}

.activity-content {
    flex: 1;
}

.activity-text {
    color: #ffffff;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.activity-time {
    color: #6b7280;
    font-size: 0.8rem;
}
`;

// Inject additional CSS
const style = document.createElement('style');
style.textContent = additionalCSS;
document.head.appendChild(style);