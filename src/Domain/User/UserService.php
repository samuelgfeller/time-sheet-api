<?php


namespace App\Domain\User;

use App\Infrastructure\Persistence\Exceptions\PersistenceRecordNotFoundException;
use App\Infrastructure\Persistence\User\UserRepository;
use Firebase\JWT\JWT;
use Psr\Log\LoggerInterface;

class UserService
{

    private UserRepository $userRepository;
    protected UserValidation $userValidation;
    protected LoggerInterface $logger;


    public function __construct(UserRepository $userRepository, UserValidation $userValidation, LoggerInterface $logger)
    {
        $this->userRepository = $userRepository;
        $this->userValidation = $userValidation;
        $this->logger = $logger;
    }

    public function findUser(int $id): array
    {
        return $this->userRepository->findUserById($id);
    }

    /**
     * @param string $email
     * @return array|null
     */
    public function findUserByEmail(string $email): ?array
    {
        return $this->userRepository->findUserByEmail($email);
    }

    /**
     * Insert user in database
     *
     * @param $user
     * @return string
     */
    public function createUser(User $user): string
    {
        $this->userValidation->validateUserRegistration($user);
        $user->setPassword(password_hash($user->getPassword(), PASSWORD_DEFAULT));
        return $this->userRepository->insertUser($user->toArray());
    }

    /**
     * Checks if user is allowed to login.
     * If yes, the user object is returned with id
     * If no, null is returned
     *
     * @param User $user
     * @return mixed|null
     */
    public function userAllowedToLogin(User $user)
    {
        $this->userValidation->validateUserLogin($user);

        $dbUser = $this->findUserByEmail($user->getEmail());
        //$this->logger->info('users/' . $user . ' has been called');
        if ($dbUser !== null && $dbUser !== [] && password_verify($user->getPassword(), $dbUser['password'])) {
            $user->setId($dbUser['id']);
            return $user;
        }
        return null;
    }

    /**
     * Generates a JWT Token with user id
     *
     * @param User $user
     * @return string
     * @throws \Exception
     */
    public function generateToken(User $user)
    {
        $durationInSec = 5000; // In seconds
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
                'userId' => $user->getId(), // userid from the users table
            ]
        ];

        return JWT::encode($data, 'ipa-project', 'HS256');
    }

    /**
     * Get user role
     *
     * @param int $id
     * @return string
     * @throws PersistenceRecordNotFoundException
     */
    public function getUserRole(int $id): string
    {
        return $this->userRepository->getUserRole($id);
    }

}
