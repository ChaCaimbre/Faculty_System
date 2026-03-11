// Auth State Management
async function checkAuth() {
    startClock(); // Start immediately so user sees "life"
    try {
        const res = await fetch('api/auth.php?action=check');
        const data = await res.json();
        if (data.authenticated) {
            document.body.classList.add('logged-in');
            document.body.classList.remove('logged-out');

            // Load components as Promises to avoid blocking
            loadCounts();
            showSection('home');
        } else {
            document.body.classList.add('logged-out');
            document.body.classList.remove('logged-in');
        }
    } catch (e) {
        console.error("Auth check failed", e);
    }
}

// Forgot Password UI Toggle
document.querySelector('.forgot-link')?.addEventListener('click', () => {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('forgotPasswordForm').style.display = 'block';
    const formTitle = document.getElementById('formTitle');
    if(formTitle) formTitle.textContent = 'Reset Password';
});

document.querySelector('.back-to-login')?.addEventListener('click', () => {
    document.getElementById('forgotPasswordForm').style.display = 'none';
    document.getElementById('loginForm').style.display = 'block';
    const formTitle = document.getElementById('formTitle');
    if(formTitle) formTitle.textContent = 'Login';
    document.getElementById('forgotError').style.display = 'none';
    document.getElementById('forgotSuccess').style.display = 'none';
});

// Reset Password Submission
document.getElementById('forgotPasswordForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fpUsername = document.getElementById('fp_username').value;
    const fpNewPassword = document.getElementById('fp_new_password').value;
    const fpConfirmPassword = document.getElementById('fp_confirm_password').value;
    const errObj = document.getElementById('forgotError');
    const succObj = document.getElementById('forgotSuccess');
    
    errObj.style.display = 'none';
    succObj.style.display = 'none';

    if (fpNewPassword !== fpConfirmPassword) {
        errObj.textContent = "Passwords do not match.";
        errObj.style.display = 'block';
        return;
    }

    if (fpNewPassword.length < 5) {
        errObj.textContent = "New password forms must be at least 5 characters.";
        errObj.style.display = 'block';
        return;
    }

    try {
        const res = await fetch('api/auth.php?action=reset_password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username: fpUsername, new_password: fpNewPassword })
        });

        const data = await res.json();
        if (data.success) {
            succObj.textContent = "Password reset successfully. You can now login.";
            succObj.style.display = 'block';
            document.getElementById('forgotPasswordForm').reset();
        } else {
            errObj.textContent = data.message || "Failed to reset password.";
            errObj.style.display = 'block';
        }
    } catch(err) {
        errObj.textContent = "Network error. Please try again.";
        errObj.style.display = 'block';
    }
});

// Login
document.getElementById('loginForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    const res = await fetch('api/auth.php?action=login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
    });

    const data = await res.json();
    if (data.success) {
        checkAuth();
    } else {
        const err = document.getElementById('loginError');
        err.textContent = data.message;
        err.style.display = 'block';
    }
});

async function logout() {
    await fetch('api/auth.php?action=logout');
    checkAuth();
}

