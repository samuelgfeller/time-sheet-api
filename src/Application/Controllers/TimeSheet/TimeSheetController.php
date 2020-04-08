<?php

namespace App\Controllers\TimeSheet;

use App\Application\Controllers\Controller;
use App\Domain\Exception\TimerAlreadyStartedException;
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

    public function list(Request $request, Response $response, array $args)
    {
        $timeSheetsWithUsers = $this->timeSheetService->findAllTimeSheets();

        // output escaping only done here https://stackoverflow.com/a/20962774/9013718
        $timeSheetsWithUsers = $this->outputEscapeService->escapeTwoDimensionalArray($timeSheetsWithUsers);

        return $this->respondWithJson($response, $timeSheetsWithUsers);
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
        $userId = (int)$this->getUserIdFromToken($request);

        try {
            $domainResult = $this->timeSheetService->startTime($userId);
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

        $domainResult = $this->timeSheetService->stopTime($userId);
        if (null !== $domainResult['insert_id']) {
            return $this->respondWithJson(
                $response,
                ['status' => 'success', 'message' => 'Timer stopped'],
                201
            );
        }
        $response = $this->respondWithJson(
            $response,
            ['status' => 'warning', 'message' => 'Timer could not be stopped']
        );
        return $response->withAddedHeader('Warning', 'Timer could not be stopped');
    }


}
