<?php
include 'db.php';
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: conta.php");
    exit();
}
$email = $_SESSION['email'];
$error = '';
$sql = "SELECT * FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
   $stmt = $conn->prepare("UPDATE usuarios SET telefone = ?, nome = ?, data_nascimento = ?, tipo_sanguineo = ?, peso = ?, altura = ?, alergia = ?, outras_informacoes = ? WHERE email = ?");
$stmt->bind_param("sssssdsss", $telefone, $nome, $data_nascimento, $tipo_sanguineo, $peso, $altura, $alergias, $informacoes, $email);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Dados atualizados com sucesso!";
        header("Location: index.php");
        exit();
    } else {
        $error = "Erro ao atualizar: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Perfil</title>
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
        .error {
            color: red;
            margin-bottom: 15px;
        }
        .success {
            color: green;
            margin-bottom: 15px;
        }
        .email {
            background-color: #f0f0f0;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-weight: bold;
        }
           a {
            text-decoration: none;
            color: inherit;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="cadastro.php" style="text-decoration: none; font-size: 1.5em;">Criar conta</a> <br>
        <h2>Editar Dados</h2>
        <div class="email">
            Email: <?php echo htmlspecialchars($usuario['email']); ?>
        </div>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="editar.php" method="POST" onsubmit="return validarFormulario()">
                    <div class="grupo"> 
            </div>
            <div class="grupo">
                <img src="imagem/telefone.png" alt="Ícone Telefone">
               <input type="tel" name="telefone" id="telefone" placeholder="00000000000" value="<?php echo htmlspecialchars($usuario['telefone']); ?>" required>
            </div>
        <div class="grupo">
                <img src="imagem/nome.png" alt="Ícone Nome">
                <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" placeholder="Nome completo" required>
            </div>
            <div class="grupo">
                <img src="imagem/nascimento.png" alt="Ícone Data de Nascimento">
                <input type="date" name="data_nascimento" id="data_nascimento" value="<?php echo htmlspecialchars($usuario['data_nascimento']); ?>" required>
            </div>
            <div class="grupo">
                <img src="imagem/sangue.avif" alt="Ícone Tipo Sanguíneo">
                <select name="tipo" id="tipo" required>
                    <option value="" disabled>Tipo sanguíneo</option>
                    <?php
                    $tipos = ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"];
                    foreach ($tipos as $t) {
                        $selected = ($t == $usuario['tipo_sanguineo']) ? 'selected' : '';
                        echo "<option value='$t' $selected>$t</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="grupo">
                <img src="imagem/peso.png" alt="Ícone Peso">
                <input type="text" name="peso" id="peso" value="<?php echo htmlspecialchars(str_replace(".", ",", $usuario['peso'])); ?>" placeholder="Peso (kg)" required>
            </div>
            <div class="grupo">
                <img src="imagem/altura.png" alt="Ícone Altura">
                <input type="text" name="altura" id="altura" value="<?php echo htmlspecialchars(str_replace(".", ",", $usuario['altura'])); ?>" placeholder="Altura (cm)" required>
            </div>
            <div class="grupo">
            <label for="alergias">Alguma alergia? </label> 
            <input type="text" name="alergias" id="alergias"><br><br>
            </div>
            <div class="grupo">
                <label for="informacoes">Outras informações </label>
            <input type="text" name="informacoes" id="informacoes"><br><br>
            </div>
            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
    <script>
        function validarFormulario() {
            const telefone = document.getElementById('telefone').value;
            const nome = document.getElementById('nome').value;
            const dataNascimento = document.getElementById('data_nascimento').value;
            const tipo = document.getElementById('tipo').value;
            const peso = document.getElementById('peso').value;
            const altura = document.getElementById('altura').value;
            const telefoneRegex = /^(?:\d{11})$/;
            const apenasPesoRegex = /^\d{1,3},\d{2}$/;
            const alturaRegex = /^\d{1,3},\d{2}$/;
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