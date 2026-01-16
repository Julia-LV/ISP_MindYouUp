<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>TicTracker – Mind You Up Portal</title>

  <!-- Tailwind Play CDN -->
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script> <!-- [web:186] -->

  <style>
    /* Small helpers to keep your existing JS toggles working */
    .top-nav.is-open { display: flex !important; }
    .info-card.is-open .info-card-body { max-height: 500px !important; opacity: 1 !important; }
    .app-card.is-open .app-card-body { display: block !important; }
    .faq-item.is-open .faq-answer { max-height: 240px !important; }

    /* Hero overlay (keeps your previous look) */
    .portal-hero::before{
      content:"";
      position:absolute;
      inset:0;
      background: rgba(0,0,0,0.5);
      z-index:1;
    }
  </style>
</head>

<body class="bg-[#fff7ea] text-[#384341] font-sans">
  <!-- Header -->
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

      <!-- Nav -->
      <nav class="top-nav hidden md:flex md:flex-row md:static md:shadow-none
                  flex-col absolute top-full left-4 right-4 mt-2
                  bg-[#0f684f] rounded-xl shadow-[0_4px_14px_rgba(0,0,0,0.25)]
                  px-4 py-3 gap-3 md:p-0 md:mt-0 md:rounded-none md:bg-transparent md:gap-4 md:flex-wrap md:items-center">
        <a href="#team" class="text-white no-underline font-medium text-[0.95rem] hover:underline">Team</a>
        <a href="#tictracker" class="text-white no-underline font-medium text-[0.95rem] hover:underline">TicTracker</a>
        <a href="#tics" class="text-white no-underline font-medium text-[0.95rem] hover:underline">What are tics?</a>
        <a href="#relax-video" class="text-white no-underline font-medium text-[0.95rem] hover:underline">Relax</a>
        <a href="#articles" class="text-white no-underline font-medium text-[0.95rem] hover:underline">Articles</a>
        <a href="#faq" class="text-white no-underline font-medium text-[0.95rem] hover:underline">FAQ</a>
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

  <!-- Hero -->
  <section class="portal-hero relative w-screen ml-[calc(-50vw+50%)] mr-[calc(-50vw+50%)]
                  min-h-[600px] px-5 py-[140px] bg-center bg-cover bg-no-repeat"
           style="background-image:url('images/1.png');">
    <div class="max-w-[1120px] mx-auto px-4 relative z-[2] flex justify-start items-center min-h-[100px]">
      <div class="max-w-[520px] bg-black/55 text-white rounded-2xl px-8 py-7">
        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-white/20 text-white
                     border border-white/60 text-[0.8rem] font-semibold uppercase tracking-wide">
          Digital support for tic disorders
        </span>

        <h2 class="mt-4 mb-2 text-[2.3rem] leading-tight font-extrabold text-white drop-shadow-[0_3px_12px_rgba(0,0,0,0.6)]">
          A calmer way to track tics
        </h2>

        <p class="text-white/95 drop-shadow-[0_2px_6px_rgba(0,0,0,0.5)] mb-4">
          TicTracker helps you log tics, see patterns, and bring clearer information into every appointment.
        </p>

        <ul class="list-disc pl-5 space-y-1">
          <li>Track tics in seconds, wherever you are</li>
          <li>Share a simple timeline with your clinician</li>
          <li>Feel less alone between appointments</li>
        </ul>
      </div>
    </div>
  </section>

  <main class="max-w-[1120px] mx-auto px-4">
    <!-- Team -->
    <section id="team" class="mt-12">
      <div class="mt-12 mb-2 text-[1.2rem] font-bold text-[#0f684f] uppercase tracking-[0.08em]">Our clinical team</div>
      <p class="text-[0.95rem] text-[#5b6664] max-w-[680px]">
        Mind You Up is led by experienced clinical and health psychologists. Click a profile to learn more.
      </p>

      <div class="flex flex-wrap gap-5 mt-4">
        <a href="claudia.php"
           class="flex items-center gap-3 bg-white rounded-full px-4 py-2 border border-[#f0d9d2]
                  shadow-[0_6px_16px_rgba(0,0,0,0.06)]
                  hover:-translate-y-0.5 hover:shadow-[0_10px_24px_rgba(0,0,0,0.12)]
                  transition">
          <div class="w-[72px] h-[72px] rounded-full overflow-hidden border-[3px] border-[#ffd7b3] shrink-0">
            <img src="images/claudia.jpg" alt="Portrait of Cláudia Chasqueira" class="w-full h-full object-cover block">
          </div>
          <div class="text-[1rem] font-semibold text-[#0f684f]">Cláudia Chasqueira</div>
        </a>

        <a href="filipa.php"
           class="flex items-center gap-3 bg-white rounded-full px-4 py-2 border border-[#f0d9d2]
                  shadow-[0_6px_16px_rgba(0,0,0,0.06)]
                  hover:-translate-y-0.5 hover:shadow-[0_10px_24px_rgba(0,0,0,0.12)]
                  transition">
          <div class="w-[72px] h-[72px] rounded-full overflow-hidden border-[3px] border-[#ffd7b3] shrink-0">
            <img src="images/filipa.jpg" alt="Portrait of Filipa Pancada Fonseca" class="w-full h-full object-cover block">
          </div>
          <div class="text-[1rem] font-semibold text-[#0f684f]">Filipa Pancada Fonseca</div>
        </a>
      </div>
    </section>

    <!-- TicTracker -->
    <section id="tictracker" class="mt-12">
      <div class="mt-12 mb-2 text-[1.2rem] font-bold text-[#0f684f] uppercase tracking-[0.08em]">How TicTracker helps</div>

      <article class="bg-white border border-[#f0d9d2] rounded-2xl p-5 shadow-[0_6px_18px_rgba(0,0,0,0.05)]">
        <h2 class="text-[1.3rem] font-bold text-[#0f684f] mt-0">Simple daily support</h2>
        <p class="mt-2">
          TicTracker is a web app that lets you log tics quickly, see patterns over time,
          and share those insights with your healthcare team.
        </p>

        <h3 class="mt-4 font-bold">What it's designed for</h3>
        <ul class="list-disc pl-5 mt-2 space-y-1">
          <li>Daily or weekly tracking without long forms</li>
          <li>Clear summaries you can review before each appointment</li>
          <li>More focused conversations about what changed and why</li>
        </ul>
      </article>
    </section>

    <!-- What are tics -->
    <section id="tics" class="mt-12">
      <div class="mt-12 mb-2 text-[1.2rem] font-bold text-[#0f684f] uppercase tracking-[0.08em]">What are tics?</div>

      <article class="info-card bg-white border border-[#f0d9d2] rounded-2xl overflow-hidden
                      shadow-[0_14px_36px_rgba(0,0,0,0.12)]">
        <button class="info-card-header w-full flex items-center justify-between gap-2 px-5 py-4 text-left bg-transparent border-0 cursor-pointer">
          <h2 class="m-0 text-[1.3rem] font-bold text-[#0f684f]">What are tics?</h2>
          <span class="info-card-arrow text-[1.1rem]" aria-hidden="true">▾</span>
        </button>

        <div class="info-card-body px-5 max-h-0 overflow-hidden opacity-0 transition-[max-height,opacity] duration-300">
          <div class="info-card-body-inner pt-1 pb-4">
            <p>
              Tics are sudden, brief movements or sounds that repeat and are not fully voluntary.
            </p>
            <p>
              For some people tics are mild. For others they can be frequent and intense and may
              affect school, work, or social life. Tracking when they appear helps everyone see
              patterns and progress.
            </p>
            <p class="text-[0.85rem] text-[#5b6664] mt-4">
              This portal does not replace medical advice. If you are worried about tics, please
              talk to a qualified healthcare professional.
            </p>
          </div>
        </div>
      </article>
    </section>

    <!-- Apps -->
    <section id="apps" class="mt-12">
      <div class="mt-12 mb-2 text-[1.2rem] font-bold text-[#0f684f] uppercase tracking-[0.08em]">Applications in this portal</div>

      <div class="grid gap-4 mt-4 lg:grid-cols-3">
        <article class="rounded-2xl border border-[#f0d9d2] bg-[#fffdf7] shadow-[0_4px_12px_rgba(0,0,0,0.05)] overflow-hidden">
          <div class="w-full flex items-center justify-between gap-2 px-5 py-4 text-left">
            <h3 class="m-0 text-[1.05rem] font-bold text-[#0f684f]">TicTracker web app</h3>
          </div>
          <div class="px-5 pb-4">
            <div class="pb-4">
              <p class="text-[0.85rem] text-[#5b6664]">
                Secure web interface for logging tic episodes and reviewing recent entries.
              </p>
              <ul class="list-disc pl-5 text-[0.85rem] text-[#5b6664] mt-2 space-y-1">
                <li>Record tic type, duration, and intensity</li>
                <li>Mark entries as self- or caregiver-reported</li>
                <li>See short-term trends at a glance</li>
              </ul>
            </div>
          </div>
        </article>

        <article class="rounded-2xl border border-[#f0d9d2] bg-[#fffdf7] shadow-[0_4px_12px_rgba(0,0,0,0.05)] overflow-hidden">
          <div class="w-full flex items-center justify-between gap-2 px-5 py-4 text-left">
            <h3 class="m-0 text-[1.05rem] font-bold text-[#0f684f]">For patients/caregivers</h3>
          </div>
          <div class="px-5 pb-4">
            <div class="pb-4">
              <p class="text-[0.85rem] text-[#5b6664]">
                Log what you notice between appointments and bring simple summaries to your next visit.
              </p>
              <p class="text-[0.85rem] text-[#5b6664] mt-2">
                Over time, shared logs help you and your team see what is improving and where support is still needed.
              </p>
            </div>
          </div>
        </article>

        <article class="rounded-2xl border border-[#f0d9d2] bg-[#fffdf7] shadow-[0_4px_12px_rgba(0,0,0,0.05)] overflow-hidden">
          <div class="w-full flex items-center justify-between gap-2 px-5 py-4 text-left">
            <h3 class="m-0 text-[1.05rem] font-bold text-[#0f684f]">For professionals</h3>
          </div>
          <div class="px-5 pb-4">
            <div class="pb-4">
              <p class="text-[0.85rem] text-[#5b6664]">
                Use TicTracker as a companion in your clinical work to explore patterns over time and support treatment choices.
              </p>
              <p class="text-[0.85rem] text-[#5b6664] mt-2">
                Recent logs are quick to scan before each session, so you can focus on what has changed since you last met.
              </p>
            </div>
          </div>
        </article>
      </div>
    </section>

    <!-- Testimonials -->
    <section class="mt-12">
      <div class="mt-12 mb-2 text-[1.2rem] font-bold text-[#0f684f] uppercase tracking-[0.08em]">What people say</div>

      <div class="grid gap-3 mt-4 [grid-template-columns:repeat(auto-fit,minmax(220px,1fr))]">
        <div class="bg-white rounded-xl p-3 border border-[#f1e1d6] text-[0.85rem] min-h-[120px] flex flex-col justify-between">
          <div>
            <strong class="block text-[#0f684f]">Sarah, Parent of 11-year-old</strong>
            <div class="text-[#f5b400] text-[0.9rem] mb-1" aria-hidden="true">★★★★★</div>
            "We finally see when tics spike and what might be setting them off. It's been a game changer."
          </div>
        </div>

        <div class="bg-white rounded-xl p-3 border border-[#f1e1d6] text-[0.85rem] min-h-[120px] flex flex-col justify-between">
          <div>
            <strong class="block text-[#0f684f]">Dr. Emily Chen, Clinician</strong>
            <div class="text-[#f5b400] text-[0.9rem] mb-1" aria-hidden="true">★★★★☆</div>
            "I can focus sessions on what really changed instead of trying to remember the last few weeks."
          </div>
        </div>

        <div class="bg-white rounded-xl p-3 border border-[#f1e1d6] text-[0.85rem] min-h-[120px] flex flex-col justify-between">
          <div>
            <strong class="block text-[#0f684f]">Alex, Young person with tics</strong>
            <div class="text-[#f5b400] text-[0.9rem] mb-1" aria-hidden="true">★★★★★</div>
            "It's nice to just tap and be done, rather than explaining everything every time."
          </div>
        </div>
      </div>
    </section>

    <!-- Relax videos -->
    <section id="relax-video" class="mt-12">
      <div class="mt-12 mb-2 text-[1.2rem] font-bold text-[#0f684f] uppercase tracking-[0.08em]">Online videos to relax</div>
      <p class="text-[0.95rem] text-[#5b6664] max-w-[680px]">
        These short videos are gentle background support. Use headphones if that feels more comfortable.
      </p>

      <div class="grid gap-4 mt-4 lg:grid-cols-3">
        <article class="rounded-2xl overflow-hidden border border-[#e3d8ff] bg-white shadow-[0_6px_18px_rgba(0,0,0,0.05)] flex flex-col">
          <div class="relative pb-[56.25%] bg-black">
            <iframe class="absolute inset-0 w-full h-full border-0"
                    src="https://www.youtube.com/embed/1ZYbU82GVz4"
                    title="Calm ambient music"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
          </div>
          <div class="p-3 text-[0.9rem]">Soft ambient sounds for a few minutes of calm.</div>
        </article>

        <article class="rounded-2xl overflow-hidden border border-[#e3d8ff] bg-white shadow-[0_6px_18px_rgba(0,0,0,0.05)] flex flex-col">
          <div class="relative pb-[56.25%] bg-black">
            <iframe class="absolute inset-0 w-full h-full border-0"
                    src="https://www.youtube.com/embed/BHACKCNDMW8"
                    title="Nature scenery and relaxing music"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
          </div>
          <div class="p-3 text-[0.9rem]">Nature scenes and gentle music to take a small break between tasks.</div>
        </article>

        <article class="rounded-2xl overflow-hidden border border-[#e3d8ff] bg-white shadow-[0_6px_18px_rgba(0,0,0,0.05)] flex flex-col">
          <div class="relative pb-[56.25%] bg-black">
            <iframe class="absolute inset-0 w-full h-full border-0"
                    src="https://www.youtube.com/embed/inpok4MKVLM"
                    title="10 minute guided meditation"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
          </div>
          <div class="p-3 text-[0.9rem]">A short breathing practice you can follow at your own pace.</div>
        </article>
      </div>
    </section>

    <!-- Articles -->
    <section id="articles" class="mt-12">
      <div class="mt-12 mb-2 text-[1.2rem] font-bold text-[#0f684f] uppercase tracking-[0.08em]">Articles</div>

      <div class="grid gap-4 mt-4 lg:grid-cols-3">
        <article class="rounded-2xl p-4 bg-white border border-[#f0d9d2] shadow-[0_4px_12px_rgba(0,0,0,0.04)] text-[0.9rem]">
          <h3 class="m-0 mb-1 font-bold text-[#0f684f]">Tics and school: what teachers can do</h3>
          <div class="text-[0.75rem] text-[#5b6664] mb-2">5 min read · For families &amp; schools</div>
          <p>Practical ideas for talking with teachers and making simple classroom adjustments.</p>
          <a href="https://www.choosingtherapy.com/tics-in-school-support/"
             class="inline-flex items-center gap-1 mt-2 text-[0.85rem] font-medium text-[#0a4936] no-underline hover:underline"
             target="_blank"
             rel="noopener noreferrer">
            Read article <span aria-hidden="true">→</span>
          </a>
        </article>

        <article class="rounded-2xl p-4 bg-white border border-[#f0d9d2] shadow-[0_4px_12px_rgba(0,0,0,0.04)] text-[0.9rem]">
          <h3 class="m-0 mb-1 font-bold text-[#0f684f]">Preparing for your first clinic visit</h3>
          <div class="text-[0.75rem] text-[#5b6664] mb-2">4 min read · For families</div>
          <p>A straightforward checklist so you feel less rushed and more prepared.</p>
          <a href="https://www.choosingtherapy.com/neurologist-appointment/"
             class="inline-flex items-center gap-1 mt-2 text-[0.85rem] font-medium text-[#0a4936] no-underline hover:underline"
             target="_blank"
             rel="noopener noreferrer">
            Read article <span aria-hidden="true">→</span>
          </a>
        </article>

        <article class="rounded-2xl p-4 bg-white border border-[#f0d9d2] shadow-[0_4px_12px_rgba(0,0,0,0.04)] text-[0.9rem]">
          <h3 class="m-0 mb-1 font-bold text-[#0f684f]">Habit reversal training for tics</h3>
          <div class="text-[0.75rem] text-[#5b6664] mb-2">6 min read · For professionals</div>
          <p>Overview of behavioural treatment for tics, including HRT and CBIT.</p>
          <a href="https://www.choosingtherapy.com/habit-reversal-training/"
             class="inline-flex items-center gap-1 mt-2 text-[0.85rem] font-medium text-[#0a4936] no-underline hover:underline"
             target="_blank"
             rel="noopener noreferrer">
            Read article <span aria-hidden="true">→</span>
          </a>
        </article>
      </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="mt-12">
      <div class="mt-12 mb-2 text-[1.2rem] font-bold text-[#0f684f] uppercase tracking-[0.08em]">Frequently Asked Questions</div>

      <div class="grid gap-3 mt-4">
        <article class="faq-item rounded-xl border border-[#e1e4ec] bg-white overflow-hidden shadow-[0_4px_10px_rgba(0,0,0,0.03)]">
          <button class="faq-question w-full px-5 py-3 flex items-center justify-between text-left bg-transparent border-0 cursor-pointer">
            <span>Is TicTracker a replacement for medical care?</span>
            <span class="faq-toggle text-[1.2rem] text-[#4a63d6]">+</span>
          </button>
          <div class="faq-answer max-h-0 overflow-hidden transition-[max-height] duration-300 px-5">
            <div class="faq-answer-inner pb-3 text-[0.9rem] text-[#5b6664]">
              No. TicTracker is a support tool. It works best alongside conversations with a qualified healthcare professional.
            </div>
          </div>
        </article>

        <article class="faq-item rounded-xl border border-[#e1e4ec] bg-white overflow-hidden shadow-[0_4px_10px_rgba(0,0,0,0.03)]">
          <button class="faq-question w-full px-5 py-3 flex items-center justify-between text-left bg-transparent border-0 cursor-pointer">
            <span>Who can use the TicTracker portal?</span>
            <span class="faq-toggle text-[1.2rem] text-[#4a63d6]">+</span>
          </button>
          <div class="faq-answer max-h-0 overflow-hidden transition-[max-height] duration-300 px-5">
            <div class="faq-answer-inner pb-3 text-[0.9rem] text-[#5b6664]">
              People with tics, parents or caregivers, and healthcare professionals can all use TicTracker with their own accounts.
            </div>
          </div>
        </article>

        <article class="faq-item rounded-xl border border-[#e1e4ec] bg-white overflow-hidden shadow-[0_4px_10px_rgba(0,0,0,0.03)]">
          <button class="faq-question w-full px-5 py-3 flex items-center justify-between text-left bg-transparent border-0 cursor-pointer">
            <span>Is my information secure?</span>
            <span class="faq-toggle text-[1.2rem] text-[#4a63d6]">+</span>
          </button>
          <div class="faq-answer max-h-0 overflow-hidden transition-[max-height] duration-300 px-5">
            <div class="faq-answer-inner pb-3 text-[0.9rem] text-[#5b6664]">
              Yes. Data is stored securely and only shared with clinicians or caregivers you choose to involve, according to local privacy laws.
            </div>
          </div>
        </article>

        <article class="faq-item rounded-xl border border-[#e1e4ec] bg-white overflow-hidden shadow-[0_4px_10px_rgba(0,0,0,0.03)]">
          <button class="faq-question w-full px-5 py-3 flex items-center justify-between text-left bg-transparent border-0 cursor-pointer">
            <span>Do I need to track every tic?</span>
            <span class="faq-toggle text-[1.2rem] text-[#4a63d6]">+</span>
          </button>
          <div class="faq-answer max-h-0 overflow-hidden transition-[max-height] duration-300 px-5">
            <div class="faq-answer-inner pb-3 text-[0.9rem] text-[#5b6664]">
              No. Many families and clinicians use TicTracker to record key patterns, spikes, or changes rather than every single tic.
            </div>
          </div>
        </article>
      </div>
    </section>

    <!-- Support banner -->
    <div class="mt-8 mb-3 rounded-xl bg-[#fff0e0] px-4 py-4 text-[0.95rem] text-[#5b6664]
                shadow-[0_4px_14px_rgba(0,0,0,0.06)]">
      <strong class="text-[#0f684f]">You're not alone.</strong>
      TicTracker is here to help you and your healthcare team make sense of day-to-day tic symptoms.
    </div>
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

  <!-- JS: app cards (removed since not needed anymore for this section) -->

  <!-- JS: collapsible "What are tics?" -->
  <script>
    (function () {
      const infoCard = document.querySelector('.info-card');
      if (!infoCard) return;
      const headerBtn = infoCard.querySelector('.info-card-header');
      headerBtn.addEventListener('click', () => infoCard.classList.toggle('is-open'));
    })();
  </script>

  <!-- JS: FAQ accordion -->
  <script>
    (function () {
      const faqItems = document.querySelectorAll('.faq-item');
      faqItems.forEach(item => {
        const btn = item.querySelector('.faq-question');
        btn.addEventListener('click', () => item.classList.toggle('is-open'));
      });
    })();
  </script>

  <!-- JS: Mobile nav toggle -->
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

  <!-- JS: Rotate hero background -->
  <script>
    (function () {
      const hero = document.querySelector('.portal-hero');
      if (!hero) return;

      const backgrounds = [
        'images/1.png',
        'images/2.png',
        'images/3.png',
        'images/4.png',
        'images/5.png',
        'images/6.png',
      ];

      let current = 0;

      function setBackground() {
        hero.style.backgroundImage = 'url(' + backgrounds[current] + ')';
        current = (current + 1) % backgrounds.length;
      }

      setBackground();
      setInterval(setBackground, 3000);
    })();
  </script>
</body>
</html>
