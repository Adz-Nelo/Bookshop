<?php

class SupplierRepository extends BaseRepository
{
    public function getAll()
    {
        return $this->connection->query(
            'SELECT id, name, contact FROM suppliers ORDER BY name'
        )->fetchAll();
    }

    public function add($data)
    {
        $statement = $this->connection->prepare(
            'INSERT INTO suppliers (name, contact) VALUES (:name, :contact)'
        );
        $statement->execute([
            'name' => trim($data['name']),
            'contact' => trim($data['contact']),
        ]);
    }
}
