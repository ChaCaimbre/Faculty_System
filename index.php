<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Management System</title>
    <link rel="stylesheet" href="assets/style.css?v=8.0">
</head>
<body class="<?php echo $isLoggedIn ? 'logged-in' : 'logged-out'; ?>">
    <!-- Login Page -->
    <div id="loginPage" class="login-card">
        <div class="login-left">
            <span class="it-faculty">IT Faculty</span>
            <h2>COMLAB Scheduler</h2>
        </div>
        <div class="login-right">
            <h1 id="formTitle">Login</h1>
            <form id="loginForm" autocomplete="off">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="username" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" id="password" required autocomplete="new-password">
                </div>
                <div class="remember-me">
                    <div class="remember-left">
                        <input type="checkbox" id="remember">
                        <label for="remember">Remember Me</label>
                    </div>
                    <span class="forgot-link" style="cursor: pointer; font-size: 0.85rem; font-weight: 700; color: #4338ca;">Forgot Password?</span>
                </div>
                <button type="submit" class="btn-login">Login</button>
                <p id="loginError" style="color: #ef4444; font-size: 0.875rem; margin-top: 1rem; text-align: center; display: none;"></p>
            </form>

            <form id="forgotPasswordForm" style="display: none;" autocomplete="off">
                <div class="form-group">
                    <label>Username (or Email)</label>
                    <input type="text" id="fp_username" required autocomplete="off">
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" id="fp_new_password" required autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" id="fp_confirm_password" required autocomplete="new-password">
                </div>
                <div class="remember-me" style="justify-content: flex-end;">
                    <span class="back-to-login" style="cursor: pointer; font-size: 0.85rem; font-weight: 700; color: #4338ca;">Back to Login</span>
                </div>
                <button type="submit" class="btn-login" style="background: #fbbf24; color: #000;">Reset Password</button>
                <p id="forgotError" style="color: #ef4444; font-size: 0.875rem; margin-top: 1rem; text-align: center; display: none;"></p>
                <p id="forgotSuccess" style="color: #10b981; font-size: 0.875rem; margin-top: 1rem; text-align: center; display: none;"></p>
            </form>
        </div>
    </div>

    <!-- Dashboard -->
    <div id="dashboardPage" class="dashboard-container">
        <!-- Drawer Overlay -->
        <div id="drawerOverlay" class="drawer-overlay" onclick="toggleMobileMenu()"></div>
        <nav class="navbar">
            <div class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#1e1b4b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </div>
            <div class="nav-brand-group">
                <div class="university-logo">
                    <img src="lnu_logo-removebg-preview.png" alt="LNU Logo">
                </div>
                <div class="brand-text">
                    <div class="brand-top">IT Faculty</div>
                    <div class="brand-bottom">COMLAB <span class="yellow">Scheduler</span></div>
                </div>
            </div>
            <div class="nav-links" id="navLinks">
                <a class="nav-link" onclick="showSection('home')">Home</a>
                <a class="nav-link active" onclick="showSection('schedules')">Schedules</a>
                <a class="nav-link" onclick="showSection('faculty')">Teachers</a>
                <a class="nav-link" onclick="showSection('rooms')">ComLabs &amp; Subjects</a>
            </div>
            <div class="user-profile-group" style="display: flex; align-items: center; gap: 15px; justify-self: end;">
                <div id="digitalClock" style="font-family: 'Playfair Display', serif; font-weight: 700; color: white; background: #1e1b4b; padding: 5px 15px; border-radius: 20px; font-size: 0.9rem; min-width: 140px; text-align: center;">00:00:00 AM</div>
                <div class="user-profile">
                    <!-- Text removed to match image profile circle -->
                </div>
                <div class="nav-dropdown" style="position: relative;">
                    <div class="dropdown-arrow" style="cursor: pointer; color: #1e1b4b; font-size: 0.8rem; padding: 5px 10px; border-radius: 8px; transition: all 0.2s;" onclick="toggleNavDropdown()">▼</div>
                    <div id="navDropdownMenu" style="display: none; position: absolute; top: 100%; right: 0; margin-top: 8px; background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border: 2px solid #1e1b4b; overflow: hidden; min-width: 160px; z-index: 999;">
                        <div style="padding: 8px 0;">
                            <a onclick="openSettings()" style="display: flex; align-items: center; gap: 10px; padding: 10px 20px; color: #1e1b4b; font-weight: 700; font-size: 0.9rem; cursor: pointer; transition: background 0.2s; text-decoration: none;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1e1b4b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                                Settings
                            </a>
                            <div style="height: 1px; background: #e2e8f0; margin: 4px 12px;"></div>
                            <a onclick="logout()" style="display: flex; align-items: center; gap: 10px; padding: 10px 20px; color: #ef4444; font-weight: 700; font-size: 0.9rem; cursor: pointer; transition: background 0.2s; text-decoration: none;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <section class="hero-banner">
            <div class="stat-cards-container">
                <div class="stat-card" onclick="animateCard(this)">
                    <div class="stat-icon">
                        <!-- 3D Graduation Cap -->
                        <svg width="36" height="36" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <ellipse cx="20" cy="38" rx="12" ry="2.5" fill="rgba(0,0,0,0.18)"/>
                          <polygon points="3,17 20,26 20,30 3,21" fill="#13104a"/>
                          <polygon points="37,17 20,26 20,30 37,21" fill="#2d2a7a"/>
                          <polygon points="20,7 37,17 20,26 3,17" fill="#312e81"/>
                          <polygon points="20,7 29,12 20,17 11,12" fill="#4845a0"/>
                          <line x1="33" y1="19" x2="33" y2="30" stroke="#f59e0b" stroke-width="2.5" stroke-linecap="round"/>
                          <circle cx="33" cy="31" r="3" fill="#fbbf24"/>
                          <circle cx="33" cy="31" r="1.5" fill="#f59e0b"/>
                          <circle cx="20" cy="26" r="2.5" fill="#fbbf24"/>
                          <circle cx="20" cy="26" r="1" fill="#f59e0b"/>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <span id="facultyCount" class="stat-number">23</span>
                        <span class="stat-label">Teachers</span>
                    </div>
                </div>
                <div class="stat-card" onclick="animateCard(this)">
                    <div class="stat-icon">
                        <!-- 3D Clock -->
                        <svg width="36" height="36" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <ellipse cx="20" cy="38" rx="13" ry="2.5" fill="rgba(0,0,0,0.18)"/>
                          <circle cx="22" cy="22" r="15" fill="#13104a"/>
                          <circle cx="20" cy="20" r="15" fill="#312e81"/>
                          <circle cx="20" cy="20" r="12" fill="#3d3a8c"/>
                          <circle cx="20" cy="20" r="11" fill="#4845a0"/>
                          <path d="M11,14 Q20,8 29,14" stroke="rgba(255,255,255,0.2)" stroke-width="2" fill="none" stroke-linecap="round"/>
                          <circle cx="20" cy="10" r="1.2" fill="rgba(255,255,255,0.7)"/>
                          <circle cx="30" cy="20" r="1.2" fill="rgba(255,255,255,0.7)"/>
                          <circle cx="20" cy="30" r="1.2" fill="rgba(255,255,255,0.7)"/>
                          <circle cx="10" cy="20" r="1.2" fill="rgba(255,255,255,0.7)"/>
                          <line x1="20" y1="20" x2="20" y2="12" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
                          <line x1="20" y1="20" x2="27" y2="20" stroke="white" stroke-width="2" stroke-linecap="round"/>
                          <circle cx="20" cy="20" r="2.5" fill="#fbbf24"/>
                          <circle cx="20" cy="20" r="1" fill="#f59e0b"/>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <span id="scheduleCount" class="stat-number">113</span>
                        <span class="stat-label">Assigned Schedules</span>
                    </div>
                </div>
                <div class="stat-card" onclick="animateCard(this)">
                    <div class="stat-icon">
                        <!-- 3D Desktop Monitor -->
                        <svg width="36" height="36" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <ellipse cx="20" cy="39" rx="11" ry="1.8" fill="rgba(0,0,0,0.18)"/>
                          <rect x="6" y="10" width="30" height="19" rx="2" fill="#13104a"/>
                          <rect x="4" y="8" width="30" height="19" rx="2" fill="#312e81"/>
                          <rect x="6" y="10" width="26" height="15" rx="1" fill="#1e1b4b"/>
                          <rect x="8" y="12" width="22" height="11" rx="1" fill="#4845a0"/>
                          <rect x="10" y="14.5" width="14" height="1.8" rx="0.9" fill="rgba(255,255,255,0.45)"/>
                          <rect x="10" y="18" width="10" height="1.5" rx="0.75" fill="rgba(255,255,255,0.25)"/>
                          <rect x="10" y="21" width="7" height="1.5" rx="0.75" fill="rgba(255,255,255,0.15)"/>
                          <rect x="28" y="12" width="1.5" height="11" rx="0.75" fill="rgba(255,255,255,0.1)"/>
                          <rect x="17" y="27" width="4" height="4" rx="1" fill="#1e1b4b"/>
                          <rect x="11" y="31" width="16" height="3" rx="1.5" fill="#13104a"/>
                          <rect x="13" y="31.5" width="12" height="1" rx="0.5" fill="rgba(255,255,255,0.1)"/>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <span id="roomCount" class="stat-number">11</span>
                        <span class="stat-label">Computer Labs</span>
                    </div>
                </div>
            </div>
        </section>

        <main id="mainContent">
            <!-- Home Section -->
            <section id="homeSection" class="content-section active">
                <div class="dashboard-content" style="padding: 2rem 1.5rem; max-width: 100%; margin: 0 auto;">
                    <h1 class="main-title">COMLAB <span class="yellow">SCHEDULER</span></h1>
                    
                    <div class="toggle-buttons">
                        <button id="toggleSchedules" class="toggle-btn active" onclick="toggleHomeView('schedules')">Schedules</button>
                        <button id="toggleTeachers" class="toggle-btn outline" onclick="toggleHomeView('teachers')">Teachers</button>
                    </div>

                    <div class="filter-section" style="width: 98%;">
                        <select id="roomFilter" class="filter-btn" onchange="handleFilterChange()">
                            <option value="all">All Schedule</option>
                        </select>
                    </div>

                    <div id="comlabGrid" class="comlab-grid" style="width: 98%; margin: 0 auto;">
                        <!-- Lab cards will be loaded dynamically -->
                        <div style="grid-column: 1/-1; padding: 2rem; color: #94a3b8;">Loading schedules...</div>
                    </div>
                    <div id="teacherFilterContainer" class="teacher-filter-bar" style="text-align: left; width: 98%; margin: 1rem auto 1.5rem; display: none;">
                        <select id="teacherSelectFilter" class="filter-btn" onchange="handleFilterChange()">
                            <option value="all">All Teacher</option>
                        </select>
                    </div>
                    <div id="teacherGrid" class="teacher-grid-wrapper" style="margin: 0 auto; display: none;">
                        <!-- Teacher table will be loaded dynamically -->
                    </div>
                </div>
            </section>

            <!-- Faculty Section -->
            <section id="facultySection" class="content-section">
                <div class="dashboard-content" style="padding: 2rem 1.5rem; max-width: 100%; margin: 0 auto;">
                    <div class="manage-header-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; width: 98%; margin-left: auto; margin-right: auto;">
                        <h1 class="management-title" style="margin: 0; width: 100%;">
                            <span style="color: #1e1b4b;">MANAGE</span> 
                            <span style="color: #fbbf24;">TEACHERS</span>
                        </h1>
                        <button class="btn-manage-add" onclick="openModal('faculty')">Add Teacher</button>
                    </div>

                    <div class="manage-controls-row" style="margin-bottom: 2rem; width: 98%; margin-left: auto; margin-right: auto; display: flex; gap: 15px;">
                        <div class="form-group" style="margin: 0; min-width: 200px;">
                            <select id="teacherStatusFilter" class="filter-dropdown-navy" style="width: 100%;">
                                <option value="all">All Teachers</option>
                                <option value="Full-time">Full-time</option>
                                <option value="Part-Time">Part-Time</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive-wrapper" style="margin: 0 auto;">
                        <table id="facultyTable" class="teacher-manage-table" style="table-layout: fixed; width: 100%;">
                            <colgroup>
                                <col style="width: 14%">
                                <col style="width: 14%">
                                <col style="width: 12%">
                                <col style="width: 38%">
                                <col style="width: 12%">
                                <col style="width: 10%">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Teacher(s) Name</th>
                                    <th>Campus</th>
                                    <th>Employment Status</th>
                                    <th>Subjects</th>
                                    <th>Section(s)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="facultyTableBody">
                                <!-- Data will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- ComLabs & Subjects Section (Combined) -->
            <section id="roomsSection" class="content-section">
                <div class="dashboard-content" style="padding: 2rem 1.5rem; max-width: 100%; margin: 0 auto;">
                    <h1 class="management-title" style="margin-bottom: 2rem; width: 98%;">
                        <span style="color: #1e1b4b;">COMLABS</span> 
                        <span style="color: #fbbf24;">&amp;</span> 
                        <span style="color: #1e1b4b;">SUBJECTS</span>
                    </h1>
                    
                    <!-- Toggle Buttons -->
                    <div class="manage-controls-row" style="display: flex; justify-content: center; gap: 2rem; margin-bottom: 3rem;">
                        <button id="toggleComLabs" class="view-toggle-btn active" style="font-family: 'Playfair Display', serif;" onclick="switchCombinedView('comlabs')">ComLabs</button>
                        <button id="toggleSubjects" class="view-toggle-btn" style="font-family: 'Playfair Display', serif;" onclick="switchCombinedView('subjects')">Subjects</button>
                    </div>

                    <!-- Filter and Add Button Row -->
                    <div class="manage-controls-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; width: 98%; margin-left: auto; margin-right: auto;">
                        <div class="filter-section" style="margin: 0;">
                            <select id="combinedManagementFilter" class="filter-dropdown-navy" onchange="loadScheduleCombinedData()">
                                <option value="all">All ComLabs</option>
                            </select>
                        </div>
                        <div style="flex-grow: 1;"></div>
                        <button id="combinedAddBtn" class="btn-outline-yellow" onclick="openEditRoomModal()">Add ComLab</button>
                    </div>

                    <div class="table-responsive-wrapper" style="margin: 0 auto;">
                        <table id="scheduleCombinedTable" class="teacher-manage-table">
                            <thead>
                                <tr id="scheduleHeaderRow">
                                    <th>ComLab(s) Name</th>
                                    <th>Campus</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="scheduleCombinedBody">
                                <!-- Data injected by app.js -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Schedules Section -->
            <section id="schedulesSection" class="content-section">
                <div class="dashboard-content" style="padding: 2rem 1.5rem; max-width: 100%; margin: 0 auto;">
                    <div class="manage-header-row" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; width: 98%; margin-left: auto; margin-right: auto;">
                        <h1 class="main-title" style="margin: 0; font-family: 'Playfair Display', serif; font-size: 2.5rem; text-transform: uppercase;">
                            <span style="color: #1e1b4b; font-weight: 800;">ROOMS</span> 
                            <span style="color: #fbbf24; font-weight: 800;">SCHEDULES</span>
                        </h1>
                        <button class="btn-outline-yellow" onclick="openModal('schedules')">Add Schedule</button>
                    </div>

                    <div class="filter-section" style="margin-bottom: 3rem; width: 98%; margin-left: auto; margin-right: auto;">
                        <select id="scheduleVisualFilter" class="filter-btn" onchange="renderSchedulesVisualGrid()">
                            <option value="all">All schedule</option>
                        </select>
                    </div>

                    <div id="schedulesVisualGrid" class="comlab-grid" style="width: 98%; margin: 0 auto;">
                        <!-- Lab cards will be loaded dynamically -->
                        <div style="grid-column: 1/-1; padding: 2rem; color: #94a3b8; text-align: center;">Loading schedules...</div>
                    </div>
                </div>
            </section>

    <!-- Subject Modal (Add/Edit Subject with Room Assignment) -->
    <div id="subjectModalOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); z-index: 2500; justify-content: center; align-items: center;">
        <div class="glass-card" style="width: 100%; max-width: 480px; padding: 2.2rem; border: 1px solid #e2e8f0; border-radius: 12px;">
            <h3 id="subjectModalTitle" style="margin-bottom: 1.8rem; color: #1e1b4b; font-family: 'Playfair Display', serif; font-size: 1.6rem; text-align: center;">Add New Subject</h3>
            <div id="subjectError" style="display: none; padding: 10px; margin-bottom: 15px; border-radius: 8px; background-color: #fee2e2; border: 1px solid #f87171; color: #b91c1c; font-size: 0.85rem; font-weight: 600;"></div>
            <form id="subjectForm">
                <div class="form-group">
                    <label>Subject Code</label>
                    <input type="text" id="sm_code" required placeholder="e.g. IT-101" style="font-family: 'Inter', sans-serif;">
                </div>
                <div class="form-group">
                    <label>Subject Name</label>
                    <input type="text" id="sm_name" required placeholder="e.g. Programming 1" style="font-family: 'Inter', sans-serif;">
                </div>
                <div class="form-group">
                    <label>Units</label>
                    <input type="number" id="sm_units" value="3" required style="font-family: 'Inter', sans-serif;">
                </div>
                <div class="form-group">
                    <label>Assign Room (Optional)</label>
                    <select id="sm_room" style="font-family: 'Inter', sans-serif;">
                        <option value="">-- No Room --</option>
                    </select>
                </div>
                <div style="display: flex; gap: 0.8rem; justify-content: center; margin-top: 1.5rem;">
                    <button type="button" class="btn" style="background: #f1f5f9; color: #475569; padding: 0.6rem 2rem; border-radius: 50px; font-weight: 600; border: none; cursor: pointer;" onclick="closeSubjectModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background: #1e1b4b; color: white; padding: 0.6rem 2.5rem; border-radius: 50px; font-weight: 700; border: none; cursor: pointer;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Room Modal -->
    <div id="roomModalOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); z-index: 2500; justify-content: center; align-items: center;">
        <div class="glass-card" style="width: 100%; max-width: 450px; padding: 2.2rem; border: 1px solid #e2e8f0; border-radius: 12px;">
            <h3 id="roomModalTitle" style="margin-bottom: 1.8rem; color: #1e1b4b; font-family: 'Playfair Display', serif; font-size: 1.6rem; text-align: center;">Room Management</h3>
            <div id="roomError" style="display: none; padding: 10px; margin-bottom: 15px; border-radius: 8px; background-color: #fee2e2; border: 1px solid #f87171; color: #b91c1c; font-size: 0.85rem; font-weight: 600;"></div>
            <form id="roomForm" onsubmit="saveNewRoom(event)">
                <div class="form-group">
                    <label>Room Name</label>
                    <input type="text" id="r_name" required placeholder="Enter room name" style="font-family: 'Inter', sans-serif;">
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <select id="r_location" required style="font-family: 'Inter', sans-serif;">
                        <option value="">Select Location</option>
                        <option value="Main Campus">Main Campus</option>
                        <option value="Young Field">Young Field</option>
                        <option value="College Building">College Building</option>
                    </select>
                </div>
                <div style="display: flex; gap: 0.8rem; justify-content: center; margin-top: 1.5rem;">
                    <button type="button" class="btn" style="background: #f1f5f9; color: #475569; padding: 0.6rem 2rem; border-radius: 50px; font-weight: 600; border: none; cursor: pointer;" onclick="closeRoomModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background: #1e1b4b; color: white; padding: 0.6rem 2.5rem; border-radius: 50px; font-weight: 700; border: none; cursor: pointer;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
        </main>
    </div>

    <!-- Modals -->
    <div id="modalOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(4px); z-index: 1000; justify-content: center; align-items: center;">
        <div class="glass-card" style="width: 100%; max-width: 440px; padding: 1.8rem; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
            <h3 id="modalTitle" style="margin-bottom: 1rem; color: #00008B; font-family: 'Playfair Display', serif; font-size: 1.4rem; text-align: center; text-transform: uppercase; font-weight: 800;">Add New</h3>
            <div id="modalError" style="display: none; padding: 10px; margin-bottom: 15px; border-radius: 8px; background-color: #fee2e2; border: 1px solid #f87171; color: #b91c1c; font-size: 0.85rem; font-weight: 600;"></div>
            <form id="modalForm">
                <div id="modalFields"></div>
                <div style="display: flex; gap: 0.8rem; margin-top: 1.5rem; justify-content: center;">
                    <button type="button" class="btn" style="background: #f1f5f9; color: #475569; padding: 0.6rem 1.8rem; border-radius: 50px; font-weight: 600; font-size: 0.85rem; border: none; cursor: pointer;" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="background: #fbbf24; color: #000; padding: 0.6rem 2rem; border-radius: 50px; font-weight: 700; font-size: 0.85rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lab Schedule Modal -->
    <div id="labModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); z-index: 2000; justify-content: center; align-items: center;">
        <div class="glass-card" style="width: 90%; max-width: 1000px; max-height: 80vh; overflow-y: auto; padding: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2 id="labModalTitle" style="font-family: 'Playfair Display', serif; color: #1e1b4b;">Lab Schedule</h2>
                <button onclick="closeLabModal()" style="background: var(--secondary); border: none; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; font-weight: 700;">Close</button>
            </div>
            <div id="labModalContent">
                <!-- Table will be injected here -->
            </div>
        </div>
    </div>

    <script src="assets/app.js?v=5.0"></script>
</body>
</html>
