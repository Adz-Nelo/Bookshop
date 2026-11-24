x<?php

class AuthRepository extends BaseRepository
{
    public function findByLogin($login)
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, username, email, password_hash, role
             FROM users
             WHERE username = :username OR email = :email
             LIMIT 1'
        );
        $statement->execute([
            'username' => trim($login),
            'email' => trim($login),
        ]);

        return $statement->fetch() ?: null;
    }

    public function registerClient($name, $username, $email, $password)
    {
        $this->connection->beginTransaction();

        try {
            $existing = $this->connection->prepare(
                'SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1'
            );
            $existing->execute([
                'username' => $username,
                'email' => $email,
            ]);

            if ($existing->fetch()) {
                $this->connection->rollBack();
                return false;
            }

            $createUser = $this->connection->prepare(
                'INSERT INTO users (name, username, email, password_hash, role)
                 VALUES (:name, :username, :email, :password_hash, "client")'
            );
            $createUser->execute([
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            $userId = (int) $this->connection->lastInsertId();

            $addCustomer = $this->connection->prepare(
                'INSERT INTO customers (user_id, name, email) VALUES (:user_id, :name, :email)
                 ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), name = VALUES(name)'
            );
            $addCustomer->execute([
                'user_id' => $userId,
                'name' => $name,
                'email' => $email,
            ]);

            $this->connection->commit();
            return $userId;
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw $exception;
        }
    }
}
