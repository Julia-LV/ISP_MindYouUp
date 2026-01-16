<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>About – TicTracker by Mind You Up</title>
  <link rel="stylesheet" href="web_portal.css" />
  <style>
    .app-footer {
      background: #0d5b43;
      color: #ffffff;
      border-top: 2px solid #0a4936;
    }

    .app-footer-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 18px 32px;
      gap: 16px;
      text-align: left;
    }

    .footer-left {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .footer-logo {
      height: 60px;
    }

    .footer-main-text {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .footer-main-text span {
      line-height: 1.4;
    }

    .footer-links {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      justify-content: flex-end;
      font-size: 0.95rem;
    }

    .footer-links a {
      color: #ffffff;
      text-decoration: underline;
    }

    .footer-links a:hover {
      text-decoration: none;
    }

    @media (max-width: 640px) {
      .app-footer-inner {
        flex-direction: column;
        align-items: flex-start;
      }
      .footer-links {
        justify-content: flex-start;
      }
    }
  </style>
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
        <!-- Links go back to sections on the home page -->
        <a href="index.php#team">Team</a>
        <a href="index.php#tictracker">TicTracker</a>
        <a href="index.php#tics">What are tics?</a>
        <a href="index.php#relax-video">Relax</a>
        <a href="index.php#articles">Articles</a>
        <a href="index.php#faq">FAQ</a>
        <a href="about.php" class="is-active">About</a>
        <a href="pages/auth/login.php" class="top-cta">
          <span>•</span> Sign in / Sign up
        </a>
      </nav>
    </div>
  </header>

  <main class="container">
    <section class="about-layout">
      <div class="about-image-wrap">
        <!-- Use your own photo: put it in /images and match the file name here -->
        <img src="images/sakura.jpg" alt="About TicTracker" />
      </div>

      <div class="about-content">
        <h1 class="about-main-title">About TicTracker</h1>
        <p class="about-intro">
          TicTracker is a calm digital space that helps families, young people, and clinicians
          see tic patterns more clearly and talk about them together.
        </p>

        <div class="about-body">
          <h2 class="about-section-heading">Clinical expertise</h2>
          <p>
            TicTracker is built from years of clinical work in hospitals, clinics, and research
            settings, with a focus on tic disorders, neurodevelopmental conditions, and mental health.
          </p>
          <p>
            TicTracker is dedicated to creating clear, evidence-informed resources about tic
            disorders and offering a calm digital space where families, young people, and clinicians
            can work together.
          </p>

          <p>
            When you use TicTracker, you are drawing on the experience of clinical and health
            psychologists who have worked for many years with tic disorders, neurodevelopmental
            conditions, and mental health across the lifespan. Our tools are designed to make
            day-to-day life a little easier: fewer gaps in memory between appointments, more clarity
            in sessions, and less pressure to remember everything.
          </p>

          <p>
            We know that living with tics can be confusing and exhausting. Symptoms change, routines
            shift, and it can be hard to explain what the last days or weeks have really been like.
            TicTracker aims to turn those scattered moments into a simple, visual story that you can
            share with the people who support you.
          </p>

          <h2 class="about-section-heading">Our mission</h2>
          <p>
            Our mission is to bring calm and clarity to tic care. We want families and professionals
            to be able to look at the same information, notice the same patterns, and make decisions
            together.
          </p>
          <p>
            Every feature we add is shaped by three questions: Is it clinically useful?
            Is it simple to use in real life? Does it feel gentle rather than overwhelming?
          </p>

          <h2 class="about-section-heading">Technology with a human focus</h2>
          <p>
            We use technology to support, not replace, human care. The goal is to make conversations
            with your clinician clearer and more focused, not to turn everything into data.
          </p>
          <p>
            Logs are designed to be quick, visuals are calm and clean, and language is
            straightforward, so you can keep using TicTracker even on busy or difficult days.
          </p>

          <p class="small-text">
            This website and TicTracker are intended to support, not replace, professional care.
            If you have concerns about tics or mental health, please speak directly with a qualified
            healthcare professional who can guide you in your specific situation.
          </p>
        </div>
      </div>
    </section>
  </main>

  <!-- Nice shared footer with logo (not centered) -->
  <footer class="app-footer">
    <div class="container small-text app-footer-inner">
      <div class="footer-left">
        <img src="images/nexus-logo.png" alt="Nexus Tech logo" class="footer-logo">

        <div class="footer-main-text">
          <span>TicTracker – Mind You Up Portal</span>
          <span>For clinical use and ongoing support in tic disorders.</span>
        </div>
      </div>

      <span class="footer-links">
        <a href="about_blank.php">About us</a>
        <span aria-hidden="true">·</span>
        <a href="/ISP_MindYouUp/Privacy_Policy.pdf"
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
