switch($uri[1]) {
    case 'register':
        $response = $auth->register($data);
        echo json_encode($response);
        break;
    
    case 'login':
        $response = $auth->login($data);
        echo json_encode($response);
        break;
        
    case 'images':
        if(!Auth::verifyToken()) {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized"]);
            break;
        }
        
        $image = new ImageController($db);
        switch($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                $response = $image->upload($_FILES['image'], Auth::getUserFromToken());
                break;
            case 'GET':
                $page = isset($_GET['page']) ? $_GET['page'] : 1;
                $response = $image->getGallery($page);
                break;
            case 'DELETE':
                if(isset($uri[2])) {
                    $response = $image->delete($uri[2], Auth::getUserFromToken());
                }
                break;
        }
        echo json_encode($response);
        break;
        
    default:
        http_response_code(404);
        echo json_encode(["message" => "Endpoint not found"]);
        break;
}