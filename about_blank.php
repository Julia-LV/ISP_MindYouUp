<?php
session_start();

$team = [
  [
    "name" => "Julia Vidal",
    "role" => "Project Manager",
    "text" => "Organizes the project, sets priorities, monitors deadlines, and ensures communication between the team and the client.",
    "img"  => "images/julia.jpg"
  ],
  [
    "name" => "Luis Duarte",
    "role" => "Software Developer",
    "text" => "Implements the application’s functionalities on both frontend and backend, ensuring integration between components.",
    "img"  => "images/luis.jpg"
  ],
  [
    "name" => "Andrey Maslennikov",
    "role" => "UX/UI Designer",
    "text" => "Creates the user interface and experience, developing visual prototypes and ensuring usability and accessibility.",
    "img"  => "images/andrey.jpg"
  ],
  [
    "name" => "Bhawna Panwar",
    "role" => "QA Engineer / Tester",
    "text" => "Tests the application in different scenarios, identifies errors, validates requirements and ensures the quality of the final product.",
    "img"  => "images/bhawna.jpg"
  ],
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>About Us – TicTracker by Mind You Up</title>
  <link rel="stylesheet" href="web_portal.css" />

  <style>
    /* SAME PALETTE AS YOUR CLÁUDIA PAGE */
    :root{
      --bg: #fff7ea;
      --card: #fffdf8;
      --border: #f0d9d2;

      --title: #0a4936;
      --accent: #0f684f;

      --text: #384341;
      --muted: #5b6664;
      --muted2:#7b8684;

      --chip-bg: #e8f4ef;
      --chip-text: #0f684f;

      --shadow: 0 18px 50px rgba(0,0,0,0.15);
      --shadow-strong: 0 20px 45px rgba(0,0,0,0.28);
      --radius: 26px;
    }

    body{
      background: var(--bg);
    }

    /* Page shell */
    .about-hero{
      padding: 48px 0 56px;
      background: var(--bg);
    }

    .about-shell{
      max-width: 1120px;
      margin: 0 auto;
      padding: 0 16px;
    }

    .about-card{
      background: var(--card);
      border-radius: var(--radius);
      border: 1px solid var(--border);
      box-shadow: var(--shadow);
      padding: 26px 30px;
    }

    /* Header block */
    .about-header{
      display: grid;
      gap: 10px;
      margin-bottom: 18px;
    }

    .about-title{
      margin: 0;
      font-size: 2.1rem;
      line-height: 1.1;
      color: var(--title);
      letter-spacing: -0.02em;
    }

    .about-lead{
      margin: 0;
      color: var(--muted);
      font-size: 0.98rem;
      max-width: 78ch;
      line-height: 1.6;
    }

    .about-chip-row{
      display:flex;
      flex-wrap:wrap;
      gap: 8px;
      margin-top: 8px;
    }
    .about-chip{
      padding: 4px 10px;
      border-radius: 999px;
      background: var(--chip-bg);
      color: var(--chip-text);
      font-size: 0.78rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }

    /* Team grid */
    .team-grid{
      display:grid;
      grid-template-columns: repeat(12, 1fr);
      gap: 18px;
      margin-top: 20px;
    }

    .team-member{
      grid-column: span 6;
      border: 1px solid var(--border);
      background: #ffffff;
      border-radius: 22px;
      box-shadow: 0 12px 28px rgba(0,0,0,0.10);
      overflow: hidden;
      transition: transform 180ms ease, box-shadow 180ms ease;
      position: relative;
    }

    .team-member::before{
      content:"";
      position:absolute;
      left:0; top:0; right:0;
      height: 4px;
      background: linear-gradient(90deg, var(--title), var(--accent));
      opacity: 0.95;
    }

    .team-member:hover{
      transform: translateY(-3px);
      box-shadow: 0 16px 36px rgba(0,0,0,0.13);
    }

    @media (max-width: 900px){
      .team-member{ grid-column: span 12; }
    }

    .member-inner{
      display:grid;
      grid-template-columns: 132px 1fr;
      gap: 16px;
      padding: 18px;
      align-items: start;
    }

    @media (max-width: 420px){
      .member-inner{ grid-template-columns: 1fr; }
    }

    .member-photo{
      width: 132px;
      max-width: 100%;
      aspect-ratio: 3/4;
      border-radius: 18px;
      overflow:hidden;
      background:#000;
      box-shadow: var(--shadow-strong);
      margin-top: 6px;
    }
    .member-photo img{
      width:100%;
      height:100%;
      object-fit: cover;
      display:block;
    }

    .member-name{
      margin: 4px 0 6px;
      font-size: 1.15rem;
      font-weight: 800;
      color: var(--title);
      letter-spacing: -0.01em;
    }

    /* Role as subtitle BELOW name */
    .member-role{
      margin: 0 0 10px;
      font-size: 0.9rem;
      font-weight: 700;
      color: var(--accent);
    }

    .member-text{
      margin: 0;
      color: var(--text);
      font-size: 0.95rem;
      line-height: 1.65;
    }

    .about-note{
      margin-top: 18px;
      font-size: 0.82rem;
      color: var(--muted2);
    }

    /* Footer copied to match your page */
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
        <a href="pages/auth/login.php" class="top-cta"><span>•</span> Sign in / Sign up</a>
      </nav>
    </div>
  </header>

  <main class="about-hero">
    <div class="about-shell">
      <section class="about-card" aria-labelledby="aboutTitle">
        <div class="about-header">
          <h1 class="about-title" id="aboutTitle">About us</h1>
          <p class="about-lead">
            This portal was developed as a university thesis project, combining planning, software development,
            UX/UI design and quality assurance to deliver a clean, clinical-friendly experience.
          </p>

          <div class="about-chip-row" aria-label="Team focus areas">
            <span class="about-chip">Project management</span>
            <span class="about-chip">Full-stack development</span>
            <span class="about-chip">UX/UI & accessibility</span>
            <span class="about-chip">Quality assurance</span>
          </div>
        </div>

        <div class="team-grid">
          <?php foreach ($team as $m): ?>
            <article class="team-member">
              <div class="member-inner">
                <div class="member-photo">
                  <img
                    src="<?php echo htmlspecialchars($m['img']); ?>"
                    alt="<?php echo htmlspecialchars($m['name']); ?> photo"
                    loading="lazy"
                  />
                </div>

                <div class="member-content">
                  <div class="member-name"><?php echo htmlspecialchars($m['name']); ?></div>
                  <div class="member-role"><?php echo htmlspecialchars($m['role']); ?></div>
                  <p class="member-text"><?php echo htmlspecialchars($m['text']); ?></p>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

      </section>
    </div>
  </main>

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
        <a href="/ISP_MindYouUp/Privacy-Policy.pdf" target="_blank" rel="noopener">Privacy Policy</a>
        <span aria-hidden="true">·</span>
        <a href="/ISP_MindYouUp/Terms-Conditions.pdf" target="_blank" rel="noopener">Terms &amp; Conditions</a>
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
