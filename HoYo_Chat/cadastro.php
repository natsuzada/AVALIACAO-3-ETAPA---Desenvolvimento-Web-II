<?php

require_once 'config/conexao.php';
require_once 'controllers/autenticacao.php';

$controller = new AuthController($pdo);

$erro = "";
$nome = "";
$username = "";
$email = "";
$senha = "";
$confirmaSenha = ""; 
$nascimento = "";
$genero = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
$nome = $_POST['name'];
$username = $_POST['username'];
$email = $_POST['email'];
$senha = $_POST['password']; 
$confirmaSenha = $_POST['password_confirm']; 
$nascimento = $_POST['birthdate'];
$genero = $_POST['gender'] ?? '';

    if (empty($nascimento)) {
        $erro = "A data de nascimento é obrigatória.";
    } else {

        $dataNasc = DateTime::createFromFormat('Y-m-d', $nascimento);
        $hoje = new DateTime();
        $idadeMinima = 18;

        if ($dataNasc === false || $dataNasc->format('Y-m-d') !== $nascimento) {
            $erro = "Formato ou data de nascimento inválida. Por favor, use o formato AAAA-MM-DD e garanta que seja uma data real.";
        }
        
        if ($erro === "" && $dataNasc > $hoje) {
            $erro = "A data de nascimento não pode ser no futuro.";
        }

        if ($erro === "") {

            $dataLimiteMinima = (clone $hoje)->sub(new DateInterval('P' . $idadeMinima . 'Y')); 
            
            if ($dataNasc > $dataLimiteMinima) {
                $erro = "Você deve ter pelo menos " . $idadeMinima . " anos para se cadastrar.";
            }
        }

        if ($erro === "") {
            $idadeMaxima = 100;

            $dataLimiteMaxima = (clone $hoje)->sub(new DateInterval('P' . $idadeMaxima . 'Y'));

            if ($dataNasc < $dataLimiteMaxima) {
                $erro = "Você deve ter no máximo " . $idadeMaxima . " anos para se cadastrar.";
            }
        }
    }

    if ($erro === "") {
        $resultado = $controller->cadastrar();

        if ($resultado) {
            $erro = $resultado;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <link rel="icon" href="./img/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles.css">

</head>
<body class="body-login">
    <div class="container">
        <h1>Cadastro</h1>

        <?php if ($erro != ""): ?>
            <p style="color: var(--erro); text-align: center; font-weight: bold;">
                <?php echo htmlspecialchars($erro); ?>
            </p>
        <?php endif; ?>

        <form method="POST" action="cadastro.php">
            
            <label>Nome completo*:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($nome); ?>" required>

            <label>Nome de usuário (sem espaços)*:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>

            <label>Email*:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

            <label>Senha* (Mínimo 6 dígitos, 1 maiúscula, 1 número):</label>
            <input type="password" name="password" value="<?php echo htmlspecialchars($senha); ?>" required>

            <label>Confirmar Senha*:</label>
            <input type="password" name="password_confirm" value="<?php echo htmlspecialchars($confirmaSenha); ?>" required>

            <label>Data de nascimento*:</label>
            <input type="date" name="birthdate" value="<?php echo htmlspecialchars($nascimento); ?>" required>

            <label>Gênero:</label>
            <select name="gender" required>
                <option value="">Selecione</option>
                <option value="feminino" <?php if($genero == 'feminino') echo 'selected'; ?>>Feminino</option>
                <option value="masculino" <?php if($genero == 'masculino') echo 'selected'; ?>>Masculino</option>
                <option value="outro" <?php if($genero == 'outro') echo 'selected'; ?>>Outro</option>
            </select>

            <button type="submit">Cadastrar</button>
        </form>

        <p><a href="index.php">Voltar ao login</a></p>
    </div>
</body>
</html>