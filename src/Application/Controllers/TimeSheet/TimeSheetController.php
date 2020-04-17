<?php

namespace App\Controllers\TimeSheet;

use App\Application\Controllers\Controller;
use App\Domain\Exception\TimerAlreadyStartedException;
use App\Domain\Exception\TimerNotStartedException;
use App\Domain\Exception\ValidationException;
use App\Domain\Timer\Timer;
use App\Domain\TimeSheet\TimeSheetService;
use App\Domain\User\UserService;
use App\Domain\Utility\ArrayReader;
use App\Domain\Validation\OutputEscapeService;
use App\Infrastructure\Persistence\Exceptions\PersistenceRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

class TimeSheetController extends Controller
{

    protected TimeSheetService $timeSheetService;
    protected UserService $userService;
    protected OutputEscapeService $outputEscapeService;

    public function __construct(
        LoggerInterface $logger,
        TimeSheetService $timeSheetService,
        UserService $userService,
        OutputEscapeService $outputEscapeService
    ) {
        parent::__construct($logger);
        $this->timeSheetService = $timeSheetService;
        $this->userService = $userService;
        $this->outputEscapeService = $outputEscapeService;
    }

    /**
     * Returns an array either the values of a running timer
     * or of all the timers depending of the 'requested_resource' value
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function getTimer(Request $request, Response $response, array $args)
    {
        $userId = (int)$this->getUserIdFromToken($request);

        $requestBody = $request->getQueryParams();

        if (isset($requestBody['requested_resource'])) {
            if ($requestBody['requested_resource'] === 'running_timer') {
                $runningTimer = $this->timeSheetService->findRunningTimer($userId);

                if ($runningTimer !== null) {
                    // Only here for the presentation but useless since the value goes in a textarea in frontend where its not interpreted by the browser
                    // It causes that the escaped strings will be printed literally for e.g. "&" will be displayed "&amp"
                    $runningTimer = $this->outputEscapeService->escapeOneDimensionalArray($runningTimer);

                    return $this->respondWithJson(
                        $response,
                        [
                            'running_timer_start' => $runningTimer['start'],
                            'activity' => $runningTimer['activity']
                        ]
                    );
                }
                // Timer not started so string "null" is sent to client
                return $this->respondWithJson($response, ['running_timer_start' => 'null']);
            }

            if ($requestBody['requested_resource'] === 'time_sheet') {
                $loggedUserId = (int)$this->getUserIdFromToken($request);

                try {
                    $userRole = $this->userService->getUserRole($loggedUserId);

                    if ($userRole === 'admin') {
                        $timeSheetsWithUsers = $this->timeSheetService->findTimeSheet();

                        // output escaping only done here https://stackoverflow.com/a/20962774/9013718
                        $timeSheetsWithUsers = $this->outputEscapeService->escapeTwoDimensionalArray(
                            $timeSheetsWithUsers
                        );

                        return $this->respondWithJson($response, $timeSheetsWithUsers);
                    }
                } catch (PersistenceRecordNotFoundException $e) {
                    // If userRole is not found
                    $responseData = [
                        'status' => 'error',
                        'message' => $e->getMessage(),
                    ];
                    return $this->respondWithJson($response, $responseData, 404);
                }
                $this->logger->notice('User ' . $loggedUserId . ' tried to view time sheet');

                return $this->respondWithJson(
                    $response,
                    ['status' => 'error', 'message' => 'You have to be admin to view the time sheet'],
                    403
                );
            }
        }
        return $this->respondWithJson(
            $response,
            ['status' => 'error', 'message' => 'requested_resource not defined in request body'],
            400
        );
    }

    /**
     * Starts a timer
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function startTime(Request $request, Response $response, array $args): Response
    {
        $timerData = $request->getParsedBody();
        $timerData['user_id'] = (int)$this->getUserIdFromToken($request);
        try {
            // Use Entity instead of DTO for simplicity https://github.com/samuelgfeller/slim-api-example/issues/2#issuecomment-597245455
            $timer = new Timer(new ArrayReader($timerData));
            $domainResult = $this->timeSheetService->startTime($timer);
            if (null !== $domainResult['insert_id']) {
                return $this->respondWithJson(
                    $response,
                    ['status' => 'success', 'start_time' => $domainResult['start_time']],
                    201
                );
            }
        } catch (ValidationException $exception) {
            return $this->respondValidationError($exception->getValidationResult(), $response);
        } catch (TimerAlreadyStartedException $alreadyStartedException) {
            $responseData = [
                'status' => 'error',
                'message' => $alreadyStartedException->getMessage(),
                'errCode' => 'timer_already_started',
            ];
            return $this->respondWithJson(
                $response,
                $responseData,
                409
            ); // conflict code https://softwareengineering.stackexchange.com/a/341824/359511
        }
        $response = $this->respondWithJson(
            $response,
            ['status' => 'warning', 'message' => 'Time sheet not created']
        );
        return $response->withAddedHeader('Warning', 'The timer could not be created');
    }

    /**
     * Stops the timer
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function stopTime(Request $request, Response $response, array $args): Response
    {
        $userId = (int)$this->getUserIdFromToken($request);

        try {
            $domainResult = $this->timeSheetService->stopTime($userId);
            if ($domainResult) {
                return $this->respondWithJson(
                    $response,
                    ['status' => 'success', 'message' => 'Timer stopped'],
                    200
                );
            }
        } catch (TimerNotStartedException $timerNotStartedException) {
            $responseData = [
                'status' => 'error',
                'message' => $timerNotStartedException->getMessage(),
                'errCode' => 'timer_not_running',
            ];
            return $this->respondWithJson(
                $response,
                $responseData,
                409
            );
        }
        $response = $this->respondWithJson(
            $response,
            ['status' => 'warning', 'message' => 'Timer could not be stopped']
        );
        return $response->withAddedHeader('Warning', 'Timer could not be stopped');
    }


}
