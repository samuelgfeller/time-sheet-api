<?php

namespace App\Domain\User;

use App\Domain\Exception\ValidationException;
use App\Domain\Validation\AppValidation;
use App\Domain\Validation\ValidationResult;
use App\Infrastructure\Persistence\User\UserRepository;
use Psr\Log\LoggerInterface;

/**
 * Class UserValidation
 */
class UserValidation extends AppValidation
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * UserValidation constructor.
     *
     * @param LoggerInterface $logger
     * @param UserRepository $userRepository
     */
    public function __construct(LoggerInterface $logger, UserRepository $userRepository)
    {
        parent::__construct($logger);
        $this->userRepository = $userRepository;
    }

    /**
     * Validate registration.
     *
     * @param User $user
     * @return ValidationResult
     */
    public function validateUserRegistration(User $user): ValidationResult
    {
        // Instantiate ValidationResult Object with default message
        $validationResult = new ValidationResult('There was a validation error when trying to register a user');

        $this->validateName($user->getName(), true, $validationResult);
        $this->validateEmail($user->getEmail(), true, $validationResult);

        // First check if validation already failed. If not email is checked in db because we know its a valid email
        if (!$validationResult->fails() && $this->userRepository->findUserByEmail($user->getEmail())) {
            $this->logger->info('Account creation tried with existing email: "' . $user->getEmail() . '"');

            $validationResult->setError('email', 'Error in registration');
        }

        $this->validatePasswords([$user->getPassword(), $user->getPassword2()], true, $validationResult);

        // If the validation failed, throw the exception which will be caught in the Controller
        $this->throwOnError($validationResult);
        $this->logger->info('Validation succeeded: User Registration');

        return $validationResult;
    }

    /**
     * Validate if user inputs for the login are valid
     *
     * @param User $user
     * @return ValidationResult
     * @throws ValidationException
     */
    public function validateUserLogin(User $user): ValidationResult
    {
        $validationResult = new ValidationResult('There was a validation error when trying to login');

        $this->validateEmail($user->getEmail(), true, $validationResult);
        $this->validatePassword($user->getPassword(), true, $validationResult);

        // If the validation failed, throw the exception which will be caught in the Controller
        $this->throwOnError($validationResult);
        $this->logger->info('Validation succeeded: User Login');

        return $validationResult;
    }

    /**
     * Validate password and password2
     *
     * @param array $passwords [$password, $password2]
     * @param bool $required
     * @param ValidationResult $validationResult
     */
    private function validatePasswords(array $passwords, bool $required, ValidationResult $validationResult): void
    {
        if (null !== $passwords[0] && null !== $passwords[1]) {
            if ($passwords[0] !== $passwords[1]) {
                $validationResult->setError('passwords', 'Passwords do not match');
            }

            // If come to this line, password is required
            $this->validatePassword($passwords[0], true, $validationResult);
            $this->validatePassword($passwords[1], true, $validationResult);
        } elseif (true === $required) {
            // If it is null but required, the user input is faulty so bad request 400 return status is sent
            $validationResult->setIsBadRequest(true, 'passwords', 'Passwords are required and not given');
        }
    }

    /**
     * Validate single password
     *
     * @param string $password
     * @param bool $required
     * @param ValidationResult $validationResult
     */
    public function validatePassword(string $password, bool $required, ValidationResult $validationResult): void
    {
        if (null !== $password) {
            $this->validateLengthMin($password, 'password', $validationResult, 3);
        } elseif (true === $required) {
            // If it is null but required, the user input is faulty so bad request 400 return status is sent
            $validationResult->setIsBadRequest(true, 'passwords', 'Password is required and not given');
        }
    }

    /**
     * Validate email
     *
     * @param string $email
     * @param bool $required
     * @param ValidationResult $validationResult
     */
    private function validateEmail(string $email, bool $required, ValidationResult $validationResult): void
    {
        // reversed, if true -> error
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $validationResult->setError('email', 'Email address could not be validated');
        } // Because reversed the null check is done here
        elseif (true === $required && null === $email) {
            // If email is mandatory. If it is null, the user input is faulty so bad request 400 return status is sent
            $validationResult->setIsBadRequest(true, 'email', 'email required but not given');
        }
    }

    /**
     * Validate Name.
     *
     * @param string $name
     * @param bool $required on update the name doesn't have to be set but on creation it has
     * @param ValidationResult $validationResult
     */
    private function validateName(string $name, bool $required, ValidationResult $validationResult): void
    {
        if (null !== $name) {
            $this->validateLengthMax($name, 'name', $validationResult, 200);
            $this->validateLengthMin($name, 'name', $validationResult, 2);
        } elseif (true === $required) {
            // Name is mandatory. If it is null, the user input is faulty so bad request 400 return status is sent
            $validationResult->setIsBadRequest(true, 'name', 'name required but not given');
        }
    }
}
