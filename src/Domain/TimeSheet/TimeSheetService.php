<?php


namespace App\Domain\TimeSheet;

use App\Domain\User\UserService;
use App\Infrastructure\Persistence\TimeSheet\TimeSheetRepository;


class TimeSheetService
{

    private TimeSheetRepository $timeSheetRepository;
    private $userService;
    protected $timeSheetValidation;

    public function __construct(
        TimeSheetRepository $timeSheetRepository,
        UserService $userService,
        TimeSheetValidation $timeSheetValidation
    ) {
        $this->timeSheetRepository = $timeSheetRepository;
        $this->userService = $userService;
        $this->timeSheetValidation = $timeSheetValidation;
    }

    public function findAllTimeSheets()
    {
        $allTimeSheets = $this->timeSheetRepository->findAllTimeSheets();
        return $this->populateTimeSheetsArrayWithUser($allTimeSheets);
    }

    public function findTimeSheet($id): array
    {
        return $this->timeSheetRepository->findTimeSheetById($id);
    }

    /**
     * Return all timeSheets which are linked to the given user
     *
     * @param $userId
     * @return array
     */
    public function findAllTimeSheetsFromUser($userId): array
    {
        $timeSheets = $this->timeSheetRepository->findAllTimeSheetsByUserId($userId);
        return $this->populateTimeSheetsArrayWithUser($timeSheets);
    }

    /**
     * Add user infos to timeSheet array
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
            // If user was deleted but timeSheet not
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
        $this->timeSheetValidation->validateTimeSheetCreationOrUpdate($timeSheet);
        return $this->timeSheetRepository->insertTimeSheet($timeSheet->toArray());
    }


    public function updateTimeSheet(TimeSheet $timeSheet): bool
    {
         $this->timeSheetValidation->validateTimeSheetCreationOrUpdate($timeSheet);
        return $this->timeSheetRepository->updateTimeSheet($timeSheet->toArray(), $timeSheet->getId());
    }

    public function deleteTimeSheet($id): bool
    {
        return $this->timeSheetRepository->deleteTimeSheet($id);
    }


}
