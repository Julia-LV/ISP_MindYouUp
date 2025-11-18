<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Filipa Pancada Fonseca – Mind You Up</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    :root {
      --primary: #0c5c46;
      --muted: #4f5a5a;
      --bg-soft: #fffaf1;
    }
    body {
      margin: 0;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background: #fff7ea;
    }
    .container {
      max-width: 960px;
      margin: 0 auto;
      padding: 0 16px 32px;
    }

    .app-header {
      position: sticky;
      top: 0;
      z-index: 50;
      background: #0f684f;
      color: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.16);
    }
    .app-header-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 10px 0;
    }
    .app-header h1 {
      margin: 0;
      font-size: 1.2rem;
      letter-spacing: 0.03em;
    }
    .top-nav {
      display: flex;
      gap: 12px;
      align-items: center;
      flex-wrap: wrap;
    }
    .top-nav a {
      color: #fff;
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 500;
    }
    .top-nav a:hover { text-decoration: underline; }

    .top-cta {
      padding: 6px 12px;
      border-radius: 999px;
      border: 1px solid rgba(255,255,255,0.85);
      font-size: 0.85rem;
      font-weight: 600;
      background: rgba(255,255,255,0.12);
      color: #fff;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .profile-hero {
      margin-top: 28px;
      display: grid;
      gap: 24px;
      align-items: center;
    }
    @media (min-width: 768px) {
      .profile-hero {
        grid-template-columns: auto minmax(0, 1fr);
      }
    }
    .profile-photo-wrap {
      width: 220px;
      height: 220px;
      border-radius: 50%;
      overflow: hidden;
      border: 7px solid #ffd7b3;
      box-shadow: 0 12px 32px rgba(0,0,0,0.18);
    }
    .profile-photo-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    .profile-name {
      font-size: 2rem;
      margin: 0 0 4px;
      color: var(--primary);
    }
    .profile-title {
      font-size: 1rem;
      color: var(--muted);
      margin-bottom: 12px;
    }
    .profile-tags {
      font-size: 0.9rem;
      color: var(--muted);
      margin-bottom: 8px;
    }
    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.9rem;
      text-decoration: none;
      color: #0f684f;
      margin-top: 6px;
    }

    .profile-section {
      margin-top: 32px;
      background: #ffffff;
      border-radius: 18px;
      padding: 20px 20px 18px;
      border: 1px solid #f0d9d2;
      box-shadow: 0 6px 18px rgba(0,0,0,0.05);
    }
    .profile-section h2 {
      margin: 0 0 8px;
      font-size: 1.35rem;
      color: var(--primary);
      border-bottom: 2px solid #ffe1c1;
      padding-bottom: 4px;
    }
    .profile-section h3 {
      margin: 16px 0 4px;
      font-size: 1rem;
      color: var(--primary);
    }
    .profile-section p {
      font-size: 0.95rem;
      color: var(--muted);
      margin: 4px 0 6px;
      line-height: 1.5;
    }

    footer {
      background: #0f684f;
      color: #f7fff9;
      padding: 12px 0 18px;
      margin-top: 28px;
      font-size: 0.85rem;
    }
  </style>
</head>
<body>
<header class="app-header">
  <div class="container app-header-inner">
    <h1><a href="index.php" style="color:#fff;text-decoration:none;">Mind You Up</a></h1>
    <nav class="top-nav">
      <a href="index.php#team">Team</a>
      <a href="index.php#tictracker">TicTracker</a>
      <a href="index.php#articles">Articles</a>
      <a href="about.php">About</a>
      <a href="https://your-app-url.example" class="top-cta" target="_blank" rel="noopener">
        <span>•</span> Sign in / Sign up
      </a>
    </nav>
  </div>
</header>

<main class="container">
  <!-- HERO -->
  <section class="profile-hero">
    <div class="profile-photo-wrap">
      <img src="images/filipa.jpg" alt="Portrait of Filipa Pancada Fonseca">
    </div>
    <div>
      <p class="small-text" style="text-transform:uppercase; letter-spacing:0.12em; margin:0 0 6px; color:#b47d4f;">
        Clinical &amp; health psychologist
      </p>
      <h1 class="profile-name">Filipa Pancada Fonseca</h1>
      <div class="profile-title">
        Clinical and Health Psychologist · CBT psychotherapist · Professor &amp; researcher
      </div>
      <div class="profile-tags">
        Co-founder of Belong Institute · Focus on health psychology &amp; technology’s impact on well-being
      </div>
      <a href="index.php#team" class="back-link">← Back to team</a>
    </div>
  </section>

  <!-- CONTENT -->
  <section class="profile-section">
    <h2>Professional background</h2>
    <p>
      Filipa is a CBT psychotherapist, professor, and researcher passionate about health psychology
      and the impact of technology on well-being. For 20 years, she worked in a hospital setting,
      gaining extensive experience in clinical intervention and developing a deep understanding of
      health and mental health challenges.
    </p>
    <p>
      Her curiosity led her to pursue a PhD while co-founding the Belong Institute, an innovative
      project dedicated to psychology and mental health across the lifespan. Through practice,
      teaching, and research, she explores how the digital era shapes our perception of health
      and influences emotional responses, studying cyberchondria as a psychological construct.
    </p>

    <h3>Research &amp; interests</h3>
    <p>
      Filipa enjoys transforming personal clinical experience, PhD data, and scientific evidence
      into practical strategies – whether in therapy, in the classroom, or in interdisciplinary
      collaborations with medicine and technology.
    </p>
    <p>
      She believes psychology must evolve with the modern world, and that understanding our
      relationship with technology is essential for building a healthier future.
    </p>
  </section>
</main>

<footer>
  <div class="container" style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;">
    <span>Mind You Up – TicTracker Portal</span>
    <span>Filipa Pancada Fonseca · Clinical &amp; Health Psychologist</span>
  </div>
</footer>
</body>
</html>
