<?php

namespace App\Domain\Timer;


use App\Domain\Utility\ArrayReader;

class Timer
{
    private ?int $id;
    private ?int $userId;
    private ?string $start;
    private ?string $stop;
    private ?string $activity;

    public function __construct(ArrayReader $arrayReader)
    {
        $this->id = $arrayReader->findInt('id');
        $this->userId = $arrayReader->findInt('user_id');
        $this->start = $arrayReader->findString('start');
        $this->stop = $arrayReader->findString('stop');
        $this->activity = $arrayReader->findString('activity');
    }

    /**
     * Returns all values of object as array.
     * The array keys should match with the database
     * column names since it is likely used to
     * modify a database table
     *
     * @return array
     */
    public function toArray(): array
    {
        $timeSheet = [];

        if ($this->id !== null) {
            $timeSheet['id'] = $this->id;
        }
        if ($this->userId !== null) {
            $timeSheet['user_id'] = $this->userId;
        }
        if ($this->start !== null) {
            $timeSheet['start'] = $this->start;
        }
        if ($this->stop !== null) {
            $timeSheet['stop'] = $this->stop;
        }
        if ($this->activity !== null) {
            $timeSheet['activity'] = $this->activity;
        }

        return $timeSheet;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @param int|null $userId
     */
    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getStart(): string
    {
        return $this->start;
    }

    /**
     * @param string $start
     */
    public function setStart(string $start): void
    {
        $this->start = $start;
    }

    /**
     * @return string
     */
    public function getStop(): string
    {
        return $this->stop;
    }

    /**
     * @param string $stop
     */
    public function setStop(string $stop): void
    {
        $this->stop = $stop;
    }

    /**
     * @return string
     */
    public function getActivity(): string
    {
        return $this->activity;
    }

    /**
     * @param string $activity
     */
    public function setActivity(string $activity): void
    {
        $this->activity = $activity;
    }


}