<?php
/**
 * DG SPORTS - Cache Class
 * Developer: DiziPortal.Com
 * File-based caching system
 */

if (!defined('DG_SPORTS_APP')) {
    die('Direct access forbidden');
}

class Cache {
    private static $instance = null;
    private $cachePath;
    private $defaultTtl;
    
    private function __construct() {
        $this->cachePath = ROOT_PATH . DS . 'cache';
        $this->defaultTtl = config('app.cache_ttl', 3600);
        
        // Create cache directory if it doesn't exist
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
        
        // Create .htaccess to protect cache directory
        $htaccessPath = $this->cachePath . DS . '.htaccess';
        if (!file_exists($htaccessPath)) {
            file_put_contents($htaccessPath, "Deny from all\n");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Generate cache key
     */
    private function getCacheKey($key) {
        return md5($key);
    }
    
    /**
     * Get cache file path
     */
    private function getCacheFilePath($key) {
        $cacheKey = $this->getCacheKey($key);
        $subDir = substr($cacheKey, 0, 2);
        
        $subDirPath = $this->cachePath . DS . $subDir;
        if (!is_dir($subDirPath)) {
            mkdir($subDirPath, 0755, true);
        }
        
        return $subDirPath . DS . $cacheKey . '.cache';
    }
    
    /**
     * Set cache value
     */
    public static function set($key, $value, $ttl = null) {
        $instance = self::getInstance();
        $ttl = $ttl ?? $instance->defaultTtl;
        $expiry = time() + $ttl;
        
        $cacheData = [
            'expiry' => $expiry,
            'value' => $value,
            'created_at' => time()
        ];
        
        $filePath = $instance->getCacheFilePath($key);
        $success = file_put_contents($filePath, serialize($cacheData), LOCK_EX) !== false;
        
        if ($success) {
            log_activity("Cache set: {$key}", 'debug', ['ttl' => $ttl]);
        }
        
        return $success;
    }
    
    /**
     * Get cache value
     */
    public static function get($key, $default = null) {
        $instance = self::getInstance();
        $filePath = $instance->getCacheFilePath($key);
        
        if (!file_exists($filePath)) {
            return $default;
        }
        
        $content = file_get_contents($filePath);
        if ($content === false) {
            return $default;
        }
        
        $cacheData = unserialize($content);
        if ($cacheData === false) {
            // Corrupted cache file, delete it
            unlink($filePath);
            return $default;
        }
        
        // Check if cache has expired
        if (time() > $cacheData['expiry']) {
            unlink($filePath);
            log_activity("Cache expired: {$key}", 'debug');
            return $default;
        }
        
        log_activity("Cache hit: {$key}", 'debug');
        return $cacheData['value'];
    }
    
    /**
     * Check if cache exists and is valid
     */
    public static function has($key) {
        $instance = self::getInstance();
        $filePath = $instance->getCacheFilePath($key);
        
        if (!file_exists($filePath)) {
            return false;
        }
        
        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }
        
        $cacheData = unserialize($content);
        if ($cacheData === false) {
            unlink($filePath);
            return false;
        }
        
        if (time() > $cacheData['expiry']) {
            unlink($filePath);
            return false;
        }
        
        return true;
    }
    
    /**
     * Delete cache entry
     */
    public static function delete($key) {
        $instance = self::getInstance();
        $filePath = $instance->getCacheFilePath($key);
        
        if (file_exists($filePath)) {
            $success = unlink($filePath);
            if ($success) {
                log_activity("Cache deleted: {$key}", 'debug');
            }
            return $success;
        }
        
        return true;
    }
    
    /**
     * Clear cache by pattern
     */
    public static function deletePattern($pattern) {
        $instance = self::getInstance();
        $deleted = 0;
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($instance->cachePath)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                $content = file_get_contents($file->getPathname());
                if ($content !== false) {
                    $cacheData = unserialize($content);
                    if ($cacheData !== false) {
                        // This is a simplified pattern matching
                        // In production, you might want more sophisticated pattern matching
                        if (strpos($file->getBasename('.cache'), $instance->getCacheKey($pattern)) === 0) {
                            unlink($file->getPathname());
                            $deleted++;
                        }
                    }
                }
            }
        }
        
