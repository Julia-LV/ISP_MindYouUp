// --- 1. GLOBAL VARIABLES & HELPERS ---
let formToSubmit = null; 

function closeModals() {
    document.getElementById('confirmModal').classList.add('hidden');
    document.getElementById('successModal').classList.add('hidden');
    
    // Clean URL params cleanly
    const url = new URL(window.location);
    url.searchParams.delete('status');
    url.searchParams.delete('msg');
    window.history.replaceState({}, '', url);
}

// Helper to determine which buttons to show based on the page name
function getPageContext() {
    const path = window.location.pathname;
    // If we are on a logging page, return 'log'
    if (path.includes('ticlog') || path.includes('diary') || path.includes('journal')) {
        return 'log';
    }
    // Otherwise (patients, professionals, profile, etc.) return 'simple'
    return 'simple';
}

function openConfirm(title, message, confirmBtnText, cancelBtnText) {
    document.getElementById('confirm-modal-title').innerText = title || "Confirm Action";
    document.getElementById('confirm-modal-message').innerText = message || "Are you sure?";
    document.getElementById('globalConfirmBtn').innerText = confirmBtnText || "Yes, Proceed";
    
    const cancelBtn = document.getElementById('globalCancelBtn');
    if(cancelBtn) cancelBtn.innerText = cancelBtnText || "Cancel";

    document.getElementById('confirmModal').classList.remove('hidden');
}

// Updated openSuccess to be smart about buttons
function openSuccess(title, message, forcedType = null) {
    if(title) document.getElementById('success-modal-title').innerText = title;
    if(message) document.getElementById('success-modal-message').innerText = message;
    
    // Determine type: Use forcedType if provided, otherwise guess based on page URL
    const type = forcedType || getPageContext();

    const footerLog = document.getElementById('footer-type-log');
    const footerSimple = document.getElementById('footer-type-simple');

    if(type === 'log') {
        footerLog.classList.remove('hidden');
        footerLog.classList.add('sm:flex'); 
        footerSimple.classList.add('hidden');
        footerSimple.classList.remove('sm:flex');
    } else {
        // Default to Simple (Okay button)
        footerSimple.classList.remove('hidden');
        footerSimple.classList.add('sm:flex');
        footerLog.classList.add('hidden');
        footerLog.classList.remove('sm:flex');
    }

    document.getElementById('successModal').classList.remove('hidden');
}

// --- 2. ACTION FUNCTIONS ---

// A. For Deleting
function confirmDelete(formId, itemName) {
    formToSubmit = document.getElementById(formId);
    openConfirm(
        'Remove Connection?', 
        'Are you sure you want to remove ' + itemName + ' from your list?', 
        'Yes, Remove'
    );
}

// B. For Adding / Requesting (UPDATED TEXT)
function confirmAdd(formId, itemName) {
    formToSubmit = document.getElementById(formId);
    openConfirm(
        'Send Request?', 
        'Do you want to send a connection request to ' + itemName + '?', 
        'Yes, Add'
    );
}

// --- 3. EVENT LISTENERS ---

document.getElementById('globalConfirmBtn').addEventListener('click', function() {
    if(formToSubmit) {
        formToSubmit.submit(); 
    } else {
        closeModals(); 
    }
});

// Check URL for Success Messages on Page Load
window.addEventListener('DOMContentLoaded', (event) => {
    const urlParams = new URLSearchParams(window.location.search);
    
    if(urlParams.get('status') === 'success') {
        const msgCode = urlParams.get('msg');
        let msg = "Action completed successfully.";
        
        if(msgCode === 'deleted') msg = "The contact has been removed.";
        else if(msgCode === 'added') msg = "Request sent successfully!";
        else if(msgCode === 'saved') msg = "Profile saved successfully!";
        
        // We don't need to pass a type here; openSuccess will detect the page URL
        openSuccess('Success!', msg);
    }
});
