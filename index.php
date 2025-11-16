<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Mind You Up – TicTracker Portal</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .portal-hero {
      margin-top: 24px;
    }
    .hero-grid {
      display: grid;
      gap: 24px;
      align-items: flex-start;
    }
    @media (min-width: 800px) {
      .hero-grid {
        grid-template-columns: minmax(0, 2.2fr) minmax(0, 1.4fr);
      }
    }
    .pill {
      display: inline-flex;
      align-items: center;
      padding: 4px 10px;
      border-radius: 999px;
      background: #e7f1ff;
      color: #0c3c78;
      font-size: 0.8rem;
      font-weight: 600;
      letter-spacing: 0.03em;
      text-transform: uppercase;
    }
    .portal-section-title {
      margin-top: 40px;
      margin-bottom: 8px;
      font-size: 1.2rem;
      font-weight: 700;
      color: var(--primary);
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }
    .portal-lead {
      font-size: 1rem;
      color: var(--muted);
      max-width: 620px;
    }
    .small-text {
      font-size: 0.9rem;
      color: var(--muted);
    }

    /* header nav */
    .top-nav {
      display: flex;
      gap: 16px;
      align-items: center;
    }
    .top-nav a {
      color: #fff;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.95rem;
    }
    .top-nav a:hover {
      text-decoration: underline;
    }

    /* ========== Applications cards ========== */
    .apps-grid {
      display: grid;
      gap: 18px;
      margin-top: 18px;
      align-items: start;
    }
    @media (min-width: 900px) {
      .apps-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }
    }

    .app-card {
      border-radius: 18px;
      border: 1px solid #f0d9d2;
      background: #fffdf7;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      padding: 0;          /* override old .app-card padding */
      min-height: auto;    /* override old .app-card min-height */
      transition:
        box-shadow 0.2s ease,
        border-color 0.2s ease,
        background-color 0.2s ease,
        transform 0.15s ease;
    }
    .app-card:hover {
      box-shadow: 0 8px 22px rgba(0,0,0,0.08);
      transform: translateY(-2px);
    }

    .app-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      width: 100%;
      padding: 16px 18px;
      border: none;
      background: transparent;
      cursor: pointer;
      text-align: left;
      font: inherit;
    }
    .app-card-header h3 {
      margin: 0;
      font-size: 1.3rem;
      color: var(--primary);
    }
    .app-card-arrow {
      font-size: 1.1rem;
      line-height: 1;
      transition: transform 0.2s ease;
    }

    .app-card-body {
      display: none;
      padding: 0 18px;
    }
    .app-card-body-inner {
      padding-bottom: 16px;
    }

    .app-card.is-open {
      box-shadow: 0 12px 30px rgba(0,0,0,0.12);
      border-color: #ffd7b3;
      background: #ffffff;
    }
    .app-card.is-open .app-card-body {
      display: block;
      padding-top: 4px;
      padding-bottom: 8px;
    }
    .app-card.is-open .app-card-arrow {
      transform: rotate(180deg);
    }

    /* ========== Collapsible info card (“What are tics?”) ========== */
    .info-card {
      border-radius: 18px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.05);
      border: 1px solid #f0d9d2;
      background: #ffffff;
      overflow: hidden;
      transition:
        box-shadow 0.2s ease,
        border-color 0.2s ease,
        background-color 0.2s ease;
    }
    .info-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      width: 100%;
      padding: 16px 18px;
      border: none;
      background: transparent;
      cursor: pointer;
      text-align: left;
      font: inherit;
    }
    .info-card-header h2 {
      margin: 0;
      font-size: 1.5rem;
      color: var(--primary);
      border-bottom: none;
    }
    .info-card-arrow {
      font-size: 1.1rem;
      line-height: 1;
      transition: transform 0.2s ease;
    }
    .info-card-body {
      padding: 0 18px 0;
      max-height: 0;
      overflow: hidden;
      opacity: 0;
      transition:
        max-height 0.25s ease,
        opacity 0.2s ease;
    }
    .info-card-body-inner {
      padding-top: 4px;
      padding-bottom: 16px;
    }
    .info-card.is-open {
      box-shadow: 0 14px 36px rgba(0,0,0,0.12);
      border-color: #ffd7b3;
      background: #fffdf7;
    }
    .info-card.is-open .info-card-body {
      max-height: 500px;
      opacity: 1;
    }
    .info-card.is-open .info-card-arrow {
      transform: rotate(180deg);
    }

    /* ========== Resources (educational cards) ========== */
    .resource-grid {
      display: grid;
      gap: 16px;
      margin-top: 16px;
      align-items: start;
    }
    @media (min-width: 900px) {
      .resource-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
      }
    }
    .resource-card {
      border-radius: 18px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.05);
      border: 1px solid #f0d9d2;
      background: #ffffff;
      padding: 18px 18px 16px;
      display: flex;
      flex-direction: column;
    }
    .resource-card h3 {
      margin: 0 0 6px;
      font-size: 1.1rem;
      color: var(--primary);
    }
    .resource-tag {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 999px;
      font-size: 0.75rem;
      background: #fff2dd;
      color: var(--primary);
      margin-bottom: 6px;
    }
    .resource-desc {
      margin-top: 0;
      margin-bottom: 8px;
      min-height: 4.8em;
    }
    .resource-card ul {
      margin: 8px 0 0 18px;
    }

    .highlight-banner {
      margin: 32px 0 8px;
      border-radius: 12px;
      background: #fff0e0;
      padding: 14px 16px;
      font-size: 0.95rem;
      color: var(--muted);
      box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    }
    .highlight-banner strong {
      color: var(--primary);
    }
  </style>
