<?php
// Standalone JSON API Handler for GetSimple Chat Plugin with Admin Moderation Support
while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Start session if not already active to check GetSimple admin authentication cookies/sessions
if (!isset($_SESSION)) {
    @session_start();
}

// Secure Check for GetSimple Admin Panel
$is_gs_admin = false;

// Go up two levels from plugins/gs_chat/ to reach the true GetSimple root directory
$gs_root = realpath(__DIR__ . '/../..'); 

// 1. Check standard GetSimple session
if (!empty($_SESSION['GS_ADMIN_USER'])) {
    $username = preg_replace('/[^a-zA-Z0-9_]/', '', $_SESSION['GS_ADMIN_USER']);
    $user_file = $gs_root . '/data/users/' . $username . '.xml';
    
    if (!empty($username) && file_exists($user_file)) {
        $is_gs_admin = true;
    }
}

// 2. Fallback: Check standard GetSimple cookies
if (!$is_gs_admin && isset($_COOKIE['GS_ADMIN_USERNAME'])) {
    $username = preg_replace('/[^a-zA-Z0-9_]/', '', $_COOKIE['GS_ADMIN_USERNAME']);
    $user_file = $gs_root . '/data/users/' . $username . '.xml';
    
    if (!empty($username) && file_exists($user_file)) {
        foreach ($_COOKIE as $key => $val) {
            if (strpos($key, 'cookie_login') === 0 || strpos($key, 'GS_') === 0) {
                $is_gs_admin = true;
                break;
            }
        }
    }
}

// 3. Fallback: If actively logged into the backend
if (!$is_gs_admin) {
    foreach ($_COOKIE as $key => $val) {
        $candidate_file = $gs_root . '/data/users/' . basename($val) . '.xml';
        if (file_exists($candidate_file)) {
            $is_gs_admin = true;
            break;
        }
    }
}

$logs_dir = __DIR__ . '/data';
$data_file = $logs_dir . '/chat_messages.json';

function gs_chat_get_storage($dir, $file) {
    if (!file_exists($dir)) {
        @mkdir($dir, 0755, true);
    }
    
    if (!file_exists($file)) {
        $initial = array(
            'messages' => array(),
            'announcement' => 'Welcome to our live community chat! Feel free to say hello.',
            'theme_color' => '#2563EB',
            'chat_position' => 'right'
        );
        @file_put_contents($file, json_encode($initial, JSON_PRETTY_PRINT));
    }
    
    $raw = @file_get_contents($file);
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        $data = array(
            'messages' => array(), 
            'announcement' => 'Welcome to our live community chat!', 
            'theme_color' => '#2563EB',
            'chat_position' => 'right'
        );
    }
    return $data;
}

$method = $_SERVER['REQUEST_METHOD'];
$data = gs_chat_get_storage($logs_dir, $data_file);

if ($method === 'GET') {
    echo json_encode(array(
        'status' => 'success',
        'messages' => array_slice($data['messages'], -100),
        'announcement' => $data['announcement'],
        'theme_color' => !empty($data['theme_color']) ? $data['theme_color'] : '#2563EB',
        'chat_position' => !empty($data['chat_position']) ? $data['chat_position'] : 'right',
        'is_admin' => $is_gs_admin
    ));
    exit;
}

if ($method === 'POST') {
    $raw_input = file_get_contents('php://input');
    $input = json_decode($raw_input, true);
    if (!$input) {
        $input = $_POST;
    }

    $action_type = $input['type'] ?? '';

    // Action 1: Send Public Message
    if ($action_type === 'send_message') {
        $username  = strip_tags(trim($input['user'] ?? ''));
        $message   = strip_tags(trim($input['text'] ?? ''));
        $avatar    = strip_tags(trim($input['avatar'] ?? 'fa-user'));
        $client_id = strip_tags(trim($input['client_id'] ?? ''));

        if (!empty($username) && !empty($message) && !empty($client_id)) {
            
            foreach ($data['messages'] as $msg) {
                $existing_user   = $msg['user'] ?? '';
                $existing_client = $msg['client_id'] ?? '';

                if (strcasecmp($existing_user, $username) === 0 && !empty($existing_client) && $existing_client !== $client_id) {
                    echo json_encode(array(
                        'status'  => 'error', 
                        'message' => 'This nickname is already taken by someone else. Please choose a different nickname.'
                    ));
                    exit;
                }

                if ($existing_client === $client_id && !empty($existing_user) && strcasecmp($existing_user, $username) !== 0) {
                    echo json_encode(array(
                        'status'  => 'error', 
                        'message' => 'You cannot change your nickname once you have started chatting. Please keep your original nickname: ' . $existing_user
                    ));
                    exit;
                }
            }

            $newMessage = array(
                'id'        => uniqid('msg_'),
                'user'      => $username,
                'text'      => $message,
                'avatar'    => $avatar,
                'client_id' => $client_id,
                'timestamp' => date('H:i')
            );

            $data['messages'][] = $newMessage;
            
            if (count($data['messages']) > 200) {
                $data['messages'] = array_slice($data['messages'], -200);
            }

            @file_put_contents($data_file, json_encode($data, JSON_PRETTY_PRINT));
            echo json_encode(array('status' => 'success', 'message' => $newMessage));
            exit;
        } else {
            echo json_encode(array('status' => 'error', 'message' => 'Invalid input or missing client session ID.'));
            exit;
        }
    }

    // Action 2: Admin Delete Message
    if ($action_type === 'delete_message') {
        if (!$is_gs_admin) {
            echo json_encode(array('status' => 'error', 'message' => 'Unauthorized admin action'));
            exit;
        }

        $msg_id = $input['id'] ?? '';
        if (!empty($msg_id)) {
            $filtered = array();
            foreach ($data['messages'] as $m) {
                if ($m['id'] !== $msg_id) {
                    $filtered[] = $m;
                }
            }
            $data['messages'] = $filtered;
            @file_put_contents($data_file, json_encode($data, JSON_PRETTY_PRINT));
            echo json_encode(array('status' => 'success', 'deleted_id' => $msg_id));
            exit;
        }
    }
}

echo json_encode(array('status' => 'error', 'message' => 'Invalid request'));
exit;