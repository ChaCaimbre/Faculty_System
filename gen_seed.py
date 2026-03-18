import sys

def parse_schedule(text):
    # This is a bit manual but organized
    lines = text.strip().split('\n')
    current_room = None
    rooms = {}

    def parse_item(item_str):
        if not item_str or item_str.lower() == 'vacant':
            return None
        # Format: IT-128/ AI32/ AMAGO or IT-130L/AI11/DALAN
        parts = [p.strip() for p in item_str.split('/')]
        if len(parts) < 3:
            return None
        return {
            'subject': parts[0],
            'section': parts[1],
            'teacher': parts[2]
        }

    # Manual rows for each room based on the layout
    # CompLab 1
    # 8-10, 10-12, 1-3, 3-5, 5-7, 7-9?, 9-11?
    # Mon/Thu Col1, Tue/Fri Col2, Wed Col4, Sat Col6
    
    # Actually, the user's text block is already rooms grouped.
    
    # CompLab 1 items:
    # 8-10: [M/H, T/F, WED, SAT]
    # 10-12: [M/H, T/F, WED, SAT]
    # 1-3: [M/H, T/F, WED, SAT]
    # ...
    # I'll manually code CompLab 1-7, CHS, CISCO into a data structure.
    
    # CompLab 1 (COMPLAB1)
    # M/H: IT-128/AI32/AMAGO, IT-128L/AI32/AMAGO, IT-130L/AI11/DALAN, IT-115/AI22/DURANGO, IT-115L/AI25/DURANGO, IT-105L/AI34/SAMPAYAN, IT-112L/AI24/COMBINIDO
    # T/F: IT-128/AI31/FUNCION, IT-128L/AI31/FUNCION, IT-115L/AI21/FERNANDEZ, vacant, IT-112L/AI21/AMAGO, IT-105L/AI35/CABANGON, IT-127L/AI31/CELESTIAL
    # WED: IT-115/AI25/DURANGO, IT-108/AI11/DALAN, vacant, IT-107/AI11/TIQUEN, IT-112/AI24/COMINIDO (COMBINIDO)
    # SAT: IT-127/AI35/MURILLO, vacant, vacant
    # times: 8:00-10:00, 10:00-12:00, 13:00-15:00, 15:00-17:00, 17:00-19:00, 19:00-21:00, 21:00-23:00

    results = []

    def add(room, day, start, end, sub, sec, fac):
        if not sub or sub == 'vacant': return
        results.append((room, day, start, end, sub, sec, fac))

    # Room 1
    t = ['08:00:00','10:00:00','12:00:00','13:00:00','15:00:00','17:00:00','19:00:00','21:00:00']
    r1mh = ["IT-128/AI32/AMAGO", "IT-128L/AI32/AMAGO", "IT-130L/AI11/DALAN", "IT-115/AI22/DURANGO", "IT-115L/AI25/DURANGO", "IT-105L/AI34/SAMPAYAN", "IT-112L/AI24/COMBINIDO"]
    r1tf = ["IT-128/AI31/FUNCION", "IT-128L/AI31/FUNCION", "IT-115L/AI21/FERNANDEZ", "vacant", "IT-112L/AI21/AMAGO", "IT-105L/AI35/CABANGON", "IT-127L/AI31/CELESTIAL"]
    r1w  = ["IT-115/AI25/DURANGO", "IT-108/AI11/DALAN", "vacant", "IT-107/AI11/TIQUEN", "IT-112/AI24/COMBINIDO"]
    r1s  = ["IT-127/AI35/MURILLO"]

    def process_mh_tf(room, mh_list, tf_list, times_list):
        for i, item in enumerate(mh_list):
            p = parse_item(item)
            if p:
                start = times_list[0] if i == 0 else (times_list[1] if i == 1 else (times_list[3] if i == 2 else (times_list[4] if i == 3 else (times_list[5] if i == 4 else (times_list[6] if i == 5 else times_list[7])))))
                end = times_list[1] if i == 0 else (times_list[2] if i == 1 else (times_list[4] if i == 2 else (times_list[5] if i == 3 else (times_list[6] if i == 4 else (times_list[7] if i == 5 else "22:00:00")))))
                add(room, 'Monday', start, end, p['subject'], p['section'], p['teacher'])
                add(room, 'Thursday', start, end, p['subject'], p['section'], p['teacher'])
        for i, item in enumerate(tf_list):
            p = parse_item(item)
            if p:
                start = times_list[0] if i == 0 else (times_list[1] if i == 1 else (times_list[3] if i == 2 else (times_list[4] if i == 3 else (times_list[5] if i == 4 else (times_list[6] if i == 5 else times_list[7])))))
                end = times_list[1] if i == 0 else (times_list[2] if i == 1 else (times_list[4] if i == 2 else (times_list[5] if i == 3 else (times_list[6] if i == 4 else (times_list[7] if i == 5 else "22:00:00")))))
                add(room, 'Tuesday', start, end, p['subject'], p['section'], p['teacher'])
                add(room, 'Friday', start, end, p['subject'], p['section'], p['teacher'])

    process_mh_tf('COMPLAB1', r1mh, r1tf, t)
    for i, item in enumerate(r1w):
        p = parse_item(item)
        if p:
            start = t[0] if i == 0 else (t[1] if i == 1 else (t[3] if i == 2 else (t[4] if i == 3 else t[5])))
            end = t[1] if i == 0 else (t[2] if i == 1 else (t[4] if i == 2 else (t[5] if i == 3 else t[6])))
            add('COMPLAB1', 'Wednesday', start, end, p['subject'], p['section'], p['teacher'])
    if len(r1s) > 0:
        p = parse_item(r1s[0])
        if p: add('COMPLAB1', 'Saturday', t[0], t[1], p['subject'], p['section'], p['teacher'])

    # Repeat for CompLab 2
    r2mh = ["IT-107/AI31/GALBAN", "IT-107L/AI13/GALBAN", "IT-108L/AI13/CINCO", "IT-105L/AI31/CABANGON", "IT-107L/AI14/GALBAN", "IT107/AI14/GALBAN", "TOUR-101L/MT13/MEMORACION"]
    r2tf = ["IT-127L/AI34/MURILLO", "IT-107L/AI15/GALBAN", "IT-108L/AI14/DALAN", "GE-113/SM31/QUISUMBING", "IT-115L/AI24/FERNANDEZ", "IT-127L/AI32/LAGONOY", "TOUR-101L/MT12/ALMENARIO"]
    r2w  = ["IT-112/AI22/ORMENETA", "IT-112/AI23/ORMENETA", "IT-107/AI15/GALBAN", "IT-127/AI33/LAGONOY", "IT-127/AI31/CELESTIAL"]
    r2sat = ["vacant", "IT-129/AI35/DIAZ"]
    
    process_mh_tf('COMPLAB2', r2mh, r2tf, t)
    for i, item in enumerate(r2w):
        p = parse_item(item)
        if p: add('COMPLAB2', 'Wednesday', (t[0] if i==0 else (t[1] if i==1 else (t[3] if i==2 else (t[4] if i==3 else t[5])))), (t[1] if i==0 else (t[2] if i==1 else (t[4] if i==2 else (t[5] if i==3 else t[6])))), p['subject'], p['section'], p['teacher'])
    p2s = parse_item(r2sat[1])
    if p2s: add('COMPLAB2', 'Saturday', t[1], t[2], p2s['subject'], p2s['section'], p2s['teacher'])

    # Room 3
    r3mh = ["IT-128/AI31/FUNCTION", "IT-128L/AI34/FUNCION", "IT-105L/AI32/CABANGON", "IT-112L/AI25/AMAGO", "IT-105/AI32/CABANGON", "IT-127L/AI33/LAGONOY", "IT-127L/AI35/MURILLO"]
    r3tf = ["IT-107L/AI11/TIQUEN", "IT-107L/AI12/TIQUEN", "IT-124/AI33/FUNCION", "IT-108/AI15/ORMENETA", "IT-108/AI15/ORMENETA", "IT-115L/AI23/FERNANDEZ", "vacant"]
    r3w  = ["IT-108/AI31/CINCO", "IT-107/AI12/TIQUEN", "IT-108/AI14/DALAN", "IT-129/AI33/DABLEO"]
    
    process_mh_tf('COMPLAB3', r3mh, r3tf, t)
    for i, item in enumerate(r3w):
        p = parse_item(item)
        if p: add('COMPLAB3', 'Wednesday', (t[0] if i==0 else (t[1] if i==1 else (t[3] if i==2 else t[4]))), (t[1] if i==0 else (t[2] if i==1 else (t[4] if i==2 else t[5]))), p['subject'], p['section'], p['teacher'])

    # Room 4
    r4mh = ["IT-105L/AI33/CABANGON", "IT-112L/AI22/ORMENETA", "IT-112L/AI23/ORMENETA", "IT-108L/AI12/ORMENETA", "IT-120L/AI21/DALAN", "IT-120L/AI21/DALAN"]
    r4tf = ["IT-120/AI24/TIBE", "IT-120L/AI24/TIBE", "GE-113/AS22/GALBAN", "IT-120L/AI22/TIBE", "IT-120/AI22/TIBE", "IT-108/AI12/ORMENETA"]
    r4w  = ["IT-112/AI21/AMAGO", "IT-112/AI25/AMAGO", "IT-127/AI32/LAGONOY", "IT-105/AI34/SAMPAYAN", "IT-127/AI34/MURILLO"]
    
    process_mh_tf('COMPLAB4', r4mh, r4tf, t)
    for i, item in enumerate(r4w):
        p = parse_item(item)
        if p: add('COMPLAB4', 'Wednesday', (t[0] if i==0 else (t[1] if i==1 else (t[3] if i==2 else (t[4] if i==3 else t[5])))), (t[1] if i==0 else (t[2] if i==1 else (t[4] if i==2 else (t[5] if i==3 else t[6])))), p['subject'], p['section'], p['teacher'])

    # Room 5
    r5mh = ["GE-113/AS21/VERECIO", "GE-113/MH21/TIQUEN", "IT-124/AI34/APOLINAR", "SPT-104/AL21/NAVARRO", "IT-129L/AI31/TIBE", "IT-126/AI32/TIBE", "IT-129L/AI33/DABLEO"]
    r5tf = ["IT-128L/AI36/AMAGO", "IT-128L/AI35/AMAGO", "SPT-111/AL31/NAVARRO", "LIS-106/AL21/NAVARRO", "SPT-110/AL21/NAVARRO", "vacant", "IT-124/AI35/MEMORACION"]
    r5w  = ["LIS-112/AL31/NAVARRO", "vacant", "vacant", "GE-107/AL21/NAVARRO", "TOUR-101/MT12/ALMENARIO"]
    r5s  = ["LIS-104/AL21/SASI"]
    
    process_mh_tf('COMPLAB5 (CON 103)', r5mh, r5tf, t)
    for i, item in enumerate(r5w):
        p = parse_item(item)
        if p: add('COMPLAB5 (CON 103)', 'Wednesday', (t[0] if i==0 else (t[1] if i==1 else (t[3] if i==2 else (t[4] if i==3 else t[5])))), (t[1] if i==0 else (t[2] if i==1 else (t[4] if i==2 else (t[5] if i==3 else t[6])))), p['subject'], p['section'], p['teacher'])
    if len(r5s) > 0:
        p = parse_item(r5s[0])
        if p: add('COMPLAB5 (CON 103)', 'Saturday', t[0], t[1], p['subject'], p['section'], p['teacher'])

    # Room 6
    r6mh = ["IT-104/AI11/APOLINAR", "IT-104/AI12/APOLINAR", "IT-104/AI14/LAURENTE", "IT-104/AI13/SAMPAYAN", "IT-104/AI15/SAMPAYAN", "vacant", "GE-113/MH22/NICOLAS"]
    r6tf = ["IT-134/AI41/LAURENTE", "vacant", "vacant", "IT-120L/1I23/DALAN", "IT-120/AI23/DALAN", "vacant", "GE-113/MH23/NICOLAS"]
    r6w  = ["IT-115/AI24/FERNANDEZ", "IT-128/AI33/CALUZA", "IT-115/AI23/FERNANDEZ", "IT-115/AI21/FERNANDEZ", "TOUR-101/MT13/MEMORACION"]
    
    process_mh_tf('COMPLAB6 (CON 104)', r6mh, r6tf, t)
    for i, item in enumerate(r6w):
        p = parse_item(item)
        if p: add('COMPLAB6 (CON 104)', 'Wednesday', (t[0] if i==0 else (t[1] if i==1 else (t[3] if i==2 else (t[4] if i==3 else t[5])))), (t[1] if i==0 else (t[2] if i==1 else (t[4] if i==2 else (t[5] if i==3 else t[6])))), p['subject'], p['section'], p['teacher'])

    # Room 7
    r7mh = ["IT-124/AI31/CALUZA", "IT-120L/AI25/TIBE", "IT-120/AI25/TIBE", "IT-134/AI42/CINCO"]
    r7tf = ["IT-124/AI32/CALUZA", "IT-128L/AI33/CALUZA", "LIS ICT-104A/L/AI/TIBE", "vacant", "TOUR-101L/MT11/SAMPAYAN", "TOUR-101/MT11/SAMPAYAN", "GE-113/SM32/MORETO"]
    r7w  = ["IT-105/AI31/CABANGON", "IT-129/AI31/TIBE", "IT-105/AI33/CABANGON", "IT-115/AI22/DURANGO"]
    
    process_mh_tf('COMPLAB7 (CON 105)', r7mh, r7tf, t)
    for i, item in enumerate(r7w):
        p = parse_item(item)
        if p: add('COMPLAB7 (CON 105)', 'Wednesday', (t[0] if i==0 else (t[1] if i==1 else (t[3] if i==2 else t[4]))), (t[1] if i==0 else (t[2] if i==1 else (t[4] if i==2 else t[5]))), p['subject'], p['section'], p['teacher'])

    # CHS
    rchs_mh = ["GE-113/MH24/TIQUEN", "IT-130L/AI11/TURCO", "IT-130L/AI15/TURCO", "IT-129/AI34/TURCO", "IT-129L/AI34/TUCO", "vacant", "IT-130L/AI14/VILLAFUERTE"]
    rchs_tf = ["IT-130/AI12/DURANGO", "IT-129L/AI32/TURCO", "IT-130L/AI12/DURANGO", "vacant", "IT-130L/AI13/DURANGO"]
    rchs_w  = ["IT-130/AI11/TURCO", "IT-129/AI32/TURCO", "IT-130/AI13/DURANGO", "IT-130/AI15/TURCO", "IT-130/AI14/VILLAFUERTE"]
    
    process_mh_tf('CHS (CON 101)', rchs_mh, rchs_tf, t)
    for i, item in enumerate(rchs_w):
        p = parse_item(item)
        if p: add('CHS (CON 101)', 'Wednesday', (t[0] if i==0 else (t[1] if i==1 else (t[3] if i==2 else (t[4] if i==3 else t[5])))), (t[1] if i==0 else (t[2] if i==1 else (t[4] if i==2 else (t[5] if i==3 else t[6])))), p['subject'], p['section'], p['teacher'])

    # CISCO
    rcis_mh = ["IT-116L/AI21/QUISUMBING", "IT-116/AI21/QUISUMBING", "IT-126/AI35/TIQUEN"]
    rcis_tf = ["IT-116L/AI23/QUISUMBING", "IT-116/AI23/QUISUMBING", "IT-116L/AI22/CINCO", "IT-116L/AI24/CABANGON", "IT-116L/AI25/CINCO", "IT-116/AI22/CINCO"]
    rcis_w  = ["vacant", "IT-105/AI35/CABANGON", "IT-116/AI25/CINCO", "IT-116/AI24/CABANGON"]
    
    process_mh_tf('CISCO (CON 102)', rcis_mh, rcis_tf, t)
    for i, item in enumerate(rcis_w):
        p = parse_item(item)
        if p: add('CISCO (CON 102)', 'Wednesday', (t[0] if i==0 else (t[1] if i==1 else (t[3] if i==2 else t[4]))), (t[1] if i==0 else (t[2] if i==1 else (t[4] if i==2 else t[5]))), p['subject'], p['section'], p['teacher'])

    # Special items
    add('CISCO (CON 102)', 'Tuesday', '17:00:00', '19:00:00', 'IT-126', 'AI34', 'TIQUEN')
    add('CISCO (CON 102)', 'Friday', '17:00:00', '19:00:00', 'IT-126', 'AI34', 'TIQUEN')
    add('CISCO (CON 102)', 'Tuesday', '19:00:00', '21:00:00', 'IT-126', 'AI33', 'TIQUEN')
    add('CISCO (CON 102)', 'Friday', '19:00:00', '21:00:00', 'IT-126', 'AI33', 'TIQUEN')
    # add('ACAD 201', 'Tuesday', '21:00:00', '23:00:00', 'IT-126', 'AI31', 'VERECIO')
    # add('ACAD 201', 'Friday', '21:00:00', '23:00:00', 'IT-126', 'AI31', 'VERECIO')

    # Generate PHP
    print('<?php')
    print('require_once "config.php";')
    print('$db = getDB();')
    print('// Clear and re-populate')
    print('$db->query("SET FOREIGN_KEY_CHECKS = 0");')
    print('$db->query("TRUNCATE TABLE schedules");')
    print('$db->query("SET FOREIGN_KEY_CHECKS = 1");')
    print('$user_id = 1; $term_id = 1;')

    print('$roomsMap = []; $res = $db->query("SELECT id, name FROM rooms"); while($r=$res->fetch_assoc()) $roomsMap[$r["name"]]=$r["id"];')
    print('$facultyMap = []; $res = $db->query("SELECT id, name FROM faculty"); while($f=$res->fetch_assoc()) $facultyMap[$f["name"]]=$f["id"];')
    print('$subjectMap = []; $res = $db->query("SELECT id, code FROM subjects"); while($s=$res->fetch_assoc()) $subjectMap[$s["code"]]=$s["id"];')
    
    print('function ensureFaculty($db, $name, &$map) { if(!isset($map[$name])) { $db->query("INSERT INTO faculty (name) VALUES (\'".$db->real_escape_string($name)."\')"); $map[$name] = $db->insert_id; } return $map[$name]; }')
    print('function ensureSubject($db, $code, &$map) { if(!isset($map[$code])) { $db->query("INSERT INTO subjects (code, name) VALUES (\'".$db->real_escape_string($code)."\', \'".$db->real_escape_string($code)."\')"); $map[$code] = $db->insert_id; } return $map[$code]; }')
    print('function ensureRoom($db, $name, &$map) { if(!isset($map[$name])) { $db->query("INSERT INTO rooms (name) VALUES (\'".$db->real_escape_string($name)."\')"); $map[$name] = $db->insert_id; } return $map[$name]; }')

    for r, d, s, e, sub, sec, fac in results:
        print(f'$fid = ensureFaculty($db, "{fac}", $facultyMap);')
        print(f'$sid = ensureSubject($db, "{sub}", $subjectMap);')
        print(f'$rid = ensureRoom($db, "{r}", $roomsMap);')
        print(f'$db->query("INSERT INTO schedules (faculty_id, subject_id, room_id, day, section, start_time, end_time, user_id, term_id) VALUES ($fid, $sid, $rid, \'{d}\', \'{sec}\', \'{s}\', \'{e}\', $user_id, $term_id)");')

    print('echo "Seeding completed!";')

parse_schedule("")
