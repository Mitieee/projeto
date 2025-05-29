<?php
include 'db.php';
session_start();
 $apiKey = getenv('Key');
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['latitude']) && isset($_POST['longitude']) && isset($_POST['atualizar_localizacao'])) {
    if (!isset($_SESSION['id_usuario'])) {
        header("HTTP/1.1 401 Unauthorized");
        exit();
    }
    $id_usuario = $_SESSION['id_usuario'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $sql = "UPDATE emergencias 
            SET latitude = ?, longitude = ? 
            WHERE id_usuario = ? AND resolvida = FALSE 
            ORDER BY data_hora DESC 
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ddi", $latitude, $longitude, $id_usuario);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo json_encode(array('success' => true, 'message' => 'Localização atualizada'));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Nenhuma emergência ativa'));
    }
    $stmt->close();
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['mensagem']) && isset($_POST['atualizar_mensagem'])) {
    $id = $_POST['id'];
    $mensagem = $_POST['mensagem'];    
    $sql = "SELECT id_remetente FROM mensagens WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $mensagem_db = $result->fetch_assoc();
        if ($mensagem_db['id_remetente'] == $_SESSION['id_usuario']) {
            $sql = "UPDATE mensagens SET mensagem = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $mensagem, $id);
            $stmt->execute();
        }
    }
    $stmt->close();
    exit();
}
if (isset($_GET['id_emergencia']) && isset($_GET['obter_mensagens'])) {
    header('Content-Type: application/json');
    $id_emergencia = $_GET['id_emergencia'];
    $ultima_id = $_GET['ultima_id'] ?? 0;  
    $sql = "SELECT m.*, u.nome, u.telefone FROM mensagens m 
            JOIN usuarios u ON m.id_remetente = u.id 
            WHERE m.id_emergencia = ? AND m.id > ?
            ORDER BY m.data_hora";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_emergencia, $ultima_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $mensagens = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    echo json_encode($mensagens);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_emergencia']) && isset($_POST['mensagem'])) {
    $id_emergencia = $_POST['id_emergencia'];
    $mensagem = $_POST['mensagem'];
    $id_remetente = $_SESSION['id_usuario'];
    $sql = "INSERT INTO mensagens (id_emergencia, id_remetente, mensagem) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $id_emergencia, $id_remetente, $mensagem);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => true]);
    exit(); 
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];   
    $sql = "SELECT id_remetente FROM mensagens WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $mensagem_db = $result->fetch_assoc();
        
        if ($mensagem_db['id_remetente'] == $_SESSION['id_usuario']) {
            $sql = "DELETE FROM mensagens WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
        }
    } 
    $stmt->close();
    exit();
}

