function askConfirm() {
    let isMoodSelected = false;
    const radios = document.querySelectorAll('input[name="emotion"][type="radio"]');

    if (radios.length > 0) {
        const checkedRadio = document.querySelector('input[name="emotion"]:checked');
        if (checkedRadio) isMoodSelected = true;
    } 
    else {
        const inputField = document.querySelector('input[name="emotion"]');
        if (inputField && inputField.value.trim() !== "") isMoodSelected = true;
    }

    if (!isMoodSelected) {
        alert("Please select a mood before saving.");
        return; 
    }

    openConfirm(
        "Log Mood",                   
        "Are you sure you want to save this emotional entry?", 
        "Yes, Save Mood"              
    );
}

document.getElementById('globalConfirmBtn').addEventListener('click', function() {
    document.getElementById('emotional-form').submit();
});

const anxietySlider = document.getElementById('anxiety');
const anxietyValue = document.getElementById('anxiety-value');
const stressSlider = document.getElementById('stress');
const stressValue = document.getElementById('stress-value');

if (anxietySlider) {
    anxietySlider.addEventListener('input', (event) => {
        anxietyValue.textContent = `Selected: ${event.target.value}`;
    });
}

if (stressSlider) {
    stressSlider.addEventListener('input', (event) => {
        stressValue.textContent = `Selected: ${event.target.value}`;
    });
}
