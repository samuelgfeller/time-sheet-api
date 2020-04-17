<?php

namespace App\Domain\Validation;

use App\Domain\Exception\ValidationException;
use Psr\Log\LoggerInterface;

/**
 * Class AppValidation
 */
abstract class AppValidation
{
    protected LoggerInterface $logger;

    /**
     * AppValidation constructor.
     * @param LoggerInterface $logger
     */
    protected function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Throw a validation exception if the validation result fails.
     *
     * @param ValidationResult $validationResult
     * @throws ValidationException
     */
    protected function throwOnError(ValidationResult $validationResult): void
    {
        if ($validationResult->fails()) {
            $this->logger->notice(
                'Validation failed: ' . $validationResult->getMessage() . "\n" . json_encode(
                    $validationResult->getErrors()
                )
            );
            throw new ValidationException($validationResult);
        }
    }

    /**
     * Check if a values string is less than a defined value.
     *
     * @param $value
     * @param $fieldname
     * @param ValidationResult $validationResult
     * @param int $length
     */
    protected function validateLengthMin($value, $fieldname, ValidationResult $validationResult, $length = 3): void
    {
        if (strlen(trim($value)) < $length) {
            $validationResult->setError($fieldname, sprintf('Required minimum length is %s', $length));
        }
    }

    /**
     * Check if a values string length is more than a defined value.
     *
     * @param $value
     * @param $fieldname
     * @param ValidationResult $validationResult
     * @param int $length
     */
    protected function validateLengthMax($value, $fieldname, ValidationResult $validationResult, $length = 255): void
    {
        if (mb_strlen(trim($value)) > $length) {
            $validationResult->setError($fieldname, sprintf('Required maximum length is %s', $length));
        }
    }
}
