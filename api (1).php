<?php
header('Content-Type: application/json');

define('DB_H', 'localhost');
define('DB_U', 'root');
define('DB_P', '');
define('DB_N', 'wpl_final');

function db() {
    $c = new mysqli(DB_H, DB_U, DB_P, DB_N);
    if ($c->connect_error) {
        echo json_encode(['success' => false, 'error' => 'DB connection failed']);
        exit;
    }
    $c->set_charset('utf8mb4');
    return $c;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'register': doRegister(); break;
    case 'login': doLogin(); break;

    case 'verify_master': verifyMaster(); break;
    case 'employee_login': employeeLogin(); break;

    case 'get_animals': getAnimals(); break;
    case 'submit_service': submitService(); break;
    case 'get_my_services': getMyServices(); break;

    case 'book_appointment': bookAppointment(); break;
    case 'get_my_appointments': getMyAppointments(); break;

    case 'feedback': submitFeedback(); break;
    case 'get_feedback_list': getFeedbackList(); break;
    case 'get_feedback_avg': getFeedbackAvg(); break;

    case 'get_user_data': getUserData(); break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
}

/* ================= USER ================= */

function doRegister() {
    $d = db();

    $f  = trim($_POST['firstName'] ?? '');
    $l  = trim($_POST['lastName'] ?? '');
    $e  = trim($_POST['email'] ?? '');
    $fn = trim($_POST['flatNo'] ?? '');
    $b  = trim($_POST['building'] ?? '');
    $c  = trim($_POST['city'] ?? '');
    $p  = trim($_POST['pincode'] ?? '');
    $pw = $_POST['password'] ?? '';

    if (!$f || !$l || !$fn || !$b || !$c || !$p || !$pw)
        die(json_encode(['success' => false, 'error' => 'All fields required']));

    if (!filter_var($e, FILTER_VALIDATE_EMAIL))
        die(json_encode(['success' => false, 'error' => 'Invalid email']));

    if (!preg_match('/^\d{6}$/', $p))
        die(json_encode(['success' => false, 'error' => 'Invalid pincode']));

    if (strlen($pw) < 8)
        die(json_encode(['success' => false, 'error' => 'Password too short']));

    $s = $d->prepare('SELECT id FROM users WHERE email=?');
    $s->bind_param('s', $e); $s->execute(); $s->store_result();

    if ($s->num_rows)
        die(json_encode(['success' => false, 'error' => 'Email exists']));

    $h = password_hash($pw, PASSWORD_BCRYPT);

    $s = $d->prepare('INSERT INTO users(first_name,last_name,email,flat_no,building,city,pincode,password) VALUES(?,?,?,?,?,?,?,?)');
    $s->bind_param('ssssssss', $f,$l,$e,$fn,$b,$c,$p,$h);

    echo json_encode(['success' => $s->execute()]);
}

function doLogin() {
    $d = db();

    $e = trim($_POST['email'] ?? '');
    $p = $_POST['password'] ?? '';

    if (!$e || !$p)
        die(json_encode(['success' => false, 'error' => 'Missing fields']));

    $s = $d->prepare('SELECT * FROM users WHERE email=?');
    $s->bind_param('s', $e); $s->execute();
    $r = $s->get_result()->fetch_assoc();

    if (!$r || !password_verify($p, $r['password']))
        die(json_encode(['success' => false, 'error' => 'Invalid login']));

    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $r['id'],
            'name' => $r['first_name']
        ]
    ]);
}

/* ================= EMPLOYEE ================= */

function verifyMaster() {
    $pw = $_POST['master_password'] ?? '';

    if ($pw === 'Manish@0312') {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Wrong master password']);
    }
}

function employeeLogin() {
    $d = db();

    $role = trim($_POST['role'] ?? '');
    $pw   = trim($_POST['role_password'] ?? '');

    if (!$role || !$pw) {
        echo json_encode(['success' => false, 'error' => 'Missing fields']);
        return;
    }

    $expected = $role . '@123';

    if ($pw !== $expected) {
        echo json_encode(['success' => false, 'error' => 'Wrong password']);
        return;
    }

    $s = $d->prepare("SELECT * FROM employees WHERE role=? LIMIT 1");
    $s->bind_param('s', $role);
    $s->execute();
    $r = $s->get_result()->fetch_assoc();

    if (!$r) {
        echo json_encode(['success' => false, 'error' => 'Role not found']);
        return;
    }

    echo json_encode([
        'success' => true,
        'employee' => [
            'id' => $r['id'],
            'name' => $r['emp_name'],
            'role' => $r['role']
        ]
    ]);
}

