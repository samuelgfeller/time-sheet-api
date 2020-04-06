<?php

namespace App\Controllers\TimeSheet;

use App\Application\Controllers\Controller;
use App\Domain\Exception\ValidationException;
use App\Domain\TimeSheet\TimeSheet;
use App\Domain\TimeSheet\TimeSheetService;
use App\Domain\User\UserService;
use App\Domain\Utility\ArrayReader;
use App\Domain\Validation\OutputEscapeService;
use Psr\Http\Message\ResponseInterface;
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

    public function create(Request $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $userId = (int)$this->getUserIdFromToken($request);

        if (null !== $timeSheetData = $request->getParsedBody()) {

            $timeSheet = new TimeSheet(new ArrayReader($timeSheetData));
            $timeSheet->setUserId($userId);

            try {
                $insertId = $this->timeSheetService->createTimeSheet($timeSheet);
            } catch (ValidationException $exception) {
                return $this->respondValidationError($exception->getValidationResult(), $response);
            }

            if (null !== $insertId) {
                return $this->respondWithJson($response, ['status' => 'success'], 201);
            }
            $response = $this->respondWithJson($response, ['status' => 'warning', 'message' => 'Time sheet not created']);
            return $response->withAddedHeader('Warning', 'The time sheet could not be created');
        }
        return $this->respondWithJson($response, ['status' => 'error', 'message' => 'Request body empty']);
    }
}
