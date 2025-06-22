<?php

function vj_db_get_connection(): PDO {
  return new PDO('pgsql:host=php-template-local-db;port=5432;dbname=template', 'template', 'template');
}

function vj_db_get_user_by_email (string $email, PDO $connection): PDOStatement {
    $user_statement = $connection->prepare('SELECT * FROM users WHERE email = :email');
    $user_statement->bindValue(':email', $email);
    $user_statement->execute();

    return $user_statement;
}

function vj_db_get_user_by_id(string $id, PDO $connection): array|false {
    $user_statement = $connection->prepare('SELECT id, username, email, created_at  FROM users WHERE id = :id');
    $user_statement->bindValue(':id', $id);
    $user_statement->execute();

    $user = $user_statement->fetch(PDO::FETCH_ASSOC);

    return $user;
}

function vj_db_session_create (string $user_id, PDO $connection): string {
    $session_statement = $connection->prepare(
        'INSERT INTO sessions (user_id, user_agent, user_ip) 
               VALUES (:user_id, :user_agent, :user_ip) 
               RETURNING id');

    $session_statement->bindValue(':user_id', $user_id);
    $session_statement->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT']);
    $session_statement->bindValue(':user_ip', $_SERVER['REMOTE_ADDR']);
    $session_statement->execute();

    return $session_statement->fetch(PDO::FETCH_ASSOC)['id'];
}

function vj_db_set_session_tag (string $db_session_id, mixed $credentials, PDO $connection): bool {
   $session_statement = $connection->prepare(
       'UPDATE sessions SET tag = :tag, iv = :iv WHERE id = :session_id'
   );

   $session_statement->bindValue(':session_id', $db_session_id);
   $session_statement->bindValue(':tag', bin2hex($credentials['tag']));
   $session_statement->bindValue(':iv', bin2hex($credentials['iv']));

   return $session_statement->execute();
}

function vj_db_get_session_by_id (string $session_id, PDO $connection): array|false {
    $session_statement = $connection->prepare('SELECT * FROM sessions WHERE id = :session_id');
    $session_statement->bindValue(':session_id', $session_id);
    $session_statement->execute();

    return $session_statement->fetch(PDO::FETCH_ASSOC);
}

function vj_db_delete_session_by_id (string $session_id, PDO $connection): void
{
    $session_statement = $connection->prepare('DELETE FROM sessions WHERE id = :id');
    $session_statement->bindValue(':id', $session_id);
    $session_statement->execute();
}