<?php


namespace App\Domain\TimeSheet;

use App\Domain\User\UserService;
use App\Infrastructure\Persistence\TimeSheet\TimeSheetRepository;


class TimeSheetService
{

    private TimeSheetRepository $timeSheetRepository;
    private UserService $userService;
    protected TimeSheetValidation $timeSheetValidation;

    public function __construct(
        TimeSheetRepository $timeSheetRepository,
        UserService $userService,
        TimeSheetValidation $timeSheetValidation
    ) {
        $this->timeSheetRepository = $timeSheetRepository;
        $this->userService = $userService;
        $this->timeSheetValidation = $timeSheetValidation;
    }

    /**
     * Return all recorded times
     *
     * @return array
     */
    public function findAllTimeSheets(): array
    {
        $allTimeSheets = $this->timeSheetRepository->findAllTimeSheets();
        return $this->populateTimeSheetsArrayWithUser($allTimeSheets);
    }

    /**
     * Add user infos to time sheet array
     *
     * @param $timeSheets
     * @return array
     */
    private function populateTimeSheetsArrayWithUser($timeSheets): array
    {
        // Add user name info to timeSheet
        $timeSheetsWithUser = [];
        foreach ($timeSheets as $timeSheet) {
            // Get user information connected to timeSheet
            $user = $this->userService->findUser($timeSheet['user_id']);
            // If user was deleted but time not, time should not be shown and also technically deleted
            if (isset($user['name'])) {
                $timeSheet['user_name'] = $user['name'];
                $timeSheetsWithUser[] = $timeSheet;
            }
        }
        return $timeSheetsWithUser;
    }

    /**
     * Insert timeSheet in database
     *
     * @param TimeSheet $timeSheet
     * @return string
     */
    public function createTimeSheet(TimeSheet $timeSheet): string
    {
        $this->timeSheetValidation->validateTimeSheetCreation($timeSheet);
        return $this->timeSheetRepository->insertTimeSheet($timeSheet->toArray());
    }
}
