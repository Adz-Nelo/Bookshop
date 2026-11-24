<?php

class BaseRepository
{
    protected $connection;

    public function __construct()
    {
        $this->connection = getDatabaseConnection();
    }
}