        log_activity("Cache pattern deleted: {$pattern}", 'debug', ['deleted_count' => $deleted]);
        return $deleted;
    }
    
    /**
     * Clear all cache
     */
    public static function clear() {
        $instance = self::getInstance();
        $deleted = 0;
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($instance->cachePath)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                unlink($file->getPathname());
                $deleted++;
            }
        }
        
        log_activity("All cache cleared", 'info', ['deleted_count' => $deleted]);
        return $deleted;
    }
    
    /**
     * Get or set cache value with callback
     */
    public static function remember($key, $callback, $ttl = null) {
        $value = self::get($key);
        
        if ($value === null) {
            $value = $callback();
            self::set($key, $value, $ttl);
        }
        
        return $value;
    }
    
    /**
     * Increment cache value
     */
    public static function increment($key, $value = 1, $ttl = null) {
        $current = self::get($key, 0);
        $new = $current + $value;
        self::set($key, $new, $ttl);
        return $new;
    }
    
    /**
     * Decrement cache value
     */
    public static function decrement($key, $value = 1, $ttl = null) {
        $current = self::get($key, 0);
        $new = max(0, $current - $value);
        self::set($key, $new, $ttl);
        return $new;
    }
    
    /**
     * Get cache statistics
     */
    public static function getStats() {
        $instance = self::getInstance();
        $totalFiles = 0;
        $totalSize = 0;
        $expiredFiles = 0;
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($instance->cachePath)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                $totalFiles++;
                $totalSize += $file->getSize();
                
                $content = file_get_contents($file->getPathname());
                if ($content !== false) {
                    $cacheData = unserialize($content);
                    if ($cacheData !== false && time() > $cacheData['expiry']) {
                        $expiredFiles++;
                    }
                }
            }
        }
        
        return [
            'total_files' => $totalFiles,
            'total_size' => $totalSize,
            'total_size_formatted' => format_bytes($totalSize),
            'expired_files' => $expiredFiles,
            'cache_path' => $instance->cachePath
        ];
    }
    
    /**
     * Clean expired cache entries
     */
    public static function cleanExpired() {
        $instance = self::getInstance();
        $cleaned = 0;
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($instance->cachePath)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'cache') {
                $content = file_get_contents($file->getPathname());
                if ($content !== false) {
                    $cacheData = unserialize($content);
                    if ($cacheData !== false && time() > $cacheData['expiry']) {
                        unlink($file->getPathname());
                        $cleaned++;
                    }
                }
            }
        }
        
        log_activity("Expired cache cleaned", 'info', ['cleaned_count' => $cleaned]);
        return $cleaned;
    }
    
    /**
     * Cache tags system (simple implementation)
     */
    public static function tags($tags) {
        return new CacheTagged($tags);
    }
}

/**
 * Tagged cache implementation
 */
class CacheTagged {
    private $tags;
    
    public function __construct($tags) {
        $this->tags = is_array($tags) ? $tags : [$tags];
    }
    
    public function set($key, $value, $ttl = null) {
        // Store tagged cache
        $success = Cache::set($key, $value, $ttl);
        
        if ($success) {
            // Store tag references
            foreach ($this->tags as $tag) {
                $tagKey = "tag:{$tag}";
                $taggedKeys = Cache::get($tagKey, []);
                $taggedKeys[] = $key;
                Cache::set($tagKey, array_unique($taggedKeys), $ttl * 2); // Tags live longer
            }
        }
        
        return $success;
    }
    
    public function get($key, $default = null) {
        return Cache::get($key, $default);
    }
    
    public function flush() {
        $deleted = 0;
        
        foreach ($this->tags as $tag) {
            $tagKey = "tag:{$tag}";
            $taggedKeys = Cache::get($tagKey, []);
            
            foreach ($taggedKeys as $key) {
                Cache::delete($key);
                $deleted++;
            }
            
            Cache::delete($tagKey);
        }
        
        return $deleted;
    }
}