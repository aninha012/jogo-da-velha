<?php
session_start();
include 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

define('IA_ID', -1);

if (!isset($_SESSION['partida_id'])) {
    $stmt = $conn->prepare(
        "INSERT INTO partidas (jogador1_id, jogador2_id) VALUES (?, ?)"
    );
    $stmt->execute([$_SESSION['usuario_id'], IA_ID]);
    $_SESSION['partida_id'] = $conn->lastInsertId();
    $_SESSION['movimentos_ordem'] = 1;
}

function salvarPartida($partidaId, $vencedorId, $conn) {
    $stmt = $conn->prepare(
        "UPDATE partidas SET vencedor_id = ? WHERE id = ?"
    );
    $stmt->execute([$vencedorId, $partidaId]);
}

function verificarVencedor($tab) {
    $comb = [
        [0,1,2], [3,4,5], [6,7,8],
        [0,3,6], [1,4,7], [2,5,8],
        [0,4,8], [2,4,6]
    ];
    foreach ($comb as $c) {
        if ($tab[$c[0]] !== '' &&
            $tab[$c[0]] == $tab[$c[1]] &&
            $tab[$c[1]] == $tab[$c[2]]) {
            return $tab[$c[0]];
        }
    }
    if (!in_array('', $tab)) return 'empate';
    return null;
}

function iaJogar(&$tabuleiro) {
    $bestScore = -1000;
    $bestMove = -1;

    for ($i = 0; $i < 9; $i++) {
        if ($tabuleiro[$i] == '') {
            $tabuleiro[$i] = 'O';
            $score = minimax($tabuleiro, 0, false);
            $tabuleiro[$i] = '';
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMove = $i;
            }
        }
    }

    if ($bestMove != -1) {
        $tabuleiro[$bestMove] = 'O';
    }
    return $bestMove;
}

function minimax($tab, $depth, $isMaximizing) {
    $result = verificarVencedor($tab);
    if ($result !== null) {
        if ($result == 'O') return 10 - $depth;
        if ($result == 'X') return $depth - 10;
        if ($result == 'empate') return 0;
    }

    if ($isMaximizing) {
        $bestScore = -1000;
        for ($i = 0; $i < 9; $i++) {
            if ($tab[$i] == '') {
                $tab[$i] = 'O';
                $score = minimax($tab, $depth + 1, false);
                $tab[$i] = '';
                $bestScore = max($score, $bestScore);
            }
        }
        return $bestScore;
    } else {
        $bestScore = 1000;
        for ($i = 0; $i < 9; $i++) {
            if ($tab[$i] == '') {
                $tab[$i] = 'X';
                $score = minimax($tab, $depth + 1, true);
                $tab[$i] = '';
                $bestScore = min($score, $bestScore);
            }
        }
        return $bestScore;
    }
}

