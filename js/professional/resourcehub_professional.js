document.addEventListener('DOMContentLoaded', function() {
    const itemType = document.getElementById('item_type');
    const catType = document.getElementById('category_type');
    const bannerFields = document.getElementById('banner_fields');
    const toggleBtn = document.getElementById('toggle_all_patients');
    const checkboxes = document.querySelectorAll('.patient-checkbox');

    function updateForm() {
        const val = itemType.value;
        
        // 1. Category logic: Enable only if "Inside a Category Box"
        if (val === 'category') {
            catType.disabled = false;
        } else {
            catType.disabled = true;
            catType.value = "";
        }

        // 2. Banner logic: Show extra banner fields (Subtitle is now always visible)
        if (val === 'banner') {
            bannerFields.classList.remove('hidden');
        } else {
            bannerFields.classList.add('hidden');
        }
    }

    // Select All / Deselect All Logic
    if(toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
            
            checkboxes.forEach(cb => {
                cb.checked = anyUnchecked;
            });

            this.textContent = anyUnchecked ? 'Deselect All' : 'Select All';
        });
    }

    itemType.addEventListener('change', updateForm);
    updateForm(); // Run on load
});
