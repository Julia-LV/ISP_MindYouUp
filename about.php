<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>About – TicTracker by Mind You Up</title>

  <!-- Tailwind Play CDN -->
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script> <!-- [web:186] -->

  <style>
    /* Keep your existing mobile nav toggle behavior */
    .top-nav.is-open { display: flex !important; }
  </style>
</head>

<body class="bg-[#fff7ea] text-[#384341] font-sans">
  <!-- Sticky top bar -->
  <header class="sticky top-0 z-50 bg-[#0f684f] text-white shadow-[0_2px_8px_rgba(0,0,0,0.16)]">
    <div class="max-w-[1120px] mx-auto px-4 py-2 flex items-center justify-between gap-6 relative">
      <div class="flex flex-col gap-0.5 pl-1">
        <a href="index.php" class="text-[1.6rem] font-bold tracking-wide no-underline text-white hover:opacity-90">
          TicTracker
        </a>
        <span class="text-[0.8rem] text-white/85 ml-2">by Mind You Up</span>
      </div>

      <!-- Mobile hamburger -->
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
        <a href="about.php" class="text-white underline font-semibold text-[0.95rem]">About</a>

        <a href="pages/auth/login.php"
           class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full border border-white/85
                  bg-white/15 text-white no-underline font-semibold text-[0.9rem]
                  shadow-[0_2px_8px_rgba(0,0,0,0.18)] hover:bg-white/25">
          <span class="text-[1rem] leading-none">•</span> Sign in / Sign up
        </a>
      </nav>
    </div>
  </header>

  <main class="max-w-[1120px] mx-auto px-4 py-10">
    <section class="grid gap-7 items-start lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1.9fr)]">
      <div class="max-w-[380px] rounded-3xl overflow-hidden shadow-[0_14px_36px_rgba(0,0,0,0.18)] bg-black
                  lg:justify-self-start">
        <img src="images/sakura.jpg" alt="About TicTracker" class="w-full h-auto block object-cover" />
      </div>

      <div>
        <h1 class="m-0 text-[2.1rem] leading-tight font-extrabold text-[#0a4936]">
          About TicTracker
        </h1>

        <p class="mt-3 text-[1.02rem] text-[#5b6664] max-w-[640px]">
          TicTracker is a calm digital space that helps families, young people, and clinicians
          see tic patterns more clearly and talk about them together.
        </p>

        <div class="mt-6 text-[0.96rem] leading-relaxed">
          <h2 class="mt-10 mb-3 text-[1.25rem] font-bold text-[#0a4936]">Clinical expertise</h2>
          <p>
            TicTracker is built from years of clinical work in hospitals, clinics, and research
            settings, with a focus on tic disorders, neurodevelopmental conditions, and mental health.
          </p>
          <p class="mt-3">
            TicTracker is dedicated to creating clear, evidence-informed resources about tic
            disorders and offering a calm digital space where families, young people, and clinicians
            can work together.
          </p>
          <p class="mt-3">
            When you use TicTracker, you are drawing on the experience of clinical and health
            psychologists who have worked for many years with tic disorders, neurodevelopmental
            conditions, and mental health across the lifespan. Our tools are designed to make
            day-to-day life a little easier: fewer gaps in memory between appointments, more clarity
            in sessions, and less pressure to remember everything.
          </p>
          <p class="mt-3">
            We know that living with tics can be confusing and exhausting. Symptoms change, routines
            shift, and it can be hard to explain what the last days or weeks have really been like.
            TicTracker aims to turn those scattered moments into a simple, visual story that you can
            share with the people who support you.
          </p>

          <h2 class="mt-10 mb-3 text-[1.25rem] font-bold text-[#0a4936]">Our mission</h2>
          <p>
            Our mission is to bring calm and clarity to tic care. We want families and professionals
            to be able to look at the same information, notice the same patterns, and make decisions
            together.
          </p>
          <p class="mt-3">
            Every feature we add is shaped by three questions: Is it clinically useful?
            Is it simple to use in real life? Does it feel gentle rather than overwhelming?
          </p>

          <h2 class="mt-10 mb-3 text-[1.25rem] font-bold text-[#0a4936]">Technology with a human focus</h2>
          <p>
            We use technology to support, not replace, human care. The goal is to make conversations
            with your clinician clearer and more focused, not to turn everything into data.
          </p>
          <p class="mt-3">
            Logs are designed to be quick, visuals are calm and clean, and language is
            straightforward, so you can keep using TicTracker even on busy or difficult days.
          </p>

          <p class="mt-6 text-[0.85rem] text-[#5b6664]">
            This website and TicTracker are intended to support, not replace, professional care.
            If you have concerns about tics or mental health, please speak directly with a qualified
            healthcare professional who can guide you in your specific situation.
          </p>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
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