if (!isset($_SESSION['tabuleiro'])) {
    $_SESSION['tabuleiro'] = array_fill(0, 9, '');
    $_SESSION['fim'] = false;
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['posicao']) && !$_SESSION['fim']) {
        $pos = (int)$_POST['posicao'];
        if ($_SESSION['tabuleiro'][$pos] == '') {
            $_SESSION['tabuleiro'][$pos] = 'X';

            $stmtMov = $conn->prepare(
                "INSERT INTO movimentos (partida_id, jogador_id, posicao, ordem) VALUES (?, ?, ?, ?)"
            );
            $stmtMov->execute([
                $_SESSION['partida_id'],
                $_SESSION['usuario_id'],
                $pos,
                $_SESSION['movimentos_ordem']
            ]);
            $_SESSION['movimentos_ordem']++;

            $vencedor = verificarVencedor($_SESSION['tabuleiro']);
            if ($vencedor) {
                $_SESSION['fim'] = true;
                if ($vencedor == 'X') {
                    salvarPartida($_SESSION['partida_id'], $_SESSION['usuario_id'], $conn);
                    $mensagem = "Você venceu!";
                } elseif ($vencedor == 'O') {
                    salvarPartida($_SESSION['partida_id'], IA_ID, $conn);
                    $mensagem = "IA venceu!";
                } else {
                    salvarPartida($_SESSION['partida_id'], null, $conn);
                    $mensagem = "Deu velha!";
                }
            } else {
                $bestMove = iaJogar($_SESSION['tabuleiro']);
                $stmtMov->execute([
                    $_SESSION['partida_id'],
                    null,
                    $bestMove,
                    $_SESSION['movimentos_ordem']
                ]);
                $_SESSION['movimentos_ordem']++;

                $vencedor = verificarVencedor($_SESSION['tabuleiro']);
                if ($vencedor) {
                    $_SESSION['fim'] = true;
                    if ($vencedor == 'X') {
                        salvarPartida($_SESSION['partida_id'], $_SESSION['usuario_id'], $conn);
                        $mensagem = "Você venceu!";
                    } elseif ($vencedor == 'O') {
                        salvarPartida($_SESSION['partida_id'], IA_ID, $conn);
                        $mensagem = "IA venceu!";
                    } else {
                        salvarPartida($_SESSION['partida_id'], null, $conn);
                        $mensagem = "Deu velha!";
                    }
                }
            }
        }
    } elseif (isset($_POST['reset'])) {
        unset($_SESSION['partida_id'], $_SESSION['movimentos_ordem']);
        $_SESSION['tabuleiro'] = array_fill(0, 9, '');
        $_SESSION['fim'] = false;
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Jogo da Velha contra IA</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background-color: #ccc;
            background-image:
                linear-gradient(to right, #bbb 1px, transparent 1px),
                linear-gradient(to bottom, #bbb 1px, transparent 1px);
            background-size: 20px 20px;
            text-align: center;
            color: #333;
        }
        h2, h3 {
            margin: 8px;
            font-weight: 400;
        }
        h2 a {
            text-decoration: none;
            color: #007bff;
            font-weight: 500;
        }
        h2 a:hover {
            text-decoration: underline;
        }
        h3 {
            font-size: 1.4em;
            color: #444;
        }
        .tabuleiro {
            display: grid;
            grid-template-columns: repeat(3, 120px);
            gap: 12px;
            background-color: #f0f0f0;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-top: 20px;
        }
        .tabuleiro button {
            width: 120px;
            height: 120px;
            font-size: 42px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: 600;
            background-color: #fff;
            border: 2px solid #888;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s, border-color 0.2s;
        }
        .tabuleiro button:hover:not([disabled]) {
            background-color: #e8e8e8;
            border-color: #666;
        }
        .tabuleiro button:active:not([disabled]) {
            transform: scale(0.96);
        }
        .tabuleiro button[disabled] {
            background-color: #ddd;
            border-color: #aaa;
            cursor: default;
            color: #555;
        }
        .botao-reset {
            font-size: 18px;
            padding: 10px 24px;
            border: none;
            border-radius: 6px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
            margin-top: 30px;
        }
        .botao-reset:hover {
            background-color: #0069d9;
        }
        .botao-reset:active {
            transform: scale(0.96);
        }
        a.historico {
            margin-top: 25px;
            font-size: 16px;
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }
        a.historico:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h2>Bem-vindo, <?= htmlspecialchars($_SESSION['usuario_nome']) ?>! <a href="logout.php">Sair</a></h2>

    <div class="tabuleiro">
        <?php for ($i = 0; $i < 9; $i++): ?>
            <form method="post">
                <input type="hidden" name="posicao" value="<?= $i ?>">
                <button type="submit" class="casa" <?= $_SESSION['tabuleiro'][$i] !== '' || $_SESSION['fim'] ? 'disabled' : '' ?>>
                    <?= htmlspecialchars($_SESSION['tabuleiro'][$i]) ?>
                </button>
            </form>
        <?php endfor; ?>
    </div>

    <form method="post">
    <input type="hidden" name="reset" value="1">
    <button type="submit" class="botao-reset">🔄 Reiniciar Jogo</button>
</form>

    <?php if ($mensagem !== ''): ?>
        <p class="mensagem"><?= $mensagem ?></p>
    <?php endif; ?>

    <a href="historico.php" class="historico">Ver Histórico de Partidas</a>
</body>
</html>