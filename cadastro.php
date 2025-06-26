<?php
include 'db.php';

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    try {
        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
        $stmt->execute([$nome, $email, $senha]);
        $mensagem = "Cadastro realizado com sucesso. <a href='login.php'>Ir para o login</a>";
    } catch (PDOException $e) {
        $mensagem = "Erro ao cadastrar: " . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #ccc;
            background-image:
                linear-gradient(to right, #bbb 1px, transparent 1px),
                linear-gradient(to bottom, #bbb 1px, transparent 1px);
            background-size: 20px 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }

        .cadastro-container {
            background-color: #fefefe;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }

        .cadastro-container h2 {
            text-align: center;
            margin-bottom: 25px;
            font-weight: 400;
        }

        .cadastro-container input[type="text"],
        .cadastro-container input[type="email"],
        .cadastro-container input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin: 10px 0;
            border: 1px solid #aaa;
            border-radius: 6px;
            font-size: 1rem;
        }

        .cadastro-container button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: #fff;
            border: none;
            font-size: 1rem;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 15px;
            transition: background-color 0.3s ease;
        }

        .cadastro-container button:hover {
            background-color: #1e7e34;
        }

        .mensagem {
            margin-top: 15px;
            text-align: center;
            font-size: 0.95rem;
        }

        .mensagem a {
            color: #007bff;
            text-decoration: none;
        }

        .mensagem a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="cadastro-container">
        <h2>Cadastro</h2>

        <?php if (!empty($mensagem)): ?>
            <div class="mensagem"><?= $mensagem ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="text" name="nome" placeholder="Digite seu nome" required>
            <input type="email" name="email" placeholder="Digite seu e-mail" required>
            <input type="password" name="senha" placeholder="Digite sua senha" required>
            <button type="submit">Cadastrar</button>
        </form>
    </div>

</body>
</html>
