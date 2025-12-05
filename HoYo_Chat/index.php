<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once 'config/conexao.php';
require_once 'controllers/autenticacao.php';

$controller = new AuthController($pdo);

$erro = "";
$sucesso = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $mensagem = $controller->entrar();
    
    if ($mensagem) {
        $erro = $mensagem;
    }
}

if (isset($_GET['cadastro']) && $_GET['cadastro'] == 'sucesso') {
    $sucesso = "Cadastro realizado com sucesso! Pode entrar.";
}

if (isset($_GET['erro']) && $_GET['erro'] == 'sessaoinvalida') {
    $erro = "Você precisa fazer login para acessar essa página.";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="icon" href="./img/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="body-login">

<div class="container">
    <h1>Faça seu Login</h1>

    <?php if ($erro != ""): ?>
        <p style="color: red; text-align: center; font-weight: bold;">
            <?php echo $erro; ?>
        </p>
    <?php endif; ?>

    <?php if ($sucesso != ""): ?>
        <p style="color: green; text-align: center; font-weight: bold;">
            <?php echo $sucesso; ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="index.php">
        <label>Email:</label>
        <input type="email" name="email" required placeholder="Digite seu e-mail">

        <label>Senha:</label>
        <input type="password" name="password" required placeholder="Digite sua senha">

        <button type="submit">Entrar</button>
    </form>

    <p>Ainda não tem conta? <a href="cadastro.php">Cadastrar-se</a></p>
</div>

</body>
</html>