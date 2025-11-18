<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>About Us – TicTracker</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <!-- Sticky top bar -->
  <header class="app-header">
    <div class="container app-header-inner">
      <div class="brand">
        <a href="index.php" class="brand-main">TicTracker</a>
        <span class="brand-sub">by Mind You Up</span>
      </div>
      <nav class="top-nav">
        <a href="index.php#relax-video">Relax</a>
        <a href="index.php#articles">Articles</a>
        <a href="index.php#faq">FAQ</a>
        <a href="about.php" class="is-active">About</a>
        <a
          href="https://your-app-url.example"
          class="top-cta"
          target="_blank"
          rel="noopener"
        >
          <span>•</span> Sign in / Sign up
        </a>
      </nav>
    </div>
  </header>

  <main class="container">
    <!-- MAIN ABOUT SECTION -->
    <section class="about-layout">
      <div class="about-image-wrap">
        <!-- put sakura.jpg inside /images next to this file -->
        <img src="images/sakura.jpg" alt="Soft pink blossoms" />
      </div>

      <div class="about-content">
        <h1 class="about-main-title">About Us</h1>

        <p class="about-intro">
          TicTracker is dedicated to creating clear, evidence-informed resources about tic disorders
          and offering a calm digital space where families, young people, and clinicians can work together.
        </p>

        <p class="about-body">
          When you use TicTracker, you’re drawing on the experience of clinical and health psychologists
          who have worked for many years with tic disorders, neurodevelopmental conditions, and mental health
          across the lifespan. Our tools are designed to make day-to-day life a little easier: fewer gaps
          in memory between appointments, more clarity in sessions, and less pressure to “remember everything”.
        </p>

        <p class="about-body">
          We know that living with tics can be confusing and exhausting. Symptoms change, routines shift,
          and it can be hard to explain what the last days or weeks have really been like. TicTracker aims
          to turn those scattered moments into a simple, visual story that you can share with the people
          who support you.
        </p>
      </div>
    </section>

    <!-- MISSION -->
    <section class="about-section">
      <h2 class="about-section-heading">Our mission</h2>
      <div class="card">
        <p>
          Our mission is to bring calm and clarity to tic care. We want families and professionals to be
          able to look at the same information, notice the same patterns, and make decisions together.
        </p>
        <p>
          Every feature we add is shaped by three questions: Is it clinically useful? Is it simple to use
          in real life? Does it feel gentle rather than overwhelming?
        </p>
      </div>
    </section>

    <!-- HOW WE WORK -->
    <section class="about-section">
      <h2 class="about-section-heading">How we work</h2>
      <div class="about-pill-grid">
        <article class="about-pill-card">
          <h3>Clinical expertise</h3>
          <p>
            TicTracker is built from years of clinical work in hospitals, clinics, and research settings,
            with a focus on tic disorders, neurodevelopmental conditions, and mental health.
          </p>
        </article>

        <article class="about-pill-card">
          <h3>Digital mindset</h3>
          <p>
            We use technology to support, not replace, human care. The goal is to make conversations with
            your clinician clearer and more focused, not to turn everything into data.
          </p>
        </article>

        <article class="about-pill-card">
          <h3>Real-life practicality</h3>
          <p>
            Logs are designed to be quick, visuals are calm and clean, and language is straightforward,
            so you can keep using TicTracker even on busy or difficult days.
          </p>
        </article>
      </div>
    </section>

    <!-- VALUES -->
    <section class="about-section">
      <h2 class="about-section-heading">What we believe</h2>
      <div class="card">
        <ul class="about-values-list">
          <li>People are always more than their symptoms or diagnosis.</li>
          <li>Good care is collaborative – families, young people, and clinicians on the same side.</li>
          <li>Small, consistent steps often matter more than big, complicated plans.</li>
          <li>Digital tools should feel calming and respectful, not stressful or demanding.</li>
        </ul>
        <p class="small-text">
          This website and TicTracker are intended to support, not replace, professional care.
          If you have concerns about tics or mental health, please speak directly with a qualified
          healthcare professional who can guide you in your specific situation.
        </p>
      </div>
    </section>
  </main>

  <footer class="app-footer">
    <div class="container small-text app-footer-inner">
      <span>TicTracker – Mind You Up Portal</span>
      <span>For clinical use and ongoing support in tic disorders.</span>
    </div>
  </footer>
</body>
</html>
