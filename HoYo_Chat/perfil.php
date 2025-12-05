<?php

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

session_start();
require_once 'config/conexao.php';
require_once 'controllers/autenticacao.php';

if (!isset($_SESSION['usuario_logado'])) {
    header('Location: index.php?erro=sessaoinvalida');
    exit;
}

$controller = new AuthController($pdo);
$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensagem = $controller->atualizarDados();
}

require_once 'models/usuario.php';
$usuarioModel = new Usuario($pdo);
$meusDados = $usuarioModel->pegarDados($_SESSION['usuario_logado']['id']);

$nome = $meusDados['nome'];
$username = $meusDados['username'];
$email = $meusDados['email'];
$nascimento = $meusDados['data_nascimento'];
$genero = $meusDados['genero'];
$foto = $meusDados['foto'];

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Perfil</title>
    <link rel="icon" href="./img/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/styles.css">
    
    <script>
        window.onpageshow = function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        };
    </script>
</head>
<body>
    
    <aside>
        <div class="sidebar-logo-container">
            <img src="img/logo.png" alt="Logo" class="sidebar-logo-img">
        </div>

        <div class="nav-icons">
            <?php 
                $paginaAtual = basename($_SERVER['PHP_SELF']); 
            ?>

            <a href="feed.php" class="menu-item <?php echo ($paginaAtual == 'feed.php') ? 'active' : ''; ?>">
                <img src="https://img.icons8.com/?size=100&id=DnO56AwGi1R4&format=png&color=000000" alt="Início">
                <span>Inicio</span>
            </a>

            <a href="pesquisar.php" class="menu-item <?php echo ($paginaAtual == 'pesquisar.php') ? 'active' : ''; ?>">
                <img src="https://img.icons8.com/?size=100&id=7695&format=png&color=000000" alt="Buscar">
                <span>Pesquisar</span>
            </a>
            
            <a href="perfil.php" class="menu-item <?php echo ($paginaAtual == 'perfil.php') ? 'active' : ''; ?>">
                <img src="https://cdn-icons-png.flaticon.com/512/1077/1077114.png" alt="Perfil">
                <span>Perfil</span>
            </a>
        </div>
        
        <div class="logout-container">
            <form action="logout.php" method="post">
                <button class="menu-item-sair" type="submit">
                    <img src="https://img.icons8.com/?size=100&id=8119&format=png&color=000000" alt="Sair">
                    <span>Sair</span>
                </button>
            </form>
        </div>
    </aside>

    <main>
        
        <div class="perfil" style="padding-bottom: 1.5rem; border-bottom: 1px solid #eee;">
            
            <?php 
                $caminhoFotoPerfil = 'assets/uploads/' . $foto;
                if (!empty($foto) && file_exists($caminhoFotoPerfil)): 
            ?>
                <img src="<?php echo $caminhoFotoPerfil; ?>" alt="Foto de Perfil" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;" />
            <?php else: ?>
                <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="Foto Padrão" style="width: 100px; height: 100px;" />
            <?php endif; ?>

            <div>
                <div class="nome" style="font-size: 1.5rem;"><?php echo $nome; ?></div>
                <div class="usuario">@<?php echo $username; ?></div>
            </div>
        </div>

        <?php if ($mensagem): ?>
            <p style="color: green; font-weight: bold; text-align: center; margin-top: 20px;">
                <?php echo $mensagem; ?>
            </p>
        <?php endif; ?>

        <div class="profile-details-card">
            <h2>Editar Informações</h2>
            
            <form method="POST" enctype="multipart/form-data">
                
                <label>Alterar Foto de Perfil:</label>
                <input type="file" name="foto" accept="image/*">

                <label>Nome Completo:</label>
                <input type="text" name="name" value="<?php echo $nome; ?>" required>

                <label>Nome de Usuário:</label>
                <input type="text" name="username" value="<?php echo $username; ?>" required>
                
                <label>Gênero:</label>
                <select name="gender">
                    <option value="feminino" <?php if($genero == 'feminino') echo 'selected'; ?>>Feminino</option>
                    <option value="masculino" <?php if($genero == 'masculino') echo 'selected'; ?>>Masculino</option>
                    <option value="outro" <?php if($genero == 'outro') echo 'selected'; ?>>Outro</option>
                </select>

                <div style="margin-top: 15px; color: #6d1c44ff;">
                    <p style="text-align: left; margin: 0;"><strong>Email:</strong> <?php echo $email; ?></p>
                    <p style="text-align: left; margin: 5px 0 0 0;"><strong>Nascimento:</strong> <?php echo date('d/m/Y', strtotime($nascimento)); ?></p>
                </div>

                <button type="submit" name="atualizar" style="width: 100%; margin-top: 20px;">Salvar Alterações</button>
            </form>
            
        </div>
        
    </main>
</body>
</html>