<?php
include 'db.php';
session_start();
$apiKey = getenv('Key');
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['latitude_atendente']) && isset($_POST['longitude_atendente']))
 {
    if (!isset($_SESSION['id_usuario'])) {
        header("HTTP/1.1 401 Unauthorized");
        exit();
    }
    $latitude = $_POST['latitude_atendente'];
    $longitude = $_POST['longitude_atendente'];
    $id_emergencia = $_POST['id_emergencia'];
    $sql = "UPDATE emergencias SET latitude_atendente = ?, longitude_atendente = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ddi", $latitude, $longitude, $id_emergencia);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro']);
    }   
    $stmt->close();
    exit();
}
if (isset($_GET['status_acao'])) {
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_emergencia']) && isset($_POST['status'])) {
        $id_emergencia = $_POST['id_emergencia'];      
        if (isset($_POST['status'])) {
            $status = $_POST['status'];           
            if ($status === 'em_atendimento') {
                $sql = "UPDATE emergencias SET em_atendimento = TRUE, resolvida = FALSE WHERE id = ?";
            } elseif ($status === 'resolvida') {
                $sql = "UPDATE emergencias SET em_atendimento = FALSE, resolvida = TRUE WHERE id = ?";
            }            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id_emergencia);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['success' => true]);
            $conn->close();
            exit();
        }
    } elseif (isset($_GET['id_emergencia'])) {
        $id_emergencia = $_GET['id_emergencia'];
        $sql = "SELECT em_atendimento, resolvida FROM emergencias WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_emergencia);
        $stmt->execute();
        $result = $stmt->get_result();
        $status = $result->fetch_assoc();
        $stmt->close();
        echo json_encode($status);
        $conn->close();
        exit();
    }   
    echo json_encode(['success' => false]);
    $conn->close();
    exit();
}
if (isset($_GET['obter_localizacao_usuario1'])) {
    header('Content-Type: application/json');
    $id_emergencia = $_GET['id_emergencia'];
    $sql = "SELECT latitude, longitude FROM emergencias 
            WHERE id = ? AND id_usuario IN 
            (SELECT id_usuario FROM emergencias WHERE id = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_emergencia);
    $stmt->execute();
    $result = $stmt->get_result();
    $localizacao = $result->fetch_assoc();
    $stmt->close();
    echo json_encode($localizacao);
    exit();
}
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}
$sql = "SELECT e.*, u.nome, u.telefone, e.tipo_emergencia FROM emergencias e 
        JOIN usuarios u ON e.id_usuario = u.id 
        ORDER BY e.data_hora DESC";
