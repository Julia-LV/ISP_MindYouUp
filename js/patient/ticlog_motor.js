// 1. Data Source 
const ticData = {
    "Simple motor tics": ["Eye blinking", "Eye movements", "Nose movements", "Mouth movements", "Facial grimace", "Head jerks/movements", "Shoulder shrugs", "Arm movements", "Hand movements", "Abdominal tensing", "Leg/foot/toe movements"],
    "Complex motor tics": ["Eye movements", "Mouth movements", "Facial expressions", "Head gestures", "Shoulder movements", "Arm/Hand movements", "Writing tics", "Dystonic postures", "Bending/Gyrating", "Rotating", "Blocking", "Compulsive behaviors", "Copropraxia", "Self-abusive behavior"],
    "Simple vocal tics": ["Sounds/Noises (coughing, throat clearing, sniffing, animal noises)"],
    "Complex phonic symptoms": ["Syllables", "Words", "Coprolalia", "Echolalia", "Palilalia", "Blocking", "Disinhibited speech"]
};

const autoMap = {
    "Eye blinking": "Orbicularis oculi (eyes)",
    "Eye movements": "Orbicularis oculi (eyes)",
    "Nose movements": "Facial muscles",
    "Mouth movements": "Facial muscles",
    "Facial grimace": "Facial muscles",
    "Head jerks/movements": "Neck muscles",
    "Shoulder shrugs": "Shoulders / Upper trapezius",
    "Abdominal tensing": "Abdominal muscles",
    "Leg/foot/toe movements": "Legs / Feet"
};

// 2. Tab Switching Logic
function switchTab(type) {
    document.getElementById('active_context').value = type;
    const activeClass = 'active font-semibold text-[#005949] border-b-2 border-[#005949]';
    const inactiveClass = 'font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent';

    const btnMotor = document.getElementById('tab-btn-motor');
    const btnVocal = document.getElementById('tab-btn-vocal');
    const paneMotor = document.getElementById('pane-motor');
    const paneVocal = document.getElementById('pane-vocal');

    if (type === 'motor') {
        btnMotor.className = "flex-1 py-3 text-center focus:outline-none transition-colors " + activeClass;
        btnVocal.className = "flex-1 py-3 text-center focus:outline-none transition-colors " + inactiveClass;
        paneMotor.style.display = 'block';
        paneVocal.style.display = 'none';
    } else {
        btnVocal.className = "flex-1 py-3 text-center focus:outline-none transition-colors " + activeClass;
        btnMotor.className = "flex-1 py-3 text-center focus:outline-none transition-colors " + inactiveClass;
        paneVocal.style.display = 'block';
        paneMotor.style.display = 'none';
    }
}

// 3. Dynamic Dropdowns
function setupDropdowns(catID, specID, isMotor) {
    const catSelect = document.getElementById(catID);
    const specSelect = document.getElementById(specID);
    const muscleSelect = document.getElementById('muscle_select');

    catSelect.addEventListener('change', function() {
        const val = this.value;
        specSelect.innerHTML = '<option value="">-- Select Tic --</option>';
        if (val && ticData[val]) {
            specSelect.disabled = false;
            ticData[val].forEach(tic => {
                specSelect.add(new Option(tic, tic));
            });
        } else {
            specSelect.disabled = true;
        }
    });

    if (isMotor) {
        specSelect.addEventListener('change', function() {
            const t = this.value;
            if (autoMap[t]) muscleSelect.value = autoMap[t];
        });
    }
}

setupDropdowns('motor_cat', 'motor_spec', true);
setupDropdowns('vocal_cat', 'vocal_spec', false);

// 4. Update Slider Text
['intensity', 'stress'].forEach(id => {
    const el = document.getElementById(id);
    const val = document.getElementById(id + '-value');
    if (el && val) {
        el.addEventListener('input', (e) => val.textContent = `Selected: ${e.target.value}`);
    }
});

// 5. Reporter Toggle
function setReporter(role, btn) {
    document.getElementById('self_reported').value = role;
    document.querySelectorAll('.rep-btn').forEach(b => {
        b.className = 'rep-btn w-1/2 py-2 rounded text-gray-500 text-sm font-medium transition-all hover:text-gray-700';
    });
    btn.className = 'rep-btn w-1/2 py-2 rounded bg-white shadow-sm text-[#005949] text-sm font-bold transition-all';
}

// 6. CONFIRM & SUBMIT LOGIC
function askConfirm(type) {
    if (type === 'no_tics') {
        // Case A: No Tics
        formToSubmit = document.getElementById('form-no-tics');
        openConfirm(
            "Log Good Day?",
            "This will record that you had NO tics today. Are you sure?",
            "Yes, Log it"
        );
    } else {
        // Case B: Standard Entry
        // 1. Populate Hidden Inputs
        const context = document.getElementById('active_context').value;
        const finalCat = document.getElementById('final_tic_category');
        const finalSpec = document.getElementById('final_specific_tic');
        const muscleInput = document.getElementById('muscle_select');

        if (context === 'motor') {
            finalCat.value = document.getElementById('motor_cat').value;
            finalSpec.value = document.getElementById('motor_spec').value;
        } else {
            finalCat.value = document.getElementById('vocal_cat').value;
            finalSpec.value = document.getElementById('vocal_spec').value;
            // Important: Clear muscle if saving a vocal tic to avoid confusion
            muscleInput.value = "";
        }

        // 2. Validate
        const cat = finalCat.value;
        const spec = finalSpec.value;
        const duration = document.querySelector('select[name="duration"]').value;

        if (!cat || !spec || !duration) {
            alert("Please select Tic Type, Specific Tic, AND Duration before saving.");
            return;
        }

        // 3. Set Form Global and Open Modal
        formToSubmit = document.getElementById('form-main');
        openConfirm(
            "Save Entry?",
            "Are you sure you want to log this tic?",
            "Yes, Save"
        );
    }
}
