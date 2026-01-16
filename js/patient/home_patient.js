lucide.createIcons();

// --- INITIAL DATA ---
const initialDates = window.homePatientData?.dates || []; 
const initialTics = window.homePatientData?.tics || [];
const initialStress = window.homePatientData?.stress || [];
const initialIntensity = window.homePatientData?.intensity || [];
const initialSleep = window.homePatientData?.sleep || [];
const initialAnxiety = window.homePatientData?.anxiety || [];

// Dynamic Charts Data
const muscleLabels = window.homePatientData?.muscleLabels || [];
const muscleData = window.homePatientData?.muscleData || [];
const hourlyData = window.homePatientData?.hourlyData || [];
const hourlyLabels = ["12AM","1AM","2AM","3AM","4AM","5AM","6AM","7AM","8AM","9AM","10AM","11AM","12PM","1PM","2PM","3PM","4PM","5PM","6PM","7PM","8PM","9PM","10PM","11PM"];

// =========================================================
// CHART INITIALIZATION
// =========================================================

const freqChart = new Chart(document.getElementById('ticFrequencyChart'), {
    type: 'line',
    data: {
        labels: initialDates,
        datasets: [{ label: 'Total Tics', data: initialTics, borderColor: '#005949', backgroundColor: 'rgba(0, 89, 73, 0.1)', borderWidth: 2, tension: 0.4, fill: true, pointRadius: 4 }]
    },
    options: { 
        responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, 
        scales: { x: { grid: { display: false }, ticks: { font: { size: 10 } } }, y: { beginAtZero: true, suggestedMax: 5, ticks: { stepSize: 1, precision: 0 } } } 
    }
});

const correlationChart = new Chart(document.getElementById('correlationChart'), {
    type: 'bar',
    data: {
        labels: initialDates, 
        datasets: [
            { type: 'line', label: 'Stress', data: initialStress, borderColor: '#fb923c', borderWidth: 2, borderDash: [5,5], pointRadius: 0, tension: 0.4 },
            { type: 'bar', label: 'Avg Intensity', data: initialIntensity, backgroundColor: '#2dd4bf', borderRadius: 4, barThickness: 16 }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
        scales: { x: { grid: { display: false }, ticks: { font: { size: 10 } } }, y: { display: true, min: 0, max: 10, ticks: { stepSize: 1 } } }
    }
});

const sleepChart = new Chart(document.getElementById('sleepDualChart'), {
    type: 'line',
    data: {
        labels: initialDates, 
        datasets: [
            { label: 'Sleep', data: initialSleep, borderColor: '#fb923c', backgroundColor: '#fb923c', yAxisID: 'y', tension: 0.3, borderWidth: 2 },
            { label: 'Anxiety', data: initialAnxiety, borderColor: '#F282A9', backgroundColor: '#F282A9', yAxisID: 'y', tension: 0.3, borderWidth: 2 },
            { label: 'Tics', data: initialTics, borderColor: '#005949', backgroundColor: '#005949', yAxisID: 'y1', tension: 0.3, borderWidth: 2, borderDash: [2, 2] }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, 
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 } } },
            y: { type: 'linear', display: false, position: 'left', min: 0, max: 10 },
            y1: { type: 'linear', display: false, position: 'right', grid: { display: false }, beginAtZero: true, suggestedMax: 5, ticks: { precision: 0 } }
        }
    }
});

new Chart(document.getElementById('musclePieChart'), { 
    type: 'pie', 
    data: { 
        labels: muscleLabels, 
        datasets: [{ 
            data: muscleData, 
            backgroundColor: ['#005949', '#F282A9', '#F26647', '#fcd34d', '#94a3b8'], 
            borderWidth: 1 
        }] 
    }, 
    options: { 
        responsive: true, 
        maintainAspectRatio: false, 
        plugins: { 
            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } 
        } 
    } 
});

new Chart(document.getElementById('hourlyAreaChart'), { 
    type: 'line', 
    data: { 
        labels: hourlyLabels, 
        datasets: [{ 
            label: 'Today\'s Activity', 
            data: hourlyData, 
            borderColor: '#2dd4bf', 
            backgroundColor: 'rgba(45, 212, 191, 0.2)', 
            borderWidth: 2, 
            tension: 0.4, 
            fill: true, 
            pointRadius: 0 
        }] 
    }, 
    options: { 
        responsive: true, 
        maintainAspectRatio: false, 
        plugins: { legend: { display: false } }, 
        scales: { x: { grid: { display: false }, ticks: { maxTicksLimit: 6 } }, y: { display: false, beginAtZero: true } } 
    } 
});


