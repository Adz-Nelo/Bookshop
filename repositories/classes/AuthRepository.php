<?php

class AuthRepository extends BaseRepository
{
    public function findByLogin(string $login)
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, username, email, password_hash, role
             FROM users
             WHERE username = ? OR email = ?
             LIMIT 1'
        );
        $trimmedLogin = trim($login);
        $statement->bind_param('ss', $trimmedLogin, $trimmedLogin);
        $statement->execute();
        $result = $statement->get_result();
        $row = $result->fetch_assoc();
        $statement->close();

        return $row ?: null;
    }

    public function registerClient(string $name, string $username, string $email, string $password)
    {
        $this->connection->autocommit(FALSE);

        try {
            $existing = $this->connection->prepare(
                'SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1'
            );
            $existing->bind_param('ss', $username, $email);
            $existing->execute();
            $existingResult = $existing->get_result();
            if ($existingResult->fetch_assoc()) {
                $existing->close();
                $this->connection->rollback();
                $this->connection->autocommit(TRUE);
                return false;
            }
            $existing->close();

            $createUser = $this->connection->prepare(
                'INSERT INTO users (name, username, email, password_hash, role)
                 VALUES (?, ?, ?, ?, "client")'
            );
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $createUser->bind_param('sss', $name, $username, $email, $passwordHash);
            $createUser->execute();
            $userId = (int) $this->connection->insert_id;
            $createUser->close();

            $addCustomer = $this->connection->prepare(
                'INSERT INTO customers (user_id, name, email) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), name = VALUES(name)'
            );
            $addCustomer->bind_param('iss', $userId, $name, $email);
            $addCustomer->execute();
            $addCustomer->close();

            $this->connection->commit();
            $this->connection->autocommit(TRUE);
            return $userId;
        } catch (Throwable $exception) {
            $this->connection->rollback();
            $this->connection->autocommit(TRUE);

            throw $exception;
        }
    }
}
