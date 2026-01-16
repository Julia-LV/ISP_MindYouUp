lucide.createIcons();

// --- 1. INITIAL SETUP ---
const initData = window.emotionalDiaryData || {};

// Trend Chart
const ctxTrend = document.getElementById('trendChart').getContext('2d');
const trendChart = new Chart(ctxTrend, {
    type: 'line',
    data: {
        labels: initData.dates,
        datasets: [
            { label: 'Stress', data: initData.stress, borderColor: '#f97316', backgroundColor: 'rgba(249, 115, 22, 0.1)', borderWidth: 2, tension: 0.4, fill: true, pointRadius: 4 },
            { label: 'Anxiety', data: initData.anxiety, borderColor: '#a855f7', backgroundColor: 'rgba(168, 85, 247, 0.05)', borderWidth: 2, tension: 0.4, fill: true, pointRadius: 4, borderDash: [5, 5] }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 10 }, x: { grid: { display: false } } }, plugins: { legend: { position: 'top' } } }
});

// Sleep Chart
const ctxSleep = document.getElementById('sleepChart').getContext('2d');
const sleepChart = new Chart(ctxSleep, {
    type: 'bar',
    data: {
        labels: initData.dates,
        datasets: [{ label: 'Sleep Quality', data: initData.sleep, backgroundColor: '#3b82f6', borderRadius: 4 }]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: false, 
        scales: { 
            y: { beginAtZero: true, max: 10 }, 
            x: { 
                grid: { display: false },
                ticks: {
                    maxRotation: 0, // <--- PREVENTS TILT
                    minRotation: 0, // <--- PREVENTS TILT
                    autoSkip: false, // <--- SHOWS ALL DAYS (Does not hide Mon/Wed etc)
                    font: { size: 10 } // Smaller font to fit full week
                }
            } 
        }, 
        plugins: { legend: { display: false } } 
    }
});

// --- Mood Chart (Doughnut) ---
const ctxMood = document.getElementById('moodChart').getContext('2d');
const moodChart = new Chart(ctxMood, {
    type: 'doughnut',
    data: {
        labels: window.emotionalDiaryMoodLabels || [],
        datasets: [{
            data: window.emotionalDiaryMoodData || [],
            backgroundColor: ['#fcd34d', '#60a5fa', '#f87171', '#c084fc', '#4ade80', '#94a3b8', '#fbbf24', '#f87171'],
            borderWidth: 0
        }]
    },
    options: { 
        responsive: true, 
        maintainAspectRatio: false, 
        plugins: { 
            legend: { 
                position: 'bottom', 
                // Adjusted to handle potentially longer text descriptions
                labels: { boxWidth: 10, font: {size: 10} } 
            } 
        } 
    }
});

// --- 2. INDEPENDENT NAVIGATION LOGIC ---
const offsets = { trend: 0, mood: 0, sleep: 0 };

function changeOffset(type, amount) {
    offsets[type] += amount;
    if (offsets[type] < 0) offsets[type] = 0;
    
    const nextBtn = document.getElementById(`btnNext_${type}`);
    if(nextBtn) nextBtn.disabled = (offsets[type] === 0);

    fetchData(type, offsets[type]);
}

async function fetchData(type, offset) {
    try {
        const url = `emotional_diary_visuals.php?ajax_fetch=1&type=${type}&offset=${offset}`;
        const response = await fetch(url);
        const data = await response.json();
        
        if (type === 'trend') {
            trendChart.data.labels = data.labels;
            trendChart.data.datasets[0].data = data.data1;
            trendChart.data.datasets[1].data = data.data2;
            trendChart.update();
        } 
        else if (type === 'sleep') {
            sleepChart.data.labels = data.labels;
            sleepChart.data.datasets[0].data = data.data1;
            sleepChart.update();
        } 
        else if (type === 'mood') {
            moodChart.data.labels = data.labels;
            moodChart.data.datasets[0].data = data.data;
            moodChart.update();
            
            const rangeText = document.getElementById('moodDateRange');
            if(rangeText && data.date_range) rangeText.textContent = data.date_range;

            const msg = document.getElementById('noMoodData');
            if (data.data.length === 0) {
                msg.classList.remove('hidden');
                document.getElementById('moodChart').classList.add('opacity-30');
            } else {
                msg.classList.add('hidden');
                document.getElementById('moodChart').classList.remove('opacity-30');
            }
        }

    } catch (error) {
        console.error("Error fetching chart data:", error);
    }
}

// Format timestamps to local date and time
document.querySelectorAll('.date-display').forEach(el => {
    const timestamp = parseInt(el.getAttribute('data-timestamp')) * 1000;
    const date = new Date(timestamp);
    el.textContent = date.toLocaleDateString('en-US', { day: '2-digit', month: 'short' });
});

document.querySelectorAll('.time-display').forEach(el => {
    const timestamp = parseInt(el.getAttribute('data-timestamp')) * 1000;
    const date = new Date(timestamp);
    el.textContent = date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
});