// =========================================================
// INDEPENDENT GRAPH NAVIGATION LOGIC
// =========================================================

let offTics = 0;
let offStress = 0;
let offSleep = 0;

async function fetchData(offset) {
    const response = await fetch(`home_patient.php?ajax_fetch=1&offset=${offset}`);
    return await response.json();
}

// 1. TIC FREQUENCY CONTROLS
const btnPrevTics = document.getElementById('btnPrevTics');
const btnNextTics = document.getElementById('btnNextTics');
btnNextTics.disabled = true;

btnPrevTics.addEventListener('click', async () => {
    offTics += 7;
    const data = await fetchData(offTics);
    freqChart.data.labels = data.labels;
    freqChart.data.datasets[0].data = data.tics;
    freqChart.update();
    btnNextTics.disabled = false;
});

btnNextTics.addEventListener('click', async () => {
    if (offTics >= 7) {
        offTics -= 7;
        const data = await fetchData(offTics);
        freqChart.data.labels = data.labels;
        freqChart.data.datasets[0].data = data.tics;
        freqChart.update();
    }
    if (offTics === 0) btnNextTics.disabled = true;
});


// 2. STRESS CONTROLS
const btnPrevStress = document.getElementById('btnPrevStress');
const btnNextStress = document.getElementById('btnNextStress');
btnNextStress.disabled = true;

btnPrevStress.addEventListener('click', async () => {
    offStress += 7;
    const data = await fetchData(offStress);
    correlationChart.data.labels = data.labels;
    correlationChart.data.datasets[0].data = data.stress; 
    correlationChart.data.datasets[1].data = data.intensity; 
    correlationChart.update();
    btnNextStress.disabled = false;
});

btnNextStress.addEventListener('click', async () => {
    if (offStress >= 7) {
        offStress -= 7;
        const data = await fetchData(offStress);
        correlationChart.data.labels = data.labels;
        correlationChart.data.datasets[0].data = data.stress;
        correlationChart.data.datasets[1].data = data.intensity;
        correlationChart.update();
    }
    if (offStress === 0) btnNextStress.disabled = true;
});


// 3. SLEEP CONTROLS
const btnPrevSleep = document.getElementById('btnPrevSleep');
const btnNextSleep = document.getElementById('btnNextSleep');
btnNextSleep.disabled = true;

btnPrevSleep.addEventListener('click', async () => {
    offSleep += 7;
    const data = await fetchData(offSleep);
    sleepChart.data.labels = data.labels;
    sleepChart.data.datasets[0].data = data.sleep;
    sleepChart.data.datasets[1].data = data.anxiety;
    sleepChart.data.datasets[2].data = data.tics;
    sleepChart.update();
    btnNextSleep.disabled = false;
});

btnNextSleep.addEventListener('click', async () => {
    if (offSleep >= 7) {
        offSleep -= 7;
        const data = await fetchData(offSleep);
        sleepChart.data.labels = data.labels;
        sleepChart.data.datasets[0].data = data.sleep;
        sleepChart.data.datasets[1].data = data.anxiety;
        sleepChart.data.datasets[2].data = data.tics;
        sleepChart.update();
    }
    if (offSleep === 0) btnNextSleep.disabled = true;
});

// Format timestamps to local date and time
document.querySelectorAll('.date-display').forEach(el => {
    const timestamp = parseInt(el.getAttribute('data-timestamp')) * 1000;
    const date = new Date(timestamp);
    el.textContent = date.toLocaleDateString('en-US', { day: '2-digit', month: 'short' });
});

document.querySelectorAll('.time-display').forEach(el => {
    const timestamp = parseInt(el.getAttribute('data-timestamp')) * 1000;
    const date = new Date(timestamp);
    el.textContent = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
});

// =========================================================
// DYNAMIC PATTERN CARD LOGIC
// =========================================================

/**
 * Ensures icons inside dynamic components are rendered
 * and handles any specific interactions for the Pattern Card
 */
function initializePatternCard() {
    // Refresh Lucide icons in case the card was rendered after initial load
    if (window.lucide) {
        window.lucide.createIcons();
    }

    const patternBtn = document.querySelector('a[href*="resourcehub_patient.php"]');
    if (patternBtn) {
        patternBtn.addEventListener('click', (e) => {
            // Optional: Log that the user followed a suggestion
            console.log("User navigating to suggested resource: " + patternBtn.href);
            
            // If you want a smooth preloader transition (from your preloader.php)
            const loader = document.getElementById('preloader');
            if (loader) {
                loader.classList.remove('preloader-hidden');
            }
        });
    }
}

// Call it on load
document.addEventListener('DOMContentLoaded', initializePatternCard);