</head>
<body>
  <!-- Top bar -->
  <header class="app-header">
    <div class="container flex justify-between items-center">
      <h1>Mind You Up</h1>
      <nav class="top-nav">
        <a href="#about">About</a>
        <a href="#tictracker">TicTracker</a>
        <a href="#tics">What are tics?</a>
        <a href="#apps">Apps</a>
      </nav>
    </div>
  </header>

  <main class="container">
    <!-- HERO -->
    <section class="portal-hero">
      <div class="hero-grid">
        <div>
          <span class="pill">Digital support for tic disorders</span>
          <h2 style="margin: 14px 0 6px; font-size: 2rem; border-bottom:none;">
            Welcome to the Mind You Up portal
          </h2>
          <p class="portal-lead">
            Mind You Up is a dedicated network of healthcare professionals specializing in mental
            health and neurological care, with a particular focus on tic disorders such as
            Tourette syndrome.
          </p>
          <p class="portal-lead" style="margin-top: 10px;">
            To enhance treatment and communication, the Nexus Team proudly developed
            <strong> TicTracker</strong> for Mind You Up. This innovative web tool empowers
            patients, families, and professionals to accurately track tic symptoms, identify
            patterns, and communicate more effectively throughout the treatment journey.
          </p>
        </div>

        <!-- Access card -->
        <aside class="card" aria-label="Account access">
          <h2 style="border-bottom:none; margin-bottom:4px;">Access TicTracker</h2>
          <p class="hint" style="margin-top: 4px;">
            Sign in or create an account to use the TicTracker web application.
          </p>
          <div class="actions" style="justify-content:flex-start; margin-top: 16px;">
            <a
              href="https://your-app-url.example"
              class="btn btn-primary"
              target="_blank"
              rel="noopener"
            >
              Sign in / Sign up
            </a>
          </div>
          <p class="small-text" style="margin-top: 10px;">
            On the next screen you can log in with your email or create a new account
            as a patient or as a healthcare professional.
          </p>
        </aside>
      </div>
    </section>

    <!-- ABOUT -->
    <section id="about" style="margin-top: 48px;">
      <div class="portal-section-title">About Mind You Up and TicTracker</div>
      <div class="card">
        <p>
          Mind You Up is comprised of experienced doctors, therapists, and psychologists committed
          to supporting individuals with tic disorders and related conditions.
        </p>
        <p>
          To make daily treatment progress more tangible, the Nexus Team developed
          <strong> TicTracker</strong> — a simple, clinically useful, and flexible web-based
          application designed for real-world therapy.
        </p>
        <p>
          TicTracker serves as a shared tool where patients and professionals can transform daily
          tic information into actionable insights for sessions and improved care.
        </p>
      </div>
    </section>

    <!-- TIC TRACKER -->
    <section id="tictracker">
      <div class="portal-section-title">TicTracker: mission and vision</div>
      <div class="card">
        <h2>Mission</h2>
        <p>
          TicTracker’s mission is to offer a reliable and easy digital tool that supports people
          with tic disorders, their caregivers and healthcare professionals in monitoring and
          treating symptoms more effectively.
        </p>
        <p>
          It focuses on simple daily tracking, clearer communication and better therapy outcomes.
        </p>

        <h2 style="margin-top: 24px;">Vision</h2>
        <p>
          TicTracker’s vision is to empower people with tic disorders through self awareness,
          ongoing support and closer connections with their healthcare team.
        </p>
        <p>
          Over time, TicTracker aims to be a trusted digital companion in tic treatment and
          to support a more personalised approach to care.
        </p>
      </div>
    </section>

    <!-- WHAT ARE TICS (collapsible) -->
    <section id="tics">
      <div class="portal-section-title">What are tics?</div>
      <article class="info-card is-open">
        <button class="info-card-header" type="button">
          <h2>What are tics?</h2>
          <span class="info-card-arrow" aria-hidden="true">▾</span>
        </button>
        <div class="info-card-body">
          <div class="info-card-body-inner">
            <p>
              Tics are sudden, brief movements or sounds that repeat and are not fully voluntary.
              They can be motor tics, like eye blinking or shoulder movements, or vocal tics, like
              throat clearing or small sounds.
            </p>
            <p>
              For some people tics are mild. For others they can be frequent and intense and may
              affect school, work or social life. Tracking when tics appear and what is happening
              around them helps everyone understand patterns and progress.
            </p>
            <p class="small-text" style="margin-top: 16px;">
              This portal does not replace medical advice. If you are worried about tics, please
              talk to a qualified healthcare professional.
            </p>
          </div>
        </div>
      </article>
    </section>

    <!-- RESOURCES -->
    <section>
      <div class="portal-section-title">Resources to learn more</div>
      <div class="resource-grid">
        <article class="resource-card">
          <span class="resource-tag">Basics</span>
          <h3>Understanding tic disorders</h3>
          <p class="small-text resource-desc">
            A simple overview of how tic disorders are diagnosed, what usually makes them better
            or worse, and how they often change over time.
          </p>
          <ul class="small-text">
            <li>Common myths and facts</li>
            <li>Different types of tics</li>
          </ul>
        </article>

        <article class="resource-card">
          <span class="resource-tag">For families</span>
          <h3>Supporting a child with tics</h3>
          <p class="small-text resource-desc">
            Practical ideas for parents and caregivers on how to respond to tics at home and at
            school without increasing stress.
          </p>
          <ul class="small-text">
            <li>How to talk about tics</li>
            <li>Working with school and friends</li>
          </ul>
        </article>

        <article class="resource-card">
          <span class="resource-tag">For professionals</span>
          <h3>Using TicTracker in practice</h3>
          <p class="small-text resource-desc">
            Suggestions for clinicians on integrating daily tic logs into sessions and treatment
            planning.
          </p>
          <ul class="small-text">
            <li>Preparing for reviews</li>
            <li>Spotting patterns in symptoms</li>
          </ul>
        </article>
      </div>
    </section>

    <!-- APPS -->
    <section id="apps">
      <div class="portal-section-title">Applications in this portal</div>

      <div class="apps-grid">
        <!-- Card 1 (closed by default now) -->
        <article class="app-card">
          <button class="app-card-header" type="button">
            <h3>TicTracker web app</h3>
            <span class="app-card-arrow" aria-hidden="true">▾</span>
          </button>
          <div class="app-card-body">
            <div class="app-card-body-inner">
              <p class="small-text">
                Secure web interface for logging tic episodes and reviewing recent entries.
              </p>
              <ul class="small-text" style="margin: 10px 0 0 18px;">
                <li>Record tic type, duration and intensity</li>
                <li>Mark entries as self reported or caregiver reported</li>
                <li>Review recent logs to see short term patterns</li>
              </ul>
            </div>
          </div>
        </article>

        <!-- Card 2 -->
        <article class="app-card">
          <button class="app-card-header" type="button">
            <h3>For patients and families</h3>
            <span class="app-card-arrow" aria-hidden="true">▾</span>
          </button>
          <div class="app-card-body">
            <div class="app-card-body-inner">
              <p class="small-text">
                Use TicTracker together with your clinician. Log what you can and bring the data
                to appointments for more focused conversations.
              </p>
              <p class="small-text">
                Over time, shared logs can help you and your healthcare team notice patterns
                and see whether treatments are helping.
              </p>
            </div>
          </div>
        </article>

        <!-- Card 3 -->
        <article class="app-card">
          <button class="app-card-header" type="button">
            <h3>For professionals</h3>
            <span class="app-card-arrow" aria-hidden="true">▾</span>
          </button>
          <div class="app-card-body">
            <div class="app-card-body-inner">
              <p class="small-text">
                Use TicTracker as a companion in your clinical work to explore patterns across time
                and support decisions about therapy and interventions.
              </p>
              <p class="small-text">
                Recent logs are easy to scan before each session, so you can focus on what has
                changed since you last met.
              </p>
            </div>
          </div>
        </article>
      </div>
    </section>

    <!-- SUPPORT NOTE -->
    <div class="highlight-banner">
      <strong>You’re not alone.</strong> Tic disorders are common and treatable. This portal and
      TicTracker are here to support ongoing care, but they work best alongside conversations with
      your own healthcare team.
    </div>
  </main>

  <footer class="app-footer">
    <div class="container small-text" style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap;">
      <span>Mind You Up – TicTracker Portal</span>
      <span>For clinical use and ongoing support in tic disorders.</span>
    </div>
  </footer>

  <script>
    // Independent open/close for app cards
    (function () {
      const appCards = document.querySelectorAll('.app-card');

      appCards.forEach(card => {
        const headerBtn = card.querySelector('.app-card-header');
        headerBtn.addEventListener('click', () => {
          card.classList.toggle('is-open');
        });
      });
    })();

    // Collapsible "What are tics?" card
    (function () {
      const infoCard = document.querySelector('.info-card');
      if (!infoCard) return;
      const headerBtn = infoCard.querySelector('.info-card-header');

      headerBtn.addEventListener('click', () => {
        infoCard.classList.toggle('is-open');
      });
    })();
  </script>
</body>
</html>
