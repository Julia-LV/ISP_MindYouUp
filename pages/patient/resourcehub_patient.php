<?php
session_start();
require_once __DIR__ . '/../../config.php';

/* ---------- AUTH GUARD only logged-in patients ---------- */
if (empty($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../auth/login.php");
    exit;
}

$role = $_SESSION['role'] ?? '';
if (strtolower($role) !== 'patient') {
    header("Location: ../professional/home_professional.php");
    exit;
}

$currentPatientId = $_SESSION['user_id'] ?? 0;

/* ---------- FIND LINKED PROFESSIONAL FOR THIS PATIENT ---------- */
$currentProfessionalId = 0;
if ($currentPatientId) {
    $sql = "SELECT Professional_ID FROM patient_profile WHERE User_ID = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $currentPatientId);
        $stmt->execute();
        $stmt->bind_result($profId);
        if ($stmt->fetch()) {
            $currentProfessionalId = (int)$profId;
        }
        $stmt->close();
    }
}

/* ---------- HELPERS ---------- */
function build_media_paths(?string $stored): array {
    if (!$stored) {
        return [false, null, null];
    }
    $stored = trim($stored);
    if ($stored === '') {
        return [false, null, null];
    }

    if (str_starts_with($stored, 'uploads/')) {
        $rel = $stored;
    } else {
        $rel = 'uploads/' . ltrim($stored, '/');
    }

    // From pages/patient - project root web is "../../"
    $web    = '../../' . $rel;
    $fs     = __DIR__ . '/../../' . $rel;
    $exists = file_exists($fs);

    return [$exists, $web, $fs];
}

/* ---------- FETCH STRATEGIES (ONLY FROM THIS PROFESSIONAL) ---------- */
$strategies = [];
if ($currentPatientId && $currentProfessionalId) {
    $sql = "SELECT rh.*
            FROM patient_resources pr
            JOIN resource_hub rh ON pr.resource_id = rh.id
            WHERE pr.patient_id = ?
              AND pr.sent_by = ?
              AND rh.item_type = 'strategy'
            ORDER BY rh.sort_order, rh.id";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('ii', $currentPatientId, $currentProfessionalId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $strategies[] = $row;
        }
        $stmt->close();
    }
}

/* ---------- FETCH SKILLS (ONLY FROM THIS PROFESSIONAL) ---------- */
$skills = [];
if ($currentPatientId && $currentProfessionalId) {
    $sql = "SELECT rh.*
            FROM patient_resources pr
            JOIN resource_hub rh ON pr.resource_id = rh.id
            WHERE pr.patient_id = ?
              AND pr.sent_by = ?
              AND rh.item_type = 'skill'
            ORDER BY rh.sort_order, rh.id";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('ii', $currentPatientId, $currentProfessionalId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $skills[] = $row;
        }
        $stmt->close();
    }
}

/* ---------- FETCH ARTICLES (ONLY FROM THIS PROFESSIONAL) ---------- */
$articles = [];
if ($currentPatientId && $currentProfessionalId) {
    $sql = "SELECT rh.*
            FROM patient_resources pr
            JOIN resource_hub rh ON pr.resource_id = rh.id
            WHERE pr.patient_id = ?
              AND pr.sent_by = ?
              AND rh.item_type = 'article'
            ORDER BY rh.sort_order, rh.id";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('ii', $currentPatientId, $currentProfessionalId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $articles[] = $row;
        }
        $stmt->close();
    }
}

/* ---------- STRATEGY NAVIGATION ---------- */
$currentStrategy = null;
$prevId = null;
$nextId = null;
$mediaExists = false;
$mediaUrlWeb = null;

