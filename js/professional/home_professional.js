lucide.createIcons();
const selectedPatientId = window.homeProfessionalData?.selectedPatientId || 0;

// --- MODAL LOGIC ---
async function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    if (id === 'modal-tics') await fetchAllData('fetch_all_tics', 'table-body-tics', ['Formatted_Date', 'Type', 'Type_Description', 'Intensity', 'Describe_Text']);
    else if (id === 'modal-emotions') await fetchAllData('fetch_all_emotions', 'table-body-emotions', ['Formatted_Date', 'Emotion', 'Stress', 'Anxiety', 'Sleep', 'Notes']);
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

async function fetchAllData(action, tbodyId, fields) {
    const tbody = document.getElementById(tbodyId);
    try {
        const res = await fetch(`?ajax_fetch=1&action=${action}&patient_id=${selectedPatientId}`);
        const json = await res.json();
        tbody.innerHTML = '';
        if (json.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="${fields.length}" class="p-4 text-center text-gray-400">No records found.</td></tr>`;
            return;
        }
        json.data.forEach(row => {
            let tr = `<tr class="hover:bg-gray-50">`;
            fields.forEach(f => {
                tr += `<td class="p-3 border-b border-gray-50">${row[f] || '-'}</td>`;
            });
            tr += `</tr>`;
            tbody.innerHTML += tr;
        });
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="${fields.length}" class="p-4 text-center text-red-500">Error loading data.</td></tr>`;
    }
}

// --- CHART INITIALIZATION ---
const initialLabels = window.homeProfessionalData?.labels || [];
const initialSeverity = window.homeProfessionalData?.severity || [];
const initialStress = window.homeProfessionalData?.stress || [];
const motorCount = window.homeProfessionalData?.motorCount || 0;
const vocalCount = window.homeProfessionalData?.vocalCount || 0;

const ctxCombo = document.getElementById('doctorComboChart').getContext('2d');
const comboChart = new Chart(ctxCombo, {
    type: 'bar',
    data: {
        labels: initialLabels,
        datasets: [{
            type: 'line',
            label: 'Avg Stress',
            data: initialStress,
            borderColor: '#fb923c',
            borderWidth: 2,
            borderDash: [5, 5],
            pointRadius: 4,
            tension: 0.3,
            yAxisID: 'y'
        }, {
            type: 'bar',
            label: 'Max Intensity',
            data: initialSeverity,
            backgroundColor: '#005949',
            borderRadius: 4,
            barThickness: 24,
            yAxisID: 'y'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false
        },
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                }
            },
            y: {
                min: 0,
                max: 10,
                grid: {
                    borderDash: [4, 4]
                }
            }
        }
    }
});

const ctxDoughnut = document.getElementById('typeDoughnutChart').getContext('2d');
new Chart(ctxDoughnut, {
    type: 'doughnut',
    data: {
        labels: ['Motor', 'Vocal'],
        datasets: [{
            data: [motorCount, vocalCount],
            backgroundColor: ['#005949', '#F8BED3'],
            borderWidth: 0,
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        cutout: '75%'
    }
});

const chartSleep = new Chart(document.getElementById('chart-sleep'), {
    type: 'line',
    data: {
        labels: [],
        datasets: []
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                suggestedMax: 10
            }
        }
    }
});

const chartMulti = new Chart(document.getElementById('chart-multi'), {
    type: 'bar',
    data: {
        labels: [],
        datasets: []
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false
        },
        scales: {
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    boxWidth: 10,
                    font: {
                        size: 10
                    }
                }
            }
        }
    }
});

const chartHourly = new Chart(document.getElementById('chart-hourly'), {
    type: 'line',
    data: {
        labels: [...Array(24).keys()].map(h => h + ":00"),
        datasets: []
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                grid: {
                    display: false
                }
            },
            y: {
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

const chartMoods = new Chart(document.getElementById('chart-moods'), {
    type: 'pie',
    data: {
        labels: [],
        datasets: []
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    boxWidth: 10,
                    font: {
                        size: 11
                    }
                }
            }
        }
    }
});

const chartMuscles = new Chart(document.getElementById('chart-muscles'), {
    type: 'polarArea',
    data: {
        labels: [],
        datasets: []
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            r: {
                ticks: {
                    display: false
                }
            }
        },
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    boxWidth: 10
                }
            }
        }
    }
});

// --- DYNAMIC GRAPH LOADING ---
const offsets = {
    main: 0,
    sleep: 0,
    multi: 0,
    hourly: 0,
    moods: 0,
    muscles: 0
};