// Nav Dropdown Toggle
function toggleNavDropdown() {
    const menu = document.getElementById('navDropdownMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

function toggleMobileMenu() {
    const links = document.getElementById('navLinks');
    const overlay = document.getElementById('drawerOverlay');
    links.classList.toggle('mobile-active');
    if (overlay) {
        overlay.classList.toggle('active');
    }
}

function openSettings() {
    toggleNavDropdown();
    alert('Settings coming soon!');
}

// Close dropdown when clicking outside
document.addEventListener('click', function (e) {
    const dropdown = document.querySelector('.nav-dropdown');
    const menu = document.getElementById('navDropdownMenu');
    if (dropdown && menu && !dropdown.contains(e.target)) {
        menu.style.display = 'none';
    }
});

// Modal Logic
let currentSection = 'faculty';
let editingFacultyId = null;
let editingScheduleId = null;

function animateCard(el) {
    if (el) {
        el.classList.add('clicked');
        setTimeout(() => el.classList.remove('clicked'), 400);
    }
}

function openModal(section, options = {}) {
    currentSection = section;
    const overlay = document.getElementById('modalOverlay');
    const title = document.getElementById('modalTitle');
    const fields = document.getElementById('modalFields');
    const form = document.getElementById('modalForm');

    const errBox = document.getElementById('modalError');
    if (errBox) errBox.style.display = 'none';

    // Restore footer buttons
    form.querySelector('div[style*="display: flex"]').style.display = 'flex';

    const sectionText = section.charAt(0).toUpperCase() + section.slice(1);
    // Default title
    title.innerHTML = `${sectionText.replace('Schedules', 'Schedule')} Management`;
    title.style.color = '#fbbf24';
    title.style.fontSize = '1.6rem';
    fields.innerHTML = '';
    overlay.style.display = 'flex';

    if (section === 'faculty') {
        const isRowEdit = !!options.scheduleId;
        const nameVal = options.name || '';
        const statusVal = options.status || 'Full-time';
        const campusVal = options.campus || 'Main Campus';
        const subjectCodeVal = options.subjectCode || '';
        const subjectNameVal = options.subjectName || '';
        const sectionsVal = options.sections || '';

        if (isRowEdit) {
            editingFacultyId = options.id || null;
            editingScheduleId = options.scheduleId || null;
            title.innerHTML = 'Edit Teacher Assignment';
            fields.innerHTML = `
                <div class="form-group">
                    <label>Teacher(s) Name</label>
                    <input type="text" id="m_name" value="${nameVal}" required>
                </div>
                <div class="form-group">
                    <label>Employment Status</label>
                    <select id="m_status" required>
                        <option value="Full-time" ${statusVal === 'Full-time' ? 'selected' : ''}>Full-time</option>
                        <option value="Part-Time" ${statusVal === 'Part-Time' ? 'selected' : ''}>Part-Time</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Designated Campus</label>
                    <select id="m_campus" required>
                        <option value="Main Campus" ${campusVal === 'Main Campus' ? 'selected' : ''}>Main Campus</option>
                        <option value="Youngfield" ${campusVal === 'Youngfield' ? 'selected' : ''}>Youngfield</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Subject Code</label>
                    <input type="text" id="m_subject_code" value="${subjectCodeVal}" placeholder="e.g. IT-101">
                </div>
                <div class="form-group">
                    <label>Subject Name</label>
                    <input type="text" id="m_subject_name" value="${subjectNameVal}" placeholder="e.g. Programming 1">
                </div>
                <div class="form-group">
                    <label>Section(s)</label>
                    <input type="text" id="m_sections" value="${sectionsVal}" placeholder="e.g. AI23, AI33">
                </div>
            `;
        } else if (options.id) {
            // Edit mode for teacher WITH NO schedules
            editingFacultyId = options.id;
            editingScheduleId = null;
            title.innerHTML = 'Edit Teacher';
            fields.innerHTML = `
                <div class="form-group">
                    <label>Teacher(s) Name</label>
                    <input type="text" id="m_name" value="${nameVal}" required>
                </div>
                <div class="form-group">
                    <label>Employment Status</label>
                    <select id="m_status" required>
                        <option value="Full-time" ${statusVal === 'Full-time' ? 'selected' : ''}>Full-time</option>
                        <option value="Part-Time" ${statusVal === 'Part-Time' ? 'selected' : ''}>Part-Time</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Designated Campus</label>
                    <select id="m_campus" required>
                        <option value="Main Campus" ${campusVal === 'Main Campus' ? 'selected' : ''}>Main Campus</option>
                        <option value="Youngfield" ${campusVal === 'Youngfield' ? 'selected' : ''}>Youngfield</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Add Subject (Optional)</label>
                    <input type="text" id="m_subject" placeholder="e.g. IT-101">
                </div>
                <div class="form-group">
                    <label>Section (Optional)</label>
                    <input type="text" id="m_section" placeholder="e.g. AI23">
                </div>
            `;
        } else {
            // Add mode
            editingFacultyId = null;
            editingScheduleId = null;
            fields.innerHTML = `
                <div class="form-group">
                    <label>Teacher(s) Name</label>
                    <input type="text" id="m_name" placeholder="e.g. Micheline G. Apolinar" required>
                </div>
                <div class="form-group">
                    <label>Employment Status</label>
                    <select id="m_status" required>
                        <option value="Full-time">Full-time</option>
                        <option value="Part-Time">Part-Time</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Designated Campus</label>
                    <select id="m_campus" required>
                        <option value="Main Campus">Main Campus</option>
                        <option value="Youngfield">Youngfield</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Add Subject (Optional)</label>
                    <input type="text" id="m_subject" placeholder="e.g. IT-101">
                </div>
            `;
        }
    } else if (section === 'subjects') {
        fields.innerHTML = `
            <div class="form-group">
                <label>Subject Code</label>
                <input type="text" id="m_code" placeholder="e.g. IT-101" required>
            </div>
            <div class="form-group">
                <label>Subject Name</label>
                <input type="text" id="m_name" placeholder="e.g. Programming 1" required>
            </div>
            <div class="form-group">
                <label>Units</label>
                <input type="number" id="m_units" value="3" required>
            </div>
        `;
    } else if (section === 'rooms') {
        fields.innerHTML = `
            <div class="form-group">
                <label>Room Name</label>
                <input type="text" id="m_name" placeholder="e.g. COMLAB 10" required>
            </div>
            <div class="form-group">
                <label>Capacity</label>
                <input type="number" id="m_capacity" value="40" required>
            </div>
            <div class="form-group">
                <label>Type</label>
                <input type="text" id="m_type" placeholder="e.g. Laboratory" required>
            </div>
        `;
    } else if (section === 'schedules') {
        fields.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 1rem;">Loading dropdown data...</div>';

        Promise.all([
            fetch('api/rooms.php', { cache: 'no-store' }).then(r => r.json()),
            fetch('api/subjects.php', { cache: 'no-store' }).then(r => r.json()),
            fetch('api/faculty.php', { cache: 'no-store' }).then(r => r.json())
        ]).then(([rooms, subjects, faculty]) => {
            // Save global data for warning checks
            window.cachedRooms = rooms;
            window.cachedFaculty = faculty;

            // Sort: COMLAB/COMPLAB 1-7 first, then other rooms alphabetically
            rooms.sort((a, b) => {
                const aName = a.name.toUpperCase();
                const bName = b.name.toUpperCase();
                const aIsLab = aName.startsWith('COMLAB') || aName.startsWith('COMPLAB');
                const bIsLab = bName.startsWith('COMLAB') || bName.startsWith('COMPLAB');
                if (aIsLab && !bIsLab) return -1;
                if (!aIsLab && bIsLab) return 1;
                return aName.localeCompare(bName, undefined, { numeric: true, sensitivity: 'base' });
            });

            fields.innerHTML = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group" id="roomGroup">
                        <label>Name Room</label>
                        <select id="m_room_id" onchange="toggleTypedInput('room'); updateCampusWarning();" required>
                            <option value="">Select Room</option>
                            ${rooms.map(r => `<option value="${r.id}">${r.name}</option>`).join('')}
                            <option value="other" style="color: #4f46e5; font-weight: 800;">+ Add Room</option>
                        </select>

                        <div id="roomTypedInputs" style="display: none; margin-top: 6px; border: 2px dashed #fbbf24; padding: 8px; border-radius: 10px; background: rgba(30, 27, 75, 0.05);">
                            <input type="text" id="m_room_name" placeholder="e.g. COMLAB 10" style="font-size: 0.85rem;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Day</label>
                        <select id="m_day" required>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="subjectGroup">
                    <label>Subject</label>
                    <select id="m_subject_id" onchange="toggleTypedInput('subject')" required>
                        <option value="">Select Subject</option>
                        ${subjects.map(s => `<option value="${s.id}">${s.code} - ${s.name}</option>`).join('')}
                        <option value="other" style="color: #4f46e5; font-weight: 800;">+ Add New Subject</option>
                    </select>
                    <div id="subjectTypedInputs" style="display: none; margin-top: 6px; border: 2px dashed #fbbf24; padding: 8px; border-radius: 10px; background: rgba(30, 27, 75, 0.05);">
                        <input type="text" id="m_subject_code" placeholder="Code (e.g. IT-101)" style="margin-bottom: 5px; font-size: 0.85rem;">
                        <input type="text" id="m_subject_name" placeholder="Subject Name" style="font-size: 0.85rem;">
                    </div>
                </div>

                <div class="form-group" id="facultyGroup">
                    <label>Teacher</label>
                    <select id="m_faculty_id" onchange="toggleTypedInput('faculty'); updateCampusWarning();" required>
                        <option value="">Select Teacher</option>
                        ${faculty.map(f => `<option value="${f.id}">${f.name}</option>`).join('')}
                        <option value="other" style="color: #4f46e5; font-weight: 800;">+ Type New Teacher</option>
                    </select>
                    <div id="facultyTypedInputs" style="display: none; margin-top: 6px; border: 2px dashed #fbbf24; padding: 8px; border-radius: 10px; background: rgba(30, 27, 75, 0.05);">
                        <input type="text" id="m_faculty_name" placeholder="Enter Teacher Name" style="font-size: 0.85rem;">
                    </div>
                </div>

                <!-- Campus Warning Message -->
                <div id="campusWarning" style="display: none; padding: 10px; margin-top: 5px; border-radius: 8px; background-color: #fff7ed; border: 1px solid #fdba74; color: #9a3412; font-size: 0.8rem; font-weight: 600; align-items: flex-start; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <span id="campusWarningText"></span>
                </div>

                <div style="margin-top: 10px;">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 0.5rem;">
                        <div class="form-group">
                            <label>Start Time</label>
                            <input type="hidden" id="m_start">
                            <div id="startTimePicker" style="display: flex; align-items: center; gap: 5px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px;">
                                <select id="m_start_hr" style="background: transparent; border: none; padding: 2px; font-size: 0.95rem; font-weight: 600; color: #1e1b4b; cursor: pointer; width: 40px; text-align: center; appearance: none;">
                                    ${[...Array(12)].map((_, i) => `<option value="${i + 1}">${String(i + 1).padStart(2, '0')}</option>`).join('')}
                                </select>
                                <span style="font-weight:700; color:#64748b; font-size: 1rem;">:</span>
                                <select id="m_start_min" style="background: transparent; border: none; padding: 2px; font-size: 0.95rem; font-weight: 600; color: #1e1b4b; cursor: pointer; width: 40px; text-align: center; appearance: none;">
                                    ${[...Array(60)].map((_, i) => `<option value="${i}">${String(i).padStart(2, '0')}</option>`).join('')}
                                </select>
                                <select id="m_start_ampm" style="background: #e2e8f0; border: none; padding: 4px 8px; font-size: 0.8rem; font-weight: 700; color: #1e1b4b; cursor: pointer; border-radius: 6px; appearance: none; text-align: center; margin-left: 2px;">
                                    <option value="AM">AM</option>
                                    <option value="PM">PM</option>
                                </select>
                                <button type="button" onclick="confirmTime('start')" style="margin-left: auto; padding: 4px 12px; background: #fbbf24; color: #000; border: none; border-radius: 6px; font-weight: 700; font-size: 0.8rem; cursor: pointer; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">OK</button>
                            </div>
                            <div id="startTimeDisplay" style="display: none; margin-top: 4px; font-size: 0.9rem; font-weight: 600; color: #1e1b4b; padding: 8px 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; text-align: center; cursor: pointer;" onclick="document.getElementById('startTimePicker').style.display='flex'; this.style.display='none';"></div>
                        </div>
                        <div class="form-group">
                            <label>End Time</label>
                            <input type="hidden" id="m_end">
                            <div id="endTimePicker" style="display: flex; align-items: center; gap: 5px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px;">
                                <select id="m_end_hr" style="background: transparent; border: none; padding: 2px; font-size: 0.95rem; font-weight: 600; color: #1e1b4b; cursor: pointer; width: 40px; text-align: center; appearance: none;">
                                    ${[...Array(12)].map((_, i) => `<option value="${i + 1}">${String(i + 1).padStart(2, '0')}</option>`).join('')}
                                </select>
                                <span style="font-weight:700; color:#64748b; font-size: 1rem;">:</span>
                                <select id="m_end_min" style="background: transparent; border: none; padding: 2px; font-size: 0.95rem; font-weight: 600; color: #1e1b4b; cursor: pointer; width: 40px; text-align: center; appearance: none;">
                                    ${[...Array(60)].map((_, i) => `<option value="${i}">${String(i).padStart(2, '0')}</option>`).join('')}
                                </select>
                                <select id="m_end_ampm" style="background: #e2e8f0; border: none; padding: 4px 8px; font-size: 0.8rem; font-weight: 700; color: #1e1b4b; cursor: pointer; border-radius: 6px; appearance: none; text-align: center; margin-left: 2px;">
                                    <option value="AM">AM</option>
                                    <option value="PM">PM</option>
                                </select>
                                <button type="button" onclick="confirmTime('end')" style="margin-left: auto; padding: 4px 12px; background: #fbbf24; color: #000; border: none; border-radius: 6px; font-weight: 700; font-size: 0.8rem; cursor: pointer; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">OK</button>
                            </div>
                            <div id="endTimeDisplay" style="display: none; margin-top: 4px; font-size: 0.9rem; font-weight: 600; color: #1e1b4b; padding: 8px 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; text-align: center; cursor: pointer;" onclick="document.getElementById('endTimePicker').style.display='flex'; this.style.display='none';"></div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Section</label>
                    <input type="text" id="m_section" placeholder="e.g. AI32">
                </div>
            `;
        });
    }
}

function confirmTime(type) {
    const prefix = type === 'start' ? 'm_start' : 'm_end';
    let hr = parseInt(document.getElementById(prefix + '_hr').value);
    const min = parseInt(document.getElementById(prefix + '_min').value);
    const ampm = document.getElementById(prefix + '_ampm').value;

    // Convert to 24hr for the hidden input
    let hr24 = hr;
    if (ampm === 'AM' && hr === 12) hr24 = 0;
    else if (ampm === 'PM' && hr !== 12) hr24 = hr + 12;

    const timeValue = String(hr24).padStart(2, '0') + ':' + String(min).padStart(2, '0');
    document.getElementById('m_' + type).value = timeValue;

    // Show display and hide picker
    const displayText = String(hr).padStart(2, '0') + ':' + String(min).padStart(2, '0') + ' ' + ampm;
    const pickerEl = document.getElementById(type + 'TimePicker');
    const displayEl = document.getElementById(type + 'TimeDisplay');
    displayEl.textContent = '✓ ' + displayText;
    displayEl.style.display = 'block';
    pickerEl.style.display = 'none';
}

function toggleTypedInput(type) {
    const select = document.getElementById(`m_${type}_id`);
    if (type === 'faculty') {
        const container = document.getElementById('facultyTypedInputs');
        container.style.display = select.value === 'other' ? 'block' : 'none';
        if (select.value === 'other') document.getElementById('m_faculty_name').focus();
    } else if (type === 'subject') {
        const container = document.getElementById('subjectTypedInputs');
        container.style.display = select.value === 'other' ? 'block' : 'none';
        if (select.value === 'other') document.getElementById('m_subject_code').focus();
    } else if (type === 'room') {
        const container = document.getElementById('roomTypedInputs');
        container.style.display = select.value === 'other' ? 'block' : 'none';
        if (select.value === 'other') document.getElementById('m_room_name').focus();
    }
}

function updateCampusWarning() {
    const facultySelect = document.getElementById('m_faculty_id');
    const roomSelect = document.getElementById('m_room_id');
    const warningDiv = document.getElementById('campusWarning');
    const warningText = document.getElementById('campusWarningText');
    
    if (!facultySelect || !roomSelect || !warningDiv || !warningText) return;

    const facultyId = facultySelect.value;
    const roomId = roomSelect.value;

    if (!facultyId || facultyId === 'other' || !roomId || roomId === 'other') {
        warningDiv.style.display = 'none';
        return;
    }

    const faculty = window.cachedFaculty?.find(f => f.id == facultyId);
    const room = window.cachedRooms?.find(r => r.id == roomId);

    if (faculty && room) {
        // Assume COMLAB/COMPLAB/Main rooms are "Main Campus", Young Field is explicitly "Youngfield"
        // This logic can be refined if rooms table has a 'location' column (currently it has 'type')
        // Based on user prompt "Main Campus or Youngfield"
        const teacherCampus = faculty.designated_campus || 'Main Campus';
        const roomName = room.name.toUpperCase();
        
        // Simple logic: If room contains "YF" or "YOUNG", it's Young Field. Otherwise Main.
        const roomCampus = (roomName.includes('YF') || roomName.includes('YOUNG')) ? 'Youngfield' : 'Main Campus';

        if (teacherCampus.toLowerCase().trim() !== roomCampus.toLowerCase().trim()) {
            warningText.textContent = `Warning: ${faculty.name} is designated for ${teacherCampus}, but ${room.name} is in the ${roomCampus}.`;
            warningDiv.style.display = 'flex';
        } else {
            warningDiv.style.display = 'none';
        }
    } else {
        warningDiv.style.display = 'none';
    }
}

function closeModal() {
    document.getElementById('modalOverlay').style.display = 'none';
}

window.onclick = function (event) {
    const overlay = document.getElementById('modalOverlay');
    const labModal = document.getElementById('labModal');
    if (event.target == overlay) {
        closeModal();
    }
    if (event.target == labModal) {
        closeLabModal();
    }
}

document.getElementById('modalForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const errBox = document.getElementById('modalError');
    if (errBox) errBox.style.display = 'none';

    const data = {};
    if (currentSection === 'faculty') {
        const name = document.getElementById('m_name').value;
        const status = document.getElementById('m_status').value;
        const campus = document.getElementById('m_campus')?.value || 'Main Campus';
        const subjectInput = document.getElementById('m_subject');
        const sectionInput = document.getElementById('m_section');

        // Row edit: update faculty + a specific schedule row
        if (editingFacultyId && editingScheduleId) {
            try {
                const facRes = await fetch(`api/faculty.php?id=${editingFacultyId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, employment_status: status, designated_campus: campus })
                });
                let facSaved;
                if (facRes.ok && facRes.headers.get('content-type') && facRes.headers.get('content-type').includes('application/json')) {
                    facSaved = await facRes.json();
                } else {
                    const txt = await facRes.text();
                    console.error('faculty PUT non-JSON response or error', facRes.status, txt);
                    if (errBox) {
                        errBox.textContent = 'Error saving teacher: ' + (txt || facRes.statusText || 'Server error');
                        errBox.style.display = 'block';
                    }
                    return;
                }
                if (!facSaved.success) {
                    if (errBox) {
                        errBox.textContent = 'Error saving teacher: ' + (facSaved.error || 'Unknown error');
                        errBox.style.display = 'block';
                    }
                    return;
                }

                const sectionsVal = document.getElementById('m_sections')?.value || '';
                const subjCodeVal = document.getElementById('m_subject_code')?.value || '';
                const subjNameVal = document.getElementById('m_subject_name')?.value || '';

                const schedRes = await fetch(`api/schedules.php?id=${editingScheduleId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        section: sectionsVal,
                        subject_code: subjCodeVal,
                        subject_name: subjNameVal
                    })
                });
                let schedSaved;
                if (schedRes.ok && schedRes.headers.get('content-type') && schedRes.headers.get('content-type').includes('application/json')) {
                    schedSaved = await schedRes.json();
                } else {
                    const txt = await schedRes.text();
                    console.error('schedules PUT non-JSON response or error', schedRes.status, txt);
                    if (errBox) {
                        errBox.textContent = 'Error saving schedule: ' + (txt || schedRes.statusText || 'Server error');
                        errBox.style.display = 'block';
                    }
                    return;
                }
                if (!schedSaved.success) {
                    if (errBox) {
                        errBox.textContent = 'Error saving schedule: ' + (schedSaved.error || 'Unknown error');
                        errBox.style.display = 'block';
                    }
                    return;
                }

                editingFacultyId = null;
                editingScheduleId = null;
                closeModal();
                loadTeacherManagementTable();
                return;
            } catch (err) {
                console.error(err);
                if (errBox) {
                    errBox.textContent = 'Failed to save changes.';
                    errBox.style.display = 'block';
                }
                return;
            }
        } else if (editingFacultyId) {
            // Edit only teacher (potentially adding their first schedule)
            try {
                const facRes = await fetch(`api/faculty.php?id=${editingFacultyId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, employment_status: status, designated_campus: campus })
                });
                const facSaved = await facRes.json();

                if (!facSaved.success) {
                    if (errBox) {
                        errBox.textContent = 'Error saving teacher: ' + (facSaved.error || 'Unknown error');
                        errBox.style.display = 'block';
                    }
                    return;
                }

                // If subject was provided for a teacher that had none
                const subject = subjectInput?.value || '';
                const section = sectionInput?.value || '';
                if (subject) {
                    await fetch('api/schedules.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            faculty_id: editingFacultyId,
                            subject_code: subject,
                            subject_name: subject,
                            section: section,
                            room_name: 'TBA',
                            day: 'Monday',
                            start_time: '08:00',
                            end_time: '09:00'
                        })
                    });
                }

                editingFacultyId = null;
                editingScheduleId = null;
                closeModal();
                loadTeacherManagementTable();
                return;
            } catch (err) {
                console.error(err);
                if (errBox) {
                    errBox.textContent = 'Failed to save changes: ' + err.message;
                    errBox.style.display = 'block';
                }
                return;
            }
        } else {
            // Add new teacher
            const subject = subjectInput?.value || '';
            const section = sectionInput?.value || '';

            const facRes = await fetch('api/faculty.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, employment_status: status, designated_campus: campus })
            });
            const facSaved = await facRes.json();

            if (facSaved.success && subject) {
                await fetch('api/schedules.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        faculty_id: facSaved.id,
                        subject_code: subject,
                        subject_name: subject,
                        section: section,
                        room_name: 'TBA',
                        day: 'Monday',
                        start_time: '08:00',
                        end_time: '09:00'
                    })
                });
            }

            if (facSaved.success) {
                closeModal();
                loadTeacherManagementTable();
                return;
            } else {
                if (errBox) {
                    errBox.textContent = 'Error saving: ' + (facSaved.error || 'Unknown error');
                    errBox.style.display = 'block';
                }
                return;
            }
        }
    }

    if (currentSection === 'schedules') {
        data.room_id = document.getElementById('m_room_id').value;
        data.room_name = document.getElementById('m_room_name')?.value || '';
        data.day = document.getElementById('m_day').value;
        data.subject_id = document.getElementById('m_subject_id').value;
        data.subject_code = document.getElementById('m_subject_code')?.value || '';
        data.subject_name = document.getElementById('m_subject_name')?.value || '';
        data.faculty_id = document.getElementById('m_faculty_id').value;
        data.faculty_name = document.getElementById('m_faculty_name')?.value || '';
        data.section = document.getElementById('m_section').value;

        // Read time pickers directly if the user hasn't clicked OK
        ['start', 'end'].forEach(type => {
            let val = document.getElementById('m_' + type).value;
            if (!val) {
                let hr = parseInt(document.getElementById('m_' + type + '_hr').value);
                const min = parseInt(document.getElementById('m_' + type + '_min').value);
                const ampm = document.getElementById('m_' + type + '_ampm').value;
                if (ampm === 'AM' && hr === 12) hr = 0;
                else if (ampm === 'PM' && hr !== 12) hr += 12;
                val = String(hr).padStart(2, '0') + ':' + String(min).padStart(2, '0');
            }
            data[type + '_time'] = val;
        });
    }

    try {
        const res = await fetch(`api/${currentSection}.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        if (!res.ok) throw new Error(`HTTP Error: ${res.status}`);

        const result = await res.json();
        if (result.success) {
            closeModal();
            if (currentSection === 'schedules') {
                renderSchedulesVisualGrid();
            } else if (currentSection === 'rooms') {
                loadScheduleCombinedData();
                populateSubjectDropdowns();
            } else {
                loadSectionData(currentSection);
                if (currentSection === 'rooms') {
                    populateRoomDropdowns();
                }
            }
        } else {
            if (errBox) {
                errBox.textContent = 'Error saving: ' + (result.error || 'Unknown error');
                errBox.style.display = 'block';
            }
        }
    } catch (err) {
        console.error(err);
        if (errBox) {
            errBox.textContent = 'Network or server error. Failed to save.';
            errBox.style.display = 'block';
        }
    }
});

// Navigation
function showSection(sectionId) {
    currentSection = sectionId;
    document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
    document.getElementById(sectionId + 'Section').classList.add('active');

    // Close mobile menu if open
    document.getElementById('navLinks')?.classList.remove('mobile-active');
    document.getElementById('drawerOverlay')?.classList.remove('active');

    // Hide hero banner for schedules and teachers
    const hero = document.querySelector('.hero-banner');
    if (hero) {
        hero.style.display = (sectionId === 'schedules' || sectionId === 'faculty' || sectionId === 'rooms') ? 'none' : 'block';
    }

    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
        // Match by the section ID in the onclick attribute
        if (link.getAttribute('onclick')?.includes(`'${sectionId}'`)) {
            link.classList.add('active');
        }
    });

    if (sectionId === 'home') {
        loadLabGrid();
    } else if (sectionId === 'schedules') {
        renderSchedulesVisualGrid();
    } else if (sectionId === 'faculty') {
        loadTeacherManagementTable();
    } else if (sectionId === 'rooms') {
        switchCombinedView('comlabs');
    } else {
        loadSectionData(sectionId);
    }
    loadCounts();
}

// Data Loading
async function loadTeacherManagementTable() {
    const tbody = document.getElementById('facultyTableBody');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="5">Loading teachers...</td></tr>';

    try {
        const [facultyRes, scheduleRes] = await Promise.all([
            fetch('api/faculty.php', { cache: 'no-store' }),
            fetch('api/teacher_schedule.php', { cache: 'no-store' })
        ]);

        const facultyData = await facultyRes.json();
        const scheduleGrouped = await scheduleRes.json();
        const filterStatus = document.getElementById('teacherStatusFilter')?.value || 'all';

        tbody.innerHTML = '';

        // Render each faculty member
        facultyData.forEach(teacher => {
            const teacherSchedules = scheduleGrouped[teacher.name] || [];

            const employmentStatus = teacher.employment_status || 'Full-time';
            if (filterStatus !== 'all' && employmentStatus !== filterStatus) return;
            const campus = teacher.designated_campus || 'Main Campus';

            if (teacherSchedules.length === 0) {
                // Teacher with no schedules
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${teacher.name}</td>
                    <td>${campus}</td>
                    <td>${employmentStatus}</td>
                    <td>None</td>
                    <td>None</td>
                    <td>
                        <div class="action-btn-group">
                            ${getActionIcons(teacher.id, teacher.name, '', null, employmentStatus, '', '', campus)}
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            } else {
                // Group schedules by subject
                const groupedBySubject = {};
                teacherSchedules.forEach(sched => {
                    const subjectDisplay = (sched.subject_name && sched.subject_name !== sched.subject_code)
                        ? `${sched.subject_code} – ${sched.subject_name}`
                        : sched.subject_code;
                    const key = subjectDisplay;
                    if (!groupedBySubject[key]) {
                        groupedBySubject[key] = {
                            subject: key,
                            code: sched.subject_code,
                            name: sched.subject_name,
                            sections: new Set(),
                            scheduleId: sched.id
                        };
                    }
                    if (sched.section) groupedBySubject[key].sections.add(sched.section);
                });

                const subjectGroups = Object.values(groupedBySubject);

                // Teacher with grouped schedules
                subjectGroups.forEach((group, index) => {
                    const tr = document.createElement('tr');
                    let nameCell = '';
                    let statusCell = '';

                    if (index === 0) {
                        nameCell = `<td class="teacher-name-cell" rowspan="${subjectGroups.length}">${teacher.name}</td>`;
                        const campusCell = `<td class="campus-cell" rowspan="${subjectGroups.length}">${campus}</td>`;
                        statusCell = `<td class="status-cell" rowspan="${subjectGroups.length}">${employmentStatus}</td>`;
                        tr.innerHTML = nameCell + campusCell + statusCell;
                    } else {
                        tr.innerHTML = '';
                    }

                    const sectionsList = Array.from(group.sections).join(', ') || 'N/A';

                    const subjectTitle = group.code || group.subject;
                    const subjectName = (group.name && group.name !== group.code) ? group.name : '';
                    const subjectHTML = subjectName
                        ? `<span class="subj-code">${subjectTitle}</span><span class="subj-name">${subjectName}</span>`
                        : `<span class="subj-code">${subjectTitle}</span>`;

                    tr.innerHTML += `
                        <td class="subject-cell">${subjectHTML}</td>
                        <td class="section-cell">${sectionsList}</td>
                        <td class="action-cell">
                            <div class="action-btn-group">
                                ${getActionIcons(teacher.id, teacher.name, group.code, group.scheduleId, employmentStatus, group.name || group.subject, sectionsList, campus)}
                            </div>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        });
    } catch (e) {
        console.error("Failed to load teacher table", e);
        tbody.innerHTML = '<tr><td colspan="4" style="color: #ef4444;">Error loading data</td></tr>';
    }
}

