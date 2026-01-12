<?php
// Language selector page - Uses Google Translate API for automatic translation
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Get current language from session/cookie
$currentLang = $_SESSION['site_lang'] ?? $_COOKIE['site_lang'] ?? 'en';

// Process language change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['lang'])) {
    $choice = in_array($_POST['lang'], ['en', 'pt', 'es', 'fr']) ? $_POST['lang'] : 'en';
    setcookie('site_lang', $choice, time() + 60 * 60 * 24 * 365, '/');
    $_SESSION['site_lang'] = $choice;
    $currentLang = $choice;
    
    // Redirect to apply language change
    header("Location: language.php");
    exit;
}

$page_title = 'Language';
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title; ?></title>
    <!-- TailwindCSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="../../CSS/settings.css" rel="stylesheet">
    <style>
        .language-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .lang-btn {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            background: #fff;
            border: 2px solid #ddd;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: left;
        }
        
        .lang-btn:hover {
            border-color: var(--accent-green);
            background: #f9f9f9;
        }
        
        .lang-btn.active {
            border-color: var(--accent-green);
            background: rgba(0, 89, 73, 0.08);
        }
        
        .lang-btn.active .check-icon {
            display: flex;
        }
        
        .lang-flag {
            font-size: 2rem;
            line-height: 1;
        }
        
        .lang-info {
            flex: 1;
        }
        
        .lang-name {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--text-dark);
        }
        
        .lang-native {
            font-size: 0.9rem;
            color: var(--muted);
        }
        
        .check-icon {
            display: none;
            width: 24px;
            height: 24px;
            background: var(--accent-green);
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            color: white;
        }
        
        .translation-note {
            text-align: center;
            margin-top: 30px;
            padding: 16px;
            background: rgba(74, 144, 217, 0.1);
            border-radius: 8px;
            color: var(--text-dark);
            font-size: 0.9rem;
        }
        
        .translation-note svg {
            vertical-align: middle;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <?php include_once __DIR__ . '/../../includes/navbar.php'; ?>
    <?php include __DIR__ . '/../../components/header_component.php'; ?>

    <div class="main-content">
        <div class="settings-wrapper">
            <div class="settings-header">
                <h1>Language</h1>
            </div>

            <form method="post" id="langForm">
                <div class="language-options">
                    <button type="submit" name="lang" value="en" class="lang-btn <?php echo $currentLang === 'en' ? 'active' : ''; ?>">
                        <span class="lang-flag">🇬🇧</span>
                        <div class="lang-info">
                            <div class="lang-name">English</div>
                            <div class="lang-native">English</div>
                        </div>
                        <div class="check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                    </button>
                    
                    <button type="submit" name="lang" value="pt" class="lang-btn <?php echo $currentLang === 'pt' ? 'active' : ''; ?>">
                        <span class="lang-flag">🇵🇹</span>
                        <div class="lang-info">
                            <div class="lang-name">Portuguese</div>
                            <div class="lang-native">Português</div>
                        </div>
                        <div class="check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                    </button>
                    
                    <button type="submit" name="lang" value="es" class="lang-btn <?php echo $currentLang === 'es' ? 'active' : ''; ?>">
                        <span class="lang-flag">🇪🇸</span>
                        <div class="lang-info">
                            <div class="lang-name">Spanish</div>
                            <div class="lang-native">Español</div>
                        </div>
                        <div class="check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                    </button>
                    
                    <button type="submit" name="lang" value="fr" class="lang-btn <?php echo $currentLang === 'fr' ? 'active' : ''; ?>">
                        <span class="lang-flag">🇫🇷</span>
                        <div class="lang-info">
                            <div class="lang-name">French</div>
                            <div class="lang-native">Français</div>
                        </div>
                        <div class="check-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                    </button>
                </div>
            </form>
            
            <div class="translation-note">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                The website will be automatically translated using Google Translate.
            </div>
        </div>
    </div>
    
    <script>
    // After language selection, trigger translation
    document.addEventListener('DOMContentLoaded', function() {
        const savedLang = '<?php echo $currentLang; ?>';
        if (savedLang && savedLang !== 'en' && typeof translatePage === 'function') {
            setTimeout(() => translatePage(savedLang), 1500);
        }
    });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>
