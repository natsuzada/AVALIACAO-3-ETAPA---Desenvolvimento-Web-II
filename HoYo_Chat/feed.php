<?php

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

session_start();
require_once 'config/conexao.php';
require_once 'controllers/postagem.php';
require_once 'models/usuario.php';

if (!isset($_SESSION['usuario_logado'])) {
    header('Location: index.php?erro=sessaoinvalida');
    exit;
}

$controller = new PostController($pdo);
$usuarioModel = new Usuario($pdo);

require_once 'models/post.php';
$postModelAux = new Post($pdo);

$controller->publicar();
$controller->gerenciarCurtida();

$dadosatualizados = $usuarioModel->pegarDados($_SESSION['usuario_logado']['id']);

if ($dadosatualizados) {
    $meuNome = $dadosatualizados['nome'];
    $meuUsername = $dadosatualizados['username'];
    $meuId = $dadosatualizados['id'];
    $minhaFoto = $dadosatualizados['foto'];
} else {
    header('Location: logout.php');
    exit;
}

$posts = $controller->carregarFeed();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Feed</title>
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
<body class="body-feed">

    <aside>
        <div class="sidebar-logo-container">
            <img src="/img/logo.png" alt="Logo" class="sidebar-logo-img">
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

        <div class="mini-perfil-card">
            <?php 
                $caminhoFotoPerfil = "https://cdn-icons-png.flaticon.com/512/847/847969.png"; 
                
                if (!empty($minhaFoto)) {
                    $caminhoFotoPerfil = "assets/uploads/" . htmlspecialchars($minhaFoto);
                }
            ?>
            
            <img src="<?php echo $caminhoFotoPerfil; ?>" alt="Foto" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;" />
            
            <div>
                <div class="nome" style="font-weight: bold;"><?php echo htmlspecialchars($meuNome); ?></div>
                <div class="usuario" style="color: #777;">@<?php echo htmlspecialchars($meuUsername); ?></div>
            </div>
            
            <div style="margin-left: auto;">
                <a href="perfil.php" class="btn-link" style="margin-top: 0; padding: 10px 25px; font-size: 1rem; border-radius: 30px;">Ver Perfil</a>
            </div>
        </div>

        <div class="campo-postagem">
            <form method="POST" action="feed.php">
                <textarea name="novoPost" placeholder="No que você está pensando, <?php echo explode(' ', $meuNome)[0]; ?>?" required style="border: none; background: #f0f2f5; padding: 15px; border-radius: 20px; height: 100px; width: 100%; box-sizing: border-box; resize: none; font-size: 1.1rem; font-family: 'Lato', sans-serif;"></textarea>
                
                <div style="text-align: right; margin-top: 10px;">
                    <button type="submit" style="border-radius: 20px; padding: 10px 30px; font-size: 1rem; font-weight: bold;">Publicar</button>
                </div>
            </form>
        </div>

        <div class="postagens">
            
            <?php if (empty($posts)): ?>
                <div class="campo-postagem" style="text-align: center; color: #777;">
                    <p>Ainda não há posts. Seja o primeiro!!</p>
                </div>
            <?php else: ?>
                
                <?php foreach ($posts as $post): ?>
                    <div class="postagem">
                        <div class="cabecalho">
                            
                            <?php 
                                $caminhoFotoPost = "https://cdn-icons-png.flaticon.com/512/847/847969.png";
                                if (!empty($post['foto'])) {
                                    $caminhoFotoPost = "assets/uploads/" . htmlspecialchars($post['foto']);
                                }
                            ?>

                            <img src="<?php echo $caminhoFotoPost; ?>" alt="User" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;" />

                            <div>
                                <strong><?php echo htmlspecialchars($post['nome']); ?></strong> 
                                <span style="color:#777; font-size: 0.9em;">@<?php echo htmlspecialchars($post['username']); ?></span>
                                <br>
                                <span style="font-size: 0.8em; color: #999;">
                                    <?php echo date('d/m/Y H:i', strtotime($post['data_criacao'])); ?>
                                </span>
                            </div>
                        </div>

                        <div class="conteudo" style="margin: 15px 0;">
                            <?php echo nl2br(htmlspecialchars($post['conteudo'])); ?>
                        </div>

                        <div class="interacoes" style="border-top: 1px solid #eee; padding-top: 10px; margin-top: 10px;">
                            <form method="POST" action="feed.php" style="display:inline;">
                                <input type="hidden" name="curtir" value="<?php echo $post['post_id']; ?>">
                                
                                <?php 
                                    $jaDeiLike = $postModelAux->jaCurtiu($meuId, $post['post_id']);
                                ?>

                                <?php if ($jaDeiLike): ?>
                                    <button type="submit" style="color: #d9534f; cursor: pointer; border:none; background:none; font-weight:bold; font-size: 0.9rem;">
                                        ❤️ Curtido (<?php echo $post['curtidas']; ?>)
                                    </button>
                                <?php else: ?>
                                    <button type="submit" style="color: #65676b; cursor: pointer; border:none; background:none; font-weight:bold; font-size: 0.9rem;">
                                        🤍 Curtir (<?php echo $post['curtidas']; ?>)
                                    </button>
                                <?php endif; ?>

                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </main>
</body>
</html>