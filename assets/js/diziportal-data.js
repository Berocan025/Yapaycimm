/*
 * DG SPORTS - Data Management
 * Developer: DiziPortal.Com
 * Data persistence and backup system for shared hosting
 */

// DiziPortal Data Manager
const DiziPortalData = {
    // Data configuration
    config: {
        storagePrefix: 'diziportal_',
        backupInterval: 300000, // 5 minutes
        maxBackups: 10,
        compressionEnabled: true
    },

    // Initialize data manager
    init() {
        console.log('💾 DiziPortal Data Manager initializing...');
        this.setupDataPersistence();
        this.startBackupScheduler();
        this.checkDataIntegrity();
    },

    // Setup data persistence
    setupDataPersistence() {
        // Check storage availability
        if (!this.isStorageAvailable()) {
            console.warn('⚠️ localStorage not available, using memory storage');
            this.useMemoryStorage = true;
            return;
        }

        // Setup auto-save
        this.setupAutoSave();

        // Setup beforeunload handler
        window.addEventListener('beforeunload', () => {
            this.saveAllData();
        });

        // Setup periodic save
        setInterval(() => {
            this.saveAllData();
        }, 60000); // Save every minute
    },

    // Check if localStorage is available
    isStorageAvailable() {
        try {
            const test = 'diziportal_test';
            localStorage.setItem(test, test);
            localStorage.removeItem(test);
            return true;
        } catch (error) {
            return false;
        }
    },

    // Setup auto-save for DiziPortalApp state changes
    setupAutoSave() {
        // Override DiziPortalApp.saveData for better integration
        const originalSaveData = DiziPortalApp.saveData;
        DiziPortalApp.saveData = () => {
            originalSaveData.call(DiziPortalApp);
            this.createBackup();
        };
    },

    // Save all data
    saveAllData() {
        try {
            if (this.useMemoryStorage) return;

            const data = {
                matches: DiziPortalApp.state.matches,
                channels: DiziPortalApp.state.channels,
                settings: DiziPortalApp.state.settings,
                timestamp: Date.now(),
                version: '1.0.0'
            };

            // Compress data if enabled
            const dataToSave = this.config.compressionEnabled ? 
                this.compressData(data) : JSON.stringify(data);

            localStorage.setItem(this.config.storagePrefix + 'data', dataToSave);
            
            console.log('💾 Data saved successfully');
        } catch (error) {
            console.error('❌ Failed to save data:', error);
            this.handleStorageError(error);
        }
    },

    // Load all data
    loadAllData() {
        try {
            if (this.useMemoryStorage) return false;

            const savedData = localStorage.getItem(this.config.storagePrefix + 'data');
            if (!savedData) return false;

            const data = this.config.compressionEnabled ? 
                this.decompressData(savedData) : JSON.parse(savedData);

            // Validate data structure
            if (!this.validateDataStructure(data)) {
                console.warn('⚠️ Invalid data structure, using defaults');
                return false;
            }

            // Restore data to DiziPortalApp
            DiziPortalApp.state.matches = data.matches || [];
            DiziPortalApp.state.channels = data.channels || [];
            DiziPortalApp.state.settings = { ...DiziPortalApp.state.settings, ...data.settings };

            console.log('✅ Data loaded successfully');
            return true;
        } catch (error) {
            console.error('❌ Failed to load data:', error);
            return false;
        }
    },

    // Validate data structure
    validateDataStructure(data) {
        return data && 
               typeof data === 'object' &&
               Array.isArray(data.matches) &&
               Array.isArray(data.channels) &&
               typeof data.settings === 'object';
    },

    // Create backup
    createBackup() {
        try {
            if (this.useMemoryStorage) return;

            const backupKey = this.config.storagePrefix + 'backup_' + Date.now();
            const data = {
                matches: DiziPortalApp.state.matches,
                channels: DiziPortalApp.state.channels,
                settings: DiziPortalApp.state.settings,
                timestamp: Date.now()
            };

            const compressedData = this.compressData(data);
            localStorage.setItem(backupKey, compressedData);

            // Clean old backups
            this.cleanOldBackups();

            console.log('💾 Backup created:', backupKey);
        } catch (error) {
            console.error('❌ Failed to create backup:', error);
        }
    },

    // Clean old backups
    cleanOldBackups() {
        try {
            const backupKeys = [];
            
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key && key.startsWith(this.config.storagePrefix + 'backup_')) {
                    backupKeys.push({
                        key: key,
                        timestamp: parseInt(key.split('_')[2])
                    });
                }
            }

            // Sort by timestamp (newest first)
            backupKeys.sort((a, b) => b.timestamp - a.timestamp);

            // Remove excess backups
            for (let i = this.config.maxBackups; i < backupKeys.length; i++) {
                localStorage.removeItem(backupKeys[i].key);
                console.log('🗑️ Removed old backup:', backupKeys[i].key);
            }
        } catch (error) {
            console.error('❌ Failed to clean old backups:', error);
        }
    },

    // Start backup scheduler
    startBackupScheduler() {
        setInterval(() => {
            this.createBackup();
        }, this.config.backupInterval);
    },

    // Restore from backup
    restoreFromBackup(timestamp) {
        try {
            const backupKey = this.config.storagePrefix + 'backup_' + timestamp;
            const backupData = localStorage.getItem(backupKey);
            
            if (!backupData) {
                throw new Error('Backup not found');
            }

            const data = this.decompressData(backupData);
            
            if (!this.validateDataStructure(data)) {
                throw new Error('Invalid backup data structure');
            }

            // Restore data
            DiziPortalApp.state.matches = data.matches;
            DiziPortalApp.state.channels = data.channels;
            DiziPortalApp.state.settings = { ...DiziPortalApp.state.settings, ...data.settings };

            // Update UI
            DiziPortalApp.renderMatches();
            DiziPortalApp.renderChannels();
            DiziPortalApp.updateStatistics();
            DiziPortalApp.updateSocialLinks();

            // Save restored data
            this.saveAllData();

            console.log('✅ Data restored from backup:', timestamp);
            DiziPortalApp.showMessage('Yedek geri yüklendi', 'success');
            
            return true;
        } catch (error) {
            console.error('❌ Failed to restore from backup:', error);
            DiziPortalApp.showMessage('Yedek geri yüklenirken hata oluştu', 'error');
            return false;
        }
    },

    // List available backups
    getAvailableBackups() {
        const backups = [];
        
        try {
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key && key.startsWith(this.config.storagePrefix + 'backup_')) {
                    const timestamp = parseInt(key.split('_')[2]);
                    backups.push({
                        timestamp: timestamp,
                        date: new Date(timestamp),
                        key: key
                    });
                }
            }

            // Sort by timestamp (newest first)
            backups.sort((a, b) => b.timestamp - a.timestamp);
        } catch (error) {
            console.error('❌ Failed to list backups:', error);
        }

        return backups;
    },

    // Export data
    exportData() {
        try {
            const data = {
                matches: DiziPortalApp.state.matches,
                channels: DiziPortalApp.state.channels,
                settings: DiziPortalApp.state.settings,
                exportDate: new Date().toISOString(),
                version: '1.0.0',
                source: 'DG SPORTS - DiziPortal.Com'
            };

            const jsonData = JSON.stringify(data, null, 2);
            const blob = new Blob([jsonData], { type: 'application/json' });
            const url = URL.createObjectURL(blob);

            const a = document.createElement('a');
            a.href = url;
            a.download = `dg-sports-backup-${new Date().toISOString().split('T')[0]}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);

            DiziPortalApp.showMessage('Veriler dışa aktarıldı', 'success');
        } catch (error) {
            console.error('❌ Failed to export data:', error);
            DiziPortalApp.showMessage('Veri dışa aktarımında hata oluştu', 'error');
        }
    },

    // Import data
    importData(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            
            reader.onload = (e) => {
                try {
                    const data = JSON.parse(e.target.result);
                    
                    if (!this.validateDataStructure(data)) {
                        throw new Error('Invalid data structure');
                    }

                    // Create backup before import
                    this.createBackup();

                    // Import data
                    DiziPortalApp.state.matches = data.matches || [];
                    DiziPortalApp.state.channels = data.channels || [];
                    DiziPortalApp.state.settings = { ...DiziPortalApp.state.settings, ...data.settings };

                    // Update UI
                    DiziPortalApp.renderMatches();
                    DiziPortalApp.renderChannels();
                    DiziPortalApp.updateStatistics();
                    DiziPortalApp.updateSocialLinks();

                    // Save imported data
                    this.saveAllData();

                    DiziPortalApp.showMessage('Veriler içe aktarıldı', 'success');
                    resolve(true);
                } catch (error) {
                    console.error('❌ Failed to import data:', error);
                    DiziPortalApp.showMessage('Veri içe aktarımında hata oluştu', 'error');
                    reject(error);
                }
            };

            reader.onerror = () => {
                reject(new Error('File read error'));
            };

            reader.readAsText(file);
        });
    },

    // Compress data using simple compression
    compressData(data) {
        try {
            const jsonString = JSON.stringify(data);
            // Simple compression by removing unnecessary spaces
            return btoa(jsonString);
        } catch (error) {
            console.warn('Compression failed, using uncompressed data');
            return JSON.stringify(data);
        }
    },

    // Decompress data
    decompressData(compressedData) {
        try {
            const jsonString = atob(compressedData);
            return JSON.parse(jsonString);
        } catch (error) {
            // Fallback to direct parsing if not compressed
            return JSON.parse(compressedData);
        }
    },

    // Check data integrity
    checkDataIntegrity() {
        try {
            // Load data if available
            if (this.loadAllData()) {
                console.log('✅ Data integrity check passed');
            } else {
                console.log('ℹ️ No saved data found, using defaults');
            }
        } catch (error) {
            console.error('❌ Data integrity check failed:', error);
            this.handleDataCorruption();
        }
    },

    // Handle data corruption
    handleDataCorruption() {
        console.warn('⚠️ Data corruption detected, attempting recovery...');
        
        // Try to restore from latest backup
        const backups = this.getAvailableBackups();
        if (backups.length > 0) {
            const latestBackup = backups[0];
            if (this.restoreFromBackup(latestBackup.timestamp)) {
                console.log('✅ Data recovered from backup');
                return;
            }
        }

        // Clear corrupted data and use defaults
        this.clearAllData();
        console.log('🔄 Using default data after corruption recovery');
        DiziPortalApp.showMessage('Veri bozulması tespit edildi, varsayılan veriler yüklendi', 'warning');
    },

    // Clear all data
    clearAllData() {
        try {
            // Clear main data
            localStorage.removeItem(this.config.storagePrefix + 'data');
            
            // Clear backups
            const keys = [];
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key && key.startsWith(this.config.storagePrefix)) {
                    keys.push(key);
                }
            }
            
            keys.forEach(key => localStorage.removeItem(key));
            
            console.log('🗑️ All data cleared');
        } catch (error) {
            console.error('❌ Failed to clear data:', error);
        }
    },

    // Handle storage errors
    handleStorageError(error) {
        if (error.name === 'QuotaExceededError') {
            console.warn('⚠️ Storage quota exceeded, cleaning up...');
            this.cleanupStorage();
        } else {
            console.error('Storage error:', error);
        }
    },

    // Cleanup storage
    cleanupStorage() {
        try {
            // Remove old backups more aggressively
            const backupKeys = [];
            
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key && key.startsWith(this.config.storagePrefix + 'backup_')) {
                    backupKeys.push({
                        key: key,
                        timestamp: parseInt(key.split('_')[2])
                    });
                }
            }

            // Sort by timestamp and keep only the 3 most recent
            backupKeys.sort((a, b) => b.timestamp - a.timestamp);
            
            for (let i = 3; i < backupKeys.length; i++) {
                localStorage.removeItem(backupKeys[i].key);
            }

            console.log('🧹 Storage cleanup completed');
            DiziPortalApp.showMessage('Depolama alanı temizlendi', 'info');
        } catch (error) {
            console.error('❌ Failed to cleanup storage:', error);
        }
    },

    // Get storage usage info
    getStorageInfo() {
        if (this.useMemoryStorage) {
            return { used: 0, available: 0, total: 0, percentage: 0 };
        }

        try {
            let totalSize = 0;
            for (let key in localStorage) {
                if (localStorage.hasOwnProperty(key)) {
                    totalSize += localStorage[key].length + key.length;
                }
            }

            // Rough estimate of available storage (5MB typical limit)
            const estimatedLimit = 5 * 1024 * 1024; // 5MB in bytes
            const percentage = (totalSize / estimatedLimit) * 100;

            return {
                used: totalSize,
                available: estimatedLimit - totalSize,
                total: estimatedLimit,
                percentage: Math.min(percentage, 100)
            };
        } catch (error) {
            console.error('Failed to get storage info:', error);
            return { used: 0, available: 0, total: 0, percentage: 0 };
        }
    }
};

// Storage utilities for shared hosting compatibility
const DiziPortalStorage = {
    // Check if running on shared hosting
    isSharedHosting() {
        const hostname = window.location.hostname;
        const sharedHostingIndicators = [
            'cpanel',
            'whm',
            'hosting',
            'shared'
        ];
        
        return sharedHostingIndicators.some(indicator => 
            hostname.toLowerCase().includes(indicator)
        );
    },

    // Optimize for shared hosting
    optimizeForSharedHosting() {
        if (this.isSharedHosting()) {
            console.log('🏢 Shared hosting detected, applying optimizations...');
            
            // Reduce backup frequency
            DiziPortalData.config.backupInterval = 600000; // 10 minutes
            DiziPortalData.config.maxBackups = 5;
            
            // Enable compression
            DiziPortalData.config.compressionEnabled = true;
            
            console.log('✅ Shared hosting optimizations applied');
        }
    },

    // Create .htaccess for asset caching (if supported)
    createHTAccessRules() {
        const htaccessContent = `
# DG SPORTS - DiziPortal.Com Optimizations
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/svg+xml "access plus 1 month"
</IfModule>

<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>

# Error pages
ErrorDocument 404 /index.html
ErrorDocument 403 /index.html
        `.trim();

        return htaccessContent;
    }
};

// Initialize data manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    DiziPortalStorage.optimizeForSharedHosting();
    DiziPortalData.init();
});

// Export for global access
window.DiziPortalData = DiziPortalData;
window.DiziPortalStorage = DiziPortalStorage;