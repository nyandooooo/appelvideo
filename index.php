<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Appel Vidéo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.jsdelivr.net/npm/peerjs@1.5.2/dist/peerjs.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    html, body {
      height: 100%;
      margin: 0;
      background-color: #111;
      overflow: hidden;
      font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    }

    #remoteVideo {
      position: absolute;
      top: 0; 
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: contain;
      background: #000;
      z-index: 1;
    }

    #myVideo {
      position: absolute;
      width: 20%;
      max-width: 150px;
      height: 25vh;
      bottom: 100px;
      right: 20px;
      border-radius: 8px;
      border: 2px solid rgba(255,255,255,0.3);
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      z-index: 2;
      background: #000;
      object-fit: cover;
    }

    .controls {
      position: absolute;
      bottom: 30px;
      left: 0;
      width: 100%;
      z-index: 3;
      display: flex;
      justify-content: center;
      gap: 25px;
    }

    .controls button {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .controls button:hover {
      transform: scale(1.1);
    }

    .call-button {
      background-color: #25D366;
      color: white;
    }

    .end-call-button {
      background-color: #FF3B30;
      color: white;
    }

    .option-button {
      background-color: rgba(255,255,255,0.2);
      color: white;
    }

    .peer-id-display {
      position: absolute;
      top: 20px;
      left: 0;
      width: 100%;
      z-index: 3;
      color: white;
      text-align: center;
      font-size: 16px;
      text-shadow: 0 1px 3px rgba(0,0,0,0.5);
    }

    .peer-name {
      font-weight: bold;
      font-size: 18px;
      margin-bottom: 5px;
    }

    .call-status {
      font-size: 14px;
      opacity: 0.8;
    }

    .input-container {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 4;
      background: rgba(0,0,0,0.8);
      padding: 20px;
      border-radius: 10px;
      width: 90%;
      max-width: 400px;
      text-align: center;
      color: white;
    }

    .incoming-call-container {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 5;
      background: rgba(0,0,0,0.9);
      padding: 30px;
      border-radius: 10px;
      width: 90%;
      max-width: 350px;
      text-align: center;
      color: white;
      display: none;
      animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
      0% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7); }
      70% { box-shadow: 0 0 0 15px rgba(37, 211, 102, 0); }
      100% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); }
    }

    .notification-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      background-color: #FF3B30;
      color: white;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      z-index: 6;
      display: none;
      cursor: pointer;
    }

    .notification-badge.ring-blocked {
      animation: pulse 1s infinite;
      background-color: #FF9500;
    }

    .input-container input {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 5px;
      border: none;
    }

    .input-container button,
    .incoming-call-container button {
      background-color: #25D366;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      margin: 5px;
    }

    .btn-reject {
      background-color: #FF3B30 !important;
    }

    @media (max-width: 768px) {
      #remoteVideo {
        object-fit: cover;
      }
      
      #myVideo {
        width: 30%;
        height: 20vh;
        bottom: 120px;
      }

      .controls {
        bottom: 20px;
        gap: 15px;
      }

      .controls button {
        width: 45px;
        height: 45px;
        font-size: 18px;
      }

      .incoming-call-container {
        width: 85%;
        padding: 20px;
      }
    }
  </style>
</head>
<body>

<video id="remoteVideo" autoplay playsinline></video>
<video id="myVideo" autoplay muted playsinline></video>

<audio id="ringtone" loop preload="auto">
  <source src="https://assets.mixkit.co/sfx/preview/mixkit-classic-phone-ring-464.mp3" type="audio/mpeg">
</audio>

<div class="notification-badge" id="notificationBadge">1</div>

<div class="peer-id-display">
  <div class="peer-name" id="peerName">Appel</div>
  <div class="call-status" id="callStatus">Prêt</div>
</div>

<div class="controls" id="callControls" style="display: none;">
  <button class="option-button" onclick="toggleMute()"><i class="fas fa-microphone"></i></button>
  <button class="option-button" onclick="toggleVideo()"><i class="fas fa-video"></i></button>
  <button class="end-call-button" onclick="endCall()"><i class="fas fa-phone"></i></button>
  <button class="option-button" onclick="rotateCamera()"><i class="fas fa-sync-alt"></i></button>
</div>

