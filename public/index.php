<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'controller.php';
include 'db.php';
session_start();
$database = new Database();
$db = $database->getConnection();
$auth = new Controller($db);


try {
    $uri = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
    $uri = parse_url($uri, PHP_URL_PATH);
    $uri = array_filter(explode('/', $uri));
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && count($uri) <= 1) {
        $indexPath = realpath(dirname(__DIR__) . '/public/templates/index.html');
        $expectedPath = realpath(dirname(__DIR__) . '/public/templates');
        
        if ($indexPath !== false && 
        strpos($indexPath, $expectedPath) === 0 && 
        pathinfo($indexPath, PATHINFO_EXTENSION) === 'html' && 
        file_exists($indexPath)) {
            
            header('Content-Type: text/html; charset=UTF-8');
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            
            readfile($indexPath);
            exit;
        }
    }
} catch (Exception $e) {
    http_response_code(404);
    echo 'Page not found';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    try {
        $rawData = file_get_contents("php://input");
        if ($rawData === false) {
            throw new Exception("Failed to read input stream");
        }
        $data = json_decode($rawData, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON: " . json_last_error_msg());
        }
    
        if ($data === null) {
            $data = [];
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

if ($uri[1] === 'api') {
    switch($uri[2]) {
        case 'register':
            $response = $auth->register($data);
            $response['status'] === 'success' ? http_response_code(200) : http_response_code(401);
            echo json_encode($response);
            break;

        case 'login':
            $_SESSION = [];
            $response = $auth->login($data);
            $response['status'] === 'success' ? http_response_code(200) : http_response_code(401);
            echo json_encode($response);
            break;

        case 'isLogged':
            error_log($_SESSION['user_id']);
            if (!isset($_SESSION) || !isset($_SESSION['user_id'])) {
                http_response_code(200);
                echo json_encode(["logged" => false]);
            } else {
                http_response_code(200);
                echo json_encode(["logged" => true]);
            }
            break;

        case 'logout':
            try {
                if (!isset($_SESSION)) {
                    throw new Exception('Session not started');
                }
                session_destroy();
                $_SESSION = [];
                echo json_encode([
                    "message" => "Logged out",
                    "logged" => false
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode([
                    "message" => "Failed to log out",
                    "status" => true
                ]);
            }
            break;

        case 'verify':
            if ($_SERVER['REQUEST_METHOD'] !== 'GET')
                break;
            $token = isset($_GET['token']) ? $_GET['token'] : null;
            $auth->verifyToken($token) ? http_response_code(201) : http_response_code(401);
            $indexPath = dirname(__DIR__) . '/public/templates/index.html';
            if (file_exists($indexPath)) {
                header('Content-Type: text/html');
                readfile($indexPath);
            }
            break;

        case 'changePassword':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                header('Content-Type: text/html');
                readfile(dirname(__DIR__) . '/public/templates/reset.html');
                exit;
            }

            $response = $auth->changePassword($data);
            if ($response) {
                header('Location: /', true, 200);
                exit;
            } 
            else {
                http_response_code(301);
            }
            break;

        case 'forgotPassword':
            $response = $auth->forgotPassword($data);
            if ($response)
                http_response_code(200);
            else
                http_response_code(401);
            break;

        case 'changeInfo':
            $response = $auth->changeInfo($data);
            if ($response['status'] === "success")
                http_response_code(200);
            else {
                http_response_code(401);
                echo json_encode($response);
            }
            break;

        case 'posts':
            if($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['page'])) {
                http_response_code(401);
                echo json_encode(['status' => 'error']);
            }
            $response = $auth->getPosts(intval($_GET['page']));
            if ($response['status'] === "success") {
                http_response_code(200);
                echo json_encode($response['data']);
            }
            else {
                http_response_code(401);
                echo json_encode($response['message']);
            }
            break;

        case 'like':
            $response = $auth->like($data);
            if ($response['status'] === "success") {
                http_response_code(200);
                echo json_encode($response['message']);
            }
            else {
                http_response_code(401);
                echo json_encode($response['message']);
            }
            break;

        case 'getComment':
            $response = $auth->comment($data);
            if ($response['status'] === "success") {
                http_response_code(200);
                echo json_encode($response['comments']);
            }
            else {
                http_response_code(401);
                echo json_encode($response['message']);
            }
            break;

        case 'sendComment':
            $response = $auth->sendComment($data);
            if ($response['status'] === "success") {
                http_response_code(200);
                echo json_encode($response);
            }
            else {
                http_response_code(401);
                echo json_encode($response['message']);
            }
            break;

        case 'gallery':
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                if (!isset($_SESSION) || !isset($_SESSION['user_id'])) {
                    http_response_code(401);
                    echo json_encode(["message" => "Please login to view gallery"]);
                    break;
                }
                $filePath = dirname(__DIR__) . '/public/templates/gallery.html';
                if (file_exists($filePath)) {
                    header('Content-Type: text/html');
                    readfile($filePath);
                }
            }
            break;

        case 'publish':
            if (!isset($_SESSION) || !isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
                http_response_code(401);
                echo json_encode(["message" => "Please login to publish posts"]);
                break;
            }
            if(!isset($data) || !isset($data['dest']) || !isset($data['addons'])) {
                http_response_code(401);
                echo json_encode(["message" => "Please provide the right data"]);
                break;
            }
            $response = $auth->publish($data);
            if ($response['status'] === "success") {
                http_response_code(200);
                echo json_encode($response);
            }
            else {
                http_response_code(401);
                echo json_encode($response['message']);
            }
            break;

        case 'delete':
            if (!isset($_SESSION) || !isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(["message" => "Please login to delete posts"]);
                break;
            }
            if(!isset($data) || !isset($data['id'])) {
                http_response_code(401);
                echo json_encode(["message" => "Please provide the right data"]);
                break;
            }
            $response = $auth->delete($data['id']);
            if ($response['status'] === "success") {
                http_response_code(200);
                echo json_encode($response);
            }
            else {
                http_response_code(401);
                echo json_encode($response['message']);
            }
            break;

        case 'notification':
            if (!isset($_SESSION) || !isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(["message" => "Please login to view notifications"]);
                break;
            }
            $response = $auth->setNotifications();
            if ($response['status'] === "success") {
                http_response_code(200);
                echo json_encode($response['message']);
            }
            else {
                http_response_code(401);
                echo json_encode($response['message']);
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(["message" => "Endpoint not found"]);
            break;
    }
}