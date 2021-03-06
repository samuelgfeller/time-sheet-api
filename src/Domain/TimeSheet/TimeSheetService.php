<?php


namespace App\Domain\TimeSheet;

use App\Domain\Exception\TimerAlreadyStartedException;
use App\Domain\Exception\TimerNotStartedException;
use App\Domain\Timer\Timer;
use App\Domain\TimeSheet\TimeSheetValidation;
use App\Domain\User\UserService;
use App\Infrastructure\Persistence\TimeSheet\TimeSheetRepository;


class TimeSheetService
{

    private TimeSheetRepository $timeSheetRepository;
    private UserService $userService;

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
    public function findTimeSheet(): array
    {
        $timeSheet = $this->timeSheetRepository->findAllTimes();
        return $this->populateTimeSheetWithUser($timeSheet);
    }

    /**
     * Returns the start time of the running timer
     * or null if no timer is running
     *
     * @param $userId
     * @return array|null
     */
    public function findRunningTimer($userId): ?array
    {
        $runningTime = $this->timeSheetRepository->findRunningTime($userId);
        if ($runningTime !== []) {
            return $runningTime;
        }
        // If no timer is running it's not an exception, not an error
        return null;
    }

    /**
     * Add user infos to time sheet array
     *
     * @param $timeSheet array with all tracked times
     * @return array
     */
    private function populateTimeSheetWithUser(array $timeSheet): array
    {
        // Add user name info to timeSheet
        $timeSheetWithUser = [];
        foreach ($timeSheet as $timer) {
            // Get user information connected to timeSheet
            $user = $this->userService->findUser($timer['user_id']);
            // If user was deleted but time not, time should not be shown since it is also technically deleted
            if (isset($user['name'])) {
                $timer['user_name'] = $user['name'];
                $timeSheetWithUser[] = $timer;
            }
        }
        return $timeSheetWithUser;
    }

    /**
     * Set the start time and write it in database
     *
     * @param Timer $timer
     * @return array with the start_time and insert id
     */
    public function startTime(Timer $timer): array
    {
        $this->timeSheetValidation->validateTimer($timer);

        // Prevent timer to be started multiple times
        if ($this->timeSheetRepository->findRunningTime($timer->getUserId()) !== []) {
            throw new TimerAlreadyStartedException('The timer is already running and can\'t be started again');
        }

        $timer->setStart(date('Y-m-d H:i:s'));

        return [
            'start_time' => $timer->getStart(),
            'insert_id' => $this->timeSheetRepository->insertTime($timer->toArray())
        ];
    }

    /**
     * Stop the time in database
     *
     * @param $userId
     * @return bool
     * @throws TimerNotStartedException
     */
    public function stopTime($userId)
    {
        $runningTime = $this->timeSheetRepository->findRunningTime($userId);
        if ($runningTime !== []) {
            return $this->timeSheetRepository->updateTime(['stop' => date('Y-m-d H:i:s')], $runningTime['id']);
        }

        throw new TimerNotStartedException('Unable to stop time because no timer is running');
    }
}