if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_emergencia'])) {
    header("Location: index.php");
    exit();
}
$id_usuario = $_SESSION['id_usuario'];
$id_emergencia = $_SESSION['id_emergencia'];
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();
$sql = "SELECT * FROM emergencias WHERE id = ? AND id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id_emergencia, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$emergencia = $result->fetch_assoc();
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
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuário 1 - Localização Compartilhada</title>
    <link rel="icon" href="imagem/emergencia.png" type="image/x-icon">
    <style>
        #map { 
        height: 450px; 
        width: 100%; }
        body { 
        display: flex; 
        flex-direction: column; 
        align-items: center; 
        margin: 0; 
        font-family: Arial;
        }
        .container { 
            max-width: 400px; 
        padding: 20px; 
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); 
        text-align: center; }
        .img { 
            height: 30px; 
            width: 30px; 
            cursor: pointer; }
        .chat-box { 
            margin-top: 20px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
            padding: 10px; 
            height: 200px; 
            overflow-y: auto; 
            background-color: #f9f9f9; }
        .chat-input { 
            margin-top: 10px; 
            display: flex; 
            gap: 10px; }
        .chat-input input { 
            flex: 1; 
            padding: 5px; 
            border: 1px solid #ddd; 
            border-radius: 5px; }
        .chat-input button { 
            padding: 5px 10px; 
            border: none; 
            background-color: #007BFF; 
            color: white; border-radius: 5px; 
            cursor: pointer; }
        .chat-input button:hover { 
            background-color: #0056b3; }
        .chat-buttons button { 
            margin-left: 5px; 
            padding: 2px 5px; 
            border: none; 
            border-radius: 3px; 
            cursor: pointer; }
        .chat-buttons .editar { 
            background-color: #28a745; 
            color: white; }
        .chat-buttons .cancelar { 
            background-color: #dc3545; 
            color: white; }
        .chat-buttons .reclamar { 
            background-color: #ffc107; 
            color: white; }
        .chat-buttons .reenviar { 
            background-color: #6c757d; 
            color: white; }
        .chat-buttons .img { 
            width: 16px; 
            height: 16px; 
            margin-right: 5px; 
            vertical-align: middle; }
        .status { 
            font-weight: bold; 
            margin: 10px 0; }
        .status.atendimento { 
            color: #17a2b8; }
        .status.resolvida { 
            color: #28a745; }
        .message-content { 
            margin-bottom: 5px; }
        .data-hora { 
            font-size: 0.8em; 
            color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Usuário 1 - Localização Compartilhada <?= htmlspecialchars(ucfirst($emergencia['tipo_emergencia'])) ?></h1>
        <img class="img" src="imagem/cancelar.png" alt="cancelar" onclick="window.location.href='index.php'">
        <div class="status <?= $emergencia['resolvida'] ? 'resolvida' : ($emergencia['em_atendimento'] ? 'atendimento' : '') ?>">
        </div>
        <div id="map"></div>
        <div class="chat-box" id="chat-box">
        </div>
        <form id="chat-form" class="chat-input">
            <input type="text" id="mensagem" name="mensagem" placeholder="Digite sua mensagem...">
            <button type="button" id="enviar-mensagem">Enviar</button>
        </form>
    </div>
  <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo $apiKey; ?>&callback=initMap"></script>
    <script> 
        let mapa, marcaUsuario1, marcaUsuario2;
        function initMap() {
            const coordenadas = {
                lat: <?= $emergencia['latitude'] ?: '-29.9158' ?>,
                lng: <?= $emergencia['longitude'] ?: '-51.1836' ?>
            };
            mapa = new google.maps.Map(document.getElementById('map'), {
                center: coordenadas,
                zoom: 15
            });
            marcaUsuario1 = new google.maps.Marker({
                position: coordenadas,
                map: mapa,
                title: "Você está aqui"
            });
            const infoWindow = new google.maps.InfoWindow({ content: "Você está aqui" });
            infoWindow.open(mapa, marcaUsuario1);
            if (navigator.geolocation) {
                navigator.geolocation.watchPosition(
                    (posicao) => {
                        const novaPosicao = {
                            lat: posicao.coords.latitude,
                            lng: posicao.coords.longitude
                        };
                        marcaUsuario1.setPosition(novaPosicao);
                        mapa.setCenter(novaPosicao);
                        localStorage.setItem('localizacaoUsuario1', JSON.stringify(novaPosicao));
                        enviarLocalizacao(novaPosicao.lat, novaPosicao.lng);
                    },
                    (erro) => console.error("Erro ao obter localização: ", erro),
                    { enableHighAccuracy: true }
                );
            } else {
                alert("Geolocalização não suportada pelo seu navegador.");
            }
        }
        function enviarLocalizacao(latitude, longitude) {
    const formData = new FormData();
    formData.append('latitude', latitude);
    formData.append('longitude', longitude);
    formData.append('atualizar_localizacao', '1');
    fetch('usuario1.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro na rede');
        }
        return response.text();
    })
    .then(data => {
        console.log('Localização atualizada:', data);
    })
    .catch(error => {
        console.error("Erro ao enviar localização:", error);
    });
}
        document.getElementById('enviar-mensagem').addEventListener('click', function() {
            const mensagemInput = document.getElementById('mensagem');
            const mensagem = mensagemInput.value.trim();
            if (mensagem === '') return;  
            fetch('usuario1.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_emergencia=<?= $id_emergencia ?>&mensagem=${encodeURIComponent(mensagem)}`
            }).then(response => {
                if (response.ok) {
                    mensagemInput.value = '';
                    atualizarChat();
                }
            }).catch(error => console.error("Erro ao enviar mensagem:", error));
        });
        function atualizarChat() {
            fetch(`usuario1.php?id_emergencia=<?= $id_emergencia ?>&obter_mensagens=1`)
        .then(response => response.json())
        .then(mensagens => {
            const chatBox = document.getElementById('chat-box');
            chatBox.innerHTML = '';      
            mensagens.forEach(msg => {
                const divMensagem = document.createElement('div');
                const classe = msg.id_remetente == <?= $id_usuario ?> ? 'remetente' : 'destinatario';
                divMensagem.className = `chat-message ${classe}`;          
                if (msg.mensagem) {
                    divMensagem.innerHTML = `
                        <div class="message-content">
                            <strong>${msg.telefone}:</strong> ${msg.mensagem}
                        </div>
                        <div class="data-hora">${new Date(msg.data_hora).toLocaleString()}</div>
                        ${msg.id_remetente == <?= $id_usuario ?> ? `
                        <div class="chat-buttons">
                            <button onclick="editarMensagem(${msg.id}, this)" class="editar"><img class="img" src="imagem/editar.png">Editar</button>
                            <button onclick="cancelarMensagem(${msg.id}, this)" class="cancelar"><img class="img" src="imagem/cancelar.png">Cancelar</button>
                            <button onclick="reclamar()" class="reclamar">Reclamar<img class="img" src="imagem/reclamar.png"></button>
                            <button onclick="reenviarMensagem(${msg.id}, this)" class="reenviar">Reenviar<img class="img" src="imagem/reenviar.png"></button>
                        </div>` : ''}
                    `;
                } else if (msg.resposta) {
                    divMensagem.innerHTML = `
                        <div class="message-content">
                            <strong><?= htmlspecialchars(ucfirst($emergencia['tipo_emergencia'])) ?>:</strong> ${msg.resposta}
                        </div>
                        <div class="data-hora">${new Date(msg.data_hora).toLocaleString()}</div>
                    `;
                }           
                chatBox.appendChild(divMensagem);
           
            });         
            chatBox.scrollTop = chatBox.scrollHeight;
        }).catch(error => console.error("Erro ao atualizar chat:", error));
}
        function verificarStatus() {
            fetch(`usuario2.php?status_acao=1&id_emergencia=<?= $id_emergencia ?>`)
        .then(response => response.json())
        .then(status => {
            const statusDiv = document.querySelector('.status');
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
function editarMensagem(idMensagem, elemento) {
    const mensagemDiv = elemento.parentElement.parentElement;
    const textoOriginal = mensagemDiv.querySelector('.message-content').textContent.split(':')[1].trim();
    const novoTexto = prompt("Editar mensagem:", textoOriginal);
    if (novoTexto === null) {
        return; 
    }
    if (novoTexto.trim() === '') {
        alert('A mensagem não pode ficar vazia!');
        return; 
    }
    if (novoTexto === textoOriginal) {
        return;
    }
    fetch('usuario1.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${idMensagem}&mensagem=${encodeURIComponent(novoTexto)}&atualizar_mensagem=1`
    }).then(response => {
        if (response.ok) {
            mensagemDiv.querySelector('.message-content').innerHTML = `
                <strong>${mensagemDiv.querySelector('strong').textContent}</strong> ${novoTexto}
            `;
        }
    }).catch(error => console.error("Erro ao editar mensagem:", error));
}

function cancelarMensagem(idMensagem, elemento) {
    if (confirm("Tem certeza que deseja cancelar esta mensagem?")) {
        fetch('usuario1.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${idMensagem}`
        }).then(response => {
            if (response.ok) {
                elemento.parentElement.parentElement.remove();
            }
        }).catch(error => console.error("Erro ao cancelar mensagem:", error));
    }
}
        function reclamar() {
            document.getElementById('mensagem').value = "Está demorando muito!";
            document.getElementById('enviar-mensagem').click();
        }  
        function reenviarMensagem(idMensagem, elemento) {
            const mensagemDiv = elemento.parentElement.parentElement;
            const textoMensagem = mensagemDiv.querySelector('.message-content').textContent.split(':')[1].trim();
            document.getElementById('mensagem').value = textoMensagem;
            document.getElementById('enviar-mensagem').click();
        }
        setInterval(atualizarChat, 300);
        setInterval(() => {
            const localizacaoUsuario2 = JSON.parse(localStorage.getItem('localizacaoUsuario2'));
            if (localizacaoUsuario2) {
                const coordenadas = {
                    lat: localizacaoUsuario2.latitude || localizacaoUsuario2.lat,
                    lng: localizacaoUsuario2.longitude || localizacaoUsuario2.lng
                };              
                if (!marcaUsuario2) {
                    marcaUsuario2 = new google.maps.Marker({
                        position: coordenadas,
                        map: mapa,
                        title: "Emergência"
                    });
                    new google.maps.InfoWindow({ content: "Emergência!" }).open(mapa, marcaUsuario2);
                } else {
                    marcaUsuario2.setPosition(coordenadas);
                }}     
        }, 300);
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', e => e.preventDefault());
        });
    </script>
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