<?php


namespace App\Domain\TimeSheet;

use App\Domain\Exception\TimerAlreadyStartedException;
use App\Domain\Timer\Timer;
use App\Domain\User\UserService;
use App\Infrastructure\Persistence\TimeSheet\TimeSheetRepository;


class TimeSheetService
{

    private TimeSheetRepository $timeSheetRepository;
    private UserService $userService;

    public function __construct(
        TimeSheetRepository $timeSheetRepository,
        UserService $userService
    ) {
        $this->timeSheetRepository = $timeSheetRepository;
        $this->userService = $userService;
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
     * Set the start time and write it in database
     *
     * @param int $userId
     * @return array with the start_time and insert id
     * @throws TimerAlreadyStartedException
     */
    public function startTime(int $userId): array
    {
        // Prevent timer to be started multiple times
        if ($this->timeSheetRepository->findRunningTime($userId) !== []) {
            throw new TimerAlreadyStartedException('The timer is already running and can\'t be started again');
        }
        $startTime = date('Y-m-d H:i:s');
        $timer = [
            'user_id' => $userId,
            'start' => $startTime,
        ];

        return [
            'start_time' => $startTime,
            'insert_id' => $this->timeSheetRepository->insertTime($timer)
        ];
    }
}
