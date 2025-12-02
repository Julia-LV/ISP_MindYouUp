<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Cláudia Chasqueira – TicTracker by Mind You Up</title>
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

    .profile-section-title {
      margin-top: 18px;
      margin-bottom: 6px;
      font-size: 1.1rem;
      font-weight: 700;
      color: #0f684f;
    }

    .profile-list {
      margin: 0 0 8px 18px;
      padding: 0;
      font-size: 0.95rem;
      line-height: 1.6;
    }

    .profile-list li {
      margin-bottom: 4px;
    }

    .profile-paragraph {
      font-size: 0.95rem;
      line-height: 1.7;
      margin: 0 0 10px;
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
      background: #e8f4ef;
      color: #0f684f;
      font-size: 0.78rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }

    .profile-footer-note {
      margin-top: 18px;
      font-size: 0.82rem;
      color: #7b8684;
    }

    /* Footer styles (same as index/about) */
    .app-footer {
      background: #0d5b43;
      color: #ffffff;
      border-top: 2px solid #0a4936;
    }

    .app-footer-inner {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
      padding: 10px 16px;
      text-align: center;
    }

    .footer-logo {
      height: 60px;
      margin-bottom: 4px;
    }

    .footer-main-text span {
      display: block;
      line-height: 1.25;
    }

    .footer-links {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      justify-content: center;
      font-size: 0.9rem;
    }

    .footer-links a {
      color: #ffffff;
      text-decoration: underline;
    }

    .footer-links a:hover {
      text-decoration: none;
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
            <img src="images/claudia.jpg" alt="Portrait of Cláudia Chasqueira">
          </div>
        </div>

        <div class="profile-content">
          <h1 class="profile-name">Cláudia Chasqueira</h1>
          <p class="profile-tagline">
            Clinical and health psychologist, cognitive‑behavioural psychotherapist, and co‑founder of Mind You Up.
          </p>

          <div class="profile-chip-row">
            <span class="profile-chip">Clinical &amp; Health Psychology</span>
            <span class="profile-chip">CBT / Integrative</span>
            <span class="profile-chip">Tic &amp; neurodevelopmental disorders</span>
          </div>

          <section>
            <h2 class="profile-section-title">Licensing &amp; certifications</h2>
            <ul class="profile-list">
              <li>Psicóloga Especialista em Psicologia Clínica e da Saúde e em Psicoterapia.</li>
              <li>Psicoterapeuta cognitivo‑comportamental.</li>
              <li>Pós‑graduação em Psicoterapia Comportamental, Cognitiva e Integrativa (APTCCi).</li>
              <li>Clinical Supervisor.</li>
            </ul>
          </section>

          <section>
            <h2 class="profile-section-title">Experiência clínica</h2>
            <p class="profile-paragraph">
              Experiência em perturbações do neurodesenvolvimento (PEA, PHDA), perturbações do espetro obsessivo e perturbações de tiques, muitas vezes em comorbilidade com outras perturbações psiquiátricas ao longo do ciclo de vida.
            </p>
            <p class="profile-paragraph">
              Interesse particular na área do desempenho desportivo e da saúde mental no desporto.
            </p>
          </section>

          <section>
            <h2 class="profile-section-title">Memberships &amp; professional roles</h2>
            <ul class="profile-list">
              <li>Membro Efetivo da Ordem dos Psicólogos Portugueses (OPP).</li>
              <li>Certificado Europeu de Psicologia (EuroPsy).</li>
              <li>Membro da Associação Portuguesa de Terapias Comportamental, Cognitiva e Integrativa (APTCCi).</li>
              <li>Membro da Sociedade Portuguesa de Psicologia do Desporto (SPPD).</li>
              <li>Affiliate Member of the American Psychological Association (APA).</li>
              <li>ORCID ID: 000‑0002‑4872‑7722.</li>
            </ul>
          </section>

          <section>
            <h2 class="profile-section-title">English summary</h2>
            <p class="profile-paragraph">
              Cláudia is a specialist in Clinical and Health Psychology and a cognitive‑behavioural psychotherapist, working with neurodevelopmental disorders (ASD, ADHD), obsessive spectrum conditions and tic disorders across the lifespan.
            </p>
            <p class="profile-paragraph">
              She is an active member of several professional associations, including OPP, APTCCi, SPPD and APA, and brings this international perspective into her clinical practice and supervision.
            </p>
          </section>

          <section>
            <h2 class="profile-section-title">Mind You Up</h2>
            <p class="profile-paragraph">
              As co‑founder of Mind You Up, Cláudia contributes to the creation of psychotherapeutic materials and digital tools – including TicTracker – that make evidence‑based strategies easier to use in daily life.
            </p>
          </section>
        </div>
      </article>
    </div>
  </main>

  <!-- Nice shared footer with logo -->
  <footer class="app-footer">
    <div class="container small-text app-footer-inner">
      <img src="images/nexus-logo.png" alt="Nexus Tech logo" class="footer-logo">

      <div class="footer-main-text">
        <span>TicTracker – Mind You Up Portal</span>
        <span>For clinical use and ongoing support in tic disorders.</span>
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
