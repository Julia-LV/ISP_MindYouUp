<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>TicTracker – Mind You Up Portal</title>
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
      justify-content: space-between;   /* left + right layout */
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
        <a href="#team">Team</a>
        <a href="#tictracker">TicTracker</a>
        <a href="#tics">What are tics?</a>
        <a href="#relax-video">Relax</a>
        <a href="#articles">Articles</a>
        <a href="#faq">FAQ</a>
        <a href="about.php">About</a>
        <a href="pages/auth/login.php" class="top-cta">
          <span>•</span> Sign in / Sign up
        </a>
      </nav>
    </div>
  </header>

  <!-- HERO (full-width background, text on left) -->
  <section class="portal-hero">
    <div class="hero-grid hero-left">
      <div class="hero-text-block">
        <span class="pill">Digital support for tic disorders</span>
        <h2 class="hero-title">
          A calmer way to track tics
        </h2>
        <p class="hero-lead">
          TicTracker helps you log tics, see patterns, and bring clearer
          information into every appointment.
        </p>
        <ul class="hero-list">
          <li>Track tics in seconds, wherever you are</li>
          <li>Share a simple timeline with your clinician</li>
          <li>Feel less alone between appointments</li>
        </ul>
      </div>
    </div>
  </section>

  <main class="container">
    <!-- TEAM: circle cards linking to separate pages -->
    <section id="team">
      <div class="section-title">Our clinical team</div>
      <p class="section-intro">
        Mind You Up is led by experienced clinical and health psychologists. Click a profile to learn more.
      </p>
      <div class="team-circle-row">
        <a href="claudia.php" class="team-circle-card">
          <div class="team-circle-photo">
            <img src="images/claudia.jpg" alt="Portrait of Cláudia Chasqueira">
          </div>
          <div class="team-circle-name">Cláudia Chasqueira</div>
        </a>

        <a href="filipa.php" class="team-circle-card">
          <div class="team-circle-photo">
            <img src="images/filipa.jpg" alt="Portrait of Filipa Pancada Fonseca">
          </div>
          <div class="team-circle-name">Filipa Pancada Fonseca</div>
        </a>
      </div>
    </section>

    <!-- TIC TRACKER -->
    <section id="tictracker">
      <div class="section-title">How TicTracker helps</div>
      <article class="card">
        <h2>Simple daily support</h2>
        <p>
          TicTracker is a web app that lets you log tics quickly, see patterns over time,
          and share those insights with your healthcare team.
        </p>

        <h3>What it’s designed for</h3>
        <ul>
          <li>Daily or weekly tracking without long forms</li>
          <li>Clear summaries you can review before each appointment</li>
          <li>More focused conversations about what changed and why</li>
        </ul>
      </article>
    </section>

    <!-- WHAT ARE TICS (collapsible) -->
    <section id="tics">
      <div class="section-title">What are tics?</div>
      <article class="info-card is-open">
        <button class="info-card-header" type="button">
          <h2>What are tics?</h2>
          <span class="info-card-arrow" aria-hidden="true">▾</span>
        </button>
        <div class="info-card-body">
          <div class="info-card-body-inner">
            <p>
              Tics are sudden, brief movements or sounds that repeat and are not fully voluntary.
            </p>
            <p>
              For some people tics are mild. For others they can be frequent and intense and may
              affect school, work, or social life. Tracking when they appear helps everyone see
              patterns and progress.
            </p>
            <p class="small-text" style="margin-top: 16px;">
              This portal does not replace medical advice. If you are worried about tics, please
              talk to a qualified healthcare professional.
            </p>
          </div>
        </div>
      </article>
    </section>

    <!-- APPS -->
    <section id="apps">
      <div class="section-title">Applications in this portal</div>
      <div class="apps-grid">
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
              <ul class="small-text">
                <li>Record tic type, duration, and intensity</li>
                <li>Mark entries as self- or caregiver-reported</li>
                <li>See short-term trends at a glance</li>
              </ul>
            </div>
          </div>
        </article>

        <article class="app-card">
          <button class="app-card-header" type="button">
            <h3>For patients/caregivers</h3>
            <span class="app-card-arrow" aria-hidden="true">▾</span>
          </button>
          <div class="app-card-body">
            <div class="app-card-body-inner">
              <p class="small-text">
                Log what you notice between appointments and bring simple summaries
                to your next visit.
              </p>
              <p class="small-text">
                Over time, shared logs help you and your team see what is improving
                and where support is still needed.
              </p>
            </div>
          </div>
        </article>

        <article class="app-card">
          <button class="app-card-header" type="button">
            <h3>For professionals</h3>
            <span class="app-card-arrow" aria-hidden="true">▾</span>
          </button>
          <div class="app-card-body">
            <div class="app-card-body-inner">
              <p class="small-text">
                Use TicTracker as a companion in your clinical work to explore patterns
                over time and support treatment choices.
              </p>
              <p class="small-text">
                Recent logs are quick to scan before each session, so you can focus on
                what has changed since you last met.
              </p>
            </div>
          </div>
        </article>
      </div>
    </section>

    <!-- TESTIMONIALS -->
    <section>
      <div class="section-title">What people say</div>
      <div class="testimonial-list">
        <div class="testimonial">
          <strong>Sarah, Parent of 11-year-old</strong>
          <div class="testimonial-stars" aria-hidden="true">★★★★★</div>
          “We finally see when tics spike and what might be setting them off. It’s been a game changer.”
        </div>
        <div class="testimonial">
          <strong>Dr. Emily Chen, Clinician</strong>
          <div class="testimonial-stars" aria-hidden="true">★★★★☆</div>
          “I can focus sessions on what really changed instead of trying to remember the last few weeks.”
        </div>
        <div class="testimonial">
          <strong>Alex, Young person with tics</strong>
          <div class="testimonial-stars" aria-hidden="true">★★★★★</div>
          “It’s nice to just tap and be done, rather than explaining everything every time.”
        </div>
      </div>
    </section>

    <!-- ONLINE VIDEOS TO RELAX -->
    <section id="relax-video">
      <div class="section-title">Online videos to relax</div>
      <p class="section-intro">
        These short videos are gentle background support. Use headphones if that feels more comfortable.
      </p>
      <div class="video-grid">
        <article class="video-card">
          <div class="video-thumb">
            <iframe src="https://www.youtube.com/embed/1ZYbU82GVz4"
                    title="Calm ambient music"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
          </div>
          <div class="video-card-body">
            Soft ambient sounds for a few minutes of calm.
          </div>
        </article>

        <article class="video-card">
          <div class="video-thumb">
            <iframe src="https://www.youtube.com/embed/BHACKCNDMW8"
                    title="Nature scenery and relaxing music"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
          </div>
          <div class="video-card-body">
            Nature scenes and gentle music to take a small break between tasks.
          </div>
        </article>

        <article class="video-card">
          <div class="video-thumb">
            <iframe src="https://www.youtube.com/embed/inpok4MKVLM"
                    title="10 minute guided meditation"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
          </div>
          <div class="video-card-body">
            A short breathing practice you can follow at your own pace.
          </div>
        </article>
      </div>
    </section>

    <!-- ARTICLES -->
    <section id="articles">
      <div class="section-title">Articles</div>
      <div class="article-list">
        <article class="article-card">
          <h3>Tics and school: what teachers can do</h3>
          <div class="article-meta">5 min read · For families &amp; schools</div>
          <p>
            Practical ideas for talking with teachers and making simple classroom adjustments.
          </p>
          <a href="https://www.choosingtherapy.com/tics-in-school-support/"
             class="article-link"
             target="_blank"
             rel="noopener">
            Read article <span>→</span>
          </a>
        </article>
        <article class="article-card">
          <h3>Preparing for your first clinic visit</h3>
          <div class="article-meta">4 min read · For families</div>
          <p>
            A straightforward checklist so you feel less rushed and more prepared.
          </p>
          <a href="https://www.choosingtherapy.com/neurologist-appointment/"
             class="article-link"
             target="_blank"
             rel="noopener">
            Read article <span>→</span>
          </a>
        </article>
        <article class="article-card">
          <h3>Habit reversal training for tics</h3>
          <div class="article-meta">6 min read · For professionals</div>
          <p>
            Overview of behavioural treatment for tics, including HRT and CBIT.
          </p>
          <a href="https://www.choosingtherapy.com/habit-reversal-training/"
             class="article-link"
             target="_blank"
             rel="noopener">
            Read article <span>→</span>
          </a>
        </article>
      </div>
    </section>

    <!-- FAQ -->
    <section id="faq">
      <div class="section-title">Frequently Asked Questions</div>
      <div class="faq-list">
        <article class="faq-item">
          <button class="faq-question" type="button">
            <span class="faq-question-text">Is TicTracker a replacement for medical care?</span>
            <span class="faq-toggle">+</span>
          </button>
          <div class="faq-answer">
            <div class="faq-answer-inner">
              No. TicTracker is a support tool. It works best alongside conversations with a qualified
              healthcare professional.
            </div>
          </div>
        </article>

        <article class="faq-item">
          <button class="faq-question" type="button">
            <span class="faq-question-text">Who can use the TicTracker portal?</span>
            <span class="faq-toggle">+</span>
          </button>
          <div class="faq-answer">
            <div class="faq-answer-inner">
              People with tics, parents or caregivers, and healthcare professionals can all use
              TicTracker with their own accounts.
            </div>
          </div>
        </article>

        <article class="faq-item">
          <button class="faq-question" type="button">
            <span class="faq-question-text">Is my information secure?</span>
            <span class="faq-toggle">+</span>
          </button>
          <div class="faq-answer">
            <div class="faq-answer-inner">
              Yes. Data is stored securely and only shared with clinicians or caregivers you choose
              to involve, according to local privacy laws.
            </div>
          </div>
        </article>

        <article class="faq-item">
          <button class="faq-question" type="button">
            <span class="faq-question-text">Do I need to track every tic?</span>
            <span class="faq-toggle">+</span>
          </button>
          <div class="faq-answer">
            <div class="faq-answer-inner">
              No. Many families and clinicians use TicTracker to record key patterns, spikes, or
              changes rather than every single tic.
            </div>
          </div>
        </article>
      </div>
    </section>

    <!-- SUPPORT NOTE -->
    <div class="highlight-banner">
      <strong>You’re not alone.</strong> TicTracker is here to help you and your healthcare
      team make sense of day-to-day tic symptoms.
    </div>
  </main>

  <!-- NICE FOOTER WITH LOGO (not centered) -->
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

  <!-- Existing JS for cards & FAQ -->
  <script>
    // app cards
    (function () {
      const appCards = document.querySelectorAll('.app-card');
      appCards.forEach(card => {
        const headerBtn = card.querySelector('.app-card-header');
        headerBtn.addEventListener('click', () => {
          card.classList.toggle('is-open');
        });
      });
    })();

    // collapsible "What are tics?"
    (function () {
      const infoCard = document.querySelector('.info-card');
      if (!infoCard) return;
      const headerBtn = infoCard.querySelector('.info-card-header');
      headerBtn.addEventListener('click', () => {
        infoCard.classList.toggle('is-open');
      });
    })();

    // FAQ accordion
    (function () {
      const faqItems = document.querySelectorAll('.faq-item');
      faqItems.forEach(item => {
        const btn = item.querySelector('.faq-question');
        btn.addEventListener('click', () => {
          item.classList.toggle('is-open');
        });
      });
    })();
  </script>

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

  <!-- Rotate hero background between multiple images -->
  <script>
    (function () {
      const hero = document.querySelector('.portal-hero');
      if (!hero) return;

      const backgrounds = [
        'images/forest.jpg',
        'images/forest2.jpg',
        'images/nature.jpg'
      ];

      let current = 0;

      function setBackground() {
        hero.style.backgroundImage = 'url(' + backgrounds[current] + ')';
        current = (current + 1) % backgrounds.length;
      }

      // Set initial background and change every 3 seconds
      setBackground();
      setInterval(setBackground, 3000);
    })();
  </script>
</body>
</html>
