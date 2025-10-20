<!DOCTYPE html>
<html>
<head>
    <title>Settings</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
            min-height: 100vh;
            background: #f9f9f9;
            margin: 0;
        }
        .settings-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            width: 100%;
            max-width: 300px;
            margin-top: 2rem;
        }
        .settings-container button {
            padding: 1rem;
            font-size: 1rem;
            width: 100%;
        }
    </style>
</head>
<body>
    <h1>Settings</h1>
    <div class="settings-container">
        <a href="notifications.php"><button>Notifications</button></a>
        <a href="privacy.php"><button>Privacy</button></a>
        <a href="language.php"><button>Language</button></a>
        <a href="about.php"><button>About the app</button></a>
    </div>
</body>
</html>

