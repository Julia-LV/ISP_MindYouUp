<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Filipa Pancada Fonseca – Mind You Up</title>
  <link rel="stylesheet" href="web_portal.css" />
</head>
<body>
  <!-- Sticky top bar -->
  <header class="app-header">
    <div class="container app-header-inner">
      <div class="brand">
        <a href="index.php" class="brand-main">TicTracker</a>
        <span class="brand-sub">by Mind You Up</span>
      </div>

      <!-- Mobile hamburger -->
      <button class="nav-toggle" type="button"
              aria-label="Toggle navigation"
              aria-expanded="false">
        <span></span>
        <span></span>
        <span></span>
      </button>

      <nav class="top-nav">
        <a href="index.php#team">Team</a>
        <a href="index.php#tictracker">TicTracker</a>
        <a href="index.php#tics">What are tics?</a>
        <a href="index.php#relax-video">Relax</a>
        <a href="index.php#articles">Articles</a>
        <a href="index.php#faq">FAQ</a>
        <a href="about.php">About</a>
        <a href="web_portal.php">Web portal</a>
        <a href="pages/auth/login.php" class="top-cta">
          <span>•</span> Sign in / Sign up
        </a>
      </nav>
    </div>
  </header>

  <main class="container">
    <section class="about-layout">
      <div class="about-image-wrap">
        <img src="images/filipa.jpg" alt="Portrait of Filipa Pancada Fonseca" />
      </div>

      <div class="about-content">
        <h1 class="about-main-title">Filipa Pancada Fonseca</h1>
        <p class="about-intro">
          Clinical and health psychologist, CBT psychotherapist, professor, and researcher
          focused on how technology shapes well‑being.
        </p>

        <div class="about-body">
          <h2 class="about-section-heading">Professional background</h2>
          <p>
            Filipa is a CBT psychotherapist, professor, and researcher passionate about health
            psychology and the impact of technology on well‑being. For 20 years, she worked in a
            hospital setting, gaining extensive experience in clinical intervention and developing
            a deep understanding of health and mental health challenges.
          </p>
          <p>
            Her curiosity led her to pursue a PhD while co‑founding the Belong Institute, an
            innovative project dedicated to psychology and mental health across the lifespan.
            Through practice, teaching, and research, she explores how the digital era shapes our
            perception of health and influences emotional responses, studying cyberchondria as a
            psychological construct.
          </p>

          <h2 class="about-section-heading">Research &amp; interests</h2>
          <p>
            Filipa enjoys transforming personal clinical experience, PhD data, and scientific
            evidence into practical strategies – whether in therapy, in the classroom, or in
            interdisciplinary collaborations with medicine and technology.
          </p>
          <p>
            She believes psychology must evolve with the modern world, and that understanding our
            relationship with technology is essential for building a healthier future.
          </p>
        </div>
      </div>
    </section>
  </main>

  <footer class="app-footer">
    <div class="container small-text app-footer-inner">
      <span>TicTracker – Mind You Up Portal</span>
      <span>For clinical use and ongoing support in tic disorders.</span>
      <span class="footer-links">
        <a href="/ISP_MindYouUp/Privacy-Policy.pdf"
           target="_blank"
           rel="noopener">
          Privacy Policy
        </a>
        <span aria-hidden="true">·</span>
        <a href="/ISP_MindYouUp/Terms-Conditions.pdf"
           target="_blank"
           rel="noopener">
          Terms &amp; Conditions
        </a>
      </span>
    </div>
  </footer>

  <!-- Mobile nav toggle -->
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
