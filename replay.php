<?php
include 'db.php';
session_start();

if (!isset($_GET['id'])) {
    echo "ID da partida não especificado.";
    exit;
}

$partidaId = $_GET['id'];

// Pega todos os movimentos da partida na ordem correta
$stmt = $conn->prepare("SELECT * FROM movimentos WHERE partida_id = ? ORDER BY ordem ASC");
$stmt->execute([$partidaId]);
$movimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Replay da Partida</title>
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
        h2 {
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
        .celula {
            width: 120px;
            height: 120px;
            font-size: 42px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: 600;
            background-color: #fff;
            border: 2px solid #888;
            border-radius: 8px;
            line-height: 120px;
            cursor: default;
            transition: background-color 0.2s, transform 0.1s, border-color 0.2s;
        }
.botao-replay {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 18px;
    padding: 12px 30px;
    border: none;
    border-radius: 8px;
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: #fff;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    margin-top: 50px; /* Aumentado */
}
.botao-replay:hover {
    background: linear-gradient(135deg, #0056b3, #003d80);
    transform: translateY(-2px);
}
.botao-replay:active {
    transform: scale(0.97);
}
.botao-replay span {
    transition: transform 0.2s;
}
.botao-replay:hover span {
    transform: translateX(2px);
}


    </style>
</head>
<body>


<h2>Replay da Partida #<?= htmlspecialchars($partidaId) ?></h2>

<div class="tabuleiro" id="tabuleiro">
    <?php for ($i = 0; $i < 9; $i++): ?>
        <div class="celula" id="celula-<?= $i ?>"></div>
    <?php endfor; ?>
</div>

<button class="botao-replay" onclick="reproduzir()">
    ▶️ <span>Reproduzir</span>
</button>

<script>
    // Passa os movimentos do PHP para o JS em formato JSON
    const movimentos = <?= json_encode($movimentos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    let index = 0;
    let tabuleiro = Array(9).fill('');
    let emReproducao = false;

    function atualizarTabuleiro() {
        for (let i = 0; i < 9; i++) {
            document.getElementById('celula-' + i).innerText = tabuleiro[i];
        }
    }

    function reproduzir() {
        if (emReproducao) return;

        if (!movimentos || movimentos.length === 0) {
            alert("Nenhum movimento encontrado para esta partida.");
            return;
        }

        // Resetar o tabuleiro e variáveis
        index = 0;
        tabuleiro = Array(9).fill('');
        atualizarTabuleiro();
        emReproducao = true;

        function passo() {
            if (index >= movimentos.length) {
                emReproducao = false;
                return;
            }

            const mov = movimentos[index];

            // Validação simples da posição
            if (typeof mov.posicao === "undefined" || mov.posicao < 0 || mov.posicao > 8) {
                console.error("Posição inválida no movimento:", mov);
                emReproducao = false;
                return;
            }

            // Alterna entre 'X' e 'O' para cada movimento
            tabuleiro[mov.posicao] = (index % 2 === 0) ? 'X' : 'O';
            atualizarTabuleiro();
            index++;

            setTimeout(passo, 700);
        }

        passo();
    }
</script>

</body>
</html>