function getActionIcons(facultyId, teacherName, subjectCode, scheduleId, employmentStatus, subjectLabel, sectionsText, campus) {
    return `
        <span class="icon-view-new" data-faculty-id="${facultyId}" data-teacher-name="${teacherName}" data-subject-code="${subjectCode || ''}" onclick="viewFacultyAssignments(this)" title="View">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
        </span>
        <span class="icon-edit-new"
              data-faculty-id="${facultyId}"
              data-faculty-name="${teacherName || ''}"
              data-schedule-id="${scheduleId || ''}"
              data-employment-status="${employmentStatus || ''}"
              data-subject="${subjectLabel || ''}"
              data-subject-code="${subjectCode || ''}"
              data-subject-name="${subjectLabel || ''}"
              data-sections="${sectionsText || ''}"
              data-campus="${campus || ''}"
              onclick="editFaculty(this)"
              title="Edit">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
        </span>
        <span class="icon-delete-new" onclick="deleteItem('faculty', ${facultyId})" title="Delete">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
        </span>
    `;
}

// Add event listener for the new filter
document.addEventListener('change', (e) => {
    if (e.target.id === 'teacherStatusFilter') {
        loadTeacherManagementTable();
    }
});

async function updateEmploymentStatus(selectEl) {
    const id = selectEl.dataset.facultyId;
    await saveFacultyRow(id);
}