/* ================= ANIMALS ================= */

function getAnimals() {
    $d = db();
    $r = $d->query('SELECT * FROM animals ORDER BY is_hot DESC');
    echo json_encode(['success' => true, 'animals' => $r->fetch_all(MYSQLI_ASSOC)]);
}

/* ================= SERVICES ================= */

function submitService() {
    $d = db();

    $uid = (int)($_POST['user_id'] ?? 0);
    $type = $_POST['service_type'] ?? '';
    $details = $_POST['details'] ?? '';
    $date = $_POST['req_date'] ?? '';
    $time = $_POST['req_time'] ?? '';

    if (!$uid || !$type || !$details || !$date)
        die(json_encode(['success' => false, 'error' => 'Missing fields']));

    $s = $d->prepare('INSERT INTO service_requests(user_id,service_type,details,req_date,req_time) VALUES(?,?,?,?,?)');
    $s->bind_param('issss', $uid,$type,$details,$date,$time);

    echo json_encode(['success' => $s->execute()]);
}

function getMyServices() {
    $d = db();
    $u = (int)($_GET['user_id'] ?? 0);

    $s = $d->prepare('SELECT * FROM service_requests WHERE user_id=?');
    $s->bind_param('i',$u);
    $s->execute();

    echo json_encode(['success'=>true,'services'=>$s->get_result()->fetch_all(MYSQLI_ASSOC)]);
}

/* ================= APPOINTMENTS ================= */

function bookAppointment() {
    $d = db();

    $uid = (int)($_POST['user_id'] ?? 0);
    $name = $_POST['animal_name'] ?? '';
    $date = $_POST['appt_date'] ?? '';

    if (!$uid || !$date)
        die(json_encode(['success'=>false,'error'=>'Missing fields']));

    $s = $d->prepare('INSERT INTO appointments(user_id,animal_name,appt_date) VALUES(?,?,?)');
    $s->bind_param('iss',$uid,$name,$date);

    echo json_encode(['success'=>$s->execute()]);
}

function getMyAppointments() {
    $d = db();
    $u = (int)($_GET['user_id'] ?? 0);

    $s = $d->prepare('SELECT * FROM appointments WHERE user_id=?');
    $s->bind_param('i',$u);
    $s->execute();

    echo json_encode(['success'=>true,'appointments'=>$s->get_result()->fetch_all(MYSQLI_ASSOC)]);
}

/* ================= FEEDBACK ================= */

function submitFeedback() {
    $d = db();

    $e = $_POST['email'] ?? '';
    $s = $_POST['service'] ?? '';
    $r = (int)($_POST['rating'] ?? 0);
    $m = $_POST['message'] ?? '';

    if (!$e || !$s || !$r || !$m)
        die(json_encode(['success'=>false,'error'=>'Missing fields']));

    $q = $d->prepare('INSERT INTO feedback(email,service,rating,message) VALUES(?,?,?,?)');
    $q->bind_param('ssis',$e,$s,$r,$m);

    echo json_encode(['success'=>$q->execute()]);
}

function getFeedbackList() {
    $d = db();
    $r = $d->query('SELECT * FROM feedback ORDER BY created_at DESC LIMIT 10');
    echo json_encode(['success'=>true,'feedbacks'=>$r->fetch_all(MYSQLI_ASSOC)]);
}

function getFeedbackAvg() {
    $d = db();
    $r = $d->query('SELECT AVG(rating) a FROM feedback')->fetch_assoc();
    echo json_encode(['success'=>true,'avg'=>$r['a'] ?? 0]);
}

/* ================= DASHBOARD ================= */

function getUserData() {
    $d = db();
    $u = (int)($_GET['user_id'] ?? 0);

    $s = $d->prepare('SELECT * FROM users WHERE id=?');
    $s->bind_param('i',$u);
    $s->execute();
    $user = $s->get_result()->fetch_assoc();

    echo json_encode(['success'=>true,'user'=>$user]);
}
?>