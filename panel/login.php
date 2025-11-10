<?php
// login.php - Panel Giriş Sayfası

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

// Oturum güvenliği
if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }

// Geriye dönük uyum: is_logged_in yoksa tanımla
if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool {
        return !empty($_SESSION['user']['id']);
    }
}


// Zaten giriş yapmışsa dashboard'a yönlendir
if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
$timeout = isset($_GET['timeout']) ? true : false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullanici_adi = sanitize_input($_POST['kullanici_adi'] ?? '');
    $sifre = $_POST['sifre'] ?? '';
    
    if (!empty($kullanici_adi) && !empty($sifre)) {
        // auth.php içindeki login_user(kullanici_adi, sifre) beklenir
        $result = login_user($kullanici_adi, $sifre);
        
        if (!empty($result['success'])) {
            redirect('dashboard.php');
        } else {
            $error = $result['message'] ?? 'Giriş başarısız.';
        }
    } else {
        $error = 'Lütfen tüm alanları doldurun.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Girişi - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            background: #ffffff;
            padding: 50px 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 420px;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo i {
            font-size: 60px;
            color: #667eea;
            margin-bottom: 15px;
        }

        .logo h1 {
            font-size: 26px;
            color: #2c3e50;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .logo p {
            font-size: 14px;
            color: #7f8c8d;
        }

        .error-message, .timeout-message {
            background: #fee;
            color: #c33;
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            border-left: 4px solid #c33;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .timeout-message {
            background: #fef3cd;
            color: #856404;
            border-left-color: #ffc107;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 16px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 14px 14px 45px;
            font-size: 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            transition: all 0.3s;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .back-link {
            text-align: center;
            margin-top: 24px;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.3s;
        }

        .back-link a:hover {
            color: #5a67d8;
        }

        .info-box {
            margin-top: 24px;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 13px;
            color: #6c757d;
            text-align: center;
            border: 1px dashed #dee2e6;
        }

        .info-box strong {
            color: #495057;
            display: block;
            margin-bottom: 8px;
        }

        .info-box .credentials {
            font-family: 'Courier New', monospace;
            background: white;
            padding: 8px;
            border-radius: 4px;
            margin-top: 8px;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 40px 30px;
            }

            .logo h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <i class="fas fa-robot"></i>
            <h1>Yönetim Paneli</h1>
            <p>Erciyes Üniversitesi Yapay Zeka Kulübü</p>
        </div>

        <?php if ($timeout): ?>
            <div class="timeout-message">
                <i class="fas fa-clock"></i>
                Oturumunuz zaman aşımına uğradı. Lütfen tekrar giriş yapın.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="kullanici_adi">Kullanıcı Adı</label>
                <div class="input-wrapper">
                    <i class="fas fa-user"></i>
                    <input 
                        type="text" 
                        id="kullanici_adi" 
                        name="kullanici_adi" 
                        placeholder="Kullanıcı adınızı girin"
                        required
                        autofocus
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="sifre">Şifre</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input 
                        type="password" 
                        id="sifre" 
                        name="sifre" 
                        placeholder="Şifrenizi girin"
                        required
                    >
                </div>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-sign-in-alt"></i> Giriş Yap
            </button>
        </form>

        <div class="back-link">
          <a href="<?= SITE_URL ?>">
    <i class="fas fa-arrow-left"></i> Ana Sayfaya Dön
</a>

        </div>

        <div class="info-box">
            <strong>🔐 Test Hesapları</strong>
            <div class="credentials">
                <strong>Admin:</strong> admin / admin123<br>
                <strong>Yönetici:</strong> yonetici / admin123<br>
                <strong>Üye:</strong> uye / admin123
            </div>
            <small style="color: #dc3545; margin-top: 8px; display: block;">
                ⚠️ Üretimde mutlaka değiştirin!
            </small>
        </div>
    </div>
</body>
</html>
