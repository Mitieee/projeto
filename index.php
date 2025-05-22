<?php
include 'db.php';
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: conta.php");
    exit();
}
$email = $_SESSION['email'];
$sql = "SELECT * FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['emergencia'])) {
    $tipo_emergencia = $_POST['emergencia'];
    $id_usuario = $usuario['id'];
    $latitude = null;
    $longitude = null;
    $sql = "INSERT INTO emergencias (id_usuario, tipo_emergencia, latitude, longitude) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issd", $id_usuario, $tipo_emergencia, $latitude, $longitude);
    if ($stmt->execute()) {
        $id_emergencia = $stmt->insert_id;
        $_SESSION['id_emergencia'] = $id_emergencia;
        header("Location: usuario1.php");
        exit();
    } else {
        $erro = "Erro ao registrar emergência: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Chamada de Emergência</title>
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
        .img {
            height: 120px;
            width: 120px;
            border: 2px solid black;
            cursor: pointer;
        }
        input[type="radio"] {
            display: none;
        }
        label img {
            border: 2px solid black;
        }
        input[type="radio"]:checked + label img {
            border-color: red;
            transform: scale(1.1);
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
        .button2 {
            border: 2px solid black;
            font-size: 18px;
            font-weight: 600;
            border-radius: 8px;
            padding: 15px;
            width: 100%;
            cursor: pointer;
            border: 0;
        }
                .erro {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($usuario) { ?>
            <h2>Informações do Usuário</h2>
             <p><strong>Email:</strong> <?= htmlspecialchars($usuario['email']); ?></p>
            <p><strong>Telefone:</strong> <?= htmlspecialchars($usuario['telefone']); ?></p>
            <p><strong>Nome:</strong> <?= htmlspecialchars($usuario['nome']); ?></p>
            <p><strong>Data de Nascimento:</strong> 
<?php 
    if (!empty($usuario['data_nascimento'])) {
        echo htmlspecialchars(date('d/m/Y', strtotime($usuario['data_nascimento'])));
    }
?>
</p>
            <p><strong>Tipo:</strong> <?= htmlspecialchars($usuario['tipo_sanguineo']); ?></p>
            <p><strong>Peso:</strong> <?= number_format(floatval($usuario['peso']), 2, ',', '.'); ?> kg</p>
            <p><strong>Altura:</strong> <?= number_format(floatval($usuario['altura']), 2, ',', '.'); ?> m</p>
            <p><strong>Alergias:</strong> <?= htmlspecialchars($usuario['alergia']); ?></p>
            <p><strong>Informações:</strong> <?= htmlspecialchars($usuario['outras_informacoes']); ?></p>
        <?php } else { ?>
            <p>Nenhum cadastro encontrado. Por favor, faça seu cadastro.</p>
        <?php } ?>
        <button class="button2" type="button" onclick="window.location.href='editar.php'">Editar Dados Pessoais <img src="imagem/dados.png" style="height: 30px; vertical-align: middle;"> </button>
        <h2>Chamada de Emergência</h2>
        <?php if (isset($erro)) { echo "<p class='erro'>$erro</p>"; } ?>
        <form action="index.php" method="POST">
            <input type="radio" id="POLÍCIA 190" name="emergencia" value="POLÍCIA 190" onclick="mostrarMensagemPreDefinida()">
            <label for="POLÍCIA 190"><img class="img" src="imagem/policia.png" alt="POLÍCIA 190" title="POLÍCIA 190">
            </label>
            <input type="radio" id="BOMBEIRO 193" name="emergencia" value="BOMBEIRO 193" onclick="mostrarMensagemPreDefinida()">
            <label for="BOMBEIRO 193">
                <img class="img" src="imagem/bombeiro.png" alt="BOMBEIRO 193" title="BOMBEIRO 193">
            </label>
            <input type="radio" id="SAMU 192" name="emergencia" value="SAMU 192" onclick="mostrarMensagemPreDefinida()">
            <label for="SAMU 192">
                <img class="img" src="imagem/emergencia1.png" alt="SAMU 192" title="SAMU 192">
            </label>
            <input type="radio" id="GUARDA MUNICIPAL 153" name="emergencia" value="GUARDA MUNICIPAL 153" onclick="mostrarMensagemPreDefinida()">
            <label for="GUARDA MUNICIPAL 153">
                <img class="img" src="imagem/guardamunicipal.jpg" alt="GUARDA MUNICIPAL 153" title="GUARDA MUNICIPAL 153">
            </label>
            <input type="radio" id="POLÍCIA CIVIL 199" name="emergencia" value="POLÍCIA CIVIL 199" onclick="mostrarMensagemPreDefinida()">
            <label for="POLÍCIA CIVIL 199">
                <img class="img" src="imagem/policiacivil.jpg" alt="POLÍCIA CIVIL 199" title="POLÍCIA CIVIL 199">
            </label>
            <input type="radio" id="DISQUE DENÚNCIA 181" name="emergencia" value="DISQUE DENÚNCIA 181" onclick="mostrarMensagemPreDefinida()">
            <label for="DISQUE DENÚNCIA 181">
                <img class="img" src="imagem/disquedenúncia.jpg" alt="DISQUE DENÚNCIA 181" title="DISQUE DENÚNCIA 181">
            </label>
            <input type="radio" id="DIREITOS HUMANOS 100" name="emergencia" value="DIREITOS HUMANOS 100" onclick="mostrarMensagemPreDefinida()">
            <label for="DIREITOS HUMANOS 100">
                <img class="img" src="imagem/direitoshumanos.jpeg" alt="DIREITOS HUMANOS 100" title="DIREITOS HUMANOS 100">
            </label>
            <input type="radio" id="DELEGACIA DA MULHER 180" name="emergencia" value="DELEGACIA DA MULHER 180" onclick="mostrarMensagemPreDefinida()">
            <label for="DELEGACIA DA MULHER 180">
                <img class="img" src="imagem/delegaciadamulher.png" alt="DELEGACIA DA MULHER 180" title="DELEGACIA DA MULHER 180">
            </label>
            <input type="radio" id="CENTRO DE VALORIZAÇÃO DA VIDA (CVV) 141" name="emergencia" value="CENTRO DE VALORIZAÇÃO DA VIDA (CVV) 141" onclick="mostrarMensagemPreDefinida()">
            <label for="CENTRO DE VALORIZAÇÃO DA VIDA (CVV) 141">
                <img class="img" src="imagem/cvv.avif" alt="CENTRO DE VALORIZAÇÃO DA VIDA (CVV) 141" title="CENTRO DE VALORIZAÇÃO DA VIDA (CVV) 141">
            </label>
            <input type="radio" id="DELEGACIA ESPECIALIZADA NO ATENDIMENTO ÀS MULHERES 3462-6700" name="emergencia" value="DELEGACIA ESPECIALIZADA NO ATENDIMENTO ÀS MULHERES 3462-6700" onclick="mostrarMensagemPreDefinida()">
            <label for="DELEGACIA ESPECIALIZADA NO ATENDIMENTO ÀS MULHERES 3462-6700">
                <img class="img" src="imagem/delegaciadamulher.png" alt="DELEGACIA ESPECIALIZADA NO ATENDIMENTO ÀS MULHERES 3462-6700" title="DELEGACIA ESPECIALIZADA NO ATENDIMENTO ÀS MULHERES 3462-6700">
            </label>
            <input type="radio" id="CENTRAL DA PESSOA IDOSA 3236-1100" name="emergencia" value="CENTRAL DA PESSOA IDOSA 3236-1100" onclick="mostrarMensagemPreDefinida()">
            <label for="CENTRAL DA PESSOA IDOSA 3236-1100">
                <img class="img" src="imagem/pessoaidosa.jpg" alt="CENTRAL DA PESSOA IDOSA 3236-1100" title="CENTRAL DA PESSOA IDOSA 3236-1100">
            </label>
            <input type="radio" id="DEFESA CIVIL 3476-3400" name="emergencia" value="DEFESA CIVIL 3476-3400" onclick="mostrarMensagemPreDefinida()">
            <label for="DEFESA CIVIL 3476-3400">
                <img class="img" src="imagem/defesacivil.jpeg" alt="DEFESA CIVIL 3476-3400" title="DEFESA CIVIL 3476-3400">
            </label>
            <input type="radio" id="PLANTÃO 24HS (51) 99322-5764" name="emergencia" value="PLANTÃO 24HS (51) 99322-5764" onclick="mostrarMensagemPreDefinida()">
            <label for="PLANTÃO 24HS (51) 99322-5764">
                <img class="img" src="imagem/plantão.jpg" alt="PLANTÃO 24HS (51) 99322-5764" title="PLANTÃO 24HS (51) 99322-5764">
            </label>
            <p><strong><span id="mensagem"></span></strong></p>
            <button class="button2" type="submit">Enviar Emergência <img src="imagem/enviar.png" style="height: 30px; vertical-align: middle;"></button>
        </form>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var campos = ["telefone", "nome", "data_nascimento", "tipo", "peso", "altura", "alergias", "informacoes"];
            campos.forEach(function (campo) {
                var valor = localStorage.getItem(campo);
                if (valor) {
                    document.getElementById("usuario-" + campo).textContent = valor;
                }
            });
        });
        function mostrarMensagemPreDefinida() {
            const tipoEmergencia = document.querySelector('input[name="emergencia"]:checked');
            const mensagens = {
                'POLÍCIA 190': 'Chamo a polícia. ',
                'BOMBEIRO 193': 'Estou em um local com incêndio. Necessito de bombeiros.',
                'SAMU 192': 'Estou com uma emergência médica. Preciso de ajuda urgente.',
                'GUARDA MUNICIPAL 153': 'Estou com uma emergência. Preciso de apoio imediato.',
                'POLÍCIA CIVIL 199': 'Estou com uma emergência. Preciso de atendimento da Polícia Civil.',
                'DIREITOS HUMANOS 100': 'Estou em uma situação de violação de direitos. Preciso de ajuda.',
                'DELEGACIA DA MULHER 180': 'Estou em situação de violência. Preciso de apoio da Delegacia da Mulher.',
                'CENTRO DE VALORIZAÇÃO DA VIDA (CVV) 141': 'Preciso conversar. Estou em sofrimento emocional.',
                'DELEGACIA ESPECIALIZADA NO ATENDIMENTO ÀS MULHERES 3462-6700': 'Estou em situação de risco. Preciso de ajuda especializada.',
                'CENTRAL DA PESSOA IDOSA 3236-1100': 'Preciso de atendimento relacionado à pessoa idosa.',
                'DEFESA CIVIL 3476-3400': 'Estou em uma situação de risco. Preciso da Defesa Civil.',
                'PLANTÃO 24HS (51) 99322-5764': 'Estou em emergência. Preciso de ajuda imediata.'
            };
            if (tipoEmergencia) {
                document.getElementById('mensagem').innerText = mensagens[tipoEmergencia.value] || '';
            }
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