if (!empty($strategies)) {
    $currentId = isset($_GET['strategy_id']) ? (int)$_GET['strategy_id'] : 0;

    if ($currentId === 0) {
        $currentStrategy = $strategies[0];
    } else {
        foreach ($strategies as $s) {
            if ((int)$s['id'] === $currentId) {
                $currentStrategy = $s;
                break;
            }
        }
        if ($currentStrategy === null) {
            $currentStrategy = $strategies[0];
        }
    }

    $ids   = array_column($strategies, 'id');
    $index = array_search((int)$currentStrategy['id'], array_map('intval', $ids), true);
    if ($index === false) {
        $index = 0;
    }

    $prevIndex = ($index === 0) ? count($strategies) - 1 : $index - 1;
    $nextIndex = ($index === count($strategies) - 1) ? 0 : $index + 1;

    $prevId = $strategies[$prevIndex]['id'];
    $nextId = $strategies[$nextIndex]['id'];

    [$mediaExists, $mediaUrlWeb] = build_media_paths($currentStrategy['media_url'] ?? null);
}

/* ---------- PAGE SETUP ---------- */
$page_title = 'Resource Hub';
$body_class = 'h-full bg-gray-100';
$no_layout  = false;

include __DIR__ . '/../../components/header_component.php';
include __DIR__ . '/../../includes/navbar.php';
?>

<!-- Inline styles (skills carousel + desktop wrapping fix) -->
<style>
/* Desktop + mobile: make skills pills wrap cleanly */
.skills-strip .skill-pill {
  display: inline-block;
  max-width: 240px;
  white-space: normal;
  overflow-wrap: anywhere;  /* allow breaks inside long words/URLs */
  hyphens: auto;            /* add hyphenation where supported */
  line-height: 1.3;
}

