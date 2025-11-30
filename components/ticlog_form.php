<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mb-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-emerald-900">Record a Tic</h2>
        <span class="text-xs font-semibold bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full">New Entry</span>
    </div>

    <form method="POST" action="" class="space-y-8">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="type_select" class="block text-sm font-bold text-gray-700 mb-2">Type of Tic</label>
                <div class="relative">
                    <select id="type_select" name="type_select" required 
                        class="block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-xl bg-gray-50 hover:bg-white transition-colors">
                        <option value="">Select a Tic...</option>
                        <optgroup label="Motor Tics">
                            <option>Eye blinking</option>
                            <option>Eye movements</option>
                            <option>Facial grimace</option>
                            <option>Head jerks or movements</option>
                            <option>Shoulder shrugs</option>
                            <option>Arm/Hand movements</option>
                            <option>Abdominal tensing</option>
                            <option>Leg/Foot movements</option>
                        </optgroup>
                        <optgroup label="Vocal Tics">
                            <option>Throat clearing/Coughing</option>
                            <option>Sniffing</option>
                            <option>Syllables/Words</option>
                            <option>Echolalia/Palilalia</option>
                        </optgroup>
                    </select>
                </div>
            </div>

            <div>
                <label for="muscle_select" class="block text-sm font-bold text-gray-700 mb-2">Muscle Group</label>
                <select id="muscle_select" name="muscle_select" 
                    class="block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm rounded-xl bg-gray-50">
                    <option value="">-- Optional --</option>
                    <option>Orbicularis oculi (eyes)</option>
                    <option>Facial muscles</option>
                    <option>Neck muscles</option>
                    <option>Shoulders / Upper trapezius</option>
                    <option>Arms / Hands</option>
                    <option>Abdominal muscles</option>
                    <option>Legs / Feet</option>
                    <option>Laryngeal muscles</option>
                </select>
            </div>
        </div>

        <hr class="border-gray-100">

        <div class="space-y-6">
            <div>
                <div class="flex justify-between mb-2">
                    <label class="font-bold text-gray-700">Intensity</label>
                    <span class="text-emerald-600 font-bold bg-emerald-50 px-2 rounded" id="intensityValue">0/10</span>
                </div>
                <input type="range" id="intensity" name="intensity" min="0" max="10" value="0" 
                       class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                       oninput="document.getElementById('intensityValue').innerText=this.value + '/10';">
                <div class="flex justify-between text-xs text-gray-400 mt-1">
                    <span>Mild</span>
                    <span>Moderate</span>
                    <span>Severe</span>
                </div>
            </div>

            <div>
                <label for="duration" class="block text-sm font-bold text-gray-700 mb-2">Duration</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="cursor-pointer">
                        <input type="radio" name="duration" value="Less than a minute" class="peer sr-only" required>
                        <div class="rounded-xl border border-gray-200 p-3 text-center hover:bg-gray-50 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 transition-all">
                            <span class="text-sm font-medium">< 1 Minute</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="duration" value="More than a minute" class="peer sr-only">
                        <div class="rounded-xl border border-gray-200 p-3 text-center hover:bg-gray-50 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 transition-all">
                            <span class="text-sm font-medium">> 1 Minute</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <hr class="border-gray-100">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
             <div>
                <div class="flex justify-between mb-2">
                    <label class="font-bold text-gray-700">Pain Level</label>
                    <span id="painValue" class="text-gray-500 text-sm">0</span>
                </div>
                <input type="range" id="pain_meter" name="pain_meter" min="0" max="5" value="0" 
                       oninput="document.getElementById('painValue').innerText=this.value;">
                <div class="flex justify-between text-xs text-gray-400 mt-1">
                    <span>No Pain</span>
                    <span>Severe</span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-3">Did you feel it coming?</label>
                <div class="flex space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="pre_tic" value="yes" class="form-radio text-emerald-600 h-5 w-5" checked>
                        <span class="ml-2 text-gray-700">Yes</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="pre_tic" value="no" class="form-radio text-pink-500 h-5 w-5">
                        <span class="ml-2 text-gray-700">No</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="pt-4 bg-gray-50 -mx-6 -mb-6 md:-mx-8 md:-mb-8 p-6 md:p-8 rounded-b-2xl border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
            
            <div class="flex items-center bg-gray-200 rounded-lg p-1">
                <button type="button" id="btn_self" onclick="setReporter('patient')" 
                    class="px-4 py-2 rounded-md text-sm font-bold shadow-sm transition-all bg-white text-emerald-800">
                    Self
                </button>
                <button type="button" id="btn_caregiver" onclick="setReporter('caregiver')" 
                    class="px-4 py-2 rounded-md text-sm font-bold text-gray-500 transition-all hover:text-gray-700">
                    Caregiver
                </button>
                <input type="hidden" name="self_reported" id="self_reported" value="patient">
            </div>

            <button type="submit" class="w-full md:w-auto px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-200 transition-all transform hover:-translate-y-0.5">
                Save Log Entry
            </button>
        </div>
    </form>
</div>

<div class="bg-gradient-to-r from-teal-500 to-emerald-600 rounded-2xl shadow-lg p-6 text-white flex flex-col md:flex-row justify-between items-center">
    <div>
        <h3 class="text-xl font-bold">Good Day?</h3>
        <p class="text-emerald-100 text-sm">If you haven't experienced any tics today, log it here.</p>
    </div>
    <form method="POST" class="mt-4 md:mt-0">
        <button type="submit" name="no_tics" class="bg-white text-emerald-700 px-6 py-2 rounded-lg font-bold shadow hover:bg-gray-50 transition-colors">
            📅 Log "No Tics"
        </button>
    </form>
</div>