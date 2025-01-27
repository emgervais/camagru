case 'comments':
        if(!Auth::verifyToken()) {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized"]);
            break;
        }
        
        $comment = new CommentController($db);
        switch($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                $response = $comment->create($data, Auth::getUserFromToken());
                break;
            case 'GET':
                if(isset($uri[2])) {
                    $response = $comment->getByImageId($uri[2]);
                }
                break;
        }
        echo json_encode($response);
        break;

    case 'likes':
        if(!Auth::verifyToken()) {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized"]);
            break;
        }
        
        $like = new LikeController($db);
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = $like->toggle($data->image_id, Auth::getUserFromToken());
            echo json_encode($response);
        }
        break;