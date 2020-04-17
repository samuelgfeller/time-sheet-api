<?php

namespace App\Domain\TimeSheet;

use App\Domain\Timer\Timer;
use App\Domain\Validation\AppValidation;
use App\Domain\Validation\ValidationResult;
use Psr\Log\LoggerInterface;

/**
 * Class TimeSheetValidation
 *
 * @package App\Service\Validation
 */
class TimeSheetValidation extends AppValidation
{
    /**
     * TimeSheetValidation constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }


    /**
     * Validating timer
     *
     * @param Timer $timer
     * @return ValidationResult
     */
    public function validateTimer(Timer $timer): ValidationResult
    {
        $validationResult = new ValidationResult('There was a validation error with the Timer');

        $this->validateLengthMax($timer->getActivity(), 'activity', $validationResult, 400);

        // If the validation failed, throw the exception that will be caught in the Controller
        $this->throwOnError($validationResult);
        $this->logger->info('Timer validation succeeded. "' . $timer->getActivity() . '" is valid');
        return $validationResult;
    }
}
