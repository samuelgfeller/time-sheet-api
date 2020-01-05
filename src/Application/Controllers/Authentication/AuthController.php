<?php

namespace App\Controller;


use App\Application\Controllers\Controller;
use App\Domain\User\UserService;
use App\Domain\User\UserValidation;
use DateTime;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Firebase\JWT\JWT;

/**
 * Class AuthController
 */
class AuthController extends Controller
{
    protected $userService;
    protected $userValidation;

    public function __construct(LoggerInterface $logger, UserService $userService, UserValidation $userValidation)
    {
        parent::__construct($logger);
        $this->userService = $userService;
        $this->userValidation = $userValidation;
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $user = $this->userService->findUser($id);
//        var_dump($this->container->get('logger'));
//        $response->getBody()->write('GET request');

        $this->logger->info('users/' . $id . ' has been called');
//        var_dump($this->logger);
        return $this->respondWithJson($response, $user);
    }

    // Björn
    public function indexAction(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->encoder->encode($request, $response, 'Auth/index.html.twig');
    }

    public function register(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        if (null !== $data) {
            $userData = [
                'name' => htmlspecialchars($data['name']),
                'email' => htmlspecialchars($data['email']),
                'password1' => $data['password1'],
                'password2' => $data['password2'],
            ];

            $validationResult = $this->userValidation->validateUserRegistration($userData);
            if ($validationResult->fails()) {
                $responseData = [
                    'status' => 'error',
                    'validation' => $validationResult->toArray(),
                ];

                return $this->respondWithJson($response, $responseData, 422);
            }

            $userData['password'] = password_hash($userData['password1'], PASSWORD_DEFAULT);
            // used to give login function
            $plainPass = $userData['password1'];
            unset($userData['password1'], $userData['password2']);

            $insertId = $this->userService->createUser($userData);

            if (null !== $insertId) {
                $this->logger->info('User "' . $userData['email'] . '" created');

                // Add email and password like it is expected in the login function
                $request = $request->withParsedBody(['email' => $userData['email'], 'password' => $plainPass]);
                // Call login function to authenticate the user
                // todo check if that is good practice or bad
                $loginResponse = $this->login($request, $response);

                $loginContent = json_decode($loginResponse->getBody(), true);

                // Clear response body after body content is saved
                $response = new \Slim\Psr7\Response();

                $responseContent = $loginContent;

                // maybe there is already a message so it has to be transformed as array
                $responseContent['message'] = [$loginContent['message']];
                $responseContent['message'][] = 'User created and logged in';

                return $this->respondWithJson($response, $responseContent);
            }
            return $this->respondWithJson($response,
                ['status' => 'error', 'message' => 'User could not be registered']);
        }
        return $this->respondWithJson($response, ['status' => 'error', 'message' => 'Request body empty']);
    }

    public function login(Request $request, Response $response): Response
    {
        // todo add check if already logged in

        $data = $request->getParsedBody();

        $userData = [
            'email' => htmlspecialchars($data['email']),
            'password' => $data['password']
        ];

        $user = $this->userService->findUserByEmail($userData['email']);
        //$this->logger->info('users/' . $user . ' has been called');
        if (password_verify($userData['password'], $user['password'])) {
            $durationInSec = 500;
            $tokenId = base64_encode(random_bytes(32));
            $issuedAt = time();
            $notBefore = $issuedAt + 2;             //Adding 2 seconds
            $expire = $notBefore + $durationInSec;            // Adding 300 seconds

            $data = [
                'iat' => $issuedAt,         // Issued at: time when the token was generated
                'jti' => $tokenId,          // Json Token Id: an unique identifier for the token
                'iss' => 'MyApp',       // Issuer
                'nbf' => $notBefore,        // Not before
                'exp' => $expire,           // Expire
                'data' => [                  // Data related to the signer user
                    'userId' => $user['id'], // userid from the users table
                    'email' => $userData['email'],
                ]
            ];

            $token = JWT::encode($data, 'test', 'HS256'); // todo change test to settings

            $this->logger->info('User "' . $userData['email'] . '" logged in. Token leased for ' . $durationInSec . 'sec');

            return $this->respondWithJson($response,
                ['token' => $token, 'status' => 'success', 'message' => 'Logged in'], 200);
        }
        $this->logger->info('Invalid login attempt from "' . $userData['email'] . '"');
        return $this->respondWithJson($response, ['status' => 'error', 'message' => 'Invalid credentials'], 500);


    }


}
