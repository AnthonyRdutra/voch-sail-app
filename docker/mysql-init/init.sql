-- Cria o usuário 'sail' se ainda não existir, com a senha 'testevoch'
CREATE USER IF NOT EXISTS 'sail'@'%' IDENTIFIED BY 'testevoch';

-- Cria o banco principal da aplicação
CREATE DATABASE IF NOT EXISTS vochteste_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Cria o banco de testes usado pelo PHPUnit
CREATE DATABASE IF NOT EXISTS vochteste_db_test
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Concede todas as permissões ao usuário 'sail' sobre os dois bancos
GRANT ALL PRIVILEGES ON vochteste_db.* TO 'sail'@'%';
GRANT ALL PRIVILEGES ON vochteste_db_test.* TO 'sail'@'%';

-- Garante acesso global (opcional, mas útil para migrations e testes)
GRANT ALL PRIVILEGES ON *.* TO 'sail'@'%' WITH GRANT OPTION;

-- Atualiza as permissões
FLUSH PRIVILEGES;
