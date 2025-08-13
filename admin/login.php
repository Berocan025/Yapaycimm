<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DG SPORTS Admin Giriş - DiziPortal.Com</title>
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Admin Styles -->
    <link rel="stylesheet" href="admin-dashboard.css">
    
    <style>
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0f0f0f 100%);
            padding: 2rem;
        }
        
        .login-card {
            background: rgba(26, 26, 26, 0.95);
            border: 1px solid rgba(220, 38, 38, 0.2);
            border-radius: 16px;
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 40px rgba(220, 38, 38, 0.1);
            backdrop-filter: blur(20px);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            border-radius: 50%;
            margin-bottom: 1rem;
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.3);
        }
        
        .login-logo i {
            font-size: 2rem;
            color: white;
        }
        
        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: #9ca3af;
            font-size: 0.95rem;
        }
        
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #d1d5db;
            font-weight: 500;
        }
        
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            background: rgba(31, 41, 55, 0.5);
            border: 1px solid rgba(107, 114, 128, 0.3);
            border-radius: 8px;
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #dc2626;
            background: rgba(31, 41, 55, 0.7);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
        
        .form-input::placeholder {
            color: #6b7280;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 1.25rem;
            height: 1.25rem;
            accent-color: #dc2626;
        }
        
        .checkbox-group label {
            color: #d1d5db;
            font-size: 0.9rem;
            cursor: pointer;
        }
        
        .login-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.3);
        }
        
        .login-btn:active {
            transform: translateY(0);
        }
        
        .login-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(107, 114, 128, 0.2);
        }
        
        .back-to-site {
            color: #9ca3af;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        
        .back-to-site:hover {
            color: #dc2626;
        }
        
        @media (max-width: 480px) {
            .login-card {
                padding: 2rem;
            }
            
            .login-title {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body class="admin-body">
    <div class="login-page">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-futbol"></i>
                </div>
                <h1 class="login-title">DG SPORTS</h1>
                <p class="login-subtitle">Admin Panel - DiziPortal.Com</p>
            </div>
            
            <?php if (isset($loginError)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?= e($loginError) ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                
                <div class="form-group">
                    <label for="username" class="form-label">Kullanıcı Adı</label>
                    <input type="text" 
                           name="username" 
                           id="username" 
                           class="form-input" 
                           placeholder="Kullanıcı adınızı girin"
                           value="<?= e($_POST['username'] ?? '') ?>"
                           required 
                           autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Şifre</label>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           class="form-input" 
                           placeholder="Şifrenizi girin"
                           required>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" 
                           name="remember" 
                           id="remember" 
                           <?= !empty($_POST['remember']) ? 'checked' : '' ?>>
                    <label for="remember">Beni hatırla (7 gün)</label>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Giriş Yap</span>
                </button>
            </form>
            
            <div class="login-footer">
                <a href="../" class="back-to-site">
                    <i class="fas fa-arrow-left"></i>
                    Ana Siteye Dön
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-focus on username field
        document.addEventListener('DOMContentLoaded', function() {
            const usernameField = document.getElementById('username');
            if (usernameField) {
                usernameField.focus();
            }
            
            // Add loading state to form submission
            const form = document.querySelector('form');
            const submitBtn = document.querySelector('.login-btn');
            
            if (form && submitBtn) {
                form.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Giriş yapılıyor...</span>';
                });
            }
        });
        
        // Clear error messages after 5 seconds
        const errorMessage = document.querySelector('.error-message');
        if (errorMessage) {
            setTimeout(() => {
                errorMessage.style.opacity = '0';
                setTimeout(() => {
                    errorMessage.remove();
                }, 300);
            }, 5000);
        }
    </script>
</body>
</html>