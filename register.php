<?php
require_once 'config.php';
require_once 'functions.php';

// Oturum kontrolü
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF kontrolü
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = "Güvenlik doğrulaması başarısız oldu. Lütfen sayfayı yenileyip tekrar deneyin.";
    } else {
        $username = sanitizeInput($_POST['username']);
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        // Input validasyonu
        if (empty($username) || empty($email) || empty($password)) {
            $error = "Tüm alanları doldurun.";
        } elseif (!validateEmail($email)) {
            $error = "Geçerli bir e-posta adresi girin.";
        } elseif (!validatePassword($password)) {
            $error = "Şifre en az 8 karakter uzunluğunda olmalı ve en az bir büyük harf, bir küçük harf, bir sayı ve bir özel karakter içermelidir.";
        } elseif ($password !== $confirmPassword) {
            $error = "Şifreler eşleşmiyor.";
        } else {
            $result = registerUser($username, $email, $password);
            if ($result === true) {
                $success = "Kayıt başarılı! Şimdi giriş yapabilirsiniz.";
                // 3 saniye sonra giriş sayfasına yönlendir
                header("refresh:3;url=login.php");
            } else {
                $error = $result;
            }
        }
    }
}

// CSRF token oluştur
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Film Öner</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Film Öner</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Ana Sayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="movies.php">Filmler</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tv_shows.php">Diziler</a>
                    </li>
                </ul>
                <form class="d-flex" action="search.php" method="get">
                    <input class="form-control me-2" type="search" placeholder="Ara" name="query" aria-label="Search">
                    <button class="btn btn-outline-success" type="submit">Ara</button>
                </form>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Giriş Yap</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="form-container">
            <h2 class="text-center mb-4">Kayıt Ol</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="mb-3 form-floating">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" class="form-control" id="username" name="username" required
                           placeholder="Kullanıcı Adı" minlength="3" maxlength="20"
                           pattern="[a-zA-Z0-9_-]+" autocomplete="username">
                    <label for="username">Kullanıcı Adı</label>
                    <div class="invalid-feedback">
                        Kullanıcı adı 3-20 karakter arasında olmalı ve sadece harf, rakam, alt çizgi ve tire içerebilir.
                    </div>
                </div>
                
                <div class="mb-3 form-floating">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" class="form-control" id="email" name="email" required
                           placeholder="E-posta" autocomplete="email">
                    <label for="email">E-posta</label>
                    <div class="invalid-feedback">
                        Geçerli bir e-posta adresi girin.
                    </div>
                </div>
                
                <div class="mb-3 form-floating">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" class="form-control" id="password" name="password" required
                           placeholder="Şifre" minlength="8" autocomplete="new-password">
                    <label for="password">Şifre</label>
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('password')"></i>
                    <div class="password-strength"></div>
                    <div class="invalid-feedback">
                        Şifre en az 8 karakter uzunluğunda olmalı ve en az bir büyük harf, bir küçük harf, bir sayı ve bir özel karakter içermelidir.
                    </div>
                </div>
                
                <div class="mb-3 form-floating">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                           placeholder="Şifre Tekrar" minlength="8" autocomplete="new-password">
                    <label for="confirm_password">Şifre Tekrar</label>
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_password')"></i>
                    <div class="invalid-feedback">
                        Şifreler eşleşmiyor.
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                    <label class="form-check-label" for="terms">
                        <a href="terms.php" class="text-primary">Kullanım Koşulları</a>'nı okudum ve kabul ediyorum.
                    </label>
                    <div class="invalid-feedback">
                        Devam etmek için kullanım koşullarını kabul etmelisiniz.
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-user-plus me-2"></i>Kayıt Ol
                </button>
            </form>
            
            <div class="text-center mt-3">
                <p>Zaten hesabınız var mı? <a href="login.php" class="text-primary">Giriş Yap</a></p>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-4">Film Öner</h5>
                    <p class="mb-4">En iyi film ve dizi önerileri için doğru adres.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-4">&copy; <?php echo date('Y'); ?> Film Öner. Tüm hakları saklıdır.</p>
                    <div class="footer-links">
                        <a href="about.php" class="text-light me-3">Hakkımızda</a>
                        <a href="contact.php" class="text-light me-3">İletişim</a>
                        <a href="privacy.php" class="text-light">Gizlilik Politikası</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Password toggle
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = passwordInput.nextElementSibling;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Password strength meter
        const passwordInput = document.getElementById('password');
        const strengthMeter = document.querySelector('.password-strength');
        const confirmPasswordInput = document.getElementById('confirm_password');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Length check
            if (password.length >= 8) strength += 1;
            
            // Contains number
            if (/\d/.test(password)) strength += 1;
            
            // Contains lowercase
            if (/[a-z]/.test(password)) strength += 1;
            
            // Contains uppercase
            if (/[A-Z]/.test(password)) strength += 1;
            
            // Contains special character
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            // Update strength meter
            strengthMeter.className = 'password-strength';
            if (strength <= 2) {
                strengthMeter.classList.add('weak');
            } else if (strength === 3) {
                strengthMeter.classList.add('medium');
            } else if (strength === 4) {
                strengthMeter.classList.add('strong');
            } else {
                strengthMeter.classList.add('very-strong');
            }
        });

        // Password confirmation check
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value !== passwordInput.value) {
                this.setCustomValidity('Şifreler eşleşmiyor');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html> 