$result = $conn->query($sql);
$emergencias = $result->fetch_all(MYSQLI_ASSOC);
$id_emergencia = $_GET['id_emergencia'] ?? null;
$emergencia_selecionada = null;
$mensagens = [];
if ($id_emergencia) {
    $sql = "SELECT e.*, u.nome, u.telefone, u.tipo_sanguineo, u.alergia, u.outras_informacoes 
            FROM emergencias e 
            JOIN usuarios u ON e.id_usuario = u.id 
            WHERE e.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_emergencia);
    $stmt->execute();
    $result = $stmt->get_result();
    $emergencia_selecionada = $result->fetch_assoc();
    $stmt->close();    
    $sql = "SELECT m.*, u.nome, u.telefone, e.tipo_emergencia 
            FROM mensagens m 
            JOIN usuarios u ON m.id_remetente = u.id 
            JOIN emergencias e ON m.id_emergencia = e.id
            WHERE m.id_emergencia = ? 
            ORDER BY m.data_hora";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_emergencia);
    $stmt->execute();
    $result = $stmt->get_result();
    $mensagens = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_emergencia']) && isset($_POST['resposta'])) {
    $id_emergencia = $_POST['id_emergencia'];
    $resposta = $_POST['resposta'];
    $id_remetente = $_SESSION['id_usuario'];
    $sql = "INSERT INTO mensagens (id_emergencia, id_remetente, resposta) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $id_emergencia, $id_remetente, $resposta);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
    $conn->close();
    exit(); 
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Usuário 2 - Localização Compartilhada</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="imagem/emergencia.png" type="image/x-icon">
    <style>
        #map { height: 450px; width: 100%; }
        body { display: flex; flex-direction: column; align-items: center; margin: 0; font-family: Arial }
        .container { max-width: 400px; padding: 20px; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); text-align: center; }
        .img { height: 30px; width: 30px; cursor: pointer; }
        .chat-box { margin-top: 20px; border: 1px solid #ddd; border-radius: 5px; padding: 10px; height: 200px; overflow-y: auto; background-color: #f9f9f9; }
        .chat-input { margin-top: 10px; display: flex; gap: 10px; }
        .chat-input input { flex: 1; padding: 5px; border: 1px solid #ddd; border-radius: 5px; }
        .chat-input button { padding: 5px 10px; border: none; background-color: #007BFF; color: white; border-radius: 5px; cursor: pointer; }
        .chat-input button:hover { background-color: #0056b3; }
        .emergencias-lista { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
        .emergencia-card { border: 1px solid #ddd; border-radius: 5px; padding: 10px; width: calc(33% - 20px); cursor: pointer; }
        .emergencia-card:hover { background-color: #f5f5f5; }
        .emergencia-card.selecionada { border-color: #007BFF; background-color: #e7f1ff; }
        .emergencia-card h3 { margin-top: 0; }
        .emergencia-info { display: flex; justify-content: space-between; }
        .status-buttons { margin-top: 10px; display: flex; gap: 10px; }
        .status-buttons button { padding: 5px 10px; border: none; border-radius: 5px; cursor: pointer; color: white; }
        .status-buttons .atendimento { background-color: #17a2b8; }
        .status-buttons .resolvida { background-color: #28a745; }
        .status { font-weight: bold; margin: 10px 0; }
        .status.atendimento { color: #17a2b8; }
        .status.resolvida { color: #28a745; }
        .info-usuario { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .info-usuario h3 { margin-top: 0; color: #007BFF; }
        .info-item { margin-bottom: 5px; }
        .message-content { margin-bottom: 5px; }
        .data-hora { font-size: 0.8em; 
        color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Usuário 2 - Localização Compartilhada</h1>
        <div class="emergencias-lista" id="emergencias-lista">
            <?php foreach ($emergencias as $emergencia): ?>              
                <div class="emergencia-card <?= $emergencia['id'] == $id_emergencia ? 'selecionada' : '' ?>" 
                     onclick="window.location.href='usuario2.php?id_emergencia=<?= $emergencia['id'] ?>'">
                    <h3><?= htmlspecialchars(ucfirst($emergencia['nome'])) ?></h3>
                    <div class="emergencia-info">
                        <span><?= date('d/m/Y H:i', strtotime($emergencia['data_hora'])) ?></span>
                    </div>   
                    <br><strong><?= htmlspecialchars($emergencia['tipo_emergencia']) ?></strong>    
                </div>
            <?php endforeach; ?>
        </div>       
        <?php if ($emergencia_selecionada): ?>
            <div class="status <?= $emergencia_selecionada['resolvida'] ? 'resolvida' : ($emergencia_selecionada['em_atendimento'] ? 'atendimento' : '') ?>">   
            </div>          
            <div class="info-usuario">
                <h3>Informações do Usuário</h3>
                <div class="info-item"><strong>Nome:</strong> <?= htmlspecialchars($emergencia_selecionada['nome']) ?></div>
                <div class="info-item"><strong>Telefone:</strong> <?= htmlspecialchars($emergencia_selecionada['telefone']) ?></div>
                <div class="info-item"><strong>Tipo Sanguíneo:</strong> <?= htmlspecialchars($emergencia_selecionada['tipo_sanguineo']) ?></div>
                <div class="info-item"><strong>Alergias:</strong> <?= htmlspecialchars($emergencia_selecionada['alergia'] ?: 'Nenhuma informada') ?></div>
                <div class="info-item"><strong>Outras Informações:</strong> <?= htmlspecialchars($emergencia_selecionada['outras_informacoes'] ?: 'Nenhuma informação adicional') ?></div>
            </div>            
            <div id="map"></div>
            <div class="chat-box" id="chat-box"></div>
            <div class="status-buttons">
                <button id="btn-atendimento" class="atendimento">
                    <img src="imagem/atendimento.png" class="img"> em atendimento
                </button>
                <button id="btn-resolvida" class="resolvida">
                    <img src="imagem/resolvida.png" class="img"> resolvida
                </button>
            </div>
            <form id="chat-form" class="chat-input">
                <input type="text" id="resposta" placeholder="Digite sua mensagem..." required>
                <button type="button" id="enviar-resposta">Enviar</button>
            </form>                   
        <?php else: ?>
            <p>selecione</p>
        <?php endif; ?>
    </div>
    <?php if ($emergencia_selecionada && $emergencia_selecionada['latitude'] && $emergencia_selecionada['longitude']): ?>
       <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apiKey; ?>&callback=initMap"></script>  
    <script>     
        let mapa, marcaUsuario1, marcaUsuario2;
        function initMap() {
            const coordenadas = {
                lat: <?= $emergencia_selecionada['latitude'] ?>,
                lng: <?= $emergencia_selecionada['longitude'] ?>
            };            
            mapa = new google.maps.Map(document.getElementById('map'), {
                center: coordenadas,
                zoom: 15
            });
            marcaUsuario1 = new google.maps.Marker({
                position: coordenadas,
                map: mapa,
                title: "usuário"
            });           
            new google.maps.InfoWindow({
                content: `
                    <div>
                        <strong>Telefone:</strong> <?= $emergencia_selecionada['telefone'] ?><br>
                        <strong>Nome:</strong> <?= $emergencia_selecionada['nome'] ?>
                    </div>
               `
            }).open(mapa, marcaUsuario1);
            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    (posicao) => {
                        const novaPosicao = {
                            lat: posicao.coords.latitude,
                            lng: posicao.coords.longitude
                        };
                        enviarLocalizacao2(novaPosicao.lat, novaPosicao.lng);
                        function enviarLocalizacao2(latitude, longitude) {
                            const formData = new FormData();
            formData.append('latitude_atendente', latitude);
            formData.append('longitude_atendente', longitude);
            formData.append('id_emergencia', <?= $emergencia_selecionada['id'] ?>);  
            fetch('usuario2.php', {
                method: 'POST',
                body: formData
            })
.then(response => {
                if (!response.ok) throw new Error('Erro na rede');
                return response.text();
            })
            .then(data => console.log('Localização atualizada:', data))
            .catch(error => console.error("Erro ao enviar localização:", error));
        }
                        if (!marcaUsuario2) {
                            marcaUsuario2 = new google.maps.Marker({
                                position: novaPosicao,
                                map: mapa,
                                title: "Você está aqui"
                            });
                            new google.maps.InfoWindow({ content: "Você está aqui" }).open(mapa, marcaUsuario2);
                        } else {
                            marcaUsuario2.setPosition(novaPosicao);
                        }
                    },
                    (erro) => console.error("Erro ao obter localização: ", erro),
                    { enableHighAccuracy: true }
                );
            } else {
                alert("Geolocalização não suportada pelo seu navegador.");
            }
            setInterval(atualizarLocalizacaoUsuario1, 3000);
        }
        function atualizarLocalizacaoUsuario1() {
            fetch(`usuario2.php?obter_localizacao_usuario1=1&id_emergencia=<?= $id_emergencia ?>`)
                .then(response => response.json())
                .then(localizacao => {
                    if (localizacao && localizacao.latitude && localizacao.longitude) {
                        const coordenadas = {
                            lat: parseFloat(localizacao.latitude),
                            lng: parseFloat(localizacao.longitude)
                        };
                        if (marcaUsuario1) {
                            marcaUsuario1.setPosition(coordenadas);
                        }
                    }
                })
                .catch(error => console.error("Erro ao obter localização:", error));
        }
        document.getElementById('enviar-resposta').addEventListener('click', function() {
            const respostaInput = document.getElementById('resposta');
            const resposta = respostaInput.value.trim();          
            if (resposta === '') return;            
            fetch('usuario2.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_emergencia=<?= $id_emergencia ?>&resposta=${encodeURIComponent(resposta)}`
            }).then(response => {
                if (response.ok) {
                    respostaInput.value = '';
                    atualizarChat();
                }
            }).catch(error => console.error("Erro ao enviar resposta:", error));
        }); 
        function atualizarChat() {
            fetch(`usuario1.php?id_emergencia=<?= $id_emergencia ?>&obter_mensagens=1`)
                .then(response => response.json())
                .then(mensagens => {
                    const chatBox = document.getElementById('chat-box');           
                    const mensagensAtuais = {};
                    mensagens.forEach(msg => mensagensAtuais[msg.id] = msg);           
                    mensagens.forEach(msg => {
                        let elementoMsg = document.getElementById(`msg-${msg.id}`);
                        if (!elementoMsg) {
                            elementoMsg = document.createElement('div');
                            elementoMsg.className = 'chat-message';
                            elementoMsg.id = `msg-${msg.id}`;
                            chatBox.appendChild(elementoMsg);
                        }
                        if (msg.mensagem) {
                            elementoMsg.innerHTML = `
                                <div class="message-content">
                                    <strong>${msg.telefone}:</strong> ${msg.mensagem}
                                </div>
                                <div class="data-hora">${new Date(msg.data_hora).toLocaleString()}</div>
                            `;
                        } else if (msg.resposta) {
                            elementoMsg.innerHTML = `
                                <div class="message-content">
                                    <strong><?= htmlspecialchars(ucfirst($emergencia_selecionada['tipo_emergencia'])) ?>:</strong> ${msg.resposta}
                                </div>
                                <div class="data-hora">${new Date(msg.data_hora).toLocaleString()}</div>
                            `;
                        }
                    });
                    const elementosMsg = chatBox.querySelectorAll('.chat-message');
                    elementosMsg.forEach(elemento => {
                        const id = parseInt(elemento.id.split('-')[1]);
                        if (!mensagensAtuais[id]) {
                            elemento.remove();
                        }
                    });           
                    chatBox.scrollTop = chatBox.scrollHeight;
                }).catch(error => console.error("Erro ao atualizar chat:", error));
        }
        setInterval(atualizarChat, 300);
        document.getElementById('btn-atendimento').addEventListener('click', function() {
            enviarStatus('em_atendimento');
        });
        document.getElementById('btn-resolvida').addEventListener('click', function() {
            enviarStatus('resolvida');
        });
        function enviarStatus(status) {
            fetch('usuario2.php?status_acao=1', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_emergencia=<?= $id_emergencia ?>&status=${status}`
            }).then(response => {
                if (response.ok) {
                    verificarStatus();
                }
            }).catch(error => console.error("Erro ao atualizar status:", error));
        }
        function verificarStatus() {
            fetch(`usuario2.php?status_acao=1&id_emergencia=<?= $id_emergencia ?>`)
                .then(response => response.json())
                .then(status => {
                    const statusDiv = document.querySelector('.status')           
                    if (status.resolvida) {
                        statusDiv.textContent = 'status: resolvida';
                        statusDiv.className = 'status resolvida';
                    } else if (status.em_atendimento) {
                        statusDiv.textContent = 'status: em atendimento';
                        statusDiv.className = 'status atendimento';
                    } else {
                        statusDiv.textContent = 'status: aguardando atendimento';
                        statusDiv.className = 'status';
                    }
                }).catch(error => console.error("Erro ao verificar status:", error));
        }
        setInterval(verificarStatus, 300);
            document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', e => e.preventDefault());
        });
    </script>
    <?php endif; ?>
    <div vw class="enabled">
        <div vw-access-button class="active"></div>
        <div vw-plugin-wrapper>
            <div class="vw-plugin-top-wrapper"></div>
        </div>
    </div>
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script>new window.VLibras.Widget('https://vlibras.gov.br/app');</script>
</body>
</html>