<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Hatası - DG SPORTS</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 40px; text-align: center; }
        .error-container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .error-icon { font-size: 4em; color: #dc3545; margin-bottom: 20px; }
        h1 { color: #dc3545; margin-bottom: 10px; }
        p { color: #6c757d; margin-bottom: 20px; }
        .btn { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; }
        .btn:hover { background: #0056b3; }
        .debug-info { background: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 5px; text-align: left; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1>Sistem Hatası</h1>
        <p>Üzgünüz, şu anda bir teknik sorun yaşanıyor.</p>
        <p>Kurulum tamamlanmadıysa lütfen kurulum scriptini çalıştırın.</p>
        
        <a href="install.php" class="btn">Kuruluma Git</a>
        <a href="index.php" class="btn">Ana Sayfayı Dene</a>
        
        <?php if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG']): ?>
        <div class="debug-info">
            <strong>Debug Bilgileri:</strong><br>
            Time: <?= date('Y-m-d H:i:s') ?><br>
            PHP Version: <?= PHP_VERSION ?><br>
            Memory Usage: <?= memory_get_peak_usage(true) / 1024 / 1024 ?>MB<br>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>