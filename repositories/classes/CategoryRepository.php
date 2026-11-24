<?php

class CategoryRepository extends BaseRepository
{
    public function getAll()
    {
        $result = $this->connection->query('SELECT name FROM categories ORDER BY name');

        return array_column($result->fetch_all(MYSQLI_ASSOC), 'name');
    }

    public function add($name)
    {
        $name = trim($name);

        if ($name !== '') {
            $statement = $this->connection->prepare(
                'INSERT INTO categories (name) VALUES (?)'
            );
            $statement->bind_param('s', $name);
            $statement->execute();
            $statement->close();
        }
    }
}
