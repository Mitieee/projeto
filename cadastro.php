<?php
include 'db.php';
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);
    $telefone = $_POST["telefone"];
    $nome = $_POST["nome"];
    $data_nascimento = $_POST["data_nascimento"];
    $tipo_sanguineo = $_POST["tipo"];
    $peso = str_replace(",", ".", $_POST["peso"]); 
    $altura = str_replace(",", ".", $_POST["altura"]);
    $peso = floatval($peso);
    $altura = floatval($altura);
    $alergias = $_POST["alergias"] ?? null;
    $informacoes = $_POST["informacoes"] ?? null;
    $sql_check = "SELECT email FROM usuarios WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        $error = "Erro: Este email já está cadastrado.";
        $stmt_check->close();
    } else {
        $stmt_check->close();
        $stmt = $conn->prepare("INSERT INTO usuarios (email, senha, telefone, nome, data_nascimento, tipo_sanguineo, peso, altura, alergia, outras_informacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssddss", $email, $senha, $telefone, $nome, $data_nascimento, $tipo_sanguineo, $peso, $altura, $alergias, $informacoes);
        if ($stmt->execute()) {
            session_start();
            $_SESSION['email'] = $email; 
           $id_usuario = $conn->insert_id;
            $_SESSION['id_usuario'] = $id_usuario;
            header("Location: index.php");
            exit();
        } else {
            $error = "Erro ao cadastrar: " . $stmt->error;
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <link rel="icon" href="imagem/emergencia.png" type="image/x-icon">
    <style>
        body {
            display: flex;
            justify-content: center;
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
        button:hover {
            background-color: #36C200;
        }
        a {
            text-decoration: none;
            color: inherit;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="conta.php" style="text-decoration: none; font-size: 1.5em; color: green;">Fazer login</a><br><h2>Cadastro</h2>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="cadastro.php" method="POST" onsubmit="return validarFormulario()">
            <div class="grupo">
                <img src="imagem/email.png" alt="Ícone Email">
                <input type="email" name="email" id="email" placeholder="Seu email" required>
            </div>
            <div class="grupo">
                <img src="imagem/senha.png" alt="Ícone Senha">
                <input type="password" name="senha" id="senha" placeholder="Sua senha" required>
            </div>
            <div class="grupo">
                <img src="imagem/telefone.png" alt="Ícone Telefone">
                <input type="tel" name="telefone" id="telefone" placeholder="00000000000" required>
            </div>
            <div class="grupo">
                <img src="imagem/nome.png" alt="Ícone Nome">
                <input type="text" name="nome" id="nome" placeholder="Nome completo" required>
            </div>
            <div class="grupo">
                <img src="imagem/nascimento.png" alt="Ícone Data de Nascimento">
                <input type="date" name="data_nascimento" id="data_nascimento" required>
            </div>
            <div class="grupo">
                <img src="imagem/sangue.avif" alt="Ícone Tipo Sanguíneo">
                <select name="tipo" id="tipo" required>
                    <option value="" disabled selected>Tipo sanguíneo</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                </select>
            </div>
            <div class="grupo">
                <img src="imagem/peso.png" alt="Ícone Peso">
                <input type="text" name="peso" id="peso" placeholder="Peso (kg)" required>
            </div>
            <div class="grupo">
                <img src="imagem/altura.png" alt="Ícone Altura">
                <input type="text" name="altura" id="altura" placeholder="Altura (cm)" required>
            </div>
            <div class="grupo">
                <label for="alergias">Alguma alergia? </label> 
                <input type="text" name="alergias" id="alergias"><br><br>
            </div>
            <div class="grupo">
                <label for="informacoes">Outras informações </label>
                <input type="text" name="informacoes" id="informacoes"><br><br>
            </div>
            <button type="submit">Cadastrar</button>
        </form>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const campos = ["email", "senha", "telefone", "nome", "data_nascimento", "tipo", "peso", "altura", "alergias", "informacoes"];
            campos.forEach(campo => {
                const valor = localStorage.getItem(campo);
                if (valor) {
                    document.getElementById(campo).value = valor;
                }
            });
        });
        document.querySelectorAll("input, select").forEach(elemento => {
            elemento.addEventListener("input", (e) => {
                localStorage.setItem(e.target.id, e.target.value);
            });
        });
        function validarFormulario() {
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            const telefone = document.getElementById('telefone').value;
            const nome = document.getElementById('nome').value;
            const dataNascimento = document.getElementById('data_nascimento').value;
            const tipo = document.getElementById('tipo').value;
            const peso = document.getElementById('peso').value;
            const altura = document.getElementById('altura').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const telefoneRegex = /^(?:\d{11})$/;
            const apenasPesoRegex = /^\d{1,3},\d{2}$/;
            const alturaRegex = /^\d{1,3},\d{2}$/;
            if (!emailRegex.test(email)) {
                alert("Por favor, insira um email válido.");
                return false;
            }
            if (senha.length < 6) {
                alert("A senha deve ter pelo menos 6 caracteres.");
                return false;
            }
            if (!telefoneRegex.test(telefone)) {
                alert("Por favor, telefone deve conter 11 digítos.");
                return false;
            }
            if (!nome.trim()) {
                alert("O campo 'Nome' é obrigatório.");
                return false;
            }
            if (!dataNascimento.trim()) {
                alert("O campo 'Data de Nascimento' é obrigatório.");
                return false;
            }
            if (!tipo) {
                alert("Por favor, selecione um tipo sanguíneo.");
                return false;
            }
            if (!apenasPesoRegex.test(peso)) {
                alert("O campo 'Peso' deve conter apenas números no formato X,XX.");
                return false;
            }
            if (!alturaRegex.test(altura)) {
                alert("O campo 'Altura' deve estar no formato X,XX.");
                return false;
            }
            return true;
        }
    </script>
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