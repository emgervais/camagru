<?php
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

switch($uri[2]) {
    case 'register':
        $response = $auth->register($data);
        echo json_encode($response);
        break;
    
    case 'login':
        $response = $auth->login($data);
        echo json_encode($response);
        break;
    default:
        http_response_code(404);
        echo json_encode(["message" => "Endpoint not found"]);
        break;
}