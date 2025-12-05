<?php

require_once 'config/conexao.php';
require_once 'models/post.php';

class PostController {
    private $postModel;

    public function __construct($pdo) {
        $this->postModel = new Post($pdo);
    }

    public function publicar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novoPost'])) {
            
            if (!isset($_SESSION['usuario_logado'])) {
                header('Location: index.php?erro=sessaoinvalida');
                exit;
            }

            $texto = $_POST['novoPost'];
            $idUsuario = $_SESSION['usuario_logado']['id'];

            if (trim($texto) == "") {
                return "O post não pode estar vazio!";
            }

            $this->postModel->criarPost($idUsuario, $texto);

            header('Location: feed.php');
            exit;
        }
    }

    public function gerenciarCurtida() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['curtir'])) {
            
            if (session_status() === PHP_SESSION_NONE) session_start();

            if (!isset($_SESSION['usuario_logado'])) {
                header('Location: index.php');
                exit;
            }

            $idDoPost = $_POST['curtir'];
            $idUsuario = $_SESSION['usuario_logado']['id'];

            $jaCurti = $this->postModel->jaCurtiu($idUsuario, $idDoPost);
            
            if ($jaCurti) {
                $this->postModel->descurtir($idUsuario, $idDoPost);
            } else {
                $this->postModel->curtir($idUsuario, $idDoPost);
            }

            header('Location: feed.php');
            exit;
        }
    }

    public function carregarFeed() {

        if (isset($_SESSION['usuario_logado'])) {
            $meuId = $_SESSION['usuario_logado']['id'];
            
            return $this->postModel->listarFeed($meuId);
        } else {
            return [];
        }
    }
}
?>