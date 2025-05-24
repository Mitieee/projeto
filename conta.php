<?php
include 'sql.php';
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $senha = $_POST["senha"];
    $sql = "SELECT id, email, senha FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['id_usuario'] = $usuario['id'];
            $stmt->close();
            $conn->close();
            header("Location: index.php");
            exit();
        } else {
            $error = "Senha incorreta.";
        }
    } else {
        $error = "Email não cadastrado. Por favor, cadastre-se primeiro.";
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" href="imagem/emergencia.png" type="image/x-icon">
    <style>
        body {
            display: flex;
    justify-content: center;
    align-items: center;     
    height: 100vh;          
    margin: 0;
    font-family: Arial;
        }
        .container {
            max-width: 400px;
            padding: 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .grupo {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .grupo img {
            width: 40px;
            height: 40px;
            margin-right: 10px;
        }
        input,
        select {
            width: 100%;
            padding: 15px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
        }
        button {
            font-size: 18px;
            font-weight: 600;
            background-color: #40DD00;
            border-radius: 8px;
            border: 0;
            padding: 15px;
            width: 100%;
            cursor: pointer;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        a {
            text-decoration: none;
            color: inherit;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="cadastro.php" style="text-decoration: none; font-size: 1.5em; color: green;">Cadastre-se</a> <br>
        <h2> Login</h2>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
<form method="POST" action="conta.php">
            <div class="grupo">
                <img src="imagem/email.png" alt="Ícone Email">
                <input type="email" name="email" id="email" placeholder="Seu e-mail" required>
            </div>
            <div class="grupo">
                <img src="imagem/senha.png" alt="Ícone Senha">
                <input type="password" name="senha" id="senha" placeholder="Sua senha" required>
            </div>
            <button type="submit">Entrar</button>
        </form>
     <Br>   <a href="senha.php" style="text-decoration: none; color: gray;">Esqueci minha senha</a>
    </div>
    <div vw class="enabled">
        <div vw-access-button class="active"></div>
        <div vw-plugin-wrapper>
          <div class="vw-plugin-top-wrapper"></div>
        </div>
    </div>
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script>
        new window.VLibras.Widget('https://vlibras.gov.br/app');
    </script>
</body>
</html>