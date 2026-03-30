// Global State
window.activeTermId = localStorage.getItem('activeTermId') || '1';

// Auth State Management
async function checkAuth() {
    startClock();
    try {
        const res = await fetch('api/auth.php?action=check');
        const data = await res.json();
        if (data.authenticated) {
            document.body.classList.add('logged-in');
            document.body.classList.remove('logged-out');

            // Initialize term data and UI
            await syncAcademicTerms();
            
            loadCounts();
            showSection('home');
            loadUserProfile();
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

// Show Register Form
document.getElementById('showRegisterLink')?.addEventListener('click', () => {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('registerForm').style.display = 'block';
    const formTitle = document.getElementById('formTitle');
    if(formTitle) formTitle.textContent = 'Create Account';
});

// Back to Login from Register
document.getElementById('backToLoginFromRegister')?.addEventListener('click', () => {
    document.getElementById('registerForm').style.display = 'none';
    document.getElementById('loginForm').style.display = 'block';
    const formTitle = document.getElementById('formTitle');
    if(formTitle) formTitle.textContent = 'Login';
    document.getElementById('registerError').style.display = 'none';
    document.getElementById('registerSuccess').style.display = 'none';
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

// Register Form Submission
document.getElementById('registerForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const regUsername = document.getElementById('reg_username').value.trim();
    const regPassword = document.getElementById('reg_password').value;
    const regConfirm  = document.getElementById('reg_confirm_password').value;
    const errObj  = document.getElementById('registerError');
    const succObj = document.getElementById('registerSuccess');

    errObj.style.display  = 'none';
    succObj.style.display = 'none';

    if (regPassword !== regConfirm) {
        errObj.textContent = "Passwords do not match.";
        errObj.style.display = 'block';
        return;
    }

    try {
        const res = await fetch('api/auth.php?action=register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username: regUsername, password: regPassword })
        });
        const data = await res.json();
        if (data.success) {
            succObj.textContent = "Account created! You can now log in.";
            succObj.style.display = 'block';
            document.getElementById('registerForm').reset();
            // Auto redirect to login after 2s
            setTimeout(() => {
                document.getElementById('registerForm').style.display = 'none';
                document.getElementById('loginForm').style.display = 'block';
                document.getElementById('formTitle').textContent = 'Login';
                succObj.style.display = 'none';
            }, 2000);
        } else {
            errObj.textContent = data.message || "Registration failed.";
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

// Utility: Format time to 24-hour (military format)
function formatTime(t) {
    if (!t) return '---';
    const [hours, minutes] = t.split(':');
    return `${hours}:${minutes}`;
}

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
    document.getElementById('settingsModalOverlay').style.display = 'flex';
    // Sync current theme buttons
    const isDark = document.body.classList.contains('dark-mode');
    updateThemeButtons(isDark ? 'dark' : 'light');
}

function closeSettings() {
    document.getElementById('settingsModalOverlay').style.display = 'none';
}

function setTheme(theme) {
    const isDark = (theme === 'dark');
    document.body.classList.toggle('dark-mode', isDark);
    localStorage.setItem('theme', theme);
    updateThemeButtons(theme);
    
    // Inject enhanced CSS if not already there
    if (!document.getElementById('dark-mode-style')) {
        const style = document.createElement('style');
        style.id = 'dark-mode-style';
        style.innerHTML = `
            body.dark-mode { 
                background-color: #0f172a !important; 
                color: #f8fafc; 
            }
            body.dark-mode .dashboard-container,
            body.dark-mode .dashboard-content,
            body.dark-mode .glass-card,
            body.dark-mode .stat-card,
            body.dark-mode .lab-card {
                background: #1e293b !important;
                border-color: #334155 !important;
                color: #f8fafc;
                box-shadow: 0 10px 15px -3px rgba(0,0,0,0.4) !important;
            }
            body.dark-mode .navbar {
                background: #1e293b !important;
                border-bottom: 1px solid #334155 !important;
            }
            body.dark-mode .nav-link:not(.active) {
                color: #94a3b8 !important;
            }
            body.dark-mode .nav-link.active {
                color: #fbbf24 !important;
            }
            body.dark-mode .brand-top, 
            body.dark-mode .brand-bottom:not(.yellow) {
                color: #f8fafc !important;
            }
            body.dark-mode th {
                background: #0f172a !important;
                color: #f8fafc !important;
                border-bottom: 2px solid #334155 !important;
            }
            /* Refined Table Backgrounds for Dark Mode */
            body.dark-mode .teacher-manage-table td:nth-child(1),
            body.dark-mode .teacher-manage-table td:nth-child(3),
            body.dark-mode .teacher-manage-table td:nth-child(4),
            body.dark-mode .teacher-manage-table td.teacher-name-cell,
            body.dark-mode .teacher-name-cell,
            body.dark-mode .teacher-table td.teacher-name-cell,
            body.dark-mode .teacher-manage-table td.status-cell,
            body.dark-mode .teacher-manage-table td.subject-cell,
            body.dark-mode .subject-cell,
            body.dark-mode .room-name-cell,
            body.dark-mode #scheduleCombinedTable td:nth-child(1) { 
                background: #1e293b !important; 
            }
            body.dark-mode .teacher-manage-table td:nth-child(2),
            body.dark-mode .teacher-manage-table td:nth-child(5),
            body.dark-mode .teacher-manage-table td:nth-child(6),
            body.dark-mode .teacher-manage-table td.campus-cell,
            body.dark-mode .teacher-manage-table td.section-cell,
            body.dark-mode .section-cell,
            body.dark-mode .teacher-table td.section-cell,
            body.dark-mode .teacher-manage-table td.action-cell,
            body.dark-mode .campus-cell,
            body.dark-mode .action-cell,
            body.dark-mode .view-btn-cell,
            body.dark-mode #scheduleCombinedTable td:nth-child(2),
            body.dark-mode #scheduleCombinedTable td:nth-child(3) { 
                background: #0f172a !important; 
            }
            body.dark-mode .teacher-manage-table td,
            body.dark-mode .teacher-table td,
            body.dark-mode .teacher-table tr:nth-child(even) td {
                color: #f1f5f9;
                border: 1px solid #334155 !important;
            }

            /* --- VIBRANT HIGHLIGHT THINGY FOR DARK MODE --- */
            body.dark-mode tr:hover td,
            body.dark-mode .teacher-table tbody tr:hover td,
            body.dark-mode .teacher-manage-table tbody tr:hover td,
            body.dark-mode #scheduleCombinedTable tbody tr:hover td {
                background: #fbbf24 !important;
                color: #1e1b4b !important;
            }
            body.dark-mode tr:hover .subj-code,
            body.dark-mode tr:hover .subj-name,
            body.dark-mode tr:hover .teacher-name-cell,
            body.dark-mode tr:hover .icon-edit-new,
            body.dark-mode tr:hover .icon-view-new {
                color: #1e1b4b !important;
            }
            /* ----------------------------------------------- */

            body.dark-mode .filter-btn, 
            body.dark-mode .filter-dropdown-navy,
            body.dark-mode .settings-group,
            body.dark-mode #navDropdownMenu {
                background: #334155 !important;
                color: #f8fafc !important;
                border: 1px solid #475569 !important;
            }
            body.dark-mode .view-toggle-btn,
            body.dark-mode .toggle-btn {
                background: #1e293b !important;
                color: #f8fafc !important;
                border-color: #fbbf24 !important;
            }
            body.dark-mode .view-toggle-btn.active,
            body.dark-mode .toggle-btn.active {
                background: #fbbf24 !important;
                color: #1e1b4b !important;
            }
            body.dark-mode .view-toggle-btn:not(.active),
            body.dark-mode .toggle-btn.outline:not(.active) {
                background: transparent !important;
                color: #94a3b8 !important;
            }
            /* Target all variants of the dark navy and almost black used */
            body.dark-mode *[style*="color: #1e1b4b"],
            body.dark-mode *[style*="color: #111827"],
            body.dark-mode *[style*="color: #00008B"],
            body.dark-mode *[style*="color: #1e293b"],
            body.dark-mode .icon-edit-new,
            body.dark-mode .dropdown-arrow,
            body.dark-mode #p_username,
            body.dark-mode h1, body.dark-mode h2, body.dark-mode h3,
            body.dark-mode .lab-card h3,
            body.dark-mode *[style*="border-left: 4px solid #1e1b4b"] {
                color: #fbbf24 !important;
            }
            /* Lighten secondary/gray text for visibility */
            body.dark-mode *[style*="color: #64748b"],
            body.dark-mode *[style*="color: #475569"],
            body.dark-mode *[style*="color: #374151"],
            body.dark-mode *[style*="color: #4b5563"],
            body.dark-mode *[style*="color: #94a3b8"] {
                color: #cbd5e1 !important;
            }
            /* Override hardcoded backgrounds in schedule cards */
            body.dark-mode *[style*="background: #ffffff"],
            body.dark-mode *[style*="background: #fdfdfd"],
            body.dark-mode *[style*="background: #fcfcfc"],
            body.dark-mode *[style*="background: #fafafa"],
            body.dark-mode *[style*="background: #f8fafc"],
            body.dark-mode *[style*="background: #f1f5f9"],
            body.dark-mode *[style*="background: #e5e7eb"],
            body.dark-mode *[style*="background: #ede9fe"],
            body.dark-mode *[style*="background: #dbeafe"],
            body.dark-mode *[style*="background: white"] {
                background: #1e293b !important;
            }
            body.dark-mode .ongoing-highlight,
            body.dark-mode *[style*="background: #fef9c3"],
            body.dark-mode *[style*="background: rgba(251, 191, 36"],
            body.dark-mode *[style*="background:rgba(251, 191, 36"] {
                background: rgba(251, 191, 36, 0.3) !important;
                border: 2px solid #fbbf24 !important;
                border-left-width: 6px !important;
                box-shadow: 0 0 15px rgba(251, 191, 36, 0.2) !important;
            }
            /* SVG stroke and fill overrides */
            body.dark-mode svg[stroke*="#1e1b4b"] { stroke: #f8fafc !important; }
            body.dark-mode svg polyline[stroke*="#1e1b4b"] { stroke: #f8fafc !important; }
            body.dark-mode svg path[stroke*="#1e1b4b"] { stroke: #f8fafc !important; }
            body.dark-mode svg path[fill*="#00008B"],
            body.dark-mode svg path[fill*="#1e1b4b"],
            body.dark-mode svg circle[fill*="#1e1b4b"] { fill: #f8fafc !important; }

            /* Match hardcoded navy borders to lighter colors */
            body.dark-mode *[style*="border-left: 3px solid #1e1b4b"],
            body.dark-mode *[style*="border-left: 4px solid #1e1b4b"],
            body.dark-mode *[style*="border-left-color: #1e1b4b"],
            body.dark-mode *[style*="border-left: 4px solid var(--text-dark)"] {
                border-left-color: #fbbf24 !important;
            }
            body.dark-mode *[style*="border-bottom: 2px solid #fbbf24"] {
                border-bottom-color: #fbbf24 !important;
            }
            
            body.dark-mode *[style*="border-left: 5px solid #e2e8f0"] {
                border-left-color: #334155 !important;
            }
            
            /* Preserve accent colors explicitly */
            body.dark-mode *[style*="color: #ef4444"],
            body.dark-mode .icon-delete-new {
                color: #ef4444 !important;
            }
            body.dark-mode *[style*="color: #fbbf24"],
            body.dark-mode .yellow {
                color: #fbbf24 !important;
            }
            body.dark-mode *[style*="color: #10b981"] {
                color: #4ade80 !important;
            }
            body.dark-mode .stat-number {
                color: #fbbf24 !important;
            }
            body.dark-mode .stat-label {
                color: #94a3b8 !important;
            }
            body.dark-mode .stat-icon {
                background: rgba(251, 191, 36, 0.1) !important;
            }
            body.dark-mode #settingsModalOverlay,
            body.dark-mode #profileModalOverlay,
            body.dark-mode #modalOverlay,
            body.dark-mode #roomModalOverlay,
            body.dark-mode #subjectModalOverlay,
            body.dark-mode #labModal {
                background: rgba(0, 0, 0, 0.8) !important;
            }
            body.dark-mode input, body.dark-mode select, body.dark-mode textarea {
                background: #1e293b !important;
                color: #f8fafc !important;
                border-color: #475569 !important;
            }
            body.dark-mode label {
                color: #cbd5e1 !important;
            }
            body.dark-mode .toggle-btn.outline {
                background: #334155 !important;
                color: #f8fafc !important;
                border-color: #475569 !important;
            }
            body.dark-mode .view-toggle-btn.active,
            body.dark-mode .toggle-btn.active,
            body.dark-mode .btn-primary,
            body.dark-mode *[style*="background: #fbbf24"] {
                color: #1e1b4b !important;
            }
            body.dark-mode *[style*="background: #fbbf24"] * {
                color: #1e1b4b !important;
            }
            body.dark-mode .subj-code {
                color: #fbbf24 !important;
            }
            body.dark-mode .subj-name {
                color: #94a3b8;
            }
            body.dark-mode .teacher-name-cell,
            body.dark-mode .room-name-cell,
            body.dark-mode .campus-cell,
            body.dark-mode .status-cell,
            body.dark-mode .section-cell,
            body.dark-mode #scheduleCombinedTable td:nth-child(1),
            body.dark-mode #scheduleCombinedTable td:nth-child(2),
            body.dark-mode #labModalContent td,
            body.dark-mode #labModalContent div[style*="color: #111827"],
            body.dark-mode #labModalContent div[style*="color: #374151"],
            body.dark-mode #labModalContent div[style*="color: #4b5563"],
            body.dark-mode #labModalContent div[style*="color: #6b7280"] {
                color: #f1f5f9 !important;
            }
            /* Target faculty name in schedule cards (hardcoded #4f46e5) */
            body.dark-mode *[style*="color: #4f46e5"],
            body.dark-mode *[style*="color: #3b82f6"] {
                color: #fbbf24 !important;
            }
            /* Add Room / Subject / Modal Buttons */
            body.dark-mode .btn-primary,
            body.dark-mode #roomModalOverlay .btn-primary,
            body.dark-mode #subjectModalOverlay .btn-primary,
            body.dark-mode #modalOverlay .btn-primary {
                background: #fbbf24 !important;
                color: #1e1b4b !important;
            }
            /* Full Schedule Modal Overrides */
            body.dark-mode #labModalContent *[style*="background: #f8fafc"],
            body.dark-mode #labModalContent *[style*="background: white"],
            body.dark-mode #labModalContent *[style*="background: #f1f5f9"] {
                background: #1e293b !important;
                border-color: #334155 !important;
            }
            body.dark-mode #labModalContent h4 {
                color: #fbbf24 !important;
                border-bottom-color: #fbbf24 !important;
            }
            /* Ensure the room name in the list is visible */
            body.dark-mode #labModalContent div[style*="color: #6b7280"] {
                color: #cbd5e1 !important;
            }
            body.dark-mode #labModalContent div[style*="background: #f8fafc"] {
                background: #0f172a !important;
                border: 1px solid #1e293b !important;
            }
            /* Faculty Full Schedule View Overrides */
            body.dark-mode #labModalContent div[style*="background: white"] {
                background: #1e293b !important;
                border-color: #334155 !important;
            }
            body.dark-mode #labModalContent div[style*="background: #f9fafb"] {
                background: #0f172a !important;
                border-bottom: 2px solid #fbbf24 !important;
            }
            body.dark-mode #labModalContent div[style*="border-bottom: 1px solid #f3f4f6"] {
                border-bottom-color: #334155 !important;
            }
            body.dark-mode #labModalContent h2[style*="color: #1e1b4b"] {
                color: #f8fafc !important;
            }
            body.dark-mode #labModalContent h3[style*="color: #111827"] {
                color: #fbbf24 !important;
            }
            /* Row text visibility */
            body.dark-mode #labModalContent div[style*="color: #111827"],
            body.dark-mode #labModalContent div[style*="color: #374151"],
            body.dark-mode #labModalContent div[style*="color: #4b5563"] {
                color: #f1f5f9 !important;
            }
            body.dark-mode #labModalContent div[style*="color: #6b7280"] {
                color: #cbd5e1 !important;
            }
        `;
        document.head.appendChild(style);
    }
}

function updateThemeButtons(theme) {
    const lightBtn = document.getElementById('lightThemeBtn');
    const darkBtn = document.getElementById('darkThemeBtn');
    if (!lightBtn || !darkBtn) return;

    if (theme === 'light') {
        lightBtn.style.borderColor = '#fbbf24';
        lightBtn.style.background = '#fffbeb';
        darkBtn.style.borderColor = '#e2e8f0';
        darkBtn.style.background = 'white';
        // Reset colors
        lightBtn.querySelector('span').style.color = '#1e1b4b';
        darkBtn.querySelector('span').style.color = '#64748b';
    } else {
        darkBtn.style.borderColor = '#fbbf24';
        darkBtn.style.background = '#1e293b'; 
        darkBtn.querySelector('span').style.color = '#fbbf24';
        lightBtn.style.borderColor = '#334155';
        lightBtn.style.background = '#1e293b';
        lightBtn.querySelector('span').style.color = '#64748b';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);
});

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

    // Shared time parsing logic for both faculty and schedule branches
    const parseT = (t) => {
        if (!t) return { h: 8, m: 0, ampm: 'AM' };
        let [hh, mm] = t.split(':');
        let h24 = parseInt(hh);
        return {
            h: h24 % 12 || 12,
            m: parseInt(mm) || 0,
            ampm: h24 >= 12 ? 'PM' : 'AM'
        };
    };

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
                <div class="form-group">
                    <label>Room</label>
                    <select id="m_room_name" class="m_room_select"></select>
                </div>
                <div class="form-group">
                    <label>Day</label>
                    <select id="m_day">
                        ${['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'].map(d => `<option value="${d}" ${options.day === d ? 'selected' : ''}>${d}</option>`).join('')}
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 10px;">
                    <div class="form-group">
                        <label>Start Time</label>
                        <input type="hidden" id="m_start" value="${options.startTime || ''}">
                        <div id="startTimePicker" style="display: flex; flex-direction: column; gap: 12px; background: #ffffff; border: 2px solid #fbbf24; border-radius: 12px; padding: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); margin-top: 10px;">
                            <div style="display: flex; align-items: center; gap: 8px; justify-content: center;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                    <span style="font-size: 0.65rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Hour</span>
                                    <select id="m_start_hr" size="7" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 5px; font-size: 1.1rem; font-weight: 700; color: #1e1b4b; cursor: pointer; width: 60px; text-align: center; border-bottom: 3px solid #fbbf24;">
                                        ${[...Array(12)].map((_, i) => `<option value="${i + 1}" ${parseT(options.startTime).h === (i + 1) ? 'selected' : ''}>${String(i + 1).padStart(2, '0')}</option>`).join('')}
                                    </select>
                                </div>
                                <span style="font-weight:900; color:#cbd5e1; font-size: 1.5rem; margin-top: 18px;">:</span>
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                    <span style="font-size: 0.65rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Min</span>
                                    <select id="m_start_min" size="7" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 5px; font-size: 1.1rem; font-weight: 700; color: #1e1b4b; cursor: pointer; width: 60px; text-align: center; border-bottom: 3px solid #fbbf24;">
                                        ${[...Array(60)].map((_, i) => `<option value="${i}" ${parseT(options.startTime).m === i ? 'selected' : ''}>${String(i).padStart(2, '0')}</option>`).join('')}
                                    </select>
                                </div>
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                    <span style="font-size: 0.65rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Period</span>
                                    <select id="m_start_ampm" size="2" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 5px; font-size: 1.1rem; font-weight: 800; color: #1e1b4b; cursor: pointer; width: 70px; text-align: center; border-bottom: 3px solid #fbbf24;">
                                        <option value="AM" ${parseT(options.startTime).ampm === 'AM' ? 'selected' : ''}>AM</option>
                                        <option value="PM" ${parseT(options.startTime).ampm === 'PM' ? 'selected' : ''}>PM</option>
                                    </select>
                                </div>
                            </div>
                            <button type="button" onclick="confirmTime('start')" style="width: 100%; padding: 12px; background: #fbbf24; color: #1e1b4b; border: none; border-radius: 10px; font-weight: 800; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 6px rgba(251, 191, 36, 0.2); margin-top: 4px;" onmouseover="this.style.background='#f59e0b'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#fbbf24'; this.style.transform='none'">OK</button>
                        </div>
                        <div id="startTimeDisplay" style="display: none; margin-top: 4px; font-size: 0.9rem; font-weight: 600; color: #1e1b4b; padding: 8px 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; text-align: center; cursor: pointer;" onclick="document.getElementById('startTimePicker').style.display='flex'; this.style.display='none';"></div>
                    </div>
                    <div class="form-group">
                        <label>End Time</label>
                        <input type="hidden" id="m_end" value="${options.endTime || ''}">
                        <div id="endTimePicker" style="display: flex; flex-direction: column; gap: 12px; background: #ffffff; border: 2px solid #fbbf24; border-radius: 12px; padding: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); margin-top: 10px;">
                            <div style="display: flex; align-items: center; gap: 8px; justify-content: center;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                    <span style="font-size: 0.65rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Hour</span>
                                    <select id="m_end_hr" size="7" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 5px; font-size: 1.1rem; font-weight: 700; color: #1e1b4b; cursor: pointer; width: 60px; text-align: center; border-bottom: 3px solid #fbbf24;">
                                        ${[...Array(12)].map((_, i) => `<option value="${i + 1}" ${parseT(options.endTime).h === (i + 1) ? 'selected' : ''}>${String(i + 1).padStart(2, '0')}</option>`).join('')}
                                    </select>
                                </div>
                                <span style="font-weight:900; color:#cbd5e1; font-size: 1.5rem; margin-top: 18px;">:</span>
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                    <span style="font-size: 0.65rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Min</span>
                                    <select id="m_end_min" size="7" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 5px; font-size: 1.1rem; font-weight: 700; color: #1e1b4b; cursor: pointer; width: 60px; text-align: center; border-bottom: 3px solid #fbbf24;">
                                        ${[...Array(60)].map((_, i) => `<option value="${i}" ${parseT(options.endTime).m === i ? 'selected' : ''}>${String(i).padStart(2, '0')}</option>`).join('')}
                                    </select>
                                </div>
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                    <span style="font-size: 0.65rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Period</span>
                                    <select id="m_end_ampm" size="2" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 5px; font-size: 1.1rem; font-weight: 800; color: #1e1b4b; cursor: pointer; width: 70px; text-align: center; border-bottom: 3px solid #fbbf24;">
                                        <option value="AM" ${parseT(options.endTime).ampm === 'AM' ? 'selected' : ''}>AM</option>
                                        <option value="PM" ${parseT(options.endTime).ampm === 'PM' ? 'selected' : ''}>PM</option>
                                    </select>
                                </div>
                            </div>
                            <button type="button" onclick="confirmTime('end')" style="width: 100%; padding: 12px; background: #fbbf24; color: #1e1b4b; border: none; border-radius: 10px; font-weight: 800; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 6px rgba(251, 191, 36, 0.2); margin-top: 4px;" onmouseover="this.style.background='#f59e0b'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#fbbf24'; this.style.transform='none'">OK</button>
                        </div>
                        <div id="endTimeDisplay" style="display: none; margin-top: 4px; font-size: 0.9rem; font-weight: 600; color: #1e1b4b; padding: 8px 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; text-align: center; cursor: pointer;" onclick="document.getElementById('endTimePicker').style.display='flex'; this.style.display='none';"></div>
                    </div>
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
                <div class="form-group">
                    <label>Room</label>
                    <select id="m_room_name" class="m_room_select"></select>
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
                <div class="form-group">
                    <label>Room</label>
                    <select id="m_room_name" class="m_room_select"></select>
                </div>
            `;
        }

        // Fetch rooms to populate placeholders
        fetch('api/rooms.php', { cache: 'no-store' })
            .then(r => r.json())
            .then(rooms => {
                const selects = document.querySelectorAll('.m_room_select');
                selects.forEach(sel => {
                    sel.innerHTML = '';
                    rooms.forEach(room => {
                        const opt = document.createElement('option');
                        opt.value = room.name;
                        opt.textContent = room.name;
                        if (options.roomName === room.name) opt.selected = true;
                        sel.appendChild(opt);
                    });
                });
            });
    } else if (section === 'subjects') {
    } else if (section === 'subjects') {
        fields.innerHTML = '<div style="text-align: center; padding: 1rem;">Loading curriculum data...</div>';
        fetch('api/curricula.php').then(r => r.json()).then(curricula => {
            fields.innerHTML = `
                <div class="form-group">
                    <label>Subject Code</label>
                    <input type="text" id="m_code" value="${options.code || ''}" placeholder="e.g. IT-101" required>
                </div>
                <div class="form-group">
                    <label>Subject Name</label>
                    <input type="text" id="m_name" value="${options.name || ''}" placeholder="e.g. Programming 1" required>
                </div>
                <div class="form-group">
                    <label>Units</label>
                    <input type="number" id="m_units" value="${options.units || 3}" required>
                </div>
            `;
        });
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
                const aName = (a.name || '').toUpperCase();
                const bName = (b.name || '').toUpperCase();
                const aIsLab = aName.startsWith('COMLAB') || aName.startsWith('COMPLAB');
                const bIsLab = bName.startsWith('COMLAB') || bName.startsWith('COMPLAB');
                if (aIsLab && !bIsLab) return -1;
                if (!aIsLab && bIsLab) return 1;
                return aName.localeCompare(bName, undefined, { numeric: true, sensitivity: 'base' });
            });

            title.innerHTML = options.id ? 'Edit Room Schedule' : 'Schedule Management';
            
            // Use shared parseT logic
            const sTime = parseT(options.start_time);
            const eTime = parseT(options.end_time);

            fields.innerHTML = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group" id="roomGroup">
                        <label>Name Room</label>
                        <select id="m_room_id" onchange="toggleTypedInput('room'); updateCampusWarning();" required>
                            <option value="">Select Room</option>
                            ${rooms.map(r => `<option value="${r.id}" ${options.room_id == r.id ? 'selected' : ''}>${r.name.replace(/COMPLAB/g, 'COMLAB')}</option>`).join('')}
                            <option value="other" style="color: #4f46e5; font-weight: 800;">+ Add Room</option>
                        </select>
                        <div id="roomTypedInputs" style="display: none; margin-top: 6px; border: 2px dashed #fbbf24; padding: 8px; border-radius: 10px; background: rgba(30, 27, 75, 0.05);">
                            <input type="text" id="m_room_name" placeholder="e.g. COMLAB 10" style="font-size: 0.85rem;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Day</label>
                        <select id="m_day" required>
                            ${['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'].map(d => `<option value="${d}" ${options.day === d ? 'selected' : ''}>${d}</option>`).join('')}
                        </select>
                    </div>
                </div>

                <div class="form-group" id="subjectGroup">
                    <label>Subject</label>
                    <select id="m_subject_id" onchange="toggleTypedInput('subject')" required>
                        <option value="">Select Subject</option>
                        ${subjects.map(s => `<option value="${s.id}" ${options.subject_id == s.id ? 'selected' : ''}>${s.code} - ${s.name}</option>`).join('')}
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
                        ${faculty.map(f => `<option value="${f.id}" ${options.faculty_id == f.id ? 'selected' : ''}>${f.name}</option>`).join('')}
                        <option value="other" style="color: #4f46e5; font-weight: 800;">+ Type New Teacher</option>
                    </select>
                    <div id="facultyTypedInputs" style="display: none; margin-top: 6px; border: 2px dashed #fbbf24; padding: 8px; border-radius: 10px; background: rgba(30, 27, 75, 0.05);">
                        <input type="text" id="m_faculty_name" placeholder="Enter Teacher Name" style="font-size: 0.85rem;">
                    </div>
                </div>

                <div id="campusWarning" style="display: none; padding: 10px; margin-top: 5px; border-radius: 8px; background-color: #fff7ed; border: 1px solid #fdba74; color: #9a3412; font-size: 0.8rem; font-weight: 600; align-items: flex-start; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <span id="campusWarningText"></span>
                </div>

                <div style="margin-top: 10px;">
                    <div style="display: grid; grid-template-columns: 1fr; gap: 15px; margin-top: 0.5rem;">
                        <div class="form-group">
                            <label>Start Time</label>
                            <input type="hidden" id="m_start" value="${options.start_time || ''}">
                             <div id="startTimePicker" style="display: flex; flex-direction: column; gap: 8px; background: #ffffff; border: 2px solid #fbbf24; border-radius: 12px; padding: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-top: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px; justify-content: center;">
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                        <span style="font-size: 0.65rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Hour</span>
                                        <select id="m_start_hr" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 12px; font-size: 1.1rem; font-weight: 700; color: #1e1b4b; cursor: pointer; width: 65px; text-align: center;">
                                            ${[...Array(12)].map((_, i) => `<option value="${i + 1}" ${sTime.h === (i + 1) ? 'selected' : ''}>${String(i + 1).padStart(2, '0')}</option>`).join('')}
                                        </select>
                                    </div>
                                    <span style="font-weight:900; color:#cbd5e1; font-size: 1.5rem; margin-top: 12px;">:</span>
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                        <span style="font-size: 0.65rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Min</span>
                                        <select id="m_start_min" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 12px; font-size: 1.1rem; font-weight: 700; color: #1e1b4b; cursor: pointer; width: 65px; text-align: center;">
                                            ${[...Array(60)].map((_, i) => `<option value="${i}" ${sTime.m === i ? 'selected' : ''}>${String(i).padStart(2, '0')}</option>`).join('')}
                                        </select>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                        <span style="font-size: 0.65rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Period</span>
                                        <select id="m_start_ampm" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 12px; font-size: 1.1rem; font-weight: 800; color: #1e1b4b; cursor: pointer; width: 75px; text-align: center;">
                                            <option value="AM" ${sTime.ampm === 'AM' ? 'selected' : ''}>AM</option>
                                            <option value="PM" ${sTime.ampm === 'PM' ? 'selected' : ''}>PM</option>
                                        </select>
                                    </div>
                                    <button type="button" onclick="confirmTime('start')" style="margin-top: 15px; padding: 8px 15px; background: #fbbf24; color: #1e1b4b; border: none; border-radius: 12px; font-weight: 900; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 8px rgba(0,0,0,0.1); text-transform: uppercase;" onmouseover="this.style.background='#f59e0b'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#fbbf24'; this.style.transform='none'">OK</button>
                                </div>
                            </div>
                            <div id="startTimeDisplay" style="display: none; margin-top: 4px; font-size: 0.9rem; font-weight: 600; color: #1e1b4b; padding: 8px 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; text-align: center; cursor: pointer;" onclick="document.getElementById('startTimePicker').style.display='flex'; this.style.display='none';">${options.start_time ? '✓ ' + formatTime(options.start_time) : ''}</div>
                        </div>
                        <div class="form-group">
                            <label>End Time</label>
                             <div id="endTimePicker" style="display: flex; flex-direction: column; gap: 8px; background: #ffffff; border: 2px solid #fbbf24; border-radius: 12px; padding: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-top: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px; justify-content: center;">
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                        <span style="font-size: 0.65rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Hour</span>
                                        <select id="m_end_hr" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 12px; font-size: 1.1rem; font-weight: 700; color: #1e1b4b; cursor: pointer; width: 65px; text-align: center;">
                                            ${[...Array(12)].map((_, i) => `<option value="${i + 1}" ${eTime.h === (i + 1) ? 'selected' : ''}>${String(i + 1).padStart(2, '0')}</option>`).join('')}
                                        </select>
                                    </div>
                                    <span style="font-weight:900; color:#cbd5e1; font-size: 1.5rem; margin-top: 12px;">:</span>
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                        <span style="font-size: 0.65rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Min</span>
                                        <select id="m_end_min" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 12px; font-size: 1.1rem; font-weight: 700; color: #1e1b4b; cursor: pointer; width: 65px; text-align: center;">
                                            ${[...Array(60)].map((_, i) => `<option value="${i}" ${eTime.m === i ? 'selected' : ''}>${String(i).padStart(2, '0')}</option>`).join('')}
                                        </select>
                                    </div>
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                        <span style="font-size: 0.65rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Period</span>
                                        <select id="m_end_ampm" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 12px; font-size: 1.1rem; font-weight: 800; color: #1e1b4b; cursor: pointer; width: 75px; text-align: center;">
                                            <option value="AM" ${eTime.ampm === 'AM' ? 'selected' : ''}>AM</option>
                                            <option value="PM" ${eTime.ampm === 'PM' ? 'selected' : ''}>PM</option>
                                        </select>
                                    </div>
                                    <button type="button" onclick="confirmTime('end')" style="margin-top: 15px; padding: 8px 15px; background: #fbbf24; color: #1e1b4b; border: none; border-radius: 12px; font-weight: 900; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 8px rgba(0,0,0,0.1); text-transform: uppercase;" onmouseover="this.style.background='#f59e0b'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#fbbf24'; this.style.transform='none'">OK</button>
                                </div>
                            </div>
                            <div id="endTimeDisplay" style="display: none; margin-top: 4px; font-size: 0.9rem; font-weight: 600; color: #1e1b4b; padding: 8px 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; text-align: center; cursor: pointer;" onclick="document.getElementById('endTimePicker').style.display='flex'; this.style.display='none';">${options.end_time ? '✓ ' + options.end_time : ''}</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Section</label>
                    <input type="text" id="m_section" placeholder="e.g. AI32" value="${options.section || ''}">
                </div>
            `;
            if (options.room_id) updateCampusWarning();
        });
    }
}

function confirmTime(type) {
    const isInline = type.includes('edit_');
    const prefix = isInline ? type : ((type === 'start' || type.includes('start')) ? 'm_start' : 'm_end');
    
    // Select the correct elements based on the prefix (which includes row ID for inline)
    const hr = parseInt(document.getElementById(prefix + '_hr').value);
    const min = parseInt(document.getElementById(prefix + '_min').value);
    const ampm = document.getElementById(prefix + '_ampm').value;

    // Convert to 24hr for the hidden input/database
    let hr24 = hr;
    if (ampm === 'AM' && hr === 12) hr24 = 0;
    else if (ampm === 'PM' && hr !== 12) hr24 = hr + 12;

    const timeValue = String(hr24).padStart(2, '0') + ':' + String(min).padStart(2, '0');
    
    if (isInline) {
        // For inline edit, update the actual time input value
        document.getElementById(type).value = timeValue;
    } else {
        // For modal, update the hidden field
        document.getElementById('m_' + type).value = timeValue;
    }

    // Show display and hide picker
    const displayText = String(hr).padStart(2, '0') + ':' + String(min).padStart(2, '0') + ' ' + ampm;
    const pickerEl = document.getElementById(type + 'TimePicker');
    
    if (isInline) {
        // For inline, just hide the picker (the input already shows the value)
        pickerEl.style.display = 'none';
    } else {
        // For modal, update display text and toggle visibility
        const displayEl = document.getElementById(type + 'TimeDisplay');
        displayEl.textContent = '✓ ' + displayText;
        displayEl.style.display = 'block';
        pickerEl.style.display = 'none';
    }
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
                const roomNameVal = document.getElementById('m_room_name')?.value || '';
                const dayVal = document.getElementById('m_day')?.value || 'Monday';
                const startVal = document.getElementById('m_start')?.value || '08:00';
                const endVal = document.getElementById('m_end')?.value || '09:00';

                const schedRes = await fetch(`api/schedules.php?id=${editingScheduleId}&term_id=${window.activeTermId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        section: sectionsVal,
                        subject_code: subjCodeVal,
                        subject_name: subjNameVal,
                        room_name: roomNameVal,
                        day: dayVal,
                        start_time: startVal,
                        end_time: endVal
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
                const room = document.getElementById('m_room_name')?.value || 'TBA';
                
                if (subject) {
                    await fetch('api/schedules.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            faculty_id: editingFacultyId,
                            subject_code: subject,
                            subject_name: subject,
                            section: section,
                            room_name: room,
                            day: 'Monday',
                            start_time: '08:00',
                            end_time: '09:00',
                            term_id: window.activeTermId
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
                const room = document.getElementById('m_room_name')?.value || 'TBA';
                await fetch('api/schedules.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        faculty_id: facSaved.id,
                        subject_code: subject,
                        subject_name: subject,
                        section: section,
                        room_name: room,
                        day: 'Monday',
                        start_time: '08:00',
                        end_time: '09:00',
                        term_id: window.activeTermId
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
    
    if (currentSection === 'subjects') {
        data.code = document.getElementById('m_code').value;
        data.name = document.getElementById('m_name').value;
        data.units = document.getElementById('m_units').value;
        data.curriculum_id = document.getElementById('m_curriculum_id').value;
        data.term_id = window.activeTermId;
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
        data.term_id = window.activeTermId;

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
        const method = (currentSection === 'schedules' && editingScheduleId) ? 'PUT' : 'POST';
        const url = (currentSection === 'schedules' && editingScheduleId) 
                    ? `api/schedules.php?id=${editingScheduleId}&term_id=${window.activeTermId}` 
                    : `api/${currentSection}.php`;

        const res = await fetch(url, {
            method: method,
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
            fetch(`api/faculty.php`, { cache: 'no-store' }),
            fetch(`api/teacher_schedule.php?term_id=${window.activeTermId}`, { cache: 'no-store' })
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
                            ${getActionIcons(teacher.id, teacher.name, '', null, employmentStatus, '', '', campus, '', '', '', '')}
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
                            scheduleId: sched.id,
                            roomName: sched.room_name,
                            day: sched.day,
                            startTime: sched.start_time,
                            endTime: sched.end_time
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
                                ${getActionIcons(teacher.id, teacher.name, group.code, group.scheduleId, employmentStatus, group.name || group.subject, sectionsList, campus, group.roomName, group.day, group.startTime, group.endTime)}
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

function getActionIcons(facultyId, teacherName, subjectCode, scheduleId, employmentStatus, subjectLabel, sectionsText, campus, roomName, day, startTime, endTime) {
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
              data-room-name="${roomName || ''}"
              data-day="${day || ''}"
              data-start-time="${startTime || ''}"
              data-end-time="${endTime || ''}"
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
        const res = await fetch(`api/teacher_schedule.php?term_id=${window.activeTermId}`);
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
        campus: el.dataset.campus || 'Main Campus',
        roomName: el.dataset.roomName || '',
        day: el.dataset.day || '',
        startTime: el.dataset.startTime || '',
        endTime: el.dataset.endTime || ''
    });
}

async function loadSectionData(section) {
    const url = (section === 'subjects' || section === 'schedules') ? `api/${section}.php?term_id=${window.activeTermId}` : `api/${section}.php`;
    const res = await fetch(url, { cache: 'no-store' });
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
                <td>${formatTime(item.start_time)} - ${formatTime(item.end_time)}</td>
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
            const url = section === 'schedules' ? `api/${section}.php?term_id=${window.activeTermId}` : `api/${section}.php`;
            const res = await fetch(url, { cache: 'no-store' });
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
        clock.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
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
            fetch(`api/lab_schedule.php?term_id=${window.activeTermId}`, { cache: 'no-store' })
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
        opt.textContent = name.toUpperCase().replace(/COMPLAB/g, 'COMLAB').replace('COMLAB ', 'COMLAB').replace('COMLAB', 'COMLAB ');
        filter.appendChild(opt);
    });
    // Restore previous selection if it still exists
    if ([...filter.options].some(o => o.value === previousValue)) {
        filter.value = previousValue;
    }

    const selectedRoomFilter = document.getElementById('roomFilter')?.value || 'all';
    const selectedTeacherFilter = document.getElementById('homeTeacherFilter')?.value || 'all';

    grid.innerHTML = '';

    const roomsToShow = selectedRoomFilter === 'all' ? sortedRoomNames : [selectedRoomFilter];

    const now = new Date();
    const todayName = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][now.getDay()];

    roomsToShow.forEach(labName => {
        let roomSchedules = allSchedules.filter(s => s.room_name === labName);

        // Filter by teacher if selected
        if (selectedTeacherFilter !== 'all') {
            roomSchedules = roomSchedules.filter(s => s.faculty_name === selectedTeacherFilter);
            if (roomSchedules.length === 0 && selectedRoomFilter !== 'all') {
                // If specific room selected but teacher not there, show nothing
                return;
            } else if (roomSchedules.length === 0) {
                // If "all labs" but teacher not in this specific lab, skip this card
                return;
            }
        }

        const card = document.createElement('div');
        card.className = 'lab-card';
        card.onclick = () => viewLabSchedule(labName, roomSchedules, true);
        
        // Match home page styling exactly
        card.style.cssText = `
            cursor: pointer;
            height: 480px;
            max-height: 480px;
            background: white;
            border: 1.5px solid #fbbf24;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: all 0.2s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.02);
            position: relative;
            margin: 0.5rem;
        `;
        card.onmouseover = () => { 
            card.style.boxShadow = '0 10px 25px rgba(0,0,0,0.08)'; 
            card.style.transform = 'translateY(-4px)'; 
        };
        card.onmouseout  = () => { 
            card.style.boxShadow = '0 5px 15px rgba(0,0,0,0.02)'; 
            card.style.transform = 'none'; 
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
                    slotsHtml += `<div style="margin-top: 1rem; margin-bottom: 0.6rem; padding-left: 8px; border-left: 4px solid #1e1b4b; font-weight: 500; color: #1e1b4b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center;">${day}</div>`;

                    groupedByDay[day].forEach(s => {
                        const timeRange = `${formatTime(s.start_time)} - ${formatTime(s.end_time)}`;
                        const startSecs = getSecs(s.start_time);
                        const endSecs = getSecs(s.end_time);

                        const isOngoing = (day === todayName) && (currentSecs >= startSecs && currentSecs <= endSecs);
                        const status = isOngoing ? "ONGOING" : "SCHEDULED";
                        const highlightStyle = isOngoing ? 'background: rgba(251, 191, 36, 0.15); border: 2px solid #fbbf24; border-left: 6px solid #fbbf24;' : 'background: white; border: 1px solid #e2e8f0;';
                        const lastName = (s.faculty_name || '').split(' ').filter(Boolean).slice(-1)[0]?.toUpperCase() || '---';

                        slotsHtml += `
                            <div class="${isOngoing ? 'ongoing-highlight' : ''}" style="${highlightStyle} padding: 0.8rem; border-radius: 8px; margin-bottom: 0.6rem; flex-shrink: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: relative;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <span style="font-size: 0.65rem; font-weight: 500; background: ${isOngoing ? '#fbbf24' : '#f1f5f9'}; color: ${isOngoing ? '#1e1b4b' : '#64748b'}; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px;">${status}</span>
                                    <span style="font-size: 0.72rem; font-weight: 400; color: ${isOngoing ? '#1e1b4b' : '#64748b'}; background: ${isOngoing ? 'rgba(30, 27, 75, 0.1)' : 'transparent'}; padding: ${isOngoing ? '2px 6px' : '0'}; border-radius: 4px;">${timeRange}</span>
                                </div>
                                <div style="margin-bottom: 4px;">
                                    <h4 style="font-size: 0.95rem; font-weight: 500; color: ${isOngoing ? '#fbbf24' : '#1e1b4b'}; line-height: 1.3; margin: 0; font-family: 'Inter', sans-serif;">${s.subject_code || ''} ${s.subject_name || ''}</h4>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                                    <div style="display: flex; align-items: center; gap: 5px; color: ${isOngoing ? '#fbbf24' : '#3b82f6'}; font-weight: 500; font-size: 0.8rem;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" style="opacity: 0.7;"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg> ${lastName}
                                    </div>
                                    <div style="font-size: 0.72rem; color: ${isOngoing ? '#fbbf24' : '#64748b'}; font-weight: 400;">
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
                    <p style="margin: 0; font-weight: 500; opacity: 0.6;">No Schedule</p>
                </div>
            `;
        }

        const displayTitle = labName.toUpperCase().replace(/\s+/g, '').replace(/COMP?LAB/g, 'COMLAB').replace(/COMLAB/g, 'COMLAB ');

        card.innerHTML = `
            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; padding: 1.2rem 1.5rem 0.6rem; margin: 0; color: #1e1b4b; height: auto; overflow: visible; display: block; font-weight: 600; letter-spacing: -0.3px; line-height: 1.2;">${displayTitle}</h3>
            <div class="schedule-label" style="display: inline-block; margin-bottom: 0.8rem; width: 100%;">
                <div style="display: flex; align-items: center; gap: 0.6rem; color: #1e1b4b; border-bottom: 2px solid #fbbf24; padding-bottom: 6px; margin: 0 1rem;">
                    <div class="icon" style="background: #fbbf24; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 1rem; box-shadow: 0 4px 8px rgba(251, 191, 36, 0.1);">
                        📅
                    </div>
                    <strong style="font-size: 0.95rem; font-family: 'Inter', sans-serif; font-weight: 500; letter-spacing: -0.2px;">Schedules:</strong>
                </div>
            </div>
            <div class="custom-scrollbar" style="display: flex; flex-direction: column; flex-grow: 1; overflow-y: auto; padding: 0.8rem; background: #fdfdfd; border-radius: 12px; margin: 0 1rem 1rem; border: 1px solid #f1f5f9;">
                <div>
                    ${slotsHtml}
                </div>
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
                const displayName = room.name.replace(/COMPLAB/g, 'COMLAB');
                filter.innerHTML += `<option value="${room.name}">${displayName}</option>`;
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
            const res = await fetch(`api/subjects.php?term_id=${window.activeTermId}`, { cache: 'no-store' });
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
            const displayName = room.name.replace(/COMPLAB/g, 'COMLAB');
            roomSelect.innerHTML += `<option value="${room.id}">${displayName}</option>`;
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
            fetch(`api/lab_schedule.php?term_id=${window.activeTermId}`, { cache: 'no-store' }),
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
        // Exclude TBA and any other placeholder rooms from the display
        const EXCLUDED_ROOMS = ['TBA', 'TBA ROOM', 'TO BE ANNOUNCED'];
        const allRoomNamesMap = new Set();
        Object.keys(groupedSchedules).forEach(n => {
            const normalized = n.replace(/COMPLAB/g, 'COMLAB');
            if (!EXCLUDED_ROOMS.some(ex => normalized.toUpperCase() === ex)) allRoomNamesMap.add(normalized);
        });
        roomsDb.forEach(r => {
            const normalized = r.name.replace(/COMPLAB/g, 'COMLAB');
            if (!EXCLUDED_ROOMS.some(ex => normalized.toUpperCase() === ex)) allRoomNamesMap.add(normalized);
        });

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
            if (selectedRoom !== 'all') {
                if (selectedRoom === 'OTHER ROOMS') {
                    if (roomOrder.includes(labName)) return;
                } else if (selectedRoom !== labName) {
                    return;
                }
            }

            const legacyName = labName.replace(/COMLAB/g, 'COMPLAB');
            const roomSchedules = groupedSchedules[labName] || groupedSchedules[legacyName] || [];

            const now = new Date();
            const todayName = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][now.getDay()];
            const currentSecs = now.getHours() * 3600 + now.getMinutes() * 60;

            const fmtT = t => {
                if (!t) return '---';
                const [h, m] = t.split(':');
                let hr = parseInt(h);
                const ampm = hr >= 12 ? 'PM' : 'AM';
                hr = hr % 12 || 12;
                return `${hr}:${m} ${ampm}`;
            };

            const card = document.createElement('div');
            card.onclick = () => viewLabSchedule(labName, roomSchedules, false);
            card.style.cssText = `
                cursor: pointer;
                height: 480px;
                max-height: 480px;
                background: white;
                border: 1.5px solid #fbbf24;
                border-radius: 12px;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                transition: all 0.2s ease;
                box-shadow: 0 5px 15px rgba(0,0,0,0.02);
                position: relative;
                margin: 0.5rem;
            `;
            card.onmouseover = () => { card.style.boxShadow = '0 10px 25px rgba(0,0,0,0.08)'; card.style.transform = 'translateY(-4px)'; };
            card.onmouseout  = () => { card.style.boxShadow = '0 5px 15px rgba(0,0,0,0.02)'; card.style.transform = 'none'; };

            // Build schedule HTML
            let slotsHtml = '';
            const dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

            if (roomSchedules.length > 0) {
                const groupedByDay = {};
                roomSchedules.forEach(s => {
                    const d = s.day || s.day_name;
                    if (!groupedByDay[d]) groupedByDay[d] = [];
                    groupedByDay[d].push(s);
                });

                dayOrder.forEach(day => {
                    if (!groupedByDay[day]) return;
                    const daySched = groupedByDay[day].sort((a, b) => a.start_time.localeCompare(b.start_time));
                    const isToday = day === todayName;

                    slotsHtml += `<div style="margin-top: 1rem; margin-bottom: 0.6rem; padding-left: 8px; border-left: 4px solid #1e1b4b; font-weight: 500; color: #1e1b4b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center;">${day}</div>`;

                    daySched.forEach((s) => {
                        const getSecs = t => { const [h, m] = t.split(':'); return parseInt(h) * 3600 + parseInt(m) * 60; };
                        const isOngoing = isToday && currentSecs >= getSecs(s.start_time) && currentSecs <= getSecs(s.end_time);
                        const lastName = (s.faculty_name || '').split(' ').filter(Boolean).slice(-1)[0]?.toUpperCase() || '---';

                        const status = isOngoing ? "ONGOING" : "SCHEDULED";
                        const highlightStyle = isOngoing ? 'background: rgba(251, 191, 36, 0.15); border: 2px solid #fbbf24; border-left: 6px solid #fbbf24;' : 'background: white; border: 1px solid #e2e8f0;';

                        slotsHtml += `
                            <div class="${isOngoing ? 'ongoing-highlight' : ''}" style="${highlightStyle} padding: 0.8rem; border-radius: 8px; margin-bottom: 0.6rem; flex-shrink: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: relative;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <span style="font-size: 0.65rem; font-weight: 500; background: ${isOngoing ? '#fbbf24' : '#f1f5f9'}; color: ${isOngoing ? '#1e1b4b' : '#64748b'}; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px;">${status}</span>
                                    <span style="font-size: 0.72rem; font-weight: 400; color: ${isOngoing ? '#1e1b4b' : '#64748b'}; background: ${isOngoing ? 'rgba(30, 27, 75, 0.1)' : 'transparent'}; padding: ${isOngoing ? '2px 6px' : '0'}; border-radius: 4px;">${fmtT(s.start_time)} - ${fmtT(s.end_time)}</span>
                                </div>
                                <div style="margin-bottom: 4px;">
                                    <h4 style="font-size: 0.95rem; font-weight: 500; color: ${isOngoing ? '#fbbf24' : '#1e1b4b'}; line-height: 1.3; margin: 0; font-family: 'Inter', sans-serif;">${s.subject_code || ''} ${s.subject_name || ''}</h4>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                                    <div style="display: flex; align-items: center; gap: 5px; color: ${isOngoing ? '#fbbf24' : '#3b82f6'}; font-weight: 500; font-size: 0.8rem;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" style="opacity: 0.7;"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg> ${lastName}
                                    </div>
                                    <div style="font-size: 0.72rem; color: ${isOngoing ? '#fbbf24' : '#64748b'}; font-weight: 400;">
                                        Sec: ${s.section || '---'}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                });
            } else {
                slotsHtml = `
                    <div style="background: white; padding: 3rem 1.5rem; border-radius: 12px; text-align: center; color: #94a3b8; border: 2px dashed #f1f5f9; flex-grow: 1; display: flex; align-items: center; justify-content: center;">
                        <p style="margin: 0; font-weight: 500; opacity: 0.6;">No Schedule</p>
                    </div>
                `;
            }

            const displayTitle = labName.toUpperCase().replace(/\s+/g, '').replace(/COMP?LAB/g, 'COMLAB').replace(/COMLAB/g, 'COMLAB ');

            card.innerHTML = `
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; padding: 1.2rem 1.5rem 0.6rem; margin: 0; color: #1e1b4b; height: auto; overflow: visible; display: block; font-weight: 600; letter-spacing: -0.3px; line-height: 1.2;">${displayTitle}</h3>
                <div class="schedule-label" style="display: inline-block; margin-bottom: 0.8rem; width: 100%;">
                    <div style="display: flex; align-items: center; gap: 0.6rem; color: #1e1b4b; border-bottom: 2px solid #fbbf24; padding-bottom: 6px; margin: 0 1rem;">
                        <div class="icon" style="background: #fbbf24; width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 1rem; box-shadow: 0 4px 8px rgba(251, 191, 36, 0.1);">
                            📅
                        </div>
                        <strong style="font-size: 0.95rem; font-family: 'Inter', sans-serif; font-weight: 500; letter-spacing: -0.2px;">Schedules:</strong>
                    </div>
                </div>
                <div class="custom-scrollbar" style="display: flex; flex-direction: column; flex-grow: 1; overflow-y: auto; padding: 0.8rem; background: #fdfdfd; border-radius: 12px; margin: 0 1rem 1rem; border: 1px solid #f1f5f9;">
                    <div>
                        ${slotsHtml}
                    </div>
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
        const res = await fetch(`api/teacher_schedule.php?term_id=${window.activeTermId}`, { cache: 'no-store' });
        const groupedSchedules = await res.json();

        const rawNames = Object.keys(groupedSchedules);
        const orderedNames = rawNames.sort((a, b) => a.localeCompare(b, undefined, {sensitivity: 'base'}));

        // Helper: display label for teacher in UI (Title Case)
        const getTeacherLabel = (name) => {
            return name.toLowerCase().split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        };

        // Populate filters if empty
        const homeTeacherFilter = document.getElementById('homeTeacherFilter');
        if (teacherFilter && teacherFilter.options.length <= 1) {
            orderedNames.forEach(name => {
                const opt = document.createElement('option');
                opt.value = name;
                opt.textContent = getTeacherLabel(name);
                teacherFilter.appendChild(opt);
                
                // Also populate the teacher filter in the schedule view
                if (homeTeacherFilter) {
                    const opt2 = opt.cloneNode(true);
                    homeTeacherFilter.appendChild(opt2);
                }
            });
        }

        const selectedTeacher = teacherFilter?.value || 'all';

        grid.innerHTML = `
            <div class="table-container" style="
                background: white;
                border-radius: 12px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.04);
                width: 100%;
                max-width: 100%;
                margin: 0.5rem;
                overflow: hidden;
            ">
                <table class="teacher-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #1e1b4b; color: white;">
                            <th style="padding: 1rem 1.2rem; text-align: left; font-family: 'Playfair Display', serif; font-size: 1rem; border: 1px solid rgba(255,255,255,0.1);">Teachers</th>
                            <th style="padding: 1rem 1.2rem; text-align: left; font-family: 'Playfair Display', serif; font-size: 1rem; border: 1px solid rgba(255,255,255,0.1);">Subject</th>
                            <th style="padding: 1rem 1.2rem; text-align: left; font-family: 'Playfair Display', serif; font-size: 1rem; border: 1px solid rgba(255,255,255,0.1);">Sections</th>
                            <th style="padding: 1rem 1.2rem; text-align: center; font-family: 'Playfair Display', serif; font-size: 1rem; border: 1px solid rgba(255,255,255,0.1);">Action</th>
                        </tr>
                    </thead>
                    <tbody id="teacherTableBody"></tbody>
                </table>
            </div>
        `;

        const tbody = document.getElementById('teacherTableBody');
        tbody.innerHTML = '';

        orderedNames.forEach(teacherName => {
            if (selectedTeacher !== 'all' && selectedTeacher !== teacherName) return;

            const baseSchedules = groupedSchedules[teacherName];

            // Group by subject (combining all sections for that subject)
            const subjectGroups = {};
            baseSchedules.forEach(s => {
                if (!s.id) return;
                const subjKey = s.subject_code; 
                if (!subjectGroups[subjKey]) {
                    subjectGroups[subjKey] = {
                        name: `${s.subject_code || ''} - ${s.subject_name || ''}`,
                        sections: new Set(),
                        schedules: []
                    };
                }
                if (s.section) subjectGroups[subjKey].sections.add(s.section);
                subjectGroups[subjKey].schedules.push(s);
            });

            let subjects = Object.keys(subjectGroups).sort();
            if (subjects.length === 0) {
                subjectGroups['none'] = {
                    name: '---',
                    sections: new Set(['---']),
                    schedules: []
                };
                subjects = ['none'];
            }

            subjects.forEach((subjKey, idx) => {
                const tr = document.createElement('tr');
                const group = subjectGroups[subjKey];

                // Teacher Name Cell (only for first row of teacher)
                if (idx === 0) {
                    const nameTd = document.createElement('td');
                    nameTd.className = 'teacher-name-cell';
                    nameTd.rowSpan = subjects.length;
                    nameTd.style.padding = '1rem';
                    nameTd.style.fontWeight = '700';
                    nameTd.style.color = '#1e1b4b';
                    nameTd.textContent = getTeacherLabel(teacherName);
                    tr.appendChild(nameTd);
                }

                // Subject Cell
                const subjTd = document.createElement('td');
                subjTd.className = 'subject-cell';
                subjTd.style.padding = '1rem';
                subjTd.innerHTML = group.name.includes(' - ') 
                    ? `<span class="subj-code">${group.name.split(' - ')[0]}</span><span class="subj-name" style="display: block; font-size: 0.85rem; color: #64748b; font-weight: 500;">${group.name.split(' - ')[1]}</span>`
                    : `<span class="subj-code">${group.name}</span>`;
                tr.appendChild(subjTd);

                // Sections Cell
                const sectionsTd = document.createElement('td');
                sectionsTd.className = 'section-cell';
                sectionsTd.style.padding = '1rem';
                sectionsTd.style.color = '#64748b';
                sectionsTd.style.fontWeight = '600';
                sectionsTd.style.fontSize = '0.9rem';
                sectionsTd.textContent = Array.from(group.sections).sort().join(', ') || '---';
                tr.appendChild(sectionsTd);

                // Action Cell
                const viewTd = document.createElement('td');
                viewTd.className = 'view-btn-cell';
                viewTd.style.textAlign = 'center';
                viewTd.style.padding = '1rem';
                
                const eyeSpan = document.createElement('span');
                eyeSpan.className = 'icon-view-new';
                eyeSpan.style.cursor = 'pointer';
                eyeSpan.style.color = '#1e1b4b';
                eyeSpan.style.transition = 'all 0.2s';
                eyeSpan.innerHTML = `<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>`;
                eyeSpan.title = "View Schedule";
                
                eyeSpan.onclick = () => viewTeacherSchedule(teacherName, baseSchedules);
                
                // Add hover effect
                eyeSpan.onmouseover = () => { eyeSpan.style.transform = 'scale(1.2)'; };
                eyeSpan.onmouseout = () => { eyeSpan.style.transform = 'scale(1)'; };

                viewTd.appendChild(eyeSpan);
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
                <p style="font-size: 0.75rem; color: #4f46e5; font-weight: 500; margin-bottom: 0.2rem; line-height: 1.2;">${label.toUpperCase()}</p>
                <p style="font-size: 0.8rem; color: #475569;">${isTeacher ? 'Room' : 'Teacher'}: ${isTeacher ? (classData.room_name?.replace('COMPLAB', 'COMLAB') || 'N/A') : classData.faculty_name}</p>
                <p style="font-size: 0.8rem; color: #475569;">Time: ${formatTime(classData.start_time)} - ${formatTime(classData.end_time)}</p>
                <p style="font-size: 0.8rem; color: #475569;">Section: ${classData.section || 'N/A'}</p>
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
        if (!s.id || !s.subject_code) return; // Skip empty/dummy rows
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
        <div style="background: #1e1b4b; color: white; padding: 2.2rem 2rem; border-radius: 16px; margin-bottom: 2.5rem; display: flex; align-items: center; gap: 2rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.15); border: 1px solid rgba(255,255,255,0.1);">
            <div style="width: 85px; height: 85px; background: #fbbf24; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; font-weight: 800; color: #1e1b4b; box-shadow: 0 10px 15px -3px rgba(251, 191, 36, 0.3); transform: rotate(-3deg);">
                ${teacherName.charAt(0)}
            </div>
            <div>
                <h2 style="margin: 0; font-size: 2.2rem; letter-spacing: -0.5px; font-family: 'Playfair Display', serif; font-weight: 800; color: #ffffff;">${teacherName}</h2>
                <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px;">
                    <span style="display: inline-block; width: 10px; height: 10px; background: #fbbf24; border-radius: 50%;"></span>
                    <p style="margin: 0; color: #fbbf24; font-size: 0.95rem; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">Faculty Schedule - Weekly Overview</p>
                </div>
            </div>
        </div>
    `;

    const now = new Date();
    const todayName = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][now.getDay()];
    // format as HH:MM:SS (24hr)
    const currentHr = now.getHours().toString().padStart(2, '0');
    const currentMin = now.getMinutes().toString().padStart(2, '0');
    const currentSec = now.getSeconds().toString().padStart(2, '0');
    const currentTimeStr = `${currentHr}:${currentMin}:${currentSec}`;

    const sortedSubjectKeys = Object.keys(subjectList).sort();
    
    if (sortedSubjectKeys.length === 0) {
        html += `
            <div style="background: white; padding: 4rem 2rem; border-radius: 16px; text-align: center; border: 2px dashed #e2e8f0; color: #94a3b8; margin: 2rem 0;">
                <p style="font-size: 1.2rem; font-weight: 500; margin: 0; opacity: 0.7;">Rest Day (No Active Assignments)</p>
            </div>
        `;
    } else {
        sortedSubjectKeys.forEach(baseKey => {
            const group = subjectList[baseKey];
            html += `
                <div style="margin-bottom: 2.5rem; border: 1.5px solid #e2e8f0; border-radius: 16px; overflow: hidden; background: white; box-shadow: 0 8px 15px -3px rgba(0,0,0,0.03);">
                    <div style="background: #f8fafc; padding: 1.25rem 2rem; border-bottom: 3.5px solid #fbbf24; display: flex; align-items: center; gap: 12px;">
                        <div style="background: #1e1b4b; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem;">📚</div>
                        <h3 style="margin: 0; color: #1e1b4b; font-size: 1.15rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.8px;">SUBJECT: ${baseKey}</h3>
                    </div>
                    <div style="padding: 0.5rem 2rem 2rem 2rem;">
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
                const isOngoing = (s.day === todayName) && (currentTimeStr >= s.start_time && currentTimeStr < s.end_time);
                const highlightStyle = isOngoing ? 'background: rgba(251, 191, 36, 0.15); border: 2px solid #fbbf24; border-left: 6px solid #fbbf24;' : '';
                const textColor = isOngoing ? '#fbbf24' : '#1e1b4b';

                html += `
                    <div class="${isOngoing ? 'ongoing-highlight' : ''}" style="display: grid; grid-template-columns: 140px 1fr 140px 160px 1fr; gap: 20px; padding: 16px 12px; border-bottom: 1px solid #f1f5f9; align-items: center; transition: background 0.2s; ${highlightStyle} border-radius: 8px; margin: 4px 0;">
                        <span style="background: ${isOngoing ? '#fbbf24' : '#1e1b4b'}; color: ${isOngoing ? '#1e1b4b' : 'white'}; padding: 6px 12px; border-radius: 10px; font-weight: 800; font-size: 0.75rem; text-align: center; letter-spacing: 0.5px;">${s.subject_code}</span>
                        <div style="font-size: 1rem; font-weight: 800; color: ${textColor}; font-family: 'Inter', sans-serif;" class="adaptive-text">${s.section || '---'}</div>
                        <div style="font-size: 0.9rem; color: #fbbf24; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">${s.day}</div>
                        <div style="font-size: 0.95rem; color: ${isOngoing ? '#ffffff' : textColor}; font-weight: 800; background: ${isOngoing ? 'rgba(30, 27, 75, 0.4)' : '#ffffff'}; padding: 6px 14px; border-radius: 12px; border: 1px solid #fbbf24; width: fit-content; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">${formatTime(s.start_time)} - ${formatTime(s.end_time)}</div>
                        <div style="font-size: 0.9rem; color: ${isOngoing ? '#fbbf24' : '#64748b'}; font-weight: 600; font-family: 'Inter', sans-serif; display: flex; align-items: center; gap: 6px;" class="adaptive-text-muted">
                            <span style="opacity: 0.8;">📍</span> ${s.room_name?.replace('COMPLAB', 'COMLAB')}
                        </div>
                    </div>
                `;
            });
        });

        html += `</div></div>`;
        });
    }

    content.innerHTML = html;
    if (document.getElementById('modalEditBtn')) {
        document.getElementById('modalEditBtn').style.display = 'none';
    }
    modal.style.display = 'flex';
}

function viewLabSchedule(labName, schedules, canEdit = true) {
    const modal = document.getElementById('labModal');
    const title = document.getElementById('labModalTitle');
    const content = document.getElementById('labModalContent');
    const editBtn = document.getElementById('modalEditBtn');

    window.isModalInEditMode = canEdit;
    window.editingSchedulesIds = new Set(); // Reset editing state
    window.modalSchedules = schedules;
    window.modalLabName = labName;
    
    title.textContent = `${labName?.replace('COMPLAB', 'COMLAB')} Weekly Schedule`;
    renderScheduleTable(schedules, content, false);
    
    if (editBtn) editBtn.style.display = 'none'; 
    
    const headerButtons = modal.querySelector('div[style*="display: flex; gap: 10px;"]');
    const footer = document.getElementById('labModalFooter');

    if (headerButtons) {
        headerButtons.style.display = canEdit ? 'none' : 'flex';
        headerButtons.innerHTML = `
            <button onclick="closeLabModalInternal()" style="background: var(--secondary); border: none; padding: 0.5rem 1.5rem; border-radius: 8px; cursor: pointer; font-weight: 700; color: #1e1b4b; transition: all 0.2s;" onmouseover="this.style.background='#ffdf8f'" onmouseout="this.style.background='#fbbf24'">Close</button>
        `;
    }

    if (footer) {
        footer.style.display = canEdit ? 'flex' : 'none';
        const saveBtn = footer.querySelector('button');
        if (saveBtn) saveBtn.textContent = 'Save Changes';
    }

    // Pre-fetch faculty list for the teacher dropdown in inline edit forms
    if (canEdit && (!window.cachedFaculty || window.cachedFaculty.length === 0)) {
        fetch('api/faculty.php', { cache: 'no-store' })
            .then(r => r.json())
            .then(f => { window.cachedFaculty = f; });
    }
    
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
                    <option value="17:00">5:00 - 7:00 PM</option>
                `;
            } else if (day !== 'all') {
                timeOptions += `
                    <option value="07:30">7:30 - 9:00 AM</option>
                    <option value="09:00">9:00 - 10:30 AM</option>
                    <option value="10:30">10:30 - 12:00 PM</option>
                    <option value="13:00">1:00 - 2:30 PM</option>
                    <option value="14:30">2:30 - 4:00 PM</option>
                    <option value="16:00">4:00 - 5:30 PM</option>
                    <option value="17:30">5:30 - 7:00 PM</option>
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

    const now = new Date();
    const todayName = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][now.getDay()];
    // format as HH:MM:SS (24hr)
    const currentHr = now.getHours().toString().padStart(2, '0');
    const currentMin = now.getMinutes().toString().padStart(2, '0');
    const currentSec = now.getSeconds().toString().padStart(2, '0');
    const currentTime = `${currentHr}:${currentMin}:${currentSec}`;

    let html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">';
    daysOrder.forEach(day => {
        const dayScheds = groupedByDay[day] || [];
        if (dayScheds.length > 0) {
            dayScheds.sort((a, b) => a.start_time.localeCompare(b.start_time));
            html += `
                <div style="background: #f8fafc; border-radius: 12px; padding: 1.5rem; border: 1px solid #e2e8f0;">
                    <h4 style="color: #4f46e5; border-bottom: 2px solid #fbbf24; display: inline-block; margin-bottom: 1rem; padding-bottom: 0.2rem; font-weight: 500;">${day}</h4>
                    <div style="display: flex; flex-direction: column; gap: 0.8rem;">
            `;
            dayScheds.forEach(s => {
                const formatTimeDisplay = (t) => {
                    if (!t) return '---';
                    const parts = t.split(':');
                    let h = parseInt(parts[0]);
                    const m = parts[1];
                    const ampm = h >= 12 ? 'PM' : 'AM';
                    h = h % 12 || 12;
                    return `${h}:${m} ${ampm}`;
                };

                const isOngoing = (day === todayName) && (currentTime >= s.start_time && currentTime < s.end_time);
                const highlightStyle = isOngoing ? 'background: rgba(251, 191, 36, 0.15); border: 2px solid #fbbf24; border-left: 6px solid #fbbf24;' : 'background: white; border: 1px solid #e2e8f0;';
                
                const formatTime = (t) => t.substring(0, 5);
                const isEditing = window.editingSchedulesIds && window.editingSchedulesIds.has(String(s.id));

                if (s.subject_code === 'VACANT') {
                    html += `
                        <div style="background: white; padding: 0.8rem; border-radius: 8px; border: 1px dashed #cbd5e1; text-align: center; position: relative;">
                            <span style="position: absolute; top: 0.4rem; right: 0.6rem; font-size: 0.72rem; color: #64748b; font-weight: 500; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">
                                ${formatTime(s.start_time)} - ${formatTime(s.end_time)}
                            </span>
                            <h3 style="font-size: 1.6rem; font-weight: 500; color: #1e1b4b; margin: 0.3rem 0 0 0; letter-spacing: 0.05em;">VACANT</h3>
                        </div>
                    `;
                    return;
                }

                if (isEditing) {
                    // Build teacher options from cached faculty
                    const facultyOptions = (window.cachedFaculty || []).map(f =>
                        `<option value="${f.id}" ${f.id == s.faculty_id ? 'selected' : ''}>${f.name}</option>`
                    ).join('');

                    html += `
                        <div style="background: #fffbeb; padding: 1rem; border-radius: 12px; border: 2px solid #fbbf24; box-shadow: 0 4px 12px rgba(251, 191, 36, 0.1); position: relative;">
                            <div style="display: grid; gap: 10px;">
                                <div style="display: flex; gap: 8px;">
                                    <div style="flex: 1;">
                                        <label style="display: block; font-size: 0.65rem; font-weight: 600; color: #92400e; margin-bottom: 3px; text-transform: uppercase;">Code</label>
                                        <input type="text" id="edit_code_${s.id}" value="${s.subject_code}" style="width: 100%; padding: 6px 8px; border-radius: 6px; border: 1px solid #fcd34d; font-size: 0.85rem; font-weight: 600; color: #1e1b4b;">
                                    </div>
                                    <div style="flex: 2;">
                                        <label style="display: block; font-size: 0.65rem; font-weight: 600; color: #92400e; margin-bottom: 3px; text-transform: uppercase;">Subject Name</label>
                                        <input type="text" id="edit_name_${s.id}" value="${s.subject_name}" style="width: 100%; padding: 6px 8px; border-radius: 6px; border: 1px solid #fcd34d; font-size: 0.85rem;">
                                    </div>
                                </div>
                                <div style="display: flex; gap: 8px;">
                                    <div style="flex: 1;">
                                        <label style="display: block; font-size: 0.65rem; font-weight: 600; color: #92400e; margin-bottom: 3px; text-transform: uppercase;">Section</label>
                                        <input type="text" id="edit_sec_${s.id}" value="${s.section || ''}" style="width: 100%; padding: 6px 8px; border-radius: 6px; border: 1px solid #fcd34d; font-size: 0.85rem;">
                                    </div>
                                </div>
                                <div style="position: relative;">
                                    <label style="display: block; font-size: 0.65rem; font-weight: 600; color: #92400e; margin-bottom: 3px; text-transform: uppercase;">Start (HH:MM)</label>
                                    <input type="text" id="edit_start_${s.id}" value="${formatTime(s.start_time)}" 
                                        onclick="document.getElementById('edit_start_${s.id}TimePicker').style.display='flex'" 
                                        readonly
                                        style="width: 100%; padding: 5px 8px; border-radius: 6px; border: 1px solid #fcd34d; font-size: 0.85rem; cursor: pointer; background: #fff;">
                                    
                                    <div id="edit_start_${s.id}TimePicker" style="display: none; position: relative; flex-direction: column; gap: 8px; background: #ffffff; border: 2px solid #fbbf24; border-radius: 12px; padding: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); z-index: 10; margin-top: 5px; width: fit-content;">
                                        <div style="display: flex; align-items: center; gap: 8px; justify-content: center;">
                                            <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                                <span style="font-size: 0.65rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Hour</span>
                                                <select id="edit_start_${s.id}_hr" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px; font-size: 1rem; font-weight: 700; color: #1e1b4b; cursor: pointer; width: 60px; text-align: center;">
                                                    ${[...Array(12)].map((_, i) => `<option value="${i + 1}" ${parseInt(s.start_time.split(':')[0]) % 12 || 12 === (i + 1) ? 'selected' : ''}>${String(i + 1).padStart(2, '0')}</option>`).join('')}
                                                </select>
                                            </div>
                                            <span style="font-weight:900; color:#cbd5e1; font-size: 1.2rem; margin-top: 12px;">:</span>
                                            <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                                <span style="font-size: 0.65rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Min</span>
                                                <select id="edit_start_${s.id}_min" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px; font-size: 1rem; font-weight: 700; color: #1e1b4b; cursor: pointer; width: 60px; text-align: center;">
                                                    ${[...Array(60)].map((_, i) => `<option value="${i}" ${parseInt(s.start_time.split(':')[1]) === i ? 'selected' : ''}>${String(i).padStart(2, '0')}</option>`).join('')}
                                                </select>
                                            </div>
                                            <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                                <span style="font-size: 0.65rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Period</span>
                                                <select id="edit_start_${s.id}_ampm" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px; font-size: 1rem; font-weight: 800; color: #1e1b4b; cursor: pointer; width: 70px; text-align: center;">
                                                    <option value="AM" ${parseInt(s.start_time.split(':')[0]) < 12 ? 'selected' : ''}>AM</option>
                                                    <option value="PM" ${parseInt(s.start_time.split(':')[0]) >= 12 ? 'selected' : ''}>PM</option>
                                                </select>
                                            </div>
                                            <button type="button" onclick="confirmTime('edit_start_${s.id}')" style="margin-top: 15px; padding: 6px 12px; background: #fbbf24; color: #1e1b4b; border: none; border-radius: 10px; font-weight: 900; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); text-transform: uppercase;" onmouseover="this.style.background='#f59e0b'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#fbbf24'; this.style.transform='none'">OK</button>
                                        </div>
                                    </div>
                                </div>
                                <div style="position: relative;">
                                    <label style="display: block; font-size: 0.65rem; font-weight: 600; color: #92400e; margin-bottom: 3px; text-transform: uppercase;">End (HH:MM)</label>
                                    <input type="text" id="edit_end_${s.id}" value="${formatTime(s.end_time)}" 
                                        onclick="document.getElementById('edit_end_${s.id}TimePicker').style.display='flex'" 
                                        readonly
                                        style="width: 100%; padding: 5px 8px; border-radius: 6px; border: 1px solid #fcd34d; font-size: 0.85rem; cursor: pointer; background: #fff;">
                                    
                                    <div id="edit_end_${s.id}TimePicker" style="display: none; position: relative; flex-direction: column; gap: 8px; background: #ffffff; border: 2px solid #fbbf24; border-radius: 12px; padding: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); z-index: 10; margin-top: 5px; width: fit-content;">
                                        <div style="display: flex; align-items: center; gap: 8px; justify-content: center;">
                                            <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                                <span style="font-size: 0.65rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Hour</span>
                                                <select id="edit_end_${s.id}_hr" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px; font-size: 1rem; font-weight: 700; color: #1e1b4b; cursor: pointer; width: 60px; text-align: center;">
                                                    ${[...Array(12)].map((_, i) => `<option value="${i + 1}" ${parseInt(s.end_time.split(':')[0]) % 12 || 12 === (i + 1) ? 'selected' : ''}>${String(i + 1).padStart(2, '0')}</option>`).join('')}
                                                </select>
                                            </div>
                                            <span style="font-weight:900; color:#cbd5e1; font-size: 1.2rem; margin-top: 12px;">:</span>
                                            <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                                <span style="font-size: 0.65rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Min</span>
                                                <select id="edit_end_${s.id}_min" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px; font-size: 1rem; font-weight: 700; color: #1e1b4b; cursor: pointer; width: 60px; text-align: center;">
                                                    ${[...Array(60)].map((_, i) => `<option value="${i}" ${parseInt(s.end_time.split(':')[1]) === i ? 'selected' : ''}>${String(i).padStart(2, '0')}</option>`).join('')}
                                                </select>
                                            </div>
                                            <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                                <span style="font-size: 0.65rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Period</span>
                                                <select id="edit_end_${s.id}_ampm" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px; font-size: 1rem; font-weight: 800; color: #1e1b4b; cursor: pointer; width: 70px; text-align: center;">
                                                    <option value="AM" ${parseInt(s.end_time.split(':')[0]) < 12 ? 'selected' : ''}>AM</option>
                                                    <option value="PM" ${parseInt(s.end_time.split(':')[0]) >= 12 ? 'selected' : ''}>PM</option>
                                                </select>
                                            </div>
                                            <button type="button" onclick="confirmTime('edit_end_${s.id}')" style="margin-top: 15px; padding: 6px 12px; background: #fbbf24; color: #1e1b4b; border: none; border-radius: 10px; font-weight: 900; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); text-transform: uppercase;" onmouseover="this.style.background='#f59e0b'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#fbbf24'; this.style.transform='none'">OK</button>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label style="display: block; font-size: 0.65rem; font-weight: 600; color: #92400e; margin-bottom: 3px; text-transform: uppercase;">Teacher</label>
                                    <select id="edit_faculty_${s.id}" style="width: 100%; padding: 6px 8px; border-radius: 6px; border: 1px solid #fcd34d; font-size: 0.85rem; background: white;" onchange="if(this.value === 'add_teacher'){ document.getElementById('edit_faculty_new_${s.id}').style.display='block'; document.getElementById('edit_faculty_name_${s.id}').focus(); } else { document.getElementById('edit_faculty_new_${s.id}').style.display='none'; }">
                                        ${facultyOptions || `<option value="" selected>${s.faculty_name || 'Select Teacher'}</option>`}
                                        <option value="add_teacher" style="color: #4f46e5; font-weight: 500;">+ add a teacher</option>
                                    </select>
                                    <div id="edit_faculty_new_${s.id}" style="display: none; margin-top: 6px; border: 2px dashed #fbbf24; padding: 8px; border-radius: 10px; background: rgba(30, 27, 75, 0.05);">
                                        <input type="text" id="edit_faculty_name_${s.id}" placeholder="Enter New Teacher Name" style="width: 100%; padding: 6px 8px; border-radius: 6px; border: 1px solid #fcd34d; font-size: 0.85rem;">
                                    </div>
                                </div>
                                <div style="display: flex; gap: 8px; align-items: center; margin-top: 4px;">
                                    <button onclick="saveInlineEntry(${s.id})" style="flex: 1; background: #10b981; color: white; border: none; padding: 7px; border-radius: 8px; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">Save Changes</button>
                                    <button onclick="toggleInlineEdit(${s.id})" style="background: transparent; color: #92400e; border: 1px solid #fcd34d; padding: 7px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">Discard</button>
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="${isOngoing ? 'ongoing-highlight' : ''}" style="${highlightStyle} padding: 0.8rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); position: relative;">
                            ${window.isModalInEditMode ? `
                                <div style="position: absolute; top: 0.5rem; right: 0.5rem; display: flex; gap: 8px; align-items: center;">
                                    <button onclick="toggleInlineEdit(${s.id})" style="background: #1e1b4b; color: white; border: none; width: 34px; height: 34px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; transition: all 0.2s; box-shadow: 0 4px 8px rgba(30, 27, 75, 0.2);" title="Edit Entry" onmouseover="this.style.background='#4338ca'; this.style.transform='scale(1.1)'" onmouseout="this.style.background='#1e1b4b'; this.style.transform='scale(1)'">✏️</button>
                                    <button onclick="deleteScheduleEntry(${s.id})" style="background: #ef4444; color: white; border: none; width: 34px; height: 34px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s; box-shadow: 0 4px 8px rgba(239, 68, 68, 0.2);" title="Delete Entry" onmouseover="this.style.background='#dc2626'; this.style.transform='scale(1.1)'" onmouseout="this.style.background='#ef4444'; this.style.transform='scale(1)'">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                    </button>
                                </div>
                            ` : ''}
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem; padding-right: ${window.isModalInEditMode ? '84px' : '0'};">
                                <div>
                                    <span style="font-size: 0.72rem; font-weight: 500; color: ${isOngoing ? '#fbbf24' : '#64748b'}; letter-spacing: 0.3px; display: block;">${s.subject_code}</span>
                                    <strong style="color: ${isOngoing ? '#fbbf24' : '#1e1b4b'}; font-size: 0.9rem; font-weight: 500;">${s.subject_name}</strong>
                                </div>
                                <span style="font-size: 0.75rem; color: ${isOngoing ? '#ffffff' : '#64748b'}; background: ${isOngoing ? 'rgba(30, 27, 75, 0.4)' : '#f1f5f9'}; padding: 2px 6px; border-radius: 4px; font-weight: 400; white-space: nowrap; margin-left: 8px; flex-shrink: 0;">
                                    ${formatTimeDisplay(s.start_time)} - ${formatTimeDisplay(s.end_time)}
                                </span>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <p style="font-size: 0.8rem; color: ${isOngoing ? '#fbbf24' : '#475569'}; margin: 0; font-weight: 400;"><strong>Section:</strong> ${s.section || 'N/A'}</p>
                                <p style="font-size: 0.8rem; color: ${isOngoing ? '#fbbf24' : '#475569'}; border-top: 1px dashed ${isOngoing ? 'rgba(251, 191, 36, 0.3)' : '#cbd5e1'}; margin-top: 5px; padding-top: 5px; margin-bottom: 0; font-weight: 400;">
                                    <strong>${isTeacherView ? 'Room: ' : 'Teacher: '}</strong> ${isTeacherView ? (s.room_name || '').replace('COMPLAB', 'COMLAB') : (s.faculty_name || '')}
                                </p>
                            </div>
                        </div>
                    `;
                }
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
        const res = await fetch(`api/subjects.php?term_id=${window.activeTermId}`);
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
                const displayName = room.name.replace(/COMPLAB/g, 'COMLAB');
                roomFilter.innerHTML += `<option value="${room.name}">${displayName}</option>`;
            });
            roomFilter.value = currentVal;
            if (!roomFilter.value) roomFilter.value = 'all';
        }

        if (visualFilter) {
            const currentVisualVal = visualFilter.value;
            visualFilter.innerHTML = '<option value="all">All schedule</option>';
            rooms.forEach(room => {
                const displayName = room.name.replace(/COMPLAB/g, 'COMLAB');
                visualFilter.innerHTML += `<option value="${room.name}">${displayName}</option>`;
            });
            visualFilter.value = currentVisualVal;
            if (!visualFilter.value) visualFilter.value = 'all';
        }

        const managementFilter = document.getElementById('roomsManagementFilter');
        if (managementFilter) {
            const currentVal = managementFilter.value;
            managementFilter.innerHTML = '<option value="all">All Available Rooms</option>';
            rooms.forEach(room => {
                const displayName = room.name.replace(/COMPLAB/g, 'COMLAB');
                managementFilter.innerHTML += `<option value="${room.name}">${displayName}</option>`;
            });
            managementFilter.value = currentVal;
            if (!managementFilter.value) managementFilter.value = 'all';
        }
    } catch (e) {
        console.error("Failed to populate room dropdowns", e);
    }
}

// Initial setup
checkAuth();
populateRoomDropdowns();

async function deleteScheduleEntry(id) {
    if (!confirm('Are you sure you want to delete this schedule entry?')) return;
    try {
        const res = await fetch(`api/schedules.php?id=${id}`, { method: 'DELETE' });
        const data = await res.json();
        if (data.success) {
            // Remove from local modal list
            window.modalSchedules = window.modalSchedules.filter(s => s.id != id);
            window.updateModalFilters();
            // Refresh global views
            loadSectionData('home');
            renderSchedulesVisualGrid();
        } else {
            alert('Error deleting: ' + (data.error || 'Unknown error'));
        }
    } catch (err) {
        console.error(err);
        alert('Fetch error');
    }
}

function toggleInlineEdit(id) {
    id = String(id);
    if (!window.editingSchedulesIds) window.editingSchedulesIds = new Set();
    if (window.editingSchedulesIds.has(id)) {
        window.editingSchedulesIds.delete(id);
    } else {
        window.editingSchedulesIds.add(id);
    }
    renderScheduleTable(window.modalSchedules, document.getElementById('labModalContent'), false);
}

async function saveInlineEntry(id) {
    id = String(id);
    const codeEl  = document.getElementById(`edit_code_${id}`);
    const nameEl  = document.getElementById(`edit_name_${id}`);
    const secEl   = document.getElementById(`edit_sec_${id}`);
    const startEl = document.getElementById(`edit_start_${id}`);
    const endEl   = document.getElementById(`edit_end_${id}`);
    const facEl   = document.getElementById(`edit_faculty_${id}`);
    const facNameEl = document.getElementById(`edit_faculty_name_${id}`);

    if (!codeEl) return;

    const saveBtn = codeEl.closest('div[style*="background: #fffbeb"]')?.querySelector('button[onclick*="saveInlineEntry"]');
    if (saveBtn) { saveBtn.textContent = 'Saving...'; saveBtn.disabled = true; }

    let faculty_id = facEl ? facEl.value : '';
    let faculty_name = (faculty_id === 'add_teacher' && facNameEl) ? facNameEl.value : '';

    const data = {
        subject_code: codeEl.value,
        subject_name: nameEl.value,
        section:      secEl.value,
        start_time:   startEl.value,
        end_time:     endEl.value,
        faculty_id:   (faculty_id === 'add_teacher') ? '' : faculty_id,
        faculty_name: faculty_name
    };

    try {
        const res = await fetch(`api/schedules.php?id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
            // Remove from editing set and re-fetch modal data
            window.editingSchedulesIds.delete(id);
            loadLabGrid();
            if (typeof renderSchedulesVisualGrid === 'function') renderSchedulesVisualGrid();
            const all = await fetch(`api/schedules.php?term_id=${window.activeTermId}`).then(r => r.json());
            window.modalSchedules = all.filter(s => s.room_name === window.modalLabName);
            renderScheduleTable(window.modalSchedules, document.getElementById('labModalContent'), false);
        } else {
            alert('Save failed: ' + (result.error || 'Unknown error'));
            if (saveBtn) { saveBtn.textContent = 'Save Changes'; saveBtn.disabled = false; }
        }
    } catch (err) {
        console.error(err);
        alert('Network error during save.');
        if (saveBtn) { saveBtn.textContent = 'Save Changes'; saveBtn.disabled = false; }
    }
}

async function confirmLabModalChanges() {
    const ids = Array.from(window.editingSchedulesIds || []);
    if (ids.length === 0) {
        closeLabModal();
        return;
    }

    const btn = document.querySelector('#labModalFooter button');
    const originalText = btn.textContent;
    btn.textContent = 'Saving...';
    btn.disabled = true;

    try {
        // Load faculty list if not already cached (needed for teacher dropdown)
        if (!window.cachedFaculty || window.cachedFaculty.length === 0) {
            const fr = await fetch('api/faculty.php', { cache: 'no-store' });
            window.cachedFaculty = await fr.json();
        }

        const promises = ids.map(id => {
            const codeEl = document.getElementById(`edit_code_${id}`);
            const nameEl = document.getElementById(`edit_name_${id}`);
            const secEl  = document.getElementById(`edit_sec_${id}`);
            const startEl= document.getElementById(`edit_start_${id}`);
            const endEl  = document.getElementById(`edit_end_${id}`);
            const facEl  = document.getElementById(`edit_faculty_${id}`);
            const facNameEl = document.getElementById(`edit_faculty_name_${id}`);

            if (!codeEl) return Promise.resolve({ success: false, error: `Fields missing for id ${id}` });

            let faculty_id = facEl ? facEl.value : '';
            let faculty_name = (faculty_id === 'add_teacher' && facNameEl) ? facNameEl.value : '';

            const data = {
                subject_code: codeEl.value,
                subject_name: nameEl.value,
                section: secEl.value,
                start_time: startEl.value,
                end_time: endEl.value,
                faculty_id: (faculty_id === 'add_teacher') ? '' : faculty_id,
                faculty_name: faculty_name,
                term_id: window.activeTermId
            };
            return fetch(`api/schedules.php?id=${id}&term_id=${window.activeTermId}`, {
                method: 'PUT',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            }).then(r => r.json());
        });

        const results = await Promise.all(promises);
        const failed = results.filter(r => !r.success);
        
        if (failed.length > 0) {
            alert('Some changes failed to save. Please check your data.');
        } else {
            // Success!
            window.editingSchedulesIds.clear();
            loadLabGrid();
            if (typeof renderSchedulesVisualGrid === 'function') renderSchedulesVisualGrid();
            
            // Re-fetch current lab schedules to update the modal view too
            const res = await fetch(`api/schedules.php?term_id=${window.activeTermId}`);
            const all = await res.json();
            window.modalSchedules = all.filter(s => s.room_name === window.modalLabName);
            renderScheduleTable(window.modalSchedules, document.getElementById('labModalContent'), false);
            
            // Close after a short delay or just stay open? 
            // The prompt says "After saving, the modal and the system should immediately reflect the updated schedule."
            // So we stay open but reflect changes.
        }
    } catch (err) {
        console.error(err);
        alert('Fetch error during save.');
    } finally {
        btn.textContent = originalText;
        btn.disabled = false;
    }
}

function openEditScheduleModal(id) {
    // Re-routed to inline editing
    toggleInlineEdit(id);
}

function closeLabModalInternal() {
    closeLabModal();
}

function confirmScheduleEdit(id) {
    // Opens the standard edit modal as saving is currently centralized there
    openEditScheduleModal(id);
}

function cancelScheduleEdit(id) {
    // Just a placeholder for refresh or cancel state if needed
    console.log('Discarding temporary changes for entry:', id);
}

// User Profile Logic
async function loadUserProfile() {
    try {
        const res = await fetch('api/profile.php');
        const data = await res.json();
        if (data.success && data.profile) {
            updateProfileUI(data.profile);
        }
    } catch (e) {
        console.error("Failed to load user profile", e);
    }
}

function updateProfileUI(profile) {
    const headerImg = document.getElementById('headerProfileImage');
    const headerInitials = document.getElementById('headerProfileInitials');
    const modalImg = document.getElementById('profileModalImagePreview');
    const modalInitials = document.getElementById('profileModalInitials');
    
    // Update labels in modal
    document.getElementById('p_username').value = profile.username;
    document.getElementById('p_display_name').value = profile.display_name;

    const initial = profile.display_name.charAt(0).toUpperCase();

    if (profile.profile_picture) {
        // has picture
        if (headerImg) { headerImg.src = profile.profile_picture; headerImg.style.display = 'block'; }
        if (headerInitials) headerInitials.style.display = 'none';

        if (modalImg) { modalImg.src = profile.profile_picture; modalImg.style.display = 'block'; }
        if (modalInitials) modalInitials.style.display = 'none';
    } else {
        // no picture
        if (headerImg) headerImg.style.display = 'none';
        if (headerInitials) { headerInitials.textContent = initial; headerInitials.style.display = 'flex'; }

        if (modalImg) modalImg.style.display = 'none';
        if (modalInitials) { modalInitials.textContent = initial; modalInitials.style.display = 'block'; }
    }
}

function openUserProfile() {
    const overlay = document.getElementById('profileModalOverlay');
    const success = document.getElementById('profileSuccess');
    const error = document.getElementById('profileError');
    if (success) success.style.display = 'none';
    if (error) error.style.display = 'none';
    
    // reload just in case
    loadUserProfile();
    
    if (overlay) overlay.style.display = 'flex';
}

function closeUserProfile() {
    const overlay = document.getElementById('profileModalOverlay');
    if (overlay) overlay.style.display = 'none';
}

let cropper = null;
let currentCroppedBlob = null;

function previewProfileImage(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const cropperModal = document.getElementById('cropperModal');
            const cropperImage = document.getElementById('cropperImage');
            
            // Set image source
            cropperImage.src = e.target.result;
            cropperModal.style.display = 'flex';
            
            // Reset previous cropper if exists
            if (cropper) {
                cropper.destroy();
            }
            
            // Initialize cropper
            cropper = new Cropper(cropperImage, {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                restore: false,
                guides: true,
                center: true,
                highlight: false,
                cropBoxMovable: true,
                cropBoxResizable: true,
                toggleDragModeOnDblclick: false,
            });
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function cancelCrop() {
    document.getElementById('cropperModal').style.display = 'none';
    if(cropper) { cropper.destroy(); cropper = null; }
    document.getElementById('profile_picture').value = '';
}

function applyCrop() {
    if (!cropper) return;
    
    // Get cropped canvas
    const canvas = cropper.getCroppedCanvas({
        width: 256,
        height: 256,
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high',
    });
    
    // Convert to blob
    canvas.toBlob((blob) => {
        currentCroppedBlob = blob;
        
        // Show preview in User Profile Modal
        const url = URL.createObjectURL(blob);
        const preview = document.getElementById('profileModalImagePreview');
        const initials = document.getElementById('profileModalInitials');
        
        preview.src = url;
        preview.style.display = 'block';
        initials.style.display = 'none';
        
        // Cleanup and close cropper modal
        document.getElementById('cropperModal').style.display = 'none';
        cropper.destroy();
        cropper = null;
    }, 'image/png');
}

async function saveUserProfile(event) {
    event.preventDefault();

    const successBox = document.getElementById('profileSuccess');
    const errorBox = document.getElementById('profileError');
    const btn = document.getElementById('profileSaveBtn');
    
    successBox.style.display = 'none';
    errorBox.style.display = 'none';
    
    const originalText = btn.textContent;
    btn.textContent = 'Saving...';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('display_name', document.getElementById('p_display_name').value);
    
    if (currentCroppedBlob) {
        formData.append('profile_picture', currentCroppedBlob, 'profile.png');
    }

    try {
        const res = await fetch('api/profile.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.success) {
            updateProfileUI(data.profile);
            successBox.textContent = 'Profile updated successfully!';
            successBox.style.display = 'block';
            currentCroppedBlob = null; // Clear blob after successful save
            
            // Allow some time for user to read success msg before optionally closing
            setTimeout(() => {
                successBox.style.display = 'none';
                closeUserProfile();
            }, 1500);
        } else {
            errorBox.textContent = data.message || 'Failed to save profile.';
            errorBox.style.display = 'block';
        }
    } catch (e) {
        errorBox.textContent = 'Network or server error.';
        errorBox.style.display = 'block';
        console.error(e);
    } finally {
        btn.textContent = originalText;
        btn.disabled = false;
    }
}

// Academic Term Helpers
async function syncAcademicTerms() {
    try {
        const res = await fetch('api/terms.php');
        const terms = await res.json();
        const select = document.getElementById('activeTermSelect');
        if (select) {
            select.innerHTML = terms.map(t => `<option value="${t.id}" ${t.id == window.activeTermId ? 'selected' : ''}>${t.name}</option>`).join('');
            
            // Update printed term as well
            const activeTermName = terms.find(t => t.id == window.activeTermId)?.name || 'Unknown Semester';
            const printTermEl = document.getElementById('printDocTerm');
            if (printTermEl) printTermEl.textContent = activeTermName;
        }
    } catch (e) {
        console.error("Failed to sync academic terms", e);
    }
}

function updateActiveTerm(id) {
    window.activeTermId = id;
    localStorage.setItem('activeTermId', id);
    
    // Refresh all data to reflect the new semester
    loadCounts();
    showSection(currentSection);
    
    // Update labels
    syncAcademicTerms();
}

// --- Report Center Logic ---
let currentExportFormat = 'excel';

function openReportModal(defaultType = 'schedules') {
    document.getElementById('reportType').value = defaultType;
    document.getElementById('reportModalOverlay').style.display = 'flex';
    setExportFormat('excel'); // default
}

function closeReportModal() {
    document.getElementById('reportModalOverlay').style.display = 'none';
}

function setExportFormat(format) {
    currentExportFormat = format;
    document.querySelectorAll('.export-format-btn').forEach(btn => {
        btn.classList.toggle('active', btn.id === `fmt_${format}`);
        btn.style.borderColor = btn.id === `fmt_${format}` ? '#fbbf24' : '#e2e8f0';
    });
}

async function generateReport(e) {
    if (e) e.preventDefault();
    const type = document.getElementById('reportType').value;
    const format = currentExportFormat;
    
    let docTitle = "";
    if (type === 'schedules') docTitle = "Official Schedule Report";
    if (type === 'faculty') docTitle = "Faculty Member List";
    if (type === 'laboratories') docTitle = "Visual Lab Schedules";
    if (type === 'subjects') docTitle = "Infrastructure & Subject List";

    if (format === 'print') {
        const titleEl = document.getElementById('printDocTitle');
        if (titleEl) titleEl.textContent = docTitle;
        closeReportModal();
        window.print();
        return;
    }

    // Logic for Excel or Word
    let dataToExport = [];
    let headers = [];

    try {
        if (type === 'schedules') {
            const res = await fetch(`api/schedules.php?term_id=${window.activeTermId}`);
            dataToExport = await res.json();
            headers = ['Day', 'Start Time', 'End Time', 'Faculty', 'Subject Code', 'Subject Name', 'Room', 'Section'];
            dataToExport = dataToExport.map(s => [s.day, s.start_time, s.end_time, s.faculty_name, s.subject_code, s.subject_name, s.room_name, s.section]);
        } else if (type === 'faculty') {
            const res = await fetch('api/faculty.php');
            dataToExport = await res.json();
            headers = ['Name', 'Status', 'Employment', 'Campus'];
            dataToExport = dataToExport.map(f => [f.name, f.status, f.employment_status, f.designated_campus]);
        } else if (type === 'laboratories') {
            const res = await fetch(`api/lab_schedule.php?term_id=${window.activeTermId}`);
            const grouped = await res.json();
            headers = ['Room', 'Day', 'Time', 'Subject', 'Section', 'Teacher'];
            Object.keys(grouped).forEach(room => {
                grouped[room].forEach(s => {
                    dataToExport.push([room, s.day, `${s.start_time} - ${s.end_time}`, s.subject_name, s.section, s.faculty_name]);
                });
            });
        } else if (type === 'subjects') {
            const res = await fetch(`api/subjects.php?term_id=${window.activeTermId}`);
            dataToExport = await res.json();
            headers = ['Subject Code', 'Subject Name', 'Units'];
            dataToExport = dataToExport.map(s => [s.code, s.name, s.units]);
        }

        if (dataToExport.length === 0) {
            alert("No data available to export.");
            return;
        }

        if (format === 'excel') {
            const ws = XLSX.utils.aoa_to_sheet([headers, ...dataToExport]);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Report");
            XLSX.writeFile(wb, `${docTitle.replace(/\s+/g, '_')}.xlsx`);
        } else if (format === 'word') {
            let tableHtml = `<table border="1" style="border-collapse:collapse; width:100%; font-family: 'Arial', sans-serif;"><thead><tr style="background:#1e1b4b; color:white;">`;
            headers.forEach(h => tableHtml += `<th style="padding:10px; border:1px solid #334155;">${h}</th>`);
            tableHtml += `</tr></thead><tbody>`;
            dataToExport.forEach((row, idx) => {
                const bg = idx % 2 === 0 ? '#ffffff' : '#f8fafc';
                tableHtml += `<tr style="background:${bg};">`;
                row.forEach(cell => tableHtml += `<td style="padding:8px; border:1px solid #e2e8f0; font-size:10pt;">${cell}</td>`);
                tableHtml += `</tr>`;
            });
            tableHtml += `</tbody></table>`;

            const currentTermName = document.getElementById('activeTermLabel')?.textContent || '1st Semester 2024-2025';

            const content = `
                <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
                <head><meta charset='utf-8'><title>${docTitle}</title>
                <style>
                    body { font-family: 'Times New Roman', serif; margin: 1in; }
                    .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1e1b4b; padding-bottom: 10px; }
                    .univ { font-size: 22pt; font-weight: bold; color: #1e1b4b; text-transform: uppercase; margin: 0; }
                    .dept { font-size: 14pt; color: #64748b; margin: 5px 0; }
                    .report-tag { font-size: 16pt; font-weight: bold; margin-top: 15px; color: #fbbf24; background: #1e1b4b; display: inline-block; padding: 5px 20px; border-radius: 5px; }
                </style>
                </head><body>
                <div class="header">
                    <p class="univ">Leyte Normal University</p>
                    <p class="dept">Information Technology Department</p>
                    <p style="margin: 2px 0;">Tacloban City, Leyte</p>
                    <div class="report-tag">${docTitle}</div>
                    <p style="font-weight: bold; margin-top: 10px;">${currentTermName}</p>
                </div>
                <p style="font-size: 9pt; color: #64748b; margin-bottom: 15px;">Generated on: ${new Date().toLocaleString()}</p>
                ${tableHtml}
                <div style="margin-top: 50px;">
                    <p style="font-weight: bold;">Certified Correct:</p>
                    <br><br>
                    <p style="border-top: 1px solid black; width: 250px; text-align: center;">Department Head / Coordinator</p>
                </div>
                </body></html>`;

            const blob = new Blob(['\ufeff', content], { type: 'application/msword' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `${docTitle.replace(/\s+/g, '_')}.doc`;
            link.click();
        }
        
        closeReportModal();

    } catch (err) {
        console.error('Report Generation Failed:', err);
        alert('Failed to generate report.');
    }
}

async function promptNewTerm() {
    const name = prompt("Enter the name for the new semester (e.g. 2nd Sem 2025-2026):");
    if (!name) return;
    
    try {
        const res = await fetch('api/terms.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name })
        });
        const data = await res.json();
        if (data.success) {
            alert("New semester added successfully!");
            syncAcademicTerms();
        } else {
            alert("Error: " + data.message);
        }
    } catch (e) {
        console.error(e);
        alert("Failed to add semester.");
    }
}

async function promptNewCurriculum() {
    const name = prompt("Enter the name for the new curriculum (e.g. New BSIT Curriculum):");
    if (!name) return;
    
    try {
        const res = await fetch('api/curricula.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name })
        });
        const data = await res.json();
        if (data.success) {
            alert("New curriculum added successfully!");
        } else {
            alert("Error: " + data.message);
        }
    } catch (e) {
        console.error(e);
        alert("Failed to add curriculum.");
    }
}
