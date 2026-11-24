<?php

class SupplierRepository extends BaseRepository
{
    public function getAll()
    {
        $result = $this->connection->query(
            'SELECT id, name, contact FROM suppliers ORDER BY name'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function add($data)
    {
        $name = trim($data['name']);
        $contact = trim($data['contact']);
        $statement = $this->connection->prepare(
            'INSERT INTO suppliers (name, contact) VALUES (?, ?)'
        );
        $statement->bind_param('ss', $name, $contact);
        $statement->execute();
        $statement->close();
    }
}
