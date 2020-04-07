<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\TimeSheet;

use App\Infrastructure\Persistence\DataManager;
use App\Infrastructure\Persistence\Exceptions\PersistenceRecordNotFoundException;
use Cake\Database\Connection;

class TimeSheetRepository extends DataManager
{

    public function __construct(Connection $conn = null)
    {
        parent::__construct($conn);
        $this->table = 'time-sheet';
    }

    /**
     * Return all posts
     *
     * @return array
     */
    public function findAllTimeSheets(): array
    {
        return $this->findAll();
    }
    
    /**
     * Return post with given id if it exists
     * otherwise null
     *
     * @param int $id
     * @return array
     */
    public function findTimeSheetById(int $id): array
    {
        return $this->findById($id);
    }
    
    /**
     * Retrieve post from database
     * If not found error is thrown
     *
     * @param int $id
     * @return array
     * @throws PersistenceRecordNotFoundException
     */
    public function getTimeSheetById(int $id): array
    {
        return $this->getById($id);
    }

    /**
     * Return all posts which are linked to the given user
     *
     * @param $userId
     * @return array
     */
    public function findAllTimeSheetsByUserId(int $userId): array
    {
        return $this->findAllBy('user_id',$userId);
    }

    /**
     * Insert post in database
     *
     * @param array $data
     * @return string lastInsertId
     */
    public function insertTime(array $data): string {
        return $this->insert($data);
    }
    
    /**
     * Delete post from database
     *
     * @param int $id
     * @return bool
     */
    public function deleteTimeSheet(int $id): bool {
        return $this->delete($id);
    }
    
    /**
     * Update values from post
     * Example of $data: ['name' => 'New name']
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateTimeSheet(array $data,int $id): bool {
        return $this->update($data, $id);
    }
}