async function loadGraph(type) {
    const offset = offsets[type];
    const rangeEl = document.getElementById(`range-${type}`);
    if (rangeEl) rangeEl.textContent = "...";
    try {
        const res = await fetch(`?ajax_fetch=1&graph=${type}&offset=${offset}&patient_id=${selectedPatientId}`);
        const data = await res.json();
        if (rangeEl && data.range) rangeEl.textContent = data.range;
        if (type === 'main') {
            comboChart.data.labels = data.labels;
            comboChart.data.datasets[0].data = data.stress;
            comboChart.data.datasets[1].data = data.severity;
            comboChart.update();
        } else if (type === 'sleep') {
            chartSleep.data.labels = data.labels;
            chartSleep.data.datasets = [{
                label: 'Sleep Quality',
                data: data.data.sleep,
                borderColor: '#F282A9',
                backgroundColor: '#F282A9',
                tension: 0.4
            }, {
                label: 'Tic Count',
                data: data.data.frequency,
                borderColor: '#005949',
                borderDash: [5, 5],
                tension: 0.4
            }];
            chartSleep.update();
        } else if (type === 'multi') {
            chartMulti.data.labels = data.labels;
            chartMulti.data.datasets = [{
                label: 'Stress',
                data: data.data.stress,
                backgroundColor: '#fb923c'
            }, {
                label: 'Anxiety',
                data: data.data.anxiety,
                backgroundColor: '#60a5fa'
            }, {
                label: 'Sleep',
                data: data.data.sleep,
                backgroundColor: '#F282A9'
            }, {
                label: 'Intensity',
                data: data.data.intensity,
                backgroundColor: '#FFEBB3'
            }, {
                label: 'Tic Count',
                data: data.data.frequency,
                backgroundColor: '#005949'
            }];
            chartMulti.update();
        } else if (type === 'hourly') {
            chartHourly.data.datasets = [{
                label: 'Tics',
                data: data.data,
                fill: true,
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                borderColor: '#3B82F6',
                tension: 0.4
            }];
            chartHourly.update();
        } else if (type === 'moods') {
            chartMoods.data.labels = data.data.labels;
            chartMoods.data.datasets = [{
                data: data.data.values,
                backgroundColor: ['#F282A9', '#FFEBB3', '#005949', '#fb923c', '#C4B5FD'],
                borderWidth: 1
            }];
            chartMoods.update();
        } else if (type === 'muscles') {
            chartMuscles.data.labels = data.data.labels;
            chartMuscles.data.datasets = [{
                data: data.data.values,
                backgroundColor: ['#005949', '#26A69A', '#4DB6AC', '#80CBC4', '#B2DFDB', '#E0F2F1']
            }];
            chartMuscles.update();
        }
    } catch (e) {
        console.error(`Error loading ${type}:`, e);
    }
}

function changeOffset(type, delta) {
    offsets[type] += delta;
    if (offsets[type] < 0) offsets[type] = 0;
    loadGraph(type);
}

// Initial Load
['sleep', 'multi', 'hourly', 'moods', 'muscles'].forEach(t => loadGraph(t));

// --- PDF EXPORT LOGIC ---
function exportPDF() {
    const original = document.getElementById('report-container');
    
    // 1. Create a hidden wrapper
    const wrapper = document.createElement('div');
    wrapper.style.position = 'absolute';
    wrapper.style.left = '-9999px';
    wrapper.style.top = '0';
    wrapper.style.width = '800px'; 
    document.body.appendChild(wrapper);

    // 2. Clone the dashboard
    const clone = original.cloneNode(true);
    clone.classList.add('pdf-export-container');
    
    // Ensure the header is visible
    const header = clone.querySelector('#pdf-header');
    if(header) header.classList.remove('hidden');

    wrapper.appendChild(clone);

    // 3. Fix Charts & Layout
    const originalCanvases = original.querySelectorAll('canvas');
    const clonedCanvases = clone.querySelectorAll('canvas');
    
    originalCanvases.forEach((canvas, index) => {
        const img = document.createElement('img');
        img.src = canvas.toDataURL('image/png', 1.0);
        
        const target = clonedCanvases[index];
        if (target) {
            // Check if this is the doughnut chart (based on its ID or container)
            if (target.id === 'typeDoughnutChart' || target.closest('.h-48')) {
                img.style.width = '200px'; // Keep doughnut small
                img.style.margin = '0 auto';
                target.parentNode.classList.add('doughnut-wrapper');
            } else {
                img.style.width = '100%'; // Full width for line/bar charts
            }
            
            img.style.height = 'auto';
            img.style.display = 'block';
            
            // Remove the fixed-height container constraints from the PDF clone
            const parent = target.parentNode;
            parent.style.height = 'auto';
            parent.style.minHeight = '0';
            parent.replaceChild(img, target);
        }
    });

    // 4. Force Tables to stay visible
    clone.querySelectorAll('.overflow-x-auto, .overflow-y-auto').forEach(el => {
        el.style.overflow = 'visible';
        el.style.height = 'auto';
        el.style.maxHeight = 'none';
        el.style.display = 'block';
    });

    // Remove buttons and interactive elements
    clone.querySelectorAll('button, .print\\:hidden, .print-hide').forEach(el => el.remove());

    // 5. PDF Options
    const patientName = window.homeProfessionalData?.patientName || 'Report';
    const opt = {
        margin: [0.3, 0.3],
        filename: `Report_${patientName}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { 
            scale: 2, 
            useCORS: true, 
            logging: false,
            width: 800
        },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' },
        pagebreak: { mode: ['css', 'legacy'], avoid: '.bg-white' }
    };

    // 6. Save and Remove Clone
    html2pdf().set(opt).from(clone).save().then(() => {
        document.body.removeChild(wrapper);
    });
}
