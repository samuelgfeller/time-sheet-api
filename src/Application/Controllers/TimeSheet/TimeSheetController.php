<?php

namespace App\Controllers\TimeSheet;

use App\Application\Controllers\Controller;
use App\Domain\Exception\TimerAlreadyStartedException;
use App\Domain\Exception\TimerNotStartedException;
use App\Domain\Timer\Timer;
use App\Domain\TimeSheet\TimeSheetService;
use App\Domain\User\UserService;
use App\Domain\Utility\ArrayReader;
use App\Domain\Validation\OutputEscapeService;
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

    /*
    // Commented out because unused. If I'd use it, it would be done approx like this. Not tested though.

    public function get(Request $request, Response $response, array $args): Response
        {
            $timeSheetId = $args['id'];
            $timeSheet = $this->timeSheetService->findTimeSheet($timeSheetId);

            // Get user information connected to timeSheet
            $user = $this->userService->findUser($timeSheet['user_id']);

            // Add user name info to timeSheet
            $timeSheetWithUser = $timeSheet;
            $timeSheetWithUser['user_name'] = $user['name'];

            $timeSheetWithUser = $this->outputEscapeService->escapeOneDimensionalArray($timeSheetWithUser);
            return $this->respondWithJson($response, $timeSheetWithUser);
        }*/

    public function getTimer(Request $request, Response $response, array $args)
    {
        $userId = (int)$this->getUserIdFromToken($request);

        $requestBody = $request->getQueryParams();

        if (isset($requestBody['requested_resource'])){
            if ($requestBody['requested_resource'] === 'running_timer'){
                $runningTimerStart = $this->timeSheetService->findRunningTimerStartTime($userId);
                if ($runningTimerStart !== null){
                    return $this->respondWithJson($response,['running_timer_start' => $runningTimerStart['start'],'activity' => $runningTimerStart['activity']]);
                }
                // Timer not started so string "null" is sent to client
                return $this->respondWithJson($response,['running_timer_start' => 'null']);
            }
            else if($requestBody['requested_resource'] === 'time_sheet'){

/*                $timeSheetsWithUsers = $this->timeSheetService->findAllTimeSheets();

                // output escaping only done here https://stackoverflow.com/a/20962774/9013718
                $timeSheetsWithUsers = $this->outputEscapeService->escapeTwoDimensionalArray($timeSheetsWithUsers);

                return $this->respondWithJson($response, $timeSheetsWithUsers);*/
            }
        }
        return $this->respondWithJson(
            $response,['status' => 'error', 'message' => 'requested_resource not defined in request body'],400
        );
    }

    /*
    // Commented out because unused. If I'd use it, it would be done approx like this. Not tested though.

    public function getOwnTimeSheets(Request $request, Response $response, array $args): Response
        {
            $loggedUserId = (int)$this->getUserIdFromToken($request);

            $timeSheets = $this->timeSheetService->findAllTimeSheetsFromUser($loggedUserId);

            $timeSheets = $this->outputEscapeService->escapeTwoDimensionalArray($timeSheets);

            return $this->respondWithJson($response, $timeSheets);
        }*/

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
        } catch (TimerNotStartedException $timerNotStartedException){
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
