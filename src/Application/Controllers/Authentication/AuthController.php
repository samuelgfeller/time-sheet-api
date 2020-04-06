<?php

namespace App\Controller;


use App\Application\Controllers\Controller;
use App\Domain\Exception\ValidationException;
use App\Domain\User\User;
use App\Domain\User\UserService;
use App\Domain\User\UserValidation;
use App\Domain\Utility\ArrayReader;
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

    public function __construct(LoggerInterface $logger, UserService $userService)
    {
        parent::__construct($logger);
        $this->userService = $userService;
    }

    public function register(Request $request, Response $response): Response
    {
        // If a html form name changes, these changes have to be done in the Entities constructor
        // too since these values will be the keys from the ArrayReader
        $userData = $request->getParsedBody();

        // Use Entity instead of DTO for simplicity https://github.com/samuelgfeller/slim-api-example/issues/2#issuecomment-597245455
        $user = new User(new ArrayReader($userData));

        try {
            $insertId = $this->userService->createUser($user);
        } catch (ValidationException $exception) {
            return $this->respondValidationError($exception->getValidationResult(), $response);
        }

        // Log user in
        if (null !== $insertId) {
            $this->logger->info('User "' . $userData['email'] . '" created');

            return $this->respondWithJson(
                $response,
                ['status' => 'success', 'message' => 'User created successfully'],
                201
            );
        }
        return $this->respondWithJson($response, ['status' => 'error', 'message' => 'User could not be registered']);
    }

    public function login(Request $request, Response $response): Response
    {
        $userData = $request->getParsedBody();

        $user = new User(new ArrayReader($userData));

        try {
            if ($userWithId = $this->userService->userAllowedToLogin($user)) {
                $token = $this->userService->generateToken($userWithId);
                return $this->respondWithJson(
                    $response,
                    ['token' => $token, 'status' => 'success', 'message' => 'Logged in'],
                    200
                );
            }
        } catch (ValidationException $exception) {
            return $this->respondValidationError($exception->getValidationResult(), $response);
        }

        $this->logger->notice('Invalid login attempt from "' . $user->getEmail() . '"');
        return $this->respondWithJson($response, ['status' => 'error', 'message' => 'Invalid credentials'], 401);
    }
}
