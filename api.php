<?php
// ===================================================
// API Endpoint for Mentors & Mentees CRUD (api.php)
// Connected with PHP PDO + MySQL
// ===================================================

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Helper function for sending JSON response
function sendJsonResponse($status, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode([
        'status'  => $status,
        'message' => $message,
        'data'    => $data
    ]);
    exit;
}

// Get action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Helper function to validate and save uploaded image
function validateAndSavePhoto($file, $idPrefix, $uploadDir) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'fileName' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Error uploading file. Please try again.'];
    }

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension'] ?? '');

    if (!in_array($extension, $allowedExtensions)) {
        return ['success' => false, 'error' => 'Invalid file format. Only JPG, PNG, and WebP images are allowed.'];
    }

    $cleanId = preg_replace('/[^a-zA-Z0-9_-]/', '', $idPrefix);
    $uniqueFileName = 'img_' . $cleanId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = $uploadDir . $uniqueFileName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'fileName' => 'uploads/' . $uniqueFileName];
    } else {
        return ['success' => false, 'error' => 'Failed to save uploaded image on the server.'];
    }
}

try {
    switch ($action) {

        // ==========================================
        // 1. MENTORS CRUD
        // ==========================================

        case 'read':
            try {
                // Try reading mentors with mentees count if mentees table exists
                $stmt = $pdo->prepare("
                    SELECT m.id, m.name, m.employee_id, m.department, m.designation, m.max_mentees, m.photo_path, m.created_at,
                           COUNT(me.id) as assigned_mentees_count
                    FROM mentors m
                    LEFT JOIN mentees me ON me.mentor_id = m.id
                    GROUP BY m.id
                    ORDER BY m.id DESC
                ");
                $stmt->execute();
                $mentors = $stmt->fetchAll();
            } catch (Exception $e) {
                // Fallback query if mentees table is not created yet
                $stmt = $pdo->prepare("SELECT id, name, employee_id, department, designation, max_mentees, photo_path, created_at, 0 as assigned_mentees_count FROM mentors ORDER BY id DESC");
                $stmt->execute();
                $mentors = $stmt->fetchAll();
            }

            sendJsonResponse('success', 'Mentors retrieved successfully.', $mentors);
            break;

        case 'create':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                sendJsonResponse('error', 'Invalid request method.', null, 405);
            }

            $name        = trim($_POST['mentorName'] ?? '');
            $employeeId  = trim($_POST['employeeId'] ?? '');
            $department  = trim($_POST['department'] ?? '');
            $designation = trim($_POST['designation'] ?? '');
            $maxMentees  = $_POST['maxMentees'] ?? '';

            if (empty($name)) sendJsonResponse('error', 'Mentor Name is required.', null, 400);
            if (empty($employeeId)) sendJsonResponse('error', 'Employee ID is required.', null, 400);
            if (empty($department)) sendJsonResponse('error', 'Department is required.', null, 400);
            if (empty($designation)) sendJsonResponse('error', 'Designation is required.', null, 400);
            if (!filter_var($maxMentees, FILTER_VALIDATE_INT) || (int)$maxMentees <= 0) {
                sendJsonResponse('error', 'Maximum Mentees Allowed must be a positive whole number.', null, 400);
            }

            $checkStmt = $pdo->prepare("SELECT id FROM mentors WHERE employee_id = ? LIMIT 1");
            $checkStmt->execute([$employeeId]);
            if ($checkStmt->fetch()) {
                sendJsonResponse('error', 'A mentor with this Employee ID already exists.', null, 400);
            }

            $photoPath = null;
            if (isset($_FILES['profilePhoto']) && $_FILES['profilePhoto']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = validateAndSavePhoto($_FILES['profilePhoto'], $employeeId, $uploadDir);
                if (!$uploadResult['success']) {
                    sendJsonResponse('error', $uploadResult['error'], null, 400);
                }
                $photoPath = $uploadResult['fileName'];
            }

            $insertStmt = $pdo->prepare("INSERT INTO mentors (name, employee_id, department, designation, max_mentees, photo_path) VALUES (?, ?, ?, ?, ?, ?)");
            $insertStmt->execute([$name, $employeeId, $department, $designation, (int)$maxMentees, $photoPath]);
            $newId = $pdo->lastInsertId();

            sendJsonResponse('success', 'Mentor added successfully.', ['id' => $newId], 201);
            break;

        case 'update':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendJsonResponse('error', 'Invalid request method.', null, 405);

            $id          = $_POST['id'] ?? '';
            $name        = trim($_POST['editMentorName'] ?? '');
            $employeeId  = trim($_POST['editEmployeeId'] ?? '');
            $department  = trim($_POST['editDepartment'] ?? '');
            $designation = trim($_POST['editDesignation'] ?? '');
            $maxMentees  = $_POST['editMaxMentees'] ?? '';

            if (!filter_var($id, FILTER_VALIDATE_INT)) sendJsonResponse('error', 'Invalid mentor ID.', null, 400);
            if (empty($name) || empty($employeeId) || empty($department) || empty($designation)) {
                sendJsonResponse('error', 'All required fields must be filled.', null, 400);
            }
            if (!filter_var($maxMentees, FILTER_VALIDATE_INT) || (int)$maxMentees <= 0) {
                sendJsonResponse('error', 'Max Mentees must be a positive integer.', null, 400);
            }

            $fetchStmt = $pdo->prepare("SELECT photo_path FROM mentors WHERE id = ?");
            $fetchStmt->execute([$id]);
            $existing = $fetchStmt->fetch();
            if (!$existing) sendJsonResponse('error', 'Mentor record not found.', null, 404);

            $checkStmt = $pdo->prepare("SELECT id FROM mentors WHERE employee_id = ? AND id != ? LIMIT 1");
            $checkStmt->execute([$employeeId, $id]);
            if ($checkStmt->fetch()) sendJsonResponse('error', 'Employee ID is already in use.', null, 400);

            $photoPath = $existing['photo_path'];
            if (isset($_FILES['editProfilePhoto']) && $_FILES['editProfilePhoto']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = validateAndSavePhoto($_FILES['editProfilePhoto'], $employeeId, $uploadDir);
                if (!$uploadResult['success']) sendJsonResponse('error', $uploadResult['error'], null, 400);
                
                if (!empty($existing['photo_path'])) {
                    $oldFilePath = __DIR__ . '/' . $existing['photo_path'];
                    if (file_exists($oldFilePath) && is_file($oldFilePath)) @unlink($oldFilePath);
                }
                $photoPath = $uploadResult['fileName'];
            }

            $updateStmt = $pdo->prepare("UPDATE mentors SET name = ?, employee_id = ?, department = ?, designation = ?, max_mentees = ?, photo_path = ? WHERE id = ?");
            $updateStmt->execute([$name, $employeeId, $department, $designation, (int)$maxMentees, $photoPath, $id]);

            sendJsonResponse('success', 'Mentor details updated successfully.');
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendJsonResponse('error', 'Invalid request method.', null, 405);
            $id = $_POST['id'] ?? '';
            if (!filter_var($id, FILTER_VALIDATE_INT)) sendJsonResponse('error', 'Invalid mentor ID.', null, 400);

            $fetchStmt = $pdo->prepare("SELECT photo_path FROM mentors WHERE id = ?");
            $fetchStmt->execute([$id]);
            $mentor = $fetchStmt->fetch();
            if (!$mentor) sendJsonResponse('error', 'Mentor not found.', null, 404);

            if (!empty($mentor['photo_path'])) {
                $filePath = __DIR__ . '/' . $mentor['photo_path'];
                if (file_exists($filePath) && is_file($filePath)) @unlink($filePath);
            }

            $deleteStmt = $pdo->prepare("DELETE FROM mentors WHERE id = ?");
            $deleteStmt->execute([$id]);

            sendJsonResponse('success', 'Mentor deleted successfully.');
            break;

        // ==========================================
        // 2. MENTEES CRUD
        // ==========================================

        case 'read_mentees':
            try {
                $stmt = $pdo->prepare("
                    SELECT me.id, me.name, me.student_id, me.department, me.academic_year, me.email, me.phone, me.mentor_id, me.photo_path, me.created_at,
                           m.name as mentor_name, m.department as mentor_department, m.employee_id as mentor_employee_id
                    FROM mentees me
                    LEFT JOIN mentors m ON me.mentor_id = m.id
                    ORDER BY me.id DESC
                ");
                $stmt->execute();
                $mentees = $stmt->fetchAll();
                sendJsonResponse('success', 'Mentees retrieved successfully.', $mentees);
            } catch (Exception $e) {
                // If mentees table doesn't exist yet, return empty list gracefully
                sendJsonResponse('success', 'Mentees table not initialized yet.', []);
            }
            break;

        case 'create_mentee':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendJsonResponse('error', 'Invalid request method.', null, 405);

            $name         = trim($_POST['menteeName'] ?? '');
            $studentId    = trim($_POST['studentId'] ?? '');
            $department   = trim($_POST['department'] ?? '');
            $academicYear = trim($_POST['academicYear'] ?? '');
            $email        = trim($_POST['email'] ?? '');
            $phone        = trim($_POST['phone'] ?? '');
            $mentorId     = !empty($_POST['mentorId']) ? (int)$_POST['mentorId'] : null;

            if (empty($name)) sendJsonResponse('error', 'Mentee Full Name is required.', null, 400);
            if (empty($studentId)) sendJsonResponse('error', 'Student ID / Roll No is required.', null, 400);
            if (empty($department)) sendJsonResponse('error', 'Department is required.', null, 400);
            if (empty($academicYear)) sendJsonResponse('error', 'Academic Year is required.', null, 400);

            $checkStmt = $pdo->prepare("SELECT id FROM mentees WHERE student_id = ? LIMIT 1");
            $checkStmt->execute([$studentId]);
            if ($checkStmt->fetch()) {
                sendJsonResponse('error', 'A student with this Student ID / Roll No is already enrolled.', null, 400);
            }

            if ($mentorId) {
                $capStmt = $pdo->prepare("
                    SELECT m.max_mentees, COUNT(me.id) as current_count
                    FROM mentors m
                    LEFT JOIN mentees me ON me.mentor_id = m.id
                    WHERE m.id = ?
                    GROUP BY m.id
                ");
                $capStmt->execute([$mentorId]);
                $mentorCap = $capStmt->fetch();
                if ($mentorCap && $mentorCap['current_count'] >= $mentorCap['max_mentees']) {
                    sendJsonResponse('error', 'Selected mentor has reached their maximum mentee capacity.', null, 400);
                }
            }

            $photoPath = null;
            if (isset($_FILES['menteePhoto']) && $_FILES['menteePhoto']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = validateAndSavePhoto($_FILES['menteePhoto'], $studentId, $uploadDir);
                if (!$uploadResult['success']) sendJsonResponse('error', $uploadResult['error'], null, 400);
                $photoPath = $uploadResult['fileName'];
            }

            $insertStmt = $pdo->prepare("
                INSERT INTO mentees (name, student_id, department, academic_year, email, phone, mentor_id, photo_path)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $insertStmt->execute([$name, $studentId, $department, $academicYear, $email, $phone, $mentorId, $photoPath]);
            $newId = $pdo->lastInsertId();

            sendJsonResponse('success', 'Mentee enrolled successfully.', ['id' => $newId], 201);
            break;

        case 'update_mentee':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendJsonResponse('error', 'Invalid request method.', null, 405);

            $id           = $_POST['id'] ?? '';
            $name         = trim($_POST['editMenteeName'] ?? '');
            $studentId    = trim($_POST['editStudentId'] ?? '');
            $department   = trim($_POST['editDepartment'] ?? '');
            $academicYear = trim($_POST['editAcademicYear'] ?? '');
            $email        = trim($_POST['editEmail'] ?? '');
            $phone        = trim($_POST['editPhone'] ?? '');
            $mentorId     = !empty($_POST['editMentorId']) ? (int)$_POST['editMentorId'] : null;

            if (!filter_var($id, FILTER_VALIDATE_INT)) sendJsonResponse('error', 'Invalid mentee ID.', null, 400);
            if (empty($name) || empty($studentId) || empty($department) || empty($academicYear)) {
                sendJsonResponse('error', 'Please fill in all required fields.', null, 400);
            }

            $fetchStmt = $pdo->prepare("SELECT photo_path FROM mentees WHERE id = ?");
            $fetchStmt->execute([$id]);
            $existing = $fetchStmt->fetch();
            if (!$existing) sendJsonResponse('error', 'Mentee not found.', null, 404);

            $checkStmt = $pdo->prepare("SELECT id FROM mentees WHERE student_id = ? AND id != ? LIMIT 1");
            $checkStmt->execute([$studentId, $id]);
            if ($checkStmt->fetch()) sendJsonResponse('error', 'Student ID is already registered.', null, 400);

            $photoPath = $existing['photo_path'];
            if (isset($_FILES['editMenteePhoto']) && $_FILES['editMenteePhoto']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = validateAndSavePhoto($_FILES['editMenteePhoto'], $studentId, $uploadDir);
                if (!$uploadResult['success']) sendJsonResponse('error', $uploadResult['error'], null, 400);

                if (!empty($existing['photo_path'])) {
                    $oldPath = __DIR__ . '/' . $existing['photo_path'];
                    if (file_exists($oldPath) && is_file($oldPath)) @unlink($oldPath);
                }
                $photoPath = $uploadResult['fileName'];
            }

            $updateStmt = $pdo->prepare("
                UPDATE mentees
                SET name = ?, student_id = ?, department = ?, academic_year = ?, email = ?, phone = ?, mentor_id = ?, photo_path = ?
                WHERE id = ?
            ");
            $updateStmt->execute([$name, $studentId, $department, $academicYear, $email, $phone, $mentorId, $photoPath, $id]);

            sendJsonResponse('success', 'Mentee details updated successfully.');
            break;

        case 'delete_mentee':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendJsonResponse('error', 'Invalid request method.', null, 405);
            $id = $_POST['id'] ?? '';
            if (!filter_var($id, FILTER_VALIDATE_INT)) sendJsonResponse('error', 'Invalid mentee ID.', null, 400);

            $fetchStmt = $pdo->prepare("SELECT photo_path FROM mentees WHERE id = ?");
            $fetchStmt->execute([$id]);
            $mentee = $fetchStmt->fetch();
            if (!$mentee) sendJsonResponse('error', 'Mentee not found.', null, 404);

            if (!empty($mentee['photo_path'])) {
                $filePath = __DIR__ . '/' . $mentee['photo_path'];
                if (file_exists($filePath) && is_file($filePath)) @unlink($filePath);
            }

            $deleteStmt = $pdo->prepare("DELETE FROM mentees WHERE id = ?");
            $deleteStmt->execute([$id]);

            sendJsonResponse('success', 'Mentee removed successfully.');
            break;

        default:
            sendJsonResponse('error', 'Invalid action specified.', null, 400);
            break;
    }
} catch (Exception $e) {
    sendJsonResponse('error', 'A server error occurred while processing your request.', null, 500);
}
?>
