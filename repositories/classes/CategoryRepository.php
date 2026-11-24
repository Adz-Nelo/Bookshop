<?php

class CategoryRepository extends BaseRepository
{
    public function getAll()
    {
        $statement = $this->connection->query('SELECT name FROM categories ORDER BY name');

        return array_column($statement->fetchAll(), 'name');
    }

    public function add($name)
    {
        $name = trim($name);

        if ($name !== '') {
            $statement = $this->connection->prepare(
                'INSERT INTO categories (name) VALUES (:name)'
            );
            $statement->execute(['name' => $name]);
        }
    }
}