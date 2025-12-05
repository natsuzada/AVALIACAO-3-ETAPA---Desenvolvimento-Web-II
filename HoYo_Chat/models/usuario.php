<?php

class Usuario {
    public $pdo;

    public function __construct($conexao) {
        $this->pdo = $conexao;
    }

    public function cadastrar($nome, $username, $email, $senha, $nasc, $genero) {
        
        if ($this->verificarEmail($email) == true) {
            return false;
        }

        if ($this->verificarUsername($username) == true) {
            return false;
        }

        $senhaSegura = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nome, username, email, senha, data_nascimento, genero) 
                VALUES (:nome, :user, :email, :senha, :nasc, :gen)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':user', $username);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':senha', $senhaSegura);
        $stmt->bindValue(':nasc', $nasc);
        $stmt->bindValue(':gen', $genero);

        return $stmt->execute();
    }

    public function logar($email, $senha) {
        $sql = "SELECT * FROM usuarios WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $dadosUsuario = $stmt->fetch();

            if (password_verify($senha, $dadosUsuario['senha'])) {
                return $dadosUsuario;
            } else {
                return false; 
            }
        } else {
            return false;
        }
    }

    public function pegarDados($id) {
        $sql = "SELECT * FROM usuarios WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function atualizar($id, $nome, $username, $genero, $nomeDaFoto = null) {
        
        if ($nomeDaFoto != null) {
            $sql = "UPDATE usuarios SET nome = :n, username = :u, genero = :g, foto = :f WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':f', $nomeDaFoto, PDO::PARAM_STR);
        } else {
            $sql = "UPDATE usuarios SET nome = :n, username = :u, genero = :g WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
        }

        $stmt->bindValue(':n', $nome);
        $stmt->bindValue(':u', $username);
        $stmt->bindValue(':g', $genero);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }

    public function verificarEmail($email) {
        $sql = "SELECT id FROM usuarios WHERE email = :e";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':e', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function verificarUsername($username) {
        $sql = "SELECT id FROM usuarios WHERE username = :u";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':u', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function buscarPessoas($termo, $meuId) {
        $sql = "SELECT id, nome, username, foto FROM usuarios 
                WHERE (nome LIKE :termo OR username LIKE :termo) 
                AND id != :meuId 
                ORDER BY nome";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':termo', "%$termo%");
        $stmt->bindValue(':meuId', $meuId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    public function seguir($eu, $outro) {
        if ($this->jaSegue($eu, $outro) == false) {
            $sql = "INSERT INTO seguidores (seguidor_id, seguido_id) VALUES (:eu, :outro)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':eu', $eu);
            $stmt->bindValue(':outro', $outro);
            return $stmt->execute();
        }
        return false;
    }

    public function deixarDeSeguir($eu, $outro) {
        $sql = "DELETE FROM seguidores WHERE seguidor_id = :eu AND seguido_id = :outro";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':eu', $eu);
        $stmt->bindValue(':outro', $outro);
        return $stmt->execute();
    }

    public function jaSegue($eu, $outro) {
        $sql = "SELECT id FROM seguidores WHERE seguidor_id = :eu AND seguido_id = :outro";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':eu', $eu);
        $stmt->bindValue(':outro', $outro);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
}
?>