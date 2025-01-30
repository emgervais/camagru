<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'auth_controller.php';
include 'db.php';

$database = new Database();
$db = $database->getConnection();
$auth = new AuthController($db);

$data = json_decode(file_get_contents("php://input"));

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);
if ($_SERVER['REQUEST_METHOD'] === 'GET' && count($uri) <= 2) {
    $indexPath = dirname(__DIR__) . '/public/templates/index.html';
    if (file_exists($indexPath)) {
        header('Content-Type: text/html');
        readfile($indexPath);
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
            $response = $auth->login($data);
            $response['status'] === 'success' ? http_response_code(200) : http_response_code(401);
            echo json_encode($response);
            break;
        case 'isLogged':
            try {
                if (!isset($_SESSION)) {
                    throw new Exception('Session not started');
                }
                http_response_code(200);
                echo json_encode([
                    "logged" => $auth->user->checkSession(),
                    "status" => true
                ]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode([
                    "logged" => false,
                    "status" => false,
                ]);
            }
            break;
        case 'logout':
            try {
                if (!isset($_SESSION)) {
                    throw new Exception('Session not started');
                }
                session_destroy();
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
            $token = isset($_GET['token']) ? $_GET['token'] : null;
            $auth->verifyToken($token) ? http_response_code(201) : http_response_code(401);//maybe not correct
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
            
        default:
            http_response_code(404);
            echo json_encode(["message" => "Endpoint not found"]);
            break;
    }
}