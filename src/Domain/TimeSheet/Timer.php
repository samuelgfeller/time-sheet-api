<?php

namespace App\Domain\Timer;


use App\Domain\Utility\ArrayReader;

class Timer
{
    private ?int $id;
    private ?int $userId;
    private string $start;
    private string $stop;

    public function __construct(ArrayReader $arrayReader) {
        $this->id = $arrayReader->findInt('id');
        $this->userId = $arrayReader->findInt('user_id');
        $this->start = $arrayReader->getString('start');
        $this->stop = $arrayReader->getString('stop');
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
        // Not include required, from db non nullable values if they are null -> for update
        if($this->id !== null){ $timeSheet['id'] = $this->id;}
        if($this->userId !== null){ $timeSheet['user_id'] = $this->userId;}

        // Message is nullable and null is a valid value so it has to be included
        $timeSheet['start'] = $this->start;
        $timeSheet['stop'] = $this->stop;

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

}