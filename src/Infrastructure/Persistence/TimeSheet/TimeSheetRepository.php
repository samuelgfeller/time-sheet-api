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
        $this->table = 'time_sheet';
    }

    /**
     * Return all posts
     *
     * @return array
     */
    public function findAllTimes(): array
    {
        return $this->findAll();
    }

    /**
     * Return all posts which are linked to the given user
     *
     * @param $userId
     * @return array
     */
    public function findRunningTime(int $userId): array
    {
        return $this->findOneBy(['stop IS' => null, 'user_id' => $userId]);
    }

    /**
     * Insert post in database
     *
     * @param array $data
     * @return string lastInsertId
     */
    public function insertTime(array $data): string
    {
        return $this->insert($data);
    }

    /**
     * Update values from post
     * Example of $data: ['name' => 'New name']
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateTime(array $data, int $id): bool
    {
        return $this->update($data, $id);
    }
}
