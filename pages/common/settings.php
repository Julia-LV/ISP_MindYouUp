<?php
/*
 // USER INFO (COMMENTED OUT)
 // Example: session + profile lookup. All lines are commented out.
 // if (session_status() === PHP_SESSION_NONE) session_start();
 // require_once __DIR__ . '/../../config.php';
 // $current_user_id = $_SESSION['user_id'] ?? null;
 // $CURRENT_USER = null;
 // if ($current_user_id) {
 //     $sql = "SELECT User_ID, First_Name, Last_Name, `E-mail`, `Role` FROM user_profile WHERE User_ID = ? LIMIT 1";
 //     if ($stmt = $conn->prepare($sql)) {
 //         $stmt->bind_param('i', $current_user_id);
 //         $stmt->execute();
 //         $stmt->bind_result($u_id,$u_first,$u_last,$u_email,$u_role);
 //         if ($stmt->fetch()) {
 //             $CURRENT_USER = ['id'=> (int)$u_id, 'first'=>$u_first, 'last'=>$u_last, 'email'=>$u_email, 'role'=>$u_role];
 //         }
 //         $stmt->close();
 //     }
 // }
*/
?>
<!DOCTYPE html>
<html>
<head>
    <title>Settings</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <!-- TailwindCSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Brand colors requested by user */
        :root {
            /* Background: Creme */
            --bg-creme: #FFF7E1; /* RGB: 255,247,225 */

            /* Accents */
            --accent-orange: #F26647; /* RGB: 242,102,71 */
            --accent-green:  #005949; /* RGB: 0,89,73 */

            /* Utility */
            --text-dark: #0b2a24; /* a very dark green for good contrast */
            --muted: rgba(11,42,36,0.65);
            --radius: 10px;
        }

        /* Page layout */
        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
            min-height: 100vh;
            background-color: var(--bg-creme);
            color: var(--text-dark);
            font-family: Arial, Helvetica, sans-serif;
        }

        h1 {
            margin: 0.5rem 0 1rem 0;
            color: var(--accent-green);
            letter-spacing: 0.2px;
        }

        .settings-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            width: 100%;
            max-width: 420px;
            margin-top: 1rem;
        }

        /* Anchor styled as button for better semantics and simple markup */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            /* fixed height to make all buttons equal */
            min-height: 56px;
            height: 56px;
            padding: 0 1rem; /* horizontal padding only; height controls vertical size */
            font-size: 1rem;
            width: 100%;
            text-decoration: none;
            color: white;
            /* make all buttons use the orange accent */
            background: linear-gradient(180deg, var(--accent-orange), #e6553e);
            /* Use a transparent border so outline variant (2px) doesn't change box size */
            border: 2px solid transparent;
            border-radius: var(--radius);
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            cursor: pointer;
            transition: transform .08s ease, box-shadow .08s ease;
            box-sizing: border-box; /* include border in height calculations */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .btn:active { transform: translateY(1px); }
        .btn:hover { box-shadow: 0 6px 16px rgba(0,0,0,0.12); }

        /* Secondary (green) variant */
        .btn.green {
            /* keep the class but make it orange as well so every button is orange */
            background: linear-gradient(180deg, var(--accent-orange), #e6553e);
        }

        /* Outline variant: make it a green filled button so all buttons share the same green background */
        .btn.outline {
            /* outline variant now uses orange filled background so it matches others */
            background: linear-gradient(180deg, var(--accent-orange), #e6553e);
            color: white;
            border-color: transparent;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }

        /* Small helper text */
        .note {
            color: var(--muted);
            font-size: 0.95rem;
            text-align: center;
        }

        @media (max-width: 480px) {
            .settings-container { max-width: 100%; }
        }
    </style>
</head>
<body>
    <?php include '../../includes/navbar.php'; ?>

    <h1>Settings</h1>
    <div class="settings-container">
        <a class="btn" href="notifications.php">Notifications</a>
        <a class="btn" href="privacy.php">Privacy</a>
        <a class="btn green" href="language.php">Language</a>
        <a class="btn outline" href="about.php">About the app</a>
    </div>
</body>
</html>
