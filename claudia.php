<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Cláudia Chasqueira – Mind You Up</title>
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
        <img src="images/claudia.jpg" alt="Portrait of Cláudia Chasqueira" />
      </div>

      <div class="about-content">
        <h1 class="about-main-title">Cláudia Chasqueira</h1>
        <p class="about-intro">
          Clinical and health psychologist, cognitive‑behavioural psychotherapist,
          and co‑founder of Mind You Up.
        </p>

        <div class="about-body">
          <h2 class="about-section-heading">Licensing &amp; certifications</h2>
          <ul class="about-values-list">
            <li>Psicóloga Especialista em Psicologia Clínica e da Saúde e em Psicoterapia.</li>
            <li>Psicoterapeuta cognitivo‑comportamental.</li>
            <li>Pós‑graduação em Psicoterapia Comportamental, Cognitiva e Integrativa (APTCCi).</li>
            <li>Clinical Supervisor.</li>
          </ul>

          <h2 class="about-section-heading">Experiência clínica</h2>
          <p>
            Experiência em perturbações do neurodesenvolvimento (PEA, PHDA), perturbações
            do espetro obsessivo e perturbações de tiques, muitas vezes em comorbilidade
            com outras perturbações psiquiátricas ao longo do ciclo de vida.
          </p>
          <p>
            Interesse particular na área do desempenho desportivo e da saúde mental
            no desporto.
          </p>

          <h2 class="about-section-heading">Memberships &amp; professional roles</h2>
          <ul class="about-values-list">
            <li>Membro Efetivo da Ordem dos Psicólogos Portugueses (OPP).</li>
            <li>Certificado Europeu de Psicologia (EuroPsy).</li>
            <li>Membro da Associação Portuguesa de Terapias Comportamental, Cognitiva e Integrativa (APTCCi).</li>
            <li>Membro da Sociedade Portuguesa de Psicologia do Desporto (SPPD).</li>
            <li>Affiliate Member of the American Psychological Association (APA).</li>
            <li>ORCID ID: 000-0002-4872-7722.</li>
          </ul>

          <h2 class="about-section-heading">English summary</h2>
          <p>
            Cláudia is a specialist in Clinical and Health Psychology and a cognitive‑behavioural
            psychotherapist (postgraduate degree in Behavioral, Cognitive and Integrative
            Psychotherapy from APTCCi). She works across the lifespan with neurodevelopmental
            disorders (ASD, ADHD), obsessive spectrum disorders and tic disorders, often in
            comorbidity with other psychiatric conditions.
          </p>
          <p>
            She is an effective member of the Portuguese Psychologists Association (OPP),
            holds the European Certificate in Psychology (EuroPsy), and is a member of APTCCi,
            the Portuguese Society of Sports Psychology (SPPD), and the American Psychological
            Association (APA).
          </p>

          <h2 class="about-section-heading">Mind You Up</h2>
          <p>
            Co‑founder of Mind You Up, dedicated to the creation and development of
            psychotherapeutic intervention materials and digital tools – including TicTracker –
            that bring evidence‑based strategies into daily life.
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
