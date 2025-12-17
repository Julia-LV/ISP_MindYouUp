<div id="confirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="confirm-modal-title">Confirm Action</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="confirm-modal-message">Are you sure you want to proceed?</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                <button type="button" id="globalConfirmBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#005949] text-base font-medium text-white hover:bg-[#004539] focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Yes, Proceed
                </button>
                <button type="button" id="globalCancelBtn" onclick="closeModals()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<div id="successModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="success-modal-title">Success!</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="success-modal-message">Action completed successfully.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="footer-type-log" class="bg-gray-50 px-4 py-3 sm:px-6 hidden sm:flex-row-reverse gap-2">
                <a href="home_patient.php" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#005949] text-base font-medium text-white hover:bg-[#004539] focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Home
                </a>
                <button type="button" onclick="closeModals()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Log Another
                </button>
            </div>

            <div id="footer-type-simple" class="bg-gray-50 px-4 py-3 sm:px-6 hidden sm:flex-row-reverse">
                <button type="button" onclick="closeModals()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#005949] text-base font-medium text-white hover:bg-[#004539] focus:outline-none sm:w-auto sm:text-sm">
                    Okay
                </button>
            </div>

        </div>
    </div>
</div>

<script>
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
</script>