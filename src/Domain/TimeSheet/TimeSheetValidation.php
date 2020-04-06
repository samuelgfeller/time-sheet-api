<?php

namespace App\Domain\TimeSheet;

use App\Domain\TimeSheet\TimeSheet;
use App\Domain\Validation\AppValidation;
use App\Domain\Validation\ValidationResult;
use Psr\Log\LoggerInterface;

/**
 * Class UserValidation
 */
class TimeSheetValidation extends AppValidation
{

    /**
     * UserValidation constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }

    /**
     * Validate timeSheet creation or update since they are the same
     *
     * @param TimeSheet $timeSheet
     */
    public function validatePostCreationOrUpdate(TimeSheet $timeSheet): void
    {
        $validationResult = new ValidationResult('There is something in the timeSheet data which couldn\'t be validated');
        // In case message gets validated in other function
        $required = true;

        // Validate message
        if (null !== $timeSheet->getMessage()) {

            $this->validateLengthMax($timeSheet->getMessage(), 'message', $validationResult, 500);
            $this->validateLengthMin($timeSheet->getMessage(), 'message', $validationResult, 4);
        } elseif (true === $required) {
            // If it is null but required, the user input is faulty so bad request 400 return status is sent
            $validationResult->setIsBadRequest(true, 'message', 'Message is required but not given');
        }

        // todo does it make sense to check if user exists?

        $this->throwOnError($validationResult);
    }

}
