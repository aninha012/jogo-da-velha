<?php
session_start();
include 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

define('IA_ID', -1);
$userId = $_SESSION['usuario_id'];

function getNomeJogador($id, $conn) {
    if ($id === null || $id == IA_ID) {
        return "IA";
    }
    $sql = "SELECT nome FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ? $user['nome'] : "Desconhecido";
}

$sql = "SELECT * FROM partidas WHERE jogador1_id = ? OR jogador2_id = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute([$userId, $userId]);
$partidas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Histórico de Partidas</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #ccc;
            background-image:
                linear-gradient(to right, #bbb 1px, transparent 1px),
                linear-gradient(to bottom, #bbb 1px, transparent 1px);
            background-size: 20px 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            text-align: center;
        }
        h2 {
            margin-top: 30px;
            font-size: 2rem;
            font-weight: 400;
            color: #222;
        }
        table {
            margin-top: 20px;
            border-collapse: collapse;
            width: 90%;
            max-width: 800px;
            background-color: #f0f0f0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        thead {
            background-color: #007bff;
        }
        thead th {
            padding: 12px 8px;
            color: #fff;
            font-size: 1.1rem;
            border-bottom: 2px solid #0056b3;
        }
        tbody tr:nth-child(even) {
            background-color: #e9e9e9;
        }
        tbody tr:nth-child(odd) {
            background-color: #fafafa;
        }
        tbody td {
            padding: 10px 8px;
            font-size: 1rem;
            border-bottom: 1px solid #ddd;
        }
        tbody tr:last-child td {
            border-bottom: none;
        }
        a.btn-voltar {
            display: inline-block;
            margin: 30px 0;
            font-size: 1rem;
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            border: 2px solid #007bff;
            border-radius: 6px;
            transition: background-color 0.2s, color 0.2s;
        }
        a.btn-voltar:hover {
            background-color: #007bff;
            color: #fff;
        }
        .replay-link {
            text-decoration: none;
            color: #28a745;
            font-weight: bold;
            font-size: 0.95rem;
        }
        .replay-link:hover {
            color: #1e7e34;
        }
    </style>
</head>
<body>

    <h2>Histórico de Partidas de <?= htmlspecialchars($_SESSION['usuario_nome']) ?></h2>

    <table>
        <thead>
            <tr>
                <th>Jogador 1</th>
                <th>Jogador 2</th>
                <th>Vencedor</th>
                <th>Replay</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($partidas) === 0): ?>
                <tr>
                    <td colspan="4">Nenhuma partida encontrada.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($partidas as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars(getNomeJogador($p['jogador1_id'], $conn)) ?></td>
                        <td><?= htmlspecialchars(getNomeJogador($p['jogador2_id'], $conn)) ?></td>
                        <td>
                            <?php
                            if ($p['vencedor_id'] === null) {
                                echo "Empate";
                            }
                            elseif ($p['vencedor_id'] == $userId) {
                                echo "Vitória";
                            }
                            else {
                                echo "Derrota";
                            }
                            ?>
                        </td>
                        <td>
                            <a class="replay-link" href="replay.php?id=<?= $p['id'] ?>">▶️ Ver Replay</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="index.php" class="btn-voltar">Voltar ao Jogo</a>

</body>
</html>