async function viewFacultyAssignments(el) {
    const teacherName = el.dataset.teacherName;
    const subjectCode = el.dataset.subjectCode;

    try {
        const res = await fetch('api/teacher_schedule.php');
        const grouped = await res.json();
        const schedules = (grouped[teacherName] || []).filter(s =>
            !subjectCode || s.subject_code === subjectCode
        );
        if (!schedules.length) {
            alert('No schedules found for this entry.');
            return;
        }
        viewTeacherSchedule(teacherName, schedules);
    } catch (e) {
        console.error(e);
        alert('Failed to load schedule details.');
    }
}

function editFaculty(el) {
    const id = el.dataset.facultyId;
    const name = el.dataset.facultyName || '';
    const scheduleId = el.dataset.scheduleId || '';
    const currentStatus = el.dataset.employmentStatus || 'Full-time';
    const subjectCode = el.dataset.subjectCode || '';
    const subjectName = el.dataset.subjectName || '';
    const sections = el.dataset.sections || '';

    openModal('faculty', {
        id,
        scheduleId: scheduleId || null,
        name,
        status: currentStatus,
        subjectCode,
        subjectName,
        sections,
        campus: el.dataset.campus || 'Main Campus'
    });
}

async function loadSectionData(section) {
    const res = await fetch(`api/${section}.php`, { cache: 'no-store' });
    const data = await res.json();
    const tbody = document.querySelector(`#${section}Table tbody`);
    if (!tbody) return;
    tbody.innerHTML = '';

    data.forEach(item => {
        const tr = document.createElement('tr');
        if (section === 'faculty') {
            tr.innerHTML = `
                <td>${item.name}</td>
                <td>${item.department}</td>
                <td>${item.email}</td>
                <td><span style="color: ${item.status === 'Active' ? '#4ade80' : '#f87171'}">${item.status}</span></td>
                <td><button onclick="deleteItem('faculty', ${item.id})" style="color: #f87171; background: none; border: none; cursor: pointer;">Delete</button></td>
            `;
        } else if (section === 'subjects') {
            tr.innerHTML = `
                <td>${item.code}</td>
                <td>${item.name}</td>
                <td>${item.units}</td>
                <td><button onclick="deleteItem('subjects', ${item.id})" style="color: #f87171; background: none; border: none; cursor: pointer;">Delete</button></td>
            `;
        } else if (section === 'rooms') {
            tr.innerHTML = `
                <td>${item.name}</td>
                <td>${item.capacity}</td>
                <td>${item.type}</td>
                <td><button onclick="deleteItem('rooms', ${item.id})" style="color: #f87171; background: none; border: none; cursor: pointer;">Delete</button></td>
            `;
        } else if (section === 'schedules') {
            tr.innerHTML = `
                <td>${item.day}</td>
                <td>${item.start_time} - ${item.end_time}</td>
                <td>${item.faculty_name}</td>
                <td>${item.subject_name}</td>
                <td>${item.room_name}</td>
                <td><button onclick="deleteItem('schedules', ${item.id})" style="color: #f87171; background: none; border: none; cursor: pointer;">Delete</button></td>
            `;
        }
        tbody.appendChild(tr);
    });

    // Update specific count on section load (Commented out to keep static values)
    // if (section === 'faculty') document.getElementById('facultyCount').textContent = data.length;
    // if (section === 'subjects') { /* Not on dashboard but good to have */ }
    // if (section === 'rooms') document.getElementById('roomCount').textContent = data.length;
    // if (section === 'schedules') document.getElementById('scheduleCount').textContent = data.length;
}

async function loadCounts() {
    const sections = ['faculty', 'rooms', 'schedules'];
    for (const section of sections) {
        try {
            const res = await fetch(`api/${section}.php`, { cache: 'no-store' });
            const data = await res.json();
            const countId = section === 'faculty' ? 'facultyCount' : (section === 'rooms' ? 'roomCount' : 'scheduleCount');
            const el = document.getElementById(countId);
            if (el) el.textContent = data.length;
        } catch (e) {
            console.error(`Failed to load count for ${section}`, e);
        }
    }
}

async function deleteItem(section, id) {
    if (!confirm('Are you sure you want to delete this?')) return;
    const res = await fetch(`api/${section}.php?id=${id}`, { method: 'DELETE' });
    const result = await res.json();
    if (result.success) {
        if (section === 'subjects') {
            loadScheduleCombinedData();
            populateSubjectDropdowns();
        } else if (section === 'rooms') {
            loadScheduleCombinedData();
            populateRoomDropdowns();
        } else if (section === 'faculty') {
            loadTeacherManagementTable();
        } else {
            loadSectionData(section);
        }
        loadCounts();
    }
}

// Digital Clock
function startClock() {
    const clock = document.getElementById('digitalClock');
    if (!clock) return;

    setInterval(() => {
        const now = new Date();
        clock.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }, 1000);
}

// Home View Toggling
function toggleHomeView(view) {
    const comlabGrid = document.getElementById('comlabGrid');
    const teacherGrid = document.getElementById('teacherGrid');
    const teacherFilter = document.getElementById('teacherFilterContainer');
    const filterSection = document.querySelector('.filter-section');
    const toggleSchedules = document.getElementById('toggleSchedules');
    const toggleTeachers = document.getElementById('toggleTeachers');
    const hero = document.querySelector('.hero-banner');

    if (view === 'schedules') {
        if (hero) hero.style.display = 'block';
        if (comlabGrid) comlabGrid.style.display = 'grid';
        if (teacherGrid) teacherGrid.style.display = 'none';
        if (teacherFilter) teacherFilter.style.display = 'none';
        if (filterSection) filterSection.style.display = 'flex';

        if (toggleSchedules) {
            toggleSchedules.classList.add('active');
            toggleSchedules.classList.remove('outline');
        }
        if (toggleTeachers) {
            toggleTeachers.classList.add('outline');
            toggleTeachers.classList.remove('active');
        }
        loadLabGrid();
    } else {
        if (hero) hero.style.display = 'none';
        if (comlabGrid) comlabGrid.style.display = 'none';
        if (teacherGrid) teacherGrid.style.display = 'block';
        if (teacherFilter) teacherFilter.style.display = 'flex';
        if (filterSection) filterSection.style.display = 'none';

        if (toggleTeachers) {
            toggleTeachers.classList.add('active');
            toggleTeachers.classList.remove('outline');
        }
        if (toggleSchedules) {
            toggleSchedules.classList.add('outline');
            toggleSchedules.classList.remove('active');
        }
        loadTeacherGrid();
    }
}

