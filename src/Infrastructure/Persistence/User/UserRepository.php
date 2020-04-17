<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\User;

use App\Infrastructure\Persistence\DataManager;
use App\Infrastructure\Persistence\Exceptions\PersistenceRecordNotFoundException;
use Cake\Database\Connection;

class UserRepository extends DataManager
{
    // Fields without password
    private array $fields = ['id', 'name', 'email', 'updated_at', 'created_at'];

    public function __construct(Connection $conn = null)
    {
        parent::__construct($conn);
        $this->table = 'user';
    }

    /**
     * Return user with given id if it exists
     * otherwise null
     *
     * @param int $id
     * @return array
     */
    public function findUserById(int $id): array
    {
        return $this->findById($id,$this->fields);
    }

    /**
     * Return user with given id if it exists
     * otherwise null
     *
     * @param string|null $email
     * @return array|null
     */
    public function findUserByEmail(?string $email): ?array
    {
        return $this->findOneBy(['email' => $email],['id','email','password']);
    }

    /**
     * Insert user in database
     *
     * @param array $data
     * @return string lastInsertId
     */
    public function insertUser(array $data): string {
        return $this->insert($data);
    }

    /**
     * Retrieve user role
     *
     * @param int $id
     * @return string
     * @throws PersistenceRecordNotFoundException
     */
    public function getUserRole(int $id) : string{
        return $this->getById($id,['role'])['role'];
    }
}
