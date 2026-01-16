<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Cláudia Chasqueira – TicTracker by Mind You Up</title>

  <!-- Tailwind Play CDN -->
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script> <!-- [web:186] -->

  <style>
    /* Keep your existing mobile nav toggle behavior */
    .top-nav.is-open { display: flex !important; }
  </style>
</head>

<body class="bg-[#fff7ea] text-[#384341] font-sans">
  <header class="sticky top-0 z-50 bg-[#0f684f] text-white shadow-[0_2px_8px_rgba(0,0,0,0.16)]">
    <div class="max-w-[1120px] mx-auto px-4 py-2 flex items-center justify-between gap-6 relative">
      <div class="flex flex-col gap-0.5 pl-1">
        <a href="index.php" class="text-[1.6rem] font-bold tracking-wide no-underline text-white hover:opacity-90">
          TicTracker
        </a>
        <span class="text-[0.8rem] text-white/85 ml-2">by Mind You Up</span>
      </div>

      <button class="nav-toggle inline-flex flex-col justify-center md:hidden p-1 ml-auto"
              type="button"
              aria-label="Toggle navigation"
              aria-expanded="false">
        <span class="block w-[22px] h-[2px] bg-white rounded-full my-1"></span>
        <span class="block w-[22px] h-[2px] bg-white rounded-full my-1"></span>
        <span class="block w-[22px] h-[2px] bg-white rounded-full my-1"></span>
      </button>

      <nav class="top-nav hidden md:flex md:flex-row md:static md:shadow-none
                  flex-col absolute top-full left-4 right-4 mt-2
                  bg-[#0f684f] rounded-xl shadow-[0_4px_14px_rgba(0,0,0,0.25)]
                  px-4 py-3 gap-3 md:p-0 md:mt-0 md:rounded-none md:bg-transparent md:gap-4 md:flex-wrap md:items-center">
        <a href="index.php#team" class="text-white no-underline font-medium text-[0.95rem] hover:underline">Team</a>
        <a href="index.php#tictracker" class="text-white no-underline font-medium text-[0.95rem] hover:underline">TicTracker</a>
        <a href="index.php#tics" class="text-white no-underline font-medium text-[0.95rem] hover:underline">What are tics?</a>
        <a href="index.php#relax-video" class="text-white no-underline font-medium text-[0.95rem] hover:underline">Relax</a>
        <a href="index.php#articles" class="text-white no-underline font-medium text-[0.95rem] hover:underline">Articles</a>
        <a href="index.php#faq" class="text-white no-underline font-medium text-[0.95rem] hover:underline">FAQ</a>
        <a href="about.php" class="text-white no-underline font-medium text-[0.95rem] hover:underline">About</a>

        <a href="pages/auth/login.php"
           class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full border border-white/85
                  bg-white/15 text-white no-underline font-semibold text-[0.9rem]
                  shadow-[0_2px_8px_rgba(0,0,0,0.18)] hover:bg-white/25">
          <span class="text-[1rem] leading-none">•</span> Sign in / Sign up
        </a>
      </nav>
    </div>
  </header>

  <main class="py-12">
    <div class="max-w-[1120px] mx-auto px-4">
      <article class="bg-[#fffdf8] rounded-[26px] border border-[#f0d9d2]
                      shadow-[0_18px_50px_rgba(0,0,0,0.15)]
                      grid gap-7 p-6 items-start
                      lg:[grid-template-columns:minmax(0,1.1fr)_minmax(0,1.9fr)]">
        <div class="flex justify-center">
          <div class="w-[280px] max-w-full aspect-[3/4] rounded-[30px] overflow-hidden bg-black
                      shadow-[0_20px_45px_rgba(0,0,0,0.28)]">
            <img src="images/claudia.jpg" alt="Portrait of Cláudia Chasqueira" class="w-full h-full object-cover block" />
          </div>
        </div>

        <div class="text-[#384341]">
          <h1 class="text-[2.1rem] leading-tight font-extrabold text-[#0a4936] m-0 mb-2">
            Cláudia Chasqueira
          </h1>

          <p class="text-[0.98rem] text-[#5b6664] m-0 mb-5 max-w-[640px]">
            Clinical and health psychologist, cognitive-behavioural psychotherapist, and co-founder of Mind You Up.
          </p>

          <div class="flex flex-wrap gap-2 mt-2 mb-2">
            <span class="px-2.5 py-1 rounded-full bg-[#e8f4ef] text-[#0f684f]
                         text-[0.78rem] font-semibold uppercase tracking-[0.06em]">
              Clinical &amp; Health Psychology
            </span>
            <span class="px-2.5 py-1 rounded-full bg-[#e8f4ef] text-[#0f684f]
                         text-[0.78rem] font-semibold uppercase tracking-[0.06em]">
              CBT / Integrative
            </span>
            <span class="px-2.5 py-1 rounded-full bg-[#e8f4ef] text-[#0f684f]
                         text-[0.78rem] font-semibold uppercase tracking-[0.06em]">
              Tic &amp; neurodevelopmental disorders
            </span>
          </div>

          <section class="mt-5">
            <h2 class="mt-4 mb-2 text-[1.1rem] font-bold text-[#0f684f]">Licensing &amp; certifications</h2>
            <ul class="list-disc pl-5 text-[0.95rem] leading-relaxed space-y-1">
              <li>Specialist Psychologist in Clinical and Health Psychology and in Psychotherapy.</li>
              <li>Cognitive-behavioural psychotherapist.</li>
              <li>Postgraduate training in Behavioural, Cognitive and Integrative Psychotherapy (APTCCi).</li>
              <li>Clinical supervisor.</li>
            </ul>
          </section>

          <section class="mt-5">
            <h2 class="mt-4 mb-2 text-[1.1rem] font-bold text-[#0f684f]">Clinical experience</h2>
            <p class="text-[0.95rem] leading-relaxed mb-3">
              Experience in neurodevelopmental conditions (ASD, ADHD), obsessive-spectrum conditions, and tic disorders—often alongside other psychiatric conditions—across the lifespan.
            </p>
            <p class="text-[0.95rem] leading-relaxed mb-0">
              Particular interest in sports performance and mental health in sport.
            </p>
          </section>

          <section class="mt-5">
            <h2 class="mt-4 mb-2 text-[1.1rem] font-bold text-[#0f684f]">Memberships &amp; professional roles</h2>
            <ul class="list-disc pl-5 text-[0.95rem] leading-relaxed space-y-1">
              <li>Full member of the Order of Portuguese Psychologists (OPP).</li>
              <li>EuroPsy certificate (European Certificate in Psychology).</li>
              <li>Member of the Portuguese Association for Behavioural, Cognitive and Integrative Therapies (APTCCi).</li>
              <li>Member of the Portuguese Society of Sport Psychology (SPPD).</li>
              <li>Affiliate Member of the American Psychological Association (APA).</li>
              <li>ORCID iD: 000-0002-4872-7722.</li>
            </ul>
          </section>

          <section class="mt-5">
            <h2 class="mt-4 mb-2 text-[1.1rem] font-bold text-[#0f684f]">Mind You Up</h2>
            <p class="text-[0.95rem] leading-relaxed mb-0">
              As co-founder of Mind You Up, Cláudia contributes to the development of psychotherapeutic materials and digital tools—including TicTracker—that help bring evidence-based strategies into everyday life.
            </p>
          </section>
        </div>
      </article>
    </div>
  </main>

  <footer class="mt-6 bg-[#0d5b43] text-white border-t-2 border-[#0a4936]">
    <div class="max-w-[1120px] mx-auto px-4 py-4 flex flex-wrap items-center justify-between gap-4 text-left text-[0.95rem]">
      <div class="flex items-center gap-3">
        <img src="images/nexus-logo.png" alt="Nexus Tech logo" class="h-[60px]" />
        <div class="flex flex-col gap-0.5">
          <span class="leading-snug">TicTracker – Mind You Up Portal</span>
          <span class="leading-snug">For clinical use and ongoing support in tic disorders.</span>
        </div>
      </div>

      <div class="flex flex-wrap items-center justify-end gap-2">
        <a href="about_blank.php" class="text-white underline hover:no-underline">About us</a>
        <span aria-hidden="true">·</span>

        <a href="/ISP_MindYouUp/Privacy-Policy.pdf" target="_blank" rel="noopener noreferrer"
           class="text-white underline hover:no-underline">
          Privacy Policy
        </a>

        <span aria-hidden="true">·</span>

        <a href="/ISP_MindYouUp/Terms-Conditions.pdf" target="_blank" rel="noopener noreferrer"
           class="text-white underline hover:no-underline">
          Terms &amp; Conditions
        </a>

        <!-- Instagram icon -->
        <a href="https://www.instagram.com/mind_you_up/"
           target="_blank"
           rel="noopener noreferrer"
           aria-label="Mind You Up on Instagram"
           class="inline-flex items-center ml-2 text-white hover:opacity-90">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
               viewBox="0 0 16 16" aria-hidden="true" focusable="false">
            <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.9 3.9 0 0 0-1.417.923A3.9 3.9 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.9 3.9 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.9 3.9 0 0 0-.923-1.417A3.9 3.9 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.035 1.204.166 1.486.275.373.145.64.319.92.599s.453.546.598.92c.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.5 2.5 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.485.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.5 2.5 0 0 1-.92-.598 2.5 2.5 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233s.008-2.388.046-3.231c.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92s.546-.453.92-.598c.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92m-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217m0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334"/>
          </svg>
        </a>
      </div>
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
