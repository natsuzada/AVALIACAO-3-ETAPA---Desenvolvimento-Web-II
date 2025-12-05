<?php

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

session_start();
require_once 'config/conexao.php';
require_once 'models/usuario.php';

if (!isset($_SESSION['usuario_logado'])) {
    header('Location: index.php?erro=sessaoinvalida');
    exit;
}

$usuarioModel = new Usuario($pdo);
$meuId = $_SESSION['usuario_logado']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_usuario_acao'])) {
    $idAlvo = $_POST['id_usuario_acao'];
    $tipoAcao = $_POST['acao']; // "seguir" ou "deixar"

    if ($tipoAcao == "seguir") {
        $usuarioModel->seguir($meuId, $idAlvo);
    } else {
        $usuarioModel->deixarDeSeguir($meuId, $idAlvo);
    }
}

$resultados = [];
$termoBusca = "";

if (isset($_GET['busca'])) {
    $termoBusca = $_GET['busca'];

    if (trim($termoBusca) != "") {
        $resultados = $usuarioModel->buscarPessoas($termoBusca, $meuId);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pesquisa</title>
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
        
        <div class="search-box-container">
            <form method="GET" action="pesquisar.php" style="display: flex; width: 100%; gap: 10px;">
                <input type="text" name="busca" class="search-input" placeholder="Buscar pessoas por nome ou @usuario..." value="<?php echo htmlspecialchars($termoBusca); ?>">
                <button type="submit" class="search-button">Buscar</button>
            </form>
        </div>

        <?php if (isset($_GET['busca']) && count($resultados) == 0): ?>
            <div style="text-align: center; color: #777; margin-top: 50px;">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" alt="Vazio" style="width: 64px; opacity: 0.5; margin-bottom: 15px;">
                <p>Nenhum usuário encontrado com "<strong><?php echo htmlspecialchars($termoBusca); ?></strong>".</p>
            </div>
        <?php endif; ?>

        <div class="users-grid">
            
            <?php foreach ($resultados as $usuario): ?>
                
                <?php 
                    $jaSigo = $usuarioModel->jaSegue($meuId, $usuario['id']); 

                    $caminhoFoto = "https://cdn-icons-png.flaticon.com/512/847/847969.png"; 
                    if (!empty($usuario['foto'])) {
                        $caminhoFoto = "assets/uploads/" . htmlspecialchars($usuario['foto']);
                    }
                ?>

                <div class="user-card">
                    <img src="<?php echo $caminhoFoto; ?>" alt="Foto de <?php echo $usuario['nome']; ?>">
                    
                    <div class="nome" title="<?php echo $usuario['nome']; ?>">
                        <?php echo $usuario['nome']; ?>
                    </div>
                    
                    <div class="username">
                        @<?php echo $usuario['username']; ?>
                    </div>

                    <form method="POST" action="pesquisar.php?busca=<?php echo htmlspecialchars($termoBusca); ?>" style="width: 100%;">
                        <input type="hidden" name="id_usuario_acao" value="<?php echo $usuario['id']; ?>">
                        
                        <?php if ($jaSigo): ?>
                            <input type="hidden" name="acao" value="deixar">
                            <button type="submit" class="btn-seguindo">Seguindo</button>
                        <?php else: ?>
                            <input type="hidden" name="acao" value="seguir">
                            <button type="submit" class="btn-seguir">Seguir</button>
                        <?php endif; ?>
                    </form>
                </div>

            <?php endforeach; ?>
        </div>

    </main>
</body>
</html>