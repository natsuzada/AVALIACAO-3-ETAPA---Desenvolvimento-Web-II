<?php
require_once 'config/conexao.php';
require_once 'models/usuario.php';

class AuthController {
    private $usuarioModel;

    public function __construct($pdo) {
        $this->usuarioModel = new usuario($pdo);
    }

    public function entrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $email = $_POST['email'];
            $senha = $_POST['password'];

            $resultado = $this->usuarioModel->logar($email, $senha);

            if ($resultado != false) {

                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                $_SESSION['usuario_logado'] = $resultado;

                header('Location: feed.php');
                exit; 
            } else {
                return "Email ou senha incorretos!";
            }
        }
    }

    public function cadastrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $nome = trim(strip_tags($_POST['name']));
            $username = trim(strip_tags($_POST['username']));
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $senha = $_POST['password'];
            $confirmaSenha = $_POST['password_confirm'];
            $nascimento = $_POST['birthdate'];
            $genero = $_POST['gender'];

            if (!preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $nome)) {
                return "O nome não pode conter números ou caracteres especiais (como parênteses ou aspas).";
            }

            if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
                return "O nome de usuário só pode conter letras, números e underline (sem espaços).";
            }

            if ($senha !== $confirmaSenha) {
                return "As senhas não conferem.";
            }

            if (strlen($senha) < 6) {
                return "A senha deve ter pelo menos 6 caracteres.";
            }
            if (!preg_match('/[A-Z]/', $senha)) {
                return "A senha deve ter pelo menos uma letra maiúscula.";
            }
            if (!preg_match('/[0-9]/', $senha)) {
                return "A senha deve ter pelo menos um número.";
            }

            if (empty($nome) || empty($username) || empty($email)) {
                return "Preencha todos os campos.";
            }

            $sucesso = $this->usuarioModel->cadastrar($nome, $username, $email, $senha, $nascimento, $genero);

            if ($sucesso == true) {
                header('Location: index.php?cadastro=sucesso');
                exit;
            } else {
                return "Erro: Email ou Nome de Usuário já estão em uso."; 
            }
        }
    }

    public function sair() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        session_unset();
        session_destroy();
        
        header('Location: index.php');
        exit;
    }

    public function atualizarDados() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar'])) {
            
            $id = $_SESSION['usuario_logado']['id'];
            
            $nome = trim(strip_tags($_POST['name']));
            $username = trim(strip_tags($_POST['username']));
            $genero = $_POST['gender'];
            
            if (!preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $nome)) return "Nome inválido.";
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) return "Username inválido.";
            if (empty($nome) || empty($username)) return "Preencha tudo.";

            $nomeFinalDoArquivo = null;

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {

                if ($_FILES['foto']['size'] > 2097152) {
                    return "A foto é muito grande! Máximo 2MB.";
                }

                $pastaDestino = 'assets/uploads/';
                
                if (!is_dir($pastaDestino)) {
                    mkdir($pastaDestino, 0777, true);
                }

                $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $nomeFinalDoArquivo = "foto_" . $id . "_" . uniqid() . "." . $extensao;

                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $pastaDestino . $nomeFinalDoArquivo)) {
                    return "Erro ao salvar o arquivo na pasta.";
                }
            }

            $sucesso = $this->usuarioModel->atualizar($id, $nome, $username, $genero, $nomeFinalDoArquivo);

            if ($sucesso) {
                $_SESSION['usuario_logado']['nome'] = $nome;
                $_SESSION['usuario_logado']['username'] = $username;
                $_SESSION['usuario_logado']['genero'] = $genero;
                return "Dados atualizados com sucesso!";
            } else {
                return "Erro ao atualizar.";
            }
        }
    }
}
?>