/* Mobile carousel */
@media (max-width: 768px) {
  .skills-strip {
    position: relative;
    margin-top: 4px;
  }
  .skills-strip::before,
  .skills-strip::after {
    content: '';
    position: absolute;
    top: 0; bottom: 0; width: 28px;
    pointer-events: none;
    z-index: 3;
  }
  .skills-strip::before {
    left: 0;
    background: linear-gradient(to right, #ffffff, rgba(255,255,255,0));
  }
  .skills-strip::after {
    right: 0;
    background: linear-gradient(to left, #ffffff, rgba(255,255,255,0));
  }

  .skills-strip .card-row {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding: 4px 32px 8px;  /* arrow gutter */
    scroll-behavior: smooth;
    -ms-overflow-style: none;
    scrollbar-width: none;
  }
  .skills-strip .card-row::-webkit-scrollbar { display: none; }

  /* two items visible */
  .skills-strip .card-row > * {
    flex: 0 0 calc(50% - 10px);
    min-width: 0;
  }

  .skills-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 26px; height: 26px;
    border-radius: 999px;
    border: 1px solid rgba(0,0,0,0.06);
    background: #ffffff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.16);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; color: #374151;
    cursor: pointer; z-index: 4;
  }
  .skills-nav--left { left: 6px; }
  .skills-nav--right { right: 6px; }
}
</style>

<main class="flex-1 w-full p-6 md:p-8 overflow-y-auto bg-[#E9F0E9]">
  <div class="p-6 md:p-8 space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-3xl font-bold text-[#005949]">Resource Hub</h2>
        <p class="mt-1 text-sm text-[#6b7280]">
          Your personalised strategies, skills, and articles from your professional.
        </p>
      </div>
      <!-- <button
        type="button"
        onclick="confirmLogout()"
        class="px-4 py-2 rounded-full bg-[#005949] text-white text-sm shadow hover:bg-[#00453f] transition"
      >
        Log out
      </button> -->
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,2.1fr)_minmax(0,1.5fr)] gap-5 lg:gap-6 items-start">
      <!-- LEFT: Strategy + Skills -->
      <div class="space-y-4">
        <!-- Daily Strategy -->
        <div class="bg-white rounded-2xl border border-[#f0e3cc] shadow-[0_10px_28px_rgba(0,0,0,0.07)] p-5 min-h-[150px]">
          <div class="flex items-baseline justify-between mb-3">
            <?php if ($currentStrategy): ?>
              <p class="text-xs text-[#6b7280]">Use this exercise to calm down</p>
            <?php endif; ?>
          </div>

          <?php if ($currentStrategy): ?>
            <?php
            [$sMediaExists, $sMediaUrlWeb] = build_media_paths($currentStrategy['media_url'] ?? null);
            $strategyUrl = null;
            if ($sMediaExists && $sMediaUrlWeb) {
                $strategyUrl = $sMediaUrlWeb;
            } else {
                $rawStrategyContent = $currentStrategy['content'] ?? '';
                if (preg_match('/https?:\/\/\S+/i', $rawStrategyContent, $m)) {
                    $strategyUrl = $m[0];
                }
            }
            $strategyHasLink = !empty($strategyUrl);
            ?>
            <div class="grid grid-cols-[auto_1fr_auto] gap-2 items-center">
              <a href="?strategy_id=<?php echo (int)$prevId; ?>"
                 class="w-8 h-8 rounded-full border border-[#e2d7c1] bg-white flex items-center justify-center text-[15px] text-[#867a5a] hover:bg-[#f9f5eb]"
                 aria-label="Previous strategy">&#8249;</a>

              <div class="flex items-center justify-between gap-3">
                <div>
                  <?php if (!empty($currentStrategy['subtitle'])): ?>
                    <p class="text-[11px] uppercase tracking-[0.06em] text-[#F26647] mb-1">
                      <?php echo htmlspecialchars($currentStrategy['subtitle']); ?>
                    </p>
                  <?php endif; ?>
                  <p class="text-[18px] font-semibold text-[#111827]">
                    <?php echo htmlspecialchars($currentStrategy['title'] ?? ''); ?>
                  </p>
                </div>

                <?php if ($strategyHasLink): ?>
                  <a href="<?php echo htmlspecialchars($strategyUrl); ?>" target="_blank"
                     class="w-11 h-11 rounded-full bg-[#005949] text-white flex items-center justify-center text-[21px] shadow-[0_7px_16px_rgba(0,0,0,0.18)] hover:bg-[#00453f]"
                     aria-label="Open strategy">&#9654;</a>
                <?php else: ?>
                  <button type="button"
                          class="w-11 h-11 rounded-full bg-[#d4d7cf] text-white flex items-center justify-center text-[21px] cursor-default"
                          aria-disabled="true">&#9654;</button>
                <?php endif; ?>
              </div>

              <a href="?strategy_id=<?php echo (int)$nextId; ?>"
                 class="w-8 h-8 rounded-full border border-[#e2d7c1] bg-white flex items-center justify-center text-[15px] text-[#867a5a] hover:bg-[#f9f5eb]"
                 aria-label="Next strategy">&#8250;</a>
            </div>
          <?php else: ?>
            <p class="text-sm text-[#6b7280]">No strategies available yet.</p>
          <?php endif; ?>
        </div>

        <!-- Skills with mobile carousel -->
        <div class="bg-white rounded-2xl border border-[#f0e3cc] shadow-[0_10px_28px_rgba(0,0,0,0.07)] p-4">
          <h2 class="text-sm font-semibold mb-2 text-[#231f20]">Categories</h2>
          <?php if (!empty($skills)): ?>
            <div class="skills-strip">
              <button type="button" class="skills-nav skills-nav--left md:hidden" aria-label="Previous skills">&#8249;</button>

              <div class="card-row flex gap-3 overflow-x-auto pb-2 md:overflow-visible md:pb-0">
                <?php foreach ($skills as $skill): ?>
                  <?php
                  [$sMediaExists2, $sMediaUrlWeb2] = build_media_paths($skill['media_url'] ?? null);
                  $skillUrl = null;
                  if ($sMediaExists2 && $sMediaUrlWeb2) {
                      $skillUrl = $sMediaUrlWeb2;
                  } else {
                      $rawSkillContent = $skill['content'] ?? '';
                      if (preg_match('/https?:\/\/\S+/i', $rawSkillContent, $m)) {
                          $skillUrl = $m[0];
                      }
                  }
                  $skillHasLink = !empty($skillUrl);
                  ?>
                  <?php if ($skillHasLink): ?>
                    <a href="<?php echo htmlspecialchars($skillUrl); ?>" target="_blank"
                       class="skill-pill bg-white rounded-full border border-[#f0e3cc] px-4 py-2 text-[13px] text-[#111827] hover:bg-[#f5f5f5]"
                       title="Open skill resource">
                      <?php echo htmlspecialchars($skill['title'] ?? ''); ?>
                    </a>
                  <?php else: ?>
                    <div class="skill-pill bg-white rounded-full border border-[#f0e3cc] px-4 py-2 text-[13px] text-[#6b7280]">
                      <?php echo htmlspecialchars($skill['title'] ?? ''); ?>
                    </div>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>

              <button type="button" class="skills-nav skills-nav--right md:hidden" aria-label="Next skills">&#8250;</button>
            </div>
          <?php else: ?>
            <p class="text-sm text-[#6b7280]">No skills added yet.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- RIGHT: Articles -->
      <div>
        <div class="bg-white rounded-2xl border border-[#f0e3cc] shadow-[0_10px_28px_rgba(0,0,0,0.07)] p-4">
          <h2 class="text-sm font-semibold mb-2 text-[#231f20]">Articles &amp; Guides</h2>
          <?php if (!empty($articles)): ?>
            <div class="flex flex-col gap-2">
              <?php foreach ($articles as $article): ?>
                <?php
                [$aMediaExists, $aMediaUrlWeb] = build_media_paths($article['media_url'] ?? null);
                $url = null;
                if ($aMediaExists && $aMediaUrlWeb) {
                    $url = $aMediaUrlWeb;
                } else {
                    $rawContent = $article['content'] ?? '';
                    if (preg_match('/https?:\/\/\S+/i', $rawContent, $m)) {
                        $url = $m[0];
                    }
                }
                $hasLink = !empty($url);
                ?>
                <?php if ($hasLink): ?>
                  <a href="<?php echo htmlspecialchars($url); ?>" target="_blank"
                     class="flex items-center gap-3 bg-white rounded-xl border border-[#f0e3cc] px-3 py-2 no-underline hover:bg-[#f9fafb]">
                    <div class="w-8 h-8 rounded-full bg-[#e6f3ec] flex-shrink-0 relative">
                      <div class="absolute inset-2 rounded-full border-2 border-[#c7e4d7]"></div>
                    </div>
                    <p class="m-0 text-[13px] text-[#111827] whitespace-nowrap overflow-hidden text-ellipsis">
                      <?php echo htmlspecialchars($article['title'] ?? ''); ?>
                    </p>
                    <span class="ml-auto text-[16px] text-[#bcae8c] flex-shrink-0">&#8250;</span>
                  </a>
                <?php else: ?>
                  <div class="flex items-center gap-3 bg-white rounded-xl border border-[#f0e3cc] px-3 py-2 opacity-65">
                    <div class="w-8 h-8 rounded-full bg-[#e6f3ec] flex-shrink-0 relative">
                      <div class="absolute inset-2 rounded-full border-2 border-[#c7e4d7]"></div>
                    </div>
                    <p class="m-0 text-[13px] text-[#6b7280] whitespace-nowrap overflow-hidden text-ellipsis">
                      <?php echo htmlspecialchars($article['title'] ?? ''); ?>
                    </p>
                    <span class="ml-auto text-[16px] text-[#bcae8c] flex-shrink-0">&#8250;</span>
                  </div>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="text-sm text-[#6b7280]">No articles yet.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

</div>

<?php
if (file_exists(__DIR__ . '/../../components/modals.php')) {
    include __DIR__ . '/../../components/modals.php';
}
?>

<script>
function confirmLogout() {
  if (confirm('Are you sure you want to log out?')) {
    window.location.href = '../auth/logout.php';
  }
}

document.addEventListener('DOMContentLoaded', function () {
  const row  = document.querySelector('.skills-strip .card-row');
  if (!row) return;

  const prev = document.querySelector('.skills-nav--left');
  const next = document.querySelector('.skills-nav--right');

  function getStep() {
    const item = row.children[0];
    return item ? item.offsetWidth + 12 : 160; // width + gap
  }

  prev && prev.addEventListener('click', function () {
    row.scrollBy({ left: -getStep(), behavior: 'smooth' });
  });

  next && next.addEventListener('click', function () {
    row.scrollBy({ left: getStep(), behavior: 'smooth' });
  });
});
</script>
