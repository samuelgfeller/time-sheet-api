<?php

namespace App\Controllers\Posts;

use App\Application\Controllers\Controller;
use App\Domain\Post\PostRepositoryInterface;
use App\Domain\Post\PostService;
use App\Domain\Post\PostValidation;
use App\Domain\User\UserService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Handlers\Strategies\RequestHandler;
use Firebase\JWT\JWT;

class PostController extends Controller
{

    protected $postService;
    protected $postValidation;
    protected $userService;

    public function __construct(
        LoggerInterface $logger,
        PostService $postService,
        PostValidation $postValidation,
        UserService $userService
    ) {
        parent::__construct($logger);
        $this->postService = $postService;
        $this->postValidation = $postValidation;
        $this->userService = $userService;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $post = $this->postService->findPost($id);

        // Get user information connected to post
        $user = $this->userService->findUser($post['user_id']);

        // Add user name info to post
        $postWithUser = $post;
        $postWithUser['user_name'] = $user['name'];
        return $this->respondWithJson($response, $postWithUser);
    }

    public function list(Request $request, Response $response, array $args)
    {
        $postsWithUsers = $this->postService->findAllPosts();

        return $this->respondWithJson($response, $postsWithUsers);

    }

    public function getOwnPosts(Request $request, Response $response, array $args): Response
    {
        // option 1 /posts?user=xxx and then $request->getQueryParams('user'); but that would mean that the user has to know its id
        // option 2 /own-posts and get user id from token data body

        $loggedUserId = (int)$this->getUserIdFromToken($request);

        $posts = $this->postService->findAllPostsFromUser($loggedUserId);

        return $this->respondWithJson($response, $posts);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $userId = (int)$this->getUserIdFromToken($request);

        $id = (int)$args['id'];

        $post = $this->postService->findPost($id);
        // I write the role logic always for each function and not a general service "isAuthorised" function because it's too different every time
        $userRole = $this->userService->getUserRole($userId);

        // Check if it's admin or if it's its own post
        if ($userRole === 'admin' || (int) $post['user_id'] === $userId) {

//      var_dump($request->getParsedBody());

            $data = $request->getParsedBody();
            // todo check if userbody is empty everywhere

            $postData = [
                'message' => $data['message'],
            ];

            $validationResult = $this->postValidation->validatePostCreationOrUpdate($postData);
            if ($validationResult->fails()) {
                $responseData = [
                    'status' => 'error',
                    'validation' => $validationResult->toArray(),
                ];

                return $this->respondWithJson($response, $responseData, 422);
            }
//        var_dump($data);
            $updated = $this->postService->updatePost($id, $postData);
            if ($updated) {
                return $this->respondWithJson($response, ['status' => 'success']);
            }
            $response = $this->respondWithJson($response, ['status' => 'warning', 'message' => 'The post was not updated']);
            return $response->withAddedHeader('Warning', 'The post was not updated');
        }
        return $this->respondWithJson($response, ['status' => 'error', 'message' => 'You have to be admin or post creator to update this post'], 401);
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        $userId = (int)$this->getUserIdFromToken($request);

        $id = (int)$args['id'];

        $post = $this->postService->findPost($id);

        $userRole = $this->userService->getUserRole($userId);

        // Check if it's admin or if it's its own post
        if ($userRole === 'admin' || (int) $post['user_id'] === $userId) {

            $deleted = $this->postService->deletePost($id);
            if ($deleted) {
                return $this->respondWithJson($response, ['status' => 'success']);
            }
            $response = $this->respondWithJson($response, ['status' => 'warning', 'message' => 'Post not deleted']);
            return $response->withAddedHeader('Warning', 'The post got not deleted');
        }
        return $this->respondWithJson($response, ['status' => 'error', 'message' => 'You have to be admin or post creator to update this post'], 401);
    }

    public function create(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $userId = (int)$this->getUserIdFromToken($request);

        $data = $request->getParsedBody();
        if (null !== $data) {
            $postData = [
                'message' => $data['message'],
                'user_id' => $userId,
            ];

            $validationResult = $this->postValidation->validatePostCreationOrUpdate($postData);
            if ($validationResult->fails()) {
                $responseData = [
                    'status' => 'error',
                    'validation' => $validationResult->toArray(),
                ];

                return $this->respondWithJson($response, $responseData, 422);
            }
            $insertId = $this->postService->createPost($postData);

            if (null !== $insertId) {
                return $this->respondWithJson($response, ['status' => 'success']);
            }
            $response = $this->respondWithJson($response, ['status' => 'warning', 'message' => 'Post not created']);
            return $response->withAddedHeader('Warning', 'The post could not be created');
        }
        return $this->respondWithJson($response, ['status' => 'error', 'message' => 'Request body empty']);
    }
}