async function renderSchedulesVisualGrid() {
    const grid = document.getElementById('schedulesVisualGrid');
    const filter = document.getElementById('scheduleVisualFilter');
    if (!grid || !filter) return;

    // Show loading state
    if (grid.innerHTML.includes('Loading schedules...') || grid.innerHTML === '') {
        grid.innerHTML = '<div style="grid-column: 1/-1; padding: 4rem; text-align: center; color: #94a3b8; font-size: 1.2rem;">Loading lab schedules...</div>';
    }

    const [roomsRes, schedulesRes] = await Promise.all([
            fetch('api/rooms.php', { cache: 'no-store' }),
            fetch('api/lab_schedule.php', { cache: 'no-store' })
        ]);

    const roomsData = await roomsRes.json();
    const groupedSchedules = await schedulesRes.json();

    // Flatten schedules for easier processing
    let allSchedules = [];
    Object.values(groupedSchedules).forEach(list => {
        allSchedules = allSchedules.concat(list);
    });

    // Get unique list of labs from both sources
    const roomNames = new Set(roomsData.map(r => r.name));
    Object.keys(groupedSchedules).forEach(name => roomNames.add(name));

    const sortedRoomNames = Array.from(roomNames).sort((a, b) => {
        const getLabNum = (name) => {
            const match = name.match(/\d+/);
            return match ? parseInt(match[0]) : 999;
        };
        const isALab = a.toUpperCase().includes('COMLAB') || a.toUpperCase().includes('COMPLAB');
        const isBLab = b.toUpperCase().includes('COMLAB') || b.toUpperCase().includes('COMPLAB');

        if (isALab && !isBLab) return -1;
        if (!isALab && isBLab) return 1;

        if (isALab && isBLab) {
            return getLabNum(a) - getLabNum(b);
        }

        return a.localeCompare(b, undefined, { numeric: true });
    });

    // Always rebuild filter dropdown to include new rooms
    const previousValue = filter.value;
    filter.innerHTML = '<option value="all">All Schedule</option>';
    sortedRoomNames.forEach(name => {
        const opt = document.createElement('option');
        opt.value = name;
        opt.textContent = name.toUpperCase().replace('COMPLAB', 'COMLAB').replace('COMLAB ', 'COMLAB').replace('COMLAB', 'COMLAB ');
        filter.appendChild(opt);
    });
    // Restore previous selection if it still exists
    if ([...filter.options].some(o => o.value === previousValue)) {
        filter.value = previousValue;
    }

    const selectedFilter = filter.value;
    grid.innerHTML = '';

    const roomsToShow = selectedFilter === 'all' ? sortedRoomNames : [selectedFilter];

    const now = new Date();
    const todayName = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][now.getDay()];

    roomsToShow.forEach(labName => {
        const roomSchedules = allSchedules.filter(s => s.room_name === labName);

        const card = document.createElement('div');
        card.className = 'lab-card';
        card.style.border = '2px solid #fbbf24';
        card.style.borderRadius = '16px';
        card.style.padding = '1.25rem';
        card.style.background = 'white';
        card.style.display = 'flex';
        card.style.flexDirection = 'column';
        card.style.height = '540px';
        card.style.maxHeight = '540px';
        card.style.boxShadow = '0 10px 25px -5px rgba(0, 0, 0, 0.05)';

        const formatSimpleTime = (t) => {
            if (!t) return '---';
            const [hours, minutes] = t.split(':');
            const h = parseInt(hours);
            const ampm = h >= 12 ? 'PM' : 'AM';
            const h12 = h % 12 || 12;
            return `${h12}:${minutes} ${ampm}`;
        };

        const getSecs = (timeStr) => {
            if (!timeStr) return 0;
            const [h, m] = timeStr.split(':');
            return parseInt(h) * 3600 + parseInt(m) * 60;
        };

        const currentSecs = now.getHours() * 3600 + now.getMinutes() * 60;

        let slotsHtml = '';
        if (roomSchedules.length > 0) {
            const groupedByDay = {};
            roomSchedules.forEach(s => {
                if (!groupedByDay[s.day]) groupedByDay[s.day] = [];
                groupedByDay[s.day].push(s);
            });

            const dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            dayOrder.forEach(day => {
                if (groupedByDay[day]) {
                    slotsHtml += `<div style="margin-top: 0.8rem; margin-bottom: 0.6rem; padding-left: 6px; border-left: 3px solid #1e1b4b; font-weight: 900; color: #1e1b4b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">${day}</div>`;

                    groupedByDay[day].forEach(s => {
                        const timeRange = `${formatSimpleTime(s.start_time)} - ${formatSimpleTime(s.end_time)}`;
                        const startSecs = getSecs(s.start_time);
                        const endSecs = getSecs(s.end_time);

                        let isOngoing = (day === todayName) && (currentSecs >= startSecs && currentSecs <= endSecs);
                        let status = isOngoing ? "ONGOING" : "SCHEDULED";
                        // Match navy + yellow system palette
                        let bgColor = isOngoing ? "#fef9c3" : "#f8fafc"; // soft yellow for ongoing, light slate for scheduled
                        let statusColor = isOngoing ? "#92400e" : "#64748b";
                        let textWeight = isOngoing ? "900" : "400";
                        let subWeight = isOngoing ? "900" : "500";

                        slotsHtml += `
                            <div style="background: ${bgColor}; padding: 0.8rem 1rem; border-radius: 10px; margin-bottom: 0.6rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(15,23,42,0.06); border-left: 5px solid ${isOngoing ? '#fbbf24' : '#e2e8f0'}; position: relative;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                    <span style="font-size: 0.65rem; font-weight: ${textWeight}; color: ${statusColor}; text-transform: uppercase;">${status}</span>
                                    <span style="font-size: 0.8rem; font-weight: ${textWeight}; color: #1e1b4b;">${timeRange}</span>
                                </div>
                                <div style="margin-bottom: 4px;">
                                    <h4 style="font-size: 1rem; font-weight: ${subWeight}; color: #1e1b4b; line-height: 1.2; margin: 0;">${s.subject_code} ${s.subject_name}</h4>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                                    <div style="display: flex; align-items: center; gap: 5px; color: #4338ca; font-weight: ${textWeight}; font-size: 0.85rem;">
                                        👤 ${s.faculty_name.split(' ')[0]}
                                    </div>
                                    <div style="font-size: 0.8rem; color: #1e1b4b; font-weight: ${textWeight};">
                                        Sec: ${s.section || '---'}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }
            });
        } else {
            slotsHtml = `
                <div style="background: white; padding: 3rem 1.5rem; border-radius: 12px; text-align: center; color: #94a3b8; border: 2px dashed #f1f5f9; flex-grow: 1; display: flex; align-items: center; justify-content: center;">
                    <p style="margin: 0; font-weight: 700; opacity: 0.6;">No Scheduled Classes</p>
                </div>
            `;
        }

        const displayTitle = labName.toUpperCase().replace('COMPLAB', 'COMLAB').replace('COMLAB ', 'COMLAB');

        card.innerHTML = `
            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; margin-bottom: 1rem; color: #1e1b4b; font-weight: 900; letter-spacing: -0.5px;">${displayTitle}</h3>
            <div style="display: inline-block; margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.6rem; color: #1e1b4b; border-bottom: 3px solid #fbbf24; padding-bottom: 6px;">
                    <div style="background: #fbbf24; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 1rem; box-shadow: 0 4px 8px rgba(251, 191, 36, 0.2);">
                        📅
                    </div>
                    <strong style="font-size: 1rem; font-family: 'Inter', sans-serif; font-weight: 800; letter-spacing: -0.3px;">Schedules:</strong>
                </div>
            </div>
            <div class="custom-scrollbar" style="display: flex; flex-direction: column; flex-grow: 1; overflow-y: auto; padding: 1.2rem; background: #f8fafc; border-radius: 12px;">
                ${slotsHtml}
            </div>
        `;
        grid.appendChild(card);
    });
}

// Schedule Combined View Logic (ComLabs & Subjects)
let currentCombinedView = 'comlabs';
let editingSubjectId = null;

function switchCombinedView(view) {
    currentCombinedView = view;

    // Update active state of toggle buttons
    document.getElementById('toggleComLabs').classList.toggle('active', view === 'comlabs');
    document.getElementById('toggleSubjects').classList.toggle('active', view === 'subjects');

    // Update Add Button text and action
    const addBtn = document.getElementById('combinedAddBtn');
    if (view === 'comlabs') {
        addBtn.textContent = 'Add ComLab';
        addBtn.setAttribute('onclick', 'openEditRoomModal()');
    } else {
        addBtn.textContent = 'Add Subject';
        addBtn.setAttribute('onclick', 'openSubjectModal()');
    }

    // Populate filter dropdown appropriately
    populateCombinedFilter();

    // Refresh table
    loadScheduleCombinedData();
}

async function populateCombinedFilter() {
    const filter = document.getElementById('combinedManagementFilter');
    if (!filter) return;

    const currentVal = filter.value;

    if (currentCombinedView === 'comlabs') {
        filter.innerHTML = '<option value="all">All ComLabs</option>';
        try {
            const res = await fetch('api/rooms.php');
            const rooms = await res.json();
            rooms.forEach(room => {
                filter.innerHTML += `<option value="${room.name}">${room.name}</option>`;
            });
        } catch (e) {
            console.error(e);
        }
    } else {
        filter.innerHTML = '<option value="all">All Subjects</option>';
        try {
            const res = await fetch('api/subjects.php');
            const subjects = await res.json();
            subjects.forEach(sub => {
                filter.innerHTML += `<option value="${sub.code}">${sub.code} - ${sub.name}</option>`;
            });
        } catch (e) {
            console.error(e);
        }
    }

    if (currentVal && Array.from(filter.options).some(o => o.value === currentVal)) {
        filter.value = currentVal;
    }
}


async function loadScheduleCombinedData() {
    const tbody = document.getElementById('scheduleCombinedBody');
    const header = document.getElementById('scheduleHeaderRow');
    if (!tbody || !header) return;

    const filterVal = document.getElementById('combinedManagementFilter')?.value || 'all';

    if (currentCombinedView === 'comlabs') {
        // --- COMLABS VIEW ---
        header.innerHTML = `
            <th>ComLab(s) Name</th>
            <th>Campus</th>
            <th>Action</th>
        `;

        try {
            const res = await fetch('api/rooms.php', { cache: 'no-store' });
            const rooms = await res.json();

            tbody.innerHTML = '';
            rooms.forEach(room => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="room-name-cell">${room.name.replace(/COMPLAB/g, 'COMLAB')}</td>
                    <td class="campus-cell">${room.type || 'Main Campus'}</td>
                    <td class="action-cell">
                        <div class="action-btn-group">
                            <span class="icon-edit-new" onclick="openEditRoomModal(${room.id}, '${room.name.replace(/'/g, "\\'")}', '${(room.type || '').replace(/'/g, "\\'")}')">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                            </span>
                            <span class="icon-delete-new" onclick="deleteItem('rooms', ${room.id})">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                            </span>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } catch (e) { console.error(e); }
    } else {
        // --- SUBJECTS VIEW ---
        header.innerHTML = `
            <th>Subject Code</th>
            <th>Subject Name</th>
            <th>Action</th>
        `;

        try {
            const res = await fetch('api/subjects.php', { cache: 'no-store' });
            const subjects = await res.json();

            tbody.innerHTML = '';
            subjects.forEach(sub => {
                if (filterVal !== 'all' && sub.code !== filterVal) return;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="subject-code-cell">${sub.code}</td>
                    <td class="subject-name-cell">${sub.name}</td>
                    <td class="action-cell">
                        <div class="action-btn-group">
                            <span class="icon-edit-new" onclick="openSubjectModal(${sub.id}, '${sub.code.replace(/'/g, "\\'")}', '${sub.name.replace(/'/g, "\\'")}', ${sub.units})">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                            </span>
                            <span class="icon-delete-new" onclick="deleteItem('subjects', ${sub.id})">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                            </span>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } catch (e) { console.error(e); }
    }
}

// Subject Modal Logic
async function openSubjectModal(id = null, code = '', name = '', units = 3) {
    editingSubjectId = id;
    document.getElementById('subjectModalOverlay').style.display = 'flex';
    document.getElementById('subjectModalTitle').textContent = id ? 'Edit Subject' : 'Add New Subject';
    const errBox = document.getElementById('subjectError');
    if (errBox) errBox.style.display = 'none';

    document.getElementById('sm_code').value = code;
    document.getElementById('sm_name').value = name;
    document.getElementById('sm_units').value = units;

    // Populate room dropdown
    const roomSelect = document.getElementById('sm_room');
    roomSelect.innerHTML = '<option value="">-- No Room --</option>';
    try {
        const res = await fetch('api/rooms.php', { cache: 'no-store' });
        const rooms = await res.json();
        rooms.forEach(room => {
            roomSelect.innerHTML += `<option value="${room.id}">${room.name}</option>`;
        });
    } catch (e) {
        console.error('Failed to load rooms for modal', e);
    }
}

function closeSubjectModal() {
    document.getElementById('subjectModalOverlay').style.display = 'none';
    document.getElementById('subjectForm').reset();
    editingSubjectId = null;
}

document.getElementById('subjectForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const code = document.getElementById('sm_code').value;
    const name = document.getElementById('sm_name').value;
    const units = parseInt(document.getElementById('sm_units').value) || 3;
    const roomId = document.getElementById('sm_room').value;

    const errBox = document.getElementById('subjectError');
    if (errBox) errBox.style.display = 'none';

    if (!code.trim() || !name.trim()) {
        if (errBox) {
            errBox.textContent = 'Please completely fill out the required Subject Code and Name fields.';
            errBox.style.display = 'block';
        }
        return;
    }

    if (isNaN(units) || units < 1) {
        if (errBox) {
            errBox.textContent = 'Units must be a valid number greater than 0.';
            errBox.style.display = 'block';
        }
        return;
    }

    const body = { code, name, units };
    if (roomId) body.room_id = roomId;

    try {
        let res;
        if (editingSubjectId) {
            res = await fetch(`api/subjects.php?id=${editingSubjectId}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
        } else {
            res = await fetch('api/subjects.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });
        }

        if (!res.ok) {
            throw new Error(`HTTP Error: ${res.status}`);
        }

        const result = await res.json();
        if (result.success) {
            closeSubjectModal();
            loadScheduleCombinedData();
            populateSubjectDropdowns();
        } else {
            if (errBox) {
                let errorMsg = result.error || 'Unknown error occurred.';
                if (errorMsg.toLowerCase().includes('duplicate') || errorMsg.includes('1062')) {
                    errorMsg = 'This Subject Code already exists. Please choose a unique code.';
                }
                errBox.textContent = 'Error saving subject: ' + errorMsg;
                errBox.style.display = 'block';
            }
        }
    } catch (err) {
        console.error(err);
        if (errBox) {
            errBox.textContent = 'Network or server error. Failed to save subject.';
            errBox.style.display = 'block';
        }
    }
});

// Close subject modal when clicking overlay
window.addEventListener('click', function (event) {
    const overlay = document.getElementById('subjectModalOverlay');
    if (event.target === overlay) {
        closeSubjectModal();
    }
});


// Lab Grid Logic
async function loadLabGrid() {
    const grid = document.getElementById('comlabGrid');
    if (!grid) return;

    const selectedRoom = document.getElementById('roomFilter')?.value || 'all';
    const selectedDay = 'all';
    const selectedTime = 'all';

    try {
        const [schedRes, roomsRes] = await Promise.all([
            fetch('api/lab_schedule.php', { cache: 'no-store' }),
            fetch('api/rooms.php', { cache: 'no-store' })
        ]);
        const groupedSchedules = await schedRes.json();
        const roomsDb = await roomsRes.json();

        grid.innerHTML = '';

        // Build room list from schedule data so all rooms with schedules get a box (including AI32, MT12, etc.)
        const roomOrder = [
            'COMLAB1', 'COMLAB2', 'COMLAB3', 'COMLAB4',
            'COMLAB5 (CON 103)', 'COMLAB6 (CON 104)', 'COMLAB7 (CON 105)',
            'CHS (CON 101)', 'CISCO (CON 102)'
        ];

        // Use all rooms from DB plus any that might only arbitrarily exist in schedules
        const allRoomNamesMap = new Set();
        Object.keys(groupedSchedules).forEach(n => allRoomNamesMap.add(n.replace(/COMPLAB/g, 'COMLAB')));
        roomsDb.forEach(r => allRoomNamesMap.add(r.name.replace(/COMPLAB/g, 'COMLAB')));

        const roomNamesCombined = Array.from(allRoomNamesMap);

        const allRooms = [...roomOrder.filter(r => roomNamesCombined.includes(r))];
        roomNamesCombined.forEach(name => {
            if (!roomOrder.includes(name)) allRooms.push(name);
        });
        allRooms.sort((a, b) => {
            const aInOrder = roomOrder.indexOf(a);
            const bInOrder = roomOrder.indexOf(b);
            if (aInOrder !== -1 && bInOrder !== -1) return aInOrder - bInOrder;
            if (aInOrder !== -1) return -1;
            if (bInOrder !== -1) return 1;
            return a.localeCompare(b);
        });

        allRooms.forEach(labName => {
            // Filter by dropdown: "all" = all boxes; "OTHER ROOMS" = only boxes not in roomOrder; else = that one room's box
            if (selectedRoom !== 'all') {
                if (selectedRoom === 'OTHER ROOMS') {
                    if (roomOrder.includes(labName)) return;
                } else if (selectedRoom !== labName) {
                    return;
                }
            }

            const legacyName = labName.replace(/COMLAB/g, 'COMPLAB');
            const schedules = groupedSchedules[labName] || groupedSchedules[legacyName] || [];

            const now = new Date();
            const filterDay = selectedDay === 'all' ? now.toLocaleDateString('en-US', { weekday: 'long' }) : selectedDay;
            const currentTime = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0') + ':' + now.getSeconds().toString().padStart(2, '0');

            schedules.sort((a, b) => a.start_time.localeCompare(b.start_time));

            let displayClasses = [];
            if (selectedTime !== 'all') {
                displayClasses = schedules.filter(s => {
                    if (s.day !== filterDay) return false;
                    const h = parseInt(s.start_time.split(':')[0]);
                    return selectedTime === 'AM' ? h < 12 : h >= 12;
                });
            } else {
                // Find ongoing class
                const currentClass = schedules.find(s =>
                    s.day === filterDay &&
                    currentTime >= s.start_time &&
                    currentTime < s.end_time
                );

                if (currentClass) {
                    displayClasses.push(currentClass);
                }

                // Find next upcoming class
                const nextClass = schedules.find(s =>
                    s.day === filterDay &&
                    s.start_time > currentTime &&
                    (!currentClass || s !== currentClass)
                );

                if (nextClass) {
                    displayClasses.push(nextClass);
                }
            }

            const card = document.createElement('div');
            card.className = 'lab-card';
            card.onclick = () => viewLabSchedule(labName, schedules);

            let slotHtml = '';

            if (displayClasses.length > 0) {
                // Sort again just in case
                displayClasses.sort((a, b) => a.start_time.localeCompare(b.start_time));

                // Check if first class is UPCOMING (meaning gap now)
                const firstIsUpcoming = displayClasses[0].start_time > currentTime;

                if (firstIsUpcoming && selectedDay === 'all' && selectedTime === 'all') {
                    let vacantStart = "07:30";
                    const previousClasses = schedules.filter(s => s.day === filterDay && s.end_time <= currentTime);
                    if (previousClasses.length > 0) {
                        previousClasses.sort((a, b) => b.end_time.localeCompare(a.end_time));
                        vacantStart = previousClasses[0].end_time.substring(0, 5);
                    }

                    let vacantEnd = displayClasses[0].start_time.substring(0, 5);

                    if (vacantStart !== vacantEnd) {
                        slotHtml += `
                            <div style="background: white; padding: 0.8rem; border-radius: 6px; text-align: center; border: 1px dashed #cbd5e1; display: flex; flex-direction: column; justify-content: center; height: auto; min-height: 80px; position: relative;">
                                <span style="position: absolute; top: 0.3rem; right: 0.3rem; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; color: #64748b; font-weight: 700;">
                                    ${vacantStart} - ${vacantEnd}
                                </span>
                                <h2 style="font-size: 1.8rem; font-weight: 900; color: #1e1b4b; margin: 0; letter-spacing: 0.05em; text-transform: uppercase;">VACANT</h2>
                            </div>
                         `;
                    }
                }

                displayClasses.forEach(cls => {
                    let isUpcoming = cls.start_time > currentTime;
                    let statusLabel = isUpcoming ? "UPCOMING" : "ONGOING";
                    let bg = isUpcoming ? "#f1f5f9" : "#dbeafe";
                    let border = isUpcoming ? "1px solid #e2e8f0" : "1px solid #bfdbfe";
                    let titleColor = isUpcoming ? "#64748b" : "#1e40af";
                    let flexData = isUpcoming ? "margin-top: auto;" : "flex-grow: 1;";

                    slotHtml += `
                        <div style="background: ${bg}; border: ${border}; padding: ${isUpcoming ? '0.4rem 0.6rem' : '0.6rem 0.8rem'}; border-radius: 6px; ${flexData} display: flex; flex-direction: column; justify-content: center;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.2rem;">
                                <span style="font-size: 0.7rem; font-weight: 700; color: ${titleColor}; letter-spacing: 0.05em;">${statusLabel}</span>
                                <span style="font-size: 0.75rem; font-weight: 600; color: #475569;">${cls.start_time.substring(0, 5)} - ${cls.end_time.substring(0, 5)}</span>
                            </div>
                            <strong style="color: #1e293b; font-size: ${isUpcoming ? '0.9rem' : '1.1rem'}; line-height: 1.3;">${cls.subject_code} ${cls.subject_name}</strong>
                            
                            <div style="margin-top: 0.5rem; display: flex; flex-direction: column;">
                                <div style="font-size: 0.8rem; color: #475569; display: flex; justify-content: space-between; align-items: center;">
                                    <span>${selectedRoom === 'all' ? '' : cls.room_name?.replace('COMPLAB', 'COMLAB')}</span>
                                    <span style="font-weight: 700; color: #1e1b4b;">Section: ${cls.section || 'N/A'}</span>
                                </div>
                                <div style="margin-top: 4px; padding-top: 4px; border-top: 1px dashed #cbd5e1; font-size: 0.85rem; font-weight: 800; color: #4f46e5; display: flex; align-items: center; gap: 4px;">
                                    <span style="font-size: 0.7rem; opacity: 0.7;">👤</span> ${cls.faculty_name}
                                </div>
                            </div>
                        </div>
                     `;
                });
            } else {
                let vacantStart = "07:30";
                if (filterDay) {
                    const previousClasses = schedules.filter(s => s.day === filterDay && s.end_time <= currentTime);
                    if (previousClasses.length > 0) {
                        previousClasses.sort((a, b) => b.end_time.localeCompare(a.end_time));
                        vacantStart = previousClasses[0].end_time.substring(0, 5);
                    }
                }
                let range = `${vacantStart} - 19:00`;

                slotHtml = `
                    <div style="background: white; padding: 1.5rem; border-radius: 8px; text-align: center; border: 1px dashed #cbd5e1; display: flex; flex-direction: column; justify-content: center; height: 100%; min-height: 120px; position: relative;">
                         <span style="position: absolute; top: 0.5rem; right: 0.5rem; background: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; color: #64748b; font-weight: 700;">
                                ${range}
                         </span>
                         <h2 style="font-size: 3rem; font-weight: 900; color: #1e1b4b; margin: 0; letter-spacing: 0.05em; text-transform: uppercase;">VACANT</h2>
                    </div>
                `;
            }

            card.innerHTML = `
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; margin-bottom: 1rem; color: #1e1b4b; height: 3.5rem; overflow: hidden; display: flex; align-items: center;">${labName.replace('COMPLAB', 'COMLAB')}</h3>
                <div class="schedule-label" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.8rem;">
                    <div class="icon">📅</div>
                    <strong style="font-size: 0.9rem;">${selectedDay === 'all' ? 'Schedules:' : selectedDay + ' Schedule:'}</strong>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 0.8rem; flex-grow: 1; height: 100%;">
                    ${slotHtml}
                </div>
            `;

            grid.appendChild(card);
        });
    } catch (e) {
        grid.innerHTML = '<div style="grid-column: 1/-1; padding: 2rem; color: #ef4444;">Failed to load schedules.</div>';
    }
}

function openEditRoomModal(id = null, name = '', type = '') {
    document.getElementById('roomModalOverlay').style.display = 'flex';
    document.getElementById('roomModalTitle').textContent = id ? 'Edit Room' : 'Add New Room';
    const errBox = document.getElementById('roomError');
    if (errBox) errBox.style.display = 'none';

    document.getElementById('r_name').value = name;
    document.getElementById('r_location').value = type;
    document.getElementById('roomForm').onsubmit = (e) => saveNewRoom(e, id);
}

function closeRoomModal() {
    document.getElementById('roomModalOverlay').style.display = 'none';
    document.getElementById('roomForm').reset();
    document.getElementById('roomForm').onsubmit = saveNewRoom;
}

async function saveNewRoom(event, editingId = null) {
    event.preventDefault();
    const name = document.getElementById('r_name').value;
    const type = document.getElementById('r_location').value;
    const capacity = 40; // Default capacity

    const errBox = document.getElementById('roomError');
    if (errBox) errBox.style.display = 'none';

    if (!name.trim()) {
        if (errBox) {
            errBox.textContent = 'Please enter a valid Room Name.';
            errBox.style.display = 'block';
        }
        return;
    }

    if (!type.trim()) {
        if (errBox) {
            errBox.textContent = 'Please select a Location for the ComLab.';
            errBox.style.display = 'block';
        }
        return;
    }

    const method = editingId ? 'PUT' : 'POST';
    const url = editingId ? `api/rooms.php?id=${editingId}` : 'api/rooms.php';

    try {
        const res = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, type, capacity })
        });

        if (!res.ok) {
            throw new Error(`HTTP Error: ${res.status}`);
        }

        const result = await res.json();
        if (result.success) {
            closeRoomModal();
            if (currentSection === 'rooms') {
                loadScheduleCombinedData();
            } else {
                loadSectionData('rooms');
            }
            populateRoomDropdowns();
        } else {
            if (errBox) {
                let errorMsg = result.error || 'Unknown error occurred.';
                if (errorMsg.toLowerCase().includes('duplicate') || errorMsg.includes('1062')) {
                    errorMsg = 'This Room Name already exists. Please choose a unique name.';
                }
                errBox.textContent = 'Error saving room: ' + errorMsg;
                errBox.style.display = 'block';
            }
        }
    } catch (err) {
        console.error(err);
        if (errBox) {
            errBox.textContent = 'Network or server error. Failed to save ComLab.';
            errBox.style.display = 'block';
        }
    }
}

function handleFilterChange() {
    const comlabGrid = document.getElementById('comlabGrid');
    if (comlabGrid && comlabGrid.style.display !== 'none') {
        loadLabGrid();
    } else {
        loadTeacherGrid();
    }
}

// Teacher Grid Logic
async function loadTeacherGrid() {
    const grid = document.getElementById('teacherGrid');
    const teacherFilter = document.getElementById('teacherSelectFilter');
    if (!grid) return;

    try {
        const res = await fetch('api/teacher_schedule.php', { cache: 'no-store' });
        const groupedSchedules = await res.json();

        const rawNames = Object.keys(groupedSchedules);

        // Helper: display label for teacher in UI (Title Case)
        const getTeacherLabel = (name) => {
            return name.toLowerCase().split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        };

        const orderedNames = rawNames.sort((a, b) => a.localeCompare(b, undefined, {sensitivity: 'base'}));

        // Populate filter if empty (except "All")
        if (teacherFilter && teacherFilter.options.length <= 1) {
            orderedNames.forEach(name => {
                const opt = document.createElement('option');
                opt.value = name;
                opt.textContent = getTeacherLabel(name);
                teacherFilter.appendChild(opt);
            });
        }

        const selectedTeacher = teacherFilter?.value || 'all';

        grid.innerHTML = `
            <table class="teacher-table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Teachers</th>
                        <th style="width: 35%;">Subjects</th>
                        <th style="width: 30%;">Sections</th>
                        <th style="width: 10%;">View</th>
                    </tr>
                </thead>
                <tbody id="teacherTableBody"></tbody>
            </table>
        `;

        const tbody = document.getElementById('teacherTableBody');
        tbody.innerHTML = '';

        orderedNames.forEach(teacherName => {
            if (selectedTeacher !== 'all' && selectedTeacher !== teacherName) return;

            const baseSchedules = groupedSchedules[teacherName];

            // Group by subject (combining all sections for that subject)
            const subjectGroups = {};
            baseSchedules.forEach(s => {
                const subjKey = s.subject_code; // Just the code to match image
                if (!subjectGroups[subjKey]) {
                    subjectGroups[subjKey] = {
                        name: `${s.subject_code} \u2013 ${s.subject_name} `,
                        sections: new Set(),
                        schedules: []
                    };
                }
                if (s.section) subjectGroups[subjKey].sections.add(s.section);
                subjectGroups[subjKey].schedules.push(s);
            });

            const subjects = Object.keys(subjectGroups).sort();

            subjects.forEach((subjKey, idx) => {
                const tr = document.createElement('tr');
                const group = subjectGroups[subjKey];

                // Teacher Name Cell (only for first row of teacher)
                if (idx === 0) {
                    const nameTd = document.createElement('td');
                    nameTd.className = 'teacher-name-cell';
                    nameTd.rowSpan = subjects.length;
                    nameTd.textContent = getTeacherLabel(teacherName);
                    tr.appendChild(nameTd);
                }

                // Subject Cell
                const subjTd = document.createElement('td');
                subjTd.className = 'subject-cell';
                subjTd.textContent = group.name;
                tr.appendChild(subjTd);

                // Section Cell
                const secTd = document.createElement('td');
                secTd.className = 'section-cell';
                secTd.textContent = Array.from(group.sections).sort().join(', ') || 'N/A';
                tr.appendChild(secTd);

                // View Cell
                const viewTd = document.createElement('td');
                viewTd.className = 'view-btn-cell';
                const btn = document.createElement('button');
                btn.className = 'view-btn';
                // Pass the teacher's full schedule to the view
                btn.onclick = () => viewTeacherSchedule(teacherName, baseSchedules);
                viewTd.appendChild(btn);
                tr.appendChild(viewTd);

                tbody.appendChild(tr);
            });
        });

    } catch (e) {
        console.error(e);
        grid.innerHTML = '<div style="padding: 2rem; color: #ef4444; background: white; text-align: center;">Failed to load teacher schedules.</div>';
    }
}

function renderSlot(classData, label, isTeacher = false) {
    if (!classData) return '';

    return `
        <div style="background: #e5e7eb; padding: 0.8rem; border-radius: 8px;">
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <p style="font-size: 0.75rem; color: #4f46e5; font-weight: 800; margin-bottom: 0.2rem; line-height: 1.2;">${label.toUpperCase()}</p>
                <p style="font-size: 0.8rem; color: #475569;"><strong>Time:</strong> ${classData.start_time.substring(0, 5)} - ${classData.end_time.substring(0, 5)}</p>
                <p style="font-size: 0.8rem; color: #475569;"><strong>Section:</strong> ${classData.section || 'N/A'}</p>
                <p style="font-size: 0.8rem; color: #475569; border-top: 1px dashed #cbd5e1; margin-top: 4px; padding-top: 4px;">
                    <strong>${isTeacher ? 'Room' : 'Teacher'}:</strong> ${isTeacher ? (classData.room_name?.replace('COMPLAB', 'COMLAB') || 'N/A') : classData.faculty_name}
                </p>
            </div>
        </div>
    `;
}

function viewTeacherSchedule(teacherName, schedules) {
    const modal = document.getElementById('labModal');
    const title = document.getElementById('labModalTitle');
    const content = document.getElementById('labModalContent');

    const labelName = teacherName.toLowerCase().split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
    title.textContent = `Full Schedule: ${labelName} `;

    const daysOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    // Group by base subject (pairing Lec and Lab)
    const subjectList = {};
    schedules.forEach(s => {
        const baseCode = s.subject_code.replace(/L$/, '');
        if (!subjectList[baseCode]) subjectList[baseCode] = { base: baseCode, subs: {} };

        if (!subjectList[baseCode].subs[s.subject_code]) {
            subjectList[baseCode].subs[s.subject_code] = {
                code: s.subject_code,
                name: s.subject_name,
                details: []
            };
        }
        subjectList[baseCode].subs[s.subject_code].details.push(s);
    });

    let html = `
        <div style="background: #111827; color: white; padding: 2rem 1.5rem; border-radius: 12px; margin-bottom: 2rem; display: flex; align-items: center; gap: 1.5rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);">
            <div style="width: 70px; height: 70px; background: #1e1b4b; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; font-weight: 900; color: white;">
                ${teacherName.charAt(0)}
            </div>
            <div>
                <h2 style="margin: 0; font-size: 1.8rem; letter-spacing: 0.5px;">${teacherName}</h2>
                <p style="margin: 4px 0 0; color: #94a3b8; font-size: 0.9rem; font-weight: 500;">Faculty Schedule - Weekly Overview</p>
            </div>
        </div>
    `;

    Object.keys(subjectList).sort().forEach(baseKey => {
        const group = subjectList[baseKey];

        html += `
            <div style="margin-bottom: 2rem; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; background: white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <div style="background: #f9fafb; padding: 1rem 1.5rem; border-bottom: 2px solid #1e1b4b;">
                    <h3 style="margin: 0; color: #111827; font-size: 1.1rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">SUBJECT: ${baseKey}</h3>
                </div>
                <div style="padding: 0.5rem 1.5rem 1.5rem 1.5rem;">
        `;

        Object.keys(group.subs).sort().forEach(subCode => {
            const sub = group.subs[subCode];

            // Sort by day then by time
            const sortedItems = sub.details.sort((a, b) => {
                const dayDiff = daysOrder.indexOf(a.day) - daysOrder.indexOf(b.day);
                if (dayDiff !== 0) return dayDiff;
                return a.start_time.localeCompare(b.start_time);
            });

            sortedItems.forEach(s => {
                html += `
                    <div style="display: grid; grid-template-columns: 140px 1fr 140px 160px 1fr; gap: 15px; padding: 12px 0; border-bottom: 1px solid #f3f4f6; align-items: center;">
                        <span style="background: #1e1b4b; color: white; padding: 4px 0; border-radius: 20px; font-weight: 900; font-size: 0.8rem; text-align: center; width: 110px;">${s.subject_code}</span>
                        <div style="font-size: 0.95rem; font-weight: 700; color: #111827;">${s.section || '---'}</div>
                        <div style="font-size: 0.9rem; color: #374151; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">${s.day}</div>
                        <div style="font-size: 0.9rem; color: #4b5563; font-weight: 600;">${s.start_time.substring(0, 5)} - ${s.end_time.substring(0, 5)}</div>
                        <div style="font-size: 0.9rem; color: #6b7280; font-weight: 500;">${s.room_name?.replace('COMPLAB', 'COMLAB')}</div>
                    </div>
                `;
            });
        });

        html += `</div></div>`;
    });

    content.innerHTML = html;
    modal.style.display = 'flex';
}

function viewLabSchedule(labName, schedules) {
    const modal = document.getElementById('labModal');
    const title = document.getElementById('labModalTitle');
    const content = document.getElementById('labModalContent');
    title.textContent = `${labName?.replace('COMPLAB', 'COMLAB')} Weekly Schedule`;
    renderScheduleTable(schedules, content, false);
    modal.style.display = 'flex';
}

function renderScheduleTable(schedules, container, isTeacherView) {
    if (schedules.length === 0) {
        container.innerHTML = '<p style="text-align: center; padding: 2rem;">No schedules assigned.</p>';
        return;
    }

    // Add Filter Bar to the Modal
    let filterHtml = `
        <div style="display: flex; gap: 1.5rem; margin-bottom: 2rem; background: #f8fafc; padding: 1.25rem; border-radius: 12px; border: 1px solid #e2e8f0; align-items: flex-end;">
            <div style="flex: 0 1 220px;">
                <label style="font-size: 0.75rem; color: #64748b; font-weight: 800; display: block; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">Days Filter:</label>
                <select id="modalDayFilter" class="filter-btn" style="width: 100%; appearance: none; padding-right: 2.5rem; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23FFFFFF%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 0.8rem center; background-size: 0.7rem auto;" onchange="window.updateModalFilters()">
                    <option value="all">All Days</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                </select>
            </div>
            <div style="flex: 0 1 220px;">
                <label style="font-size: 0.75rem; color: #64748b; font-weight: 800; display: block; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">Time Filter:</label>
                <select id="modalTimeFilter" class="filter-btn" style="width: 100%; appearance: none; padding-right: 2.5rem; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23FFFFFF%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22/%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 0.8rem center; background-size: 0.7rem auto;" onchange="window.updateModalFilters()">
                    <option value="all">All Time</option>
                </select>
            </div>
        </div>
        <div id="modalGridContainer"></div>
    `;

    container.innerHTML = filterHtml;
    const gridContainer = document.getElementById('modalGridContainer');

    // Store data for the filter function
    window.modalSchedules = schedules;
    window.modalIsTeacherView = isTeacherView;

    window.updateModalFilters = function () {
        const day = document.getElementById('modalDayFilter').value;
        const timeFilter = document.getElementById('modalTimeFilter');
        const selectedTime = timeFilter.value;

        // Update Time Options if Day changed
        if (event && event.target.id === 'modalDayFilter') {
            let timeOptions = '<option value="all">All Time</option>';
            if (day === 'Wednesday' || day === 'Saturday') {
                timeOptions += `
                    <option value="08:00">8:00 - 10:00</option>
                    <option value="10:00">10:00 - 12:00</option>
                    <option value="13:00">1:00 - 3:00</option>
                    <option value="15:00">3:00 - 5:00</option>
                    <option value="17:00">5:00 - 7:00</option>
                `;
            } else if (day !== 'all') {
                timeOptions += `
                    <option value="07:30">7:30 - 9:00</option>
                    <option value="09:00">9:00 - 10:30</option>
                    <option value="10:30">10:30 - 12:00</option>
                    <option value="13:00">1:00 - 2:30</option>
                    <option value="14:30">2:30 - 4:00</option>
                    <option value="16:00">4:00 - 5:30</option>
                    <option value="17:30">5:30 - 7:00</option>
                `;
            }
            timeFilter.innerHTML = timeOptions;
        }

        const filtered = window.modalSchedules.filter(s => {
            const dayMatch = day === 'all' || s.day === day;
            const timeVal = document.getElementById('modalTimeFilter').value;
            const timeMatch = timeVal === 'all' || s.start_time.startsWith(timeVal);
            return dayMatch && timeMatch;
        });

        renderModalGrid(filtered, gridContainer, window.modalIsTeacherView);
    };

    // Initial render
    window.updateModalFilters();
}

function renderModalGrid(schedules, container, isTeacherView) {
    if (schedules.length === 0) {
        container.innerHTML = '<p style="text-align: center; padding: 2rem; color: #64748b;">No classes match this filter.</p>';
        return;
    }
    const daysOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const groupedByDay = {};
    schedules.forEach(s => {
        if (!groupedByDay[s.day]) groupedByDay[s.day] = [];
        groupedByDay[s.day].push(s);
    });

    let html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">';
    daysOrder.forEach(day => {
        const dayScheds = groupedByDay[day] || [];
        if (dayScheds.length > 0) {
            dayScheds.sort((a, b) => a.start_time.localeCompare(b.start_time));
            html += `
                <div style="background: #f8fafc; border-radius: 12px; padding: 1.5rem; border: 1px solid #e2e8f0;">
                    <h4 style="color: #4f46e5; border-bottom: 2px solid #1e1b4b; display: inline-block; margin-bottom: 1rem; padding-bottom: 0.2rem;">${day}</h4>
                    <div style="display: flex; flex-direction: column; gap: 0.8rem;">
            `;
            dayScheds.forEach(s => {
                // Helper to format time for Wednesday (12-hour, no AM/PM, no leading zero)
                const formatTime = (t) => {
                    if (day === 'Wednesday') {
                        let [h, m] = t.split(':');
                        h = parseInt(h);
                        if (h > 12) h -= 12;
                        return `${h}:${m}`;
                    }
                    return t.substring(0, 5);
                };

                // Render VACANT card if this is a VACANT slot
                if (s.subject_code === 'VACANT') {
                    html += `
                        <div style="background: white; padding: 0.8rem; border-radius: 8px; border: 1px dashed #cbd5e1; text-align: center; position: relative;">
                            <span style="position: absolute; top: 0.4rem; right: 0.6rem; font-size: 0.72rem; color: #64748b; font-weight: 700; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">
                                ${formatTime(s.start_time)} - ${formatTime(s.end_time)}
                            </span>
                            <h3 style="font-size: 1.6rem; font-weight: 900; color: #1e1b4b; margin: 0.3rem 0 0 0; letter-spacing: 0.05em;">VACANT</h3>
                        </div>
                    `;
                    return;
                }

                // Render Actual Schedule
                html += `
                    <div style="background: white; padding: 0.8rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <strong style="color: #1e1b4b; font-size: 0.9rem;">${s.subject_code} ${s.subject_name}</strong>
                            <span style="font-size: 0.75rem; color: #64748b; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-weight: 600;">
                                ${formatTime(s.start_time)} - ${formatTime(s.end_time)}
                            </span>
                        </div>
                        <p style="font-size: 0.8rem; color: #475569;"><strong>Section:</strong> ${s.section || 'N/A'}</p>
                        <p style="font-size: 0.8rem; color: #4f46e5; font-weight: 700; border-top: 1px dashed #cbd5e1; margin-top: 5px; padding-top: 5px;">
                            ${isTeacherView ? 'Room: ' + s.room_name?.replace('COMPLAB', 'COMLAB') : 'Teacher: ' + s.faculty_name}
                        </p>
                    </div>
                `;
            });
            html += '</div></div > ';
        }
    });
    html += '</div>';
    container.innerHTML = html;
}

function closeLabModal() {
    document.getElementById('labModal').style.display = 'none';
}

async function populateSubjectDropdowns() {
    try {
        const res = await fetch('api/subjects.php');
        const subjects = await res.json();

        const subjectFilter = document.getElementById('subjectsManagementFilter');

        if (subjectFilter) {
            const currentVal = subjectFilter.value;
            subjectFilter.innerHTML = '<option value="all">All Subjects</option>';
            subjects.forEach(sub => {
                subjectFilter.innerHTML += `<option value="${sub.code}">${sub.code} - ${sub.name}</option>`;
            });
            subjectFilter.value = currentVal;
            if (!subjectFilter.value) subjectFilter.value = 'all';
        }
    } catch (e) {
        console.error("Failed to populate subject dropdowns", e);
    }
}

async function populateRoomDropdowns() {
    try {
        const res = await fetch('api/rooms.php');
        const rooms = await res.json();

        const roomFilter = document.getElementById('roomFilter');
        const visualFilter = document.getElementById('scheduleVisualFilter');

        if (roomFilter) {
            const currentVal = roomFilter.value;
            roomFilter.innerHTML = '<option value="all">All Schedule</option>';
            rooms.forEach(room => {
                roomFilter.innerHTML += `<option value="${room.name}">${room.name}</option>`;
            });
            roomFilter.value = currentVal;
            if (!roomFilter.value) roomFilter.value = 'all';
        }

        if (visualFilter) {
            const currentVisualVal = visualFilter.value;
            visualFilter.innerHTML = '<option value="all">All schedule</option>';
            rooms.forEach(room => {
                visualFilter.innerHTML += `<option value="${room.name}">${room.name}</option>`;
            });
            visualFilter.value = currentVisualVal;
            if (!visualFilter.value) visualFilter.value = 'all';
        }

        const managementFilter = document.getElementById('roomsManagementFilter');
        if (managementFilter) {
            const currentVal = managementFilter.value;
            managementFilter.innerHTML = '<option value="all">All Available Rooms</option>';
            rooms.forEach(room => {
                managementFilter.innerHTML += `<option value="${room.name}">${room.name}</option>`;
            });
            managementFilter.value = currentVal;
            if (!managementFilter.value) managementFilter.value = 'all';
        }
    } catch (e) {
        console.error("Failed to populate room dropdowns", e);
    }
}

// Initialization
checkAuth();
populateRoomDropdowns();
