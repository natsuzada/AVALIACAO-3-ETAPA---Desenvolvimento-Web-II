<?php
class Post {
    private $pdo;

    public function __construct($conexao) {
        $this->pdo = $conexao;
    }

    public function criarPost($idUsuario, $texto) {
        $sql = "INSERT INTO posts (usuario_id, conteudo) VALUES (:id, :conteudo)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idUsuario);
        $stmt->bindValue(':conteudo', $texto);
        return $stmt->execute();
    }

    public function listarFeed($idMeuUsuario) {
        $sql = "SELECT 
                    p.id AS post_id,
                    p.conteudo,
                    p.curtidas,
                    p.data_criacao,
                    u.id AS usuario_id,
                    u.nome,
                    u.username,
                    u.foto 
                FROM posts p
                INNER JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.usuario_id IN (
                    SELECT seguido_id FROM seguidores WHERE seguidor_id = :id1
                    UNION
                    SELECT :id2
                )
                ORDER BY p.data_criacao DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id1', $idMeuUsuario);
        $stmt->bindValue(':id2', $idMeuUsuario);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function jaCurtiu($idUsuario, $idPost) {
        $sql = "SELECT id FROM curtidas_reais WHERE usuario_id = :u AND post_id = :p";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':u', $idUsuario);
        $stmt->bindValue(':p', $idPost);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function curtir($idUsuario, $idPost) {

        $sqlInsert = "INSERT INTO curtidas_reais (usuario_id, post_id) VALUES (:u, :p)";
        $stmt = $this->pdo->prepare($sqlInsert);
        $stmt->bindValue(':u', $idUsuario);
        $stmt->bindValue(':p', $idPost);
        $stmt->execute();

        $sqlUpdate = "UPDATE posts SET curtidas = curtidas + 1 WHERE id = :p";
        $stmt2 = $this->pdo->prepare($sqlUpdate);
        $stmt2->bindValue(':p', $idPost);
        $stmt2->execute();
    }

    public function descurtir($idUsuario, $idPost) {

        $sqlDelete = "DELETE FROM curtidas_reais WHERE usuario_id = :u AND post_id = :p";
        $stmt = $this->pdo->prepare($sqlDelete);
        $stmt->bindValue(':u', $idUsuario);
        $stmt->bindValue(':p', $idPost);
        $stmt->execute();

        $sqlUpdate = "UPDATE posts SET curtidas = GREATEST(curtidas - 1, 0) WHERE id = :p";
        $stmt2 = $this->pdo->prepare($sqlUpdate);
        $stmt2->bindValue(':p', $idPost);
        $stmt2->execute();
    }
}
?>