<div class="input-container" id="inputContainer">
  <h3>Video Call</h3>
  <p>Votre ID: <strong id="myIdDisplay">...</strong></p>
  <input type="text" id="peer-id" class="form-control" placeholder="Entrez l'ID à appeler">
  <button class="btn btn-success mt-3" onclick="startCall()"><i class="fas fa-phone"></i> Appeler</button>
</div>

<div class="incoming-call-container" id="incomingCallContainer">
  <h3 id="incomingCallTitle">Appel entrant</h3>
  <p id="incomingCallId">ID: ...</p>
  <div class="mt-4">
    <button class="btn btn-success" onclick="acceptCall()"><i class="fas fa-phone"></i> Répondre</button>
    <button class="btn btn-reject" onclick="rejectCall()"><i class="fas fa-phone-slash"></i> Refuser</button>
  </div>
</div>

<script>
  let myStream;
  let currentCall;
  let isMuted = false;
  let isVideoOff = false;
  let isFrontCamera = true;
  let incomingCall = null;
  let ringtone = document.getElementById('ringtone');
  let notificationBadge = document.getElementById('notificationBadge');

  // Précharger et configurer la sonnerie
  ringtone.volume = 0.5;
  ringtone.load();

  function generateShortId() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let id = '';
    for (let i = 0; i < 6; i++) {
      id += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return id;
  }

  async function getMediaStream(front = true) {
    const constraints = {
      audio: true,
      video: {
        facingMode: front ? 'user' : 'environment',
        width: { ideal: 1280 },
        height: { ideal: 720 }
      }
    };
    
    try {
      const stream = await navigator.mediaDevices.getUserMedia(constraints);
      return stream;
    } catch (err) {
      console.error("Erreur d'accès à la caméra:", err);
      alert("Impossible d'accéder à la caméra. Vérifiez les permissions.");
      return null;
    }
  }

  async function initialize() {
    myStream = await getMediaStream();
    if (!myStream) return;
    
    myVideo.srcObject = myStream;

    peer.on('call', call => {
      // Si déjà en appel, ignorer le nouvel appel
      if (currentCall) {
        call.close();
        return;
      }
      
      incomingCall = call;
      callStatus.textContent = "Appel entrant...";
      peerName.textContent = `Appel de ${call.peer}`;
      
      // Afficher la notification
      incomingCallId.textContent = `ID: ${call.peer}`;
      incomingCallContainer.style.display = "block";
      inputContainer.style.display = "none";
      notificationBadge.style.display = "flex";
      
      // Jouer la sonnerie avec gestion d'erreur
      ringtone.play().catch(e => {
        console.log("Impossible de jouer la sonnerie:", e);
        notificationBadge.classList.add("ring-blocked");
        notificationBadge.title = "Cliquez pour activer la sonnerie";
      });
      
      // Notification du navigateur
      if ("Notification" in window && Notification.permission === "granted") {
        new Notification("Appel entrant", {
          body: `Appel de ${call.peer}`,
          icon: "https://web.whatsapp.com/favicon.ico"
        });
      }
    });
  }

  function acceptCall() {
    if (!incomingCall || !myStream) return;
    
    // Arrêter la sonnerie et cacher le badge
    stopRingtone();
    
    incomingCall.answer(myStream);
    currentCall = incomingCall;
    
    incomingCall.on('stream', remoteStream => {
      remoteVideo.srcObject = remoteStream;
      callStatus.textContent = "En appel";
      callControls.style.display = "flex";
      incomingCallContainer.style.display = "none";
    });
    
    incomingCall.on('close', endCall);
    incomingCall = null;
  }

  function stopRingtone() {
    ringtone.pause();
    ringtone.currentTime = 0;
    notificationBadge.style.display = "none";
    notificationBadge.classList.remove("ring-blocked");
    notificationBadge.onclick = null;
  }

  function rejectCall() {
    if (incomingCall) {
      incomingCall.close();
      incomingCall = null;
    }
    
    stopRingtone();
    
    incomingCallContainer.style.display = "none";
    inputContainer.style.display = "block";
    callStatus.textContent = "Prêt";
    peerName.textContent = "Appel";
  }

  window.startCall = async function() {
    const remoteId = document.getElementById('peer-id').value.trim();
    if (!remoteId) return alert("Entrez un ID !");
    
    if (!myStream) {
      myStream = await getMediaStream();
      if (!myStream) return;
    }
    
    callStatus.textContent = "Appel en cours...";
    peerName.textContent = `Appel à ${remoteId}`;
    
    currentCall = peer.call(remoteId, myStream);
    currentCall.on('stream', remoteStream => {
      remoteVideo.srcObject = remoteStream;
      callStatus.textContent = "En appel";
      callControls.style.display = "flex";
      inputContainer.style.display = "none";
    });
    
    currentCall.on('close', endCall);
  };

  function endCall() {
    if (currentCall) currentCall.close();
    if (incomingCall) incomingCall.close();
    
    if (remoteVideo.srcObject) {
      remoteVideo.srcObject.getTracks().forEach(track => track.stop());
      remoteVideo.srcObject = null;
    }
    
    stopRingtone();
    
    callControls.style.display = "none";
    inputContainer.style.display = "block";
    incomingCallContainer.style.display = "none";
    callStatus.textContent = "Appel terminé";
    
    setTimeout(() => {
      callStatus.textContent = "Prêt";
      peerName.textContent = "Appel";
    }, 2000);
  }

  function toggleMute() {
    if (!myStream) return;
    
    isMuted = !isMuted;
    myStream.getAudioTracks().forEach(track => {
      track.enabled = !isMuted;
    });
    
    const muteButton = document.querySelector('.controls button:nth-child(1)');
    muteButton.innerHTML = isMuted ? '<i class="fas fa-microphone-slash"></i>' : '<i class="fas fa-microphone"></i>';
    muteButton.style.backgroundColor = isMuted ? '#FF3B30' : 'rgba(255,255,255,0.2)';
  }

  function toggleVideo() {
    if (!myStream) return;
    
    isVideoOff = !isVideoOff;
    myStream.getVideoTracks().forEach(track => {
      track.enabled = !isVideoOff;
    });
    
    const videoButton = document.querySelector('.controls button:nth-child(2)');
    videoButton.innerHTML = isVideoOff ? '<i class="fas fa-video-slash"></i>' : '<i class="fas fa-video"></i>';
    videoButton.style.backgroundColor = isVideoOff ? '#FF3B30' : 'rgba(255,255,255,0.2)';
    
    myVideo.style.display = isVideoOff ? 'none' : 'block';
  }

  async function rotateCamera() {
    if (!myStream) return;
    
    isFrontCamera = !isFrontCamera;
    
    // Arrêter et enlever l'ancienne piste vidéo
    const oldVideoTracks = myStream.getVideoTracks();
    oldVideoTracks.forEach(track => {
      track.stop();
      myStream.removeTrack(track);
    });
    
    // Obtenir la nouvelle piste
    const newStream = await getMediaStream(isFrontCamera);
    if (!newStream) {
      // Restaurer l'ancienne caméra si échec
      isFrontCamera = !isFrontCamera;
      return;
    }
    
    const newVideoTrack = newStream.getVideoTracks()[0];
    myStream.addTrack(newVideoTrack);
    
    // Mettre à jour l'affichage local
    myVideo.srcObject = myStream;
    
    // Mettre à jour l'appel en cours si nécessaire
    if (currentCall) {
      const senders = currentCall.peerConnection.getSenders();
      const videoSender = senders.find(s => s.track && s.track.kind === 'video');
      if (videoSender) {
        videoSender.replaceTrack(newVideoTrack).catch(console.error);
      }
    }
    
    // Libérer le nouveau flux (nous n'avons besoin que de la piste)
    newStream.getTracks().filter(t => t !== newVideoTrack).forEach(t => t.stop());
  }

  // Gestion du clic sur le badge de notification
  notificationBadge.onclick = function() {
    ringtone.play().catch(console.error);
    this.classList.remove("ring-blocked");
    this.onclick = null;
  };

  // Demander la permission de notification
  if ("Notification" in window) {
    Notification.requestPermission().then(permission => {
      console.log("Permission notifications:", permission);
    });
  }

  const peer = new Peer(generateShortId());
  
  peer.on('open', id => {
    myIdDisplay.textContent = id;
  });

  peer.on('error', err => {
    console.error("Erreur PeerJS:", err);
    alert("Erreur de connexion: " + err.message);
  });

  initialize();
</script>

</body>
</html>