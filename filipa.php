<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Filipa Pancada Fonseca – TicTracker by Mind You Up</title>
  <link rel="stylesheet" href="web_portal.css" />
  <style>
    body {
      background: #fff7ea;
    }
    .profile-hero {
  padding: 48px 0 56px;
  background: #fff7ea;  /* same as body */
}

    
    .profile-shell {
      max-width: 1120px;
      margin: 0 auto;
      padding: 0 16px;
    }
    .profile-card {
      background: #fffdf8;
      border-radius: 26px;
      border: 1px solid #f0d9d2;
      box-shadow: 0 18px 50px rgba(0,0,0,0.15);
      display: grid;
      gap: 28px;
      padding: 26px 30px 26px;
      align-items: flex-start;
    }
    @media (min-width: 960px) {
      .profile-card {
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.9fr);
      }
    }
    .profile-photo-wrap {
      display: flex;
      justify-content: center;
    }
    .profile-photo-inner {
      width: 280px;
      max-width: 100%;
      aspect-ratio: 3/4;
      border-radius: 30px;
      overflow: hidden;
      box-shadow: 0 20px 45px rgba(0,0,0,0.28);
      background: #000;
    }
    .profile-photo-inner img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    .profile-content {
      color: #384341;
    }
    .profile-name {
      font-size: 2.1rem;
      line-height: 1.1;
      margin: 0 0 6px;
      color: #0a4936;
    }
    .profile-tagline {
      font-size: 0.98rem;
      color: #5b6664;
      margin: 0 0 18px;
      max-width: 640px;
    }
    .profile-chip-row {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 10px;
      margin-bottom: 6px;
    }
    .profile-chip {
      padding: 4px 10px;
      border-radius: 999px;
      background: #e9f0ff;
      color: #2643a4;
      font-size: 0.78rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }
    .profile-section-title {
      margin-top: 18px;
      margin-bottom: 6px;
      font-size: 1.1rem;
      font-weight: 700;
      color: #0f684f;
    }
    .profile-paragraph {
      font-size: 0.95rem;
      line-height: 1.7;
      margin: 0 0 10px;
    }
    .profile-footer-note {
      margin-top: 18px;
      font-size: 0.82rem;
      color: #7b8684;
    }
  </style>
</head>
<body>
  <header class="app-header">
    <div class="container app-header-inner">
      <div class="brand">
        <a href="index.php" class="brand-main">TicTracker</a>
        <span class="brand-sub">by Mind You Up</span>
      </div>
      <button class="nav-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
      <nav class="top-nav">
        <a href="index.php#team">Team</a>
        <a href="index.php#tictracker">TicTracker</a>
        <a href="index.php#tics">What are tics?</a>
        <a href="index.php#relax-video">Relax</a>
        <a href="index.php#articles">Articles</a>
        <a href="index.php#faq">FAQ</a>
        <a href="about.php">About</a>
        <a href="pages/auth/login.php" class="top-cta">
          <span>•</span> Sign in / Sign up
        </a>
      </nav>
    </div>
  </header>

  <main class="profile-hero">
    <div class="profile-shell">
      <article class="profile-card">
        <div class="profile-photo-wrap">
          <div class="profile-photo-inner">
            <img src="images/filipa.jpg" alt="Portrait of Filipa Pancada Fonseca">
          </div>
        </div>

        <div class="profile-content">
          <h1 class="profile-name">Filipa Pancada Fonseca</h1>
          <p class="profile-tagline">
            Clinical and health psychologist, CBT psychotherapist, professor, and researcher focused on how technology shapes well‑being.
          </p>

          <div class="profile-chip-row">
            <span class="profile-chip">CBT Psychotherapist</span>
            <span class="profile-chip">Health psychology</span>
            <span class="profile-chip">Digital well‑being</span>
          </div>

          <section>
            <h2 class="profile-section-title">Professional background</h2>
            <p class="profile-paragraph">
              Filipa is a CBT psychotherapist, professor, and researcher passionate about health psychology and the impact of technology on well‑being. For 20 years she worked in a hospital setting, gaining extensive experience in clinical intervention and a deep understanding of health and mental health challenges.
            </p>
            <p class="profile-paragraph">
              Her curiosity led her to pursue a PhD while co‑founding the Belong Institute, an innovative project dedicated to psychology and mental health across the lifespan.
            </p>
          </section>

          <section>
            <h2 class="profile-section-title">Research & interests</h2>
            <p class="profile-paragraph">
              Through practice, teaching, and research, she explores how the digital era shapes our perception of health and influences emotional responses, including the study of cyberchondria as a psychological construct.
            </p>
            <p class="profile-paragraph">
              Filipa enjoys transforming clinical experience, PhD data, and scientific evidence into practical strategies – in therapy, the classroom, and interdisciplinary collaborations with medicine and technology.
            </p>
          </section>

          <section>
            <h2 class="profile-section-title">Looking ahead</h2>
            <p class="profile-paragraph">
              She believes psychology must evolve with the modern world and that understanding our relationship with technology is essential for building a healthier future, online and offline.
            </p>
          </section>

        
        </div>
      </article>
    </div>
  </main>

  <footer class="app-footer">
    <div class="container small-text app-footer-inner">
      <span>TicTracker – Mind You Up Portal</span>
      <span>For clinical use and ongoing support in tic disorders.</span>
      <span class="footer-links">
        <a href="Privacy-Policy.pdf" target="_blank" rel="noopener">Privacy Policy</a>
        <span aria-hidden="true">·</span>
        <a href="Terms-Conditions.pdf" target="_blank" rel="noopener">Terms &amp; Conditions</a>
      </span>
    </div>
  </footer>

  <script>
    (function () {
      const toggle = document.querySelector('.nav-toggle');
      const nav = document.querySelector('.top-nav');
      if (!toggle || !nav) return;
      toggle.addEventListener('click', () => {
        const isOpen = nav.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });
    })();
  </script>
</body>
</html>
