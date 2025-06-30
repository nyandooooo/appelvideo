const socket = io("https://your-socket-server.com"); // à remplacer

const localVideo = document.getElementById("localVideo");
const remoteVideo = document.getElementById("remoteVideo");

const peer = new RTCPeerConnection({
  iceServers: [{ urls: "stun:stun.l.google.com:19302" }]
});

navigator.mediaDevices.getUserMedia({ video: true, audio: true })
  .then(stream => {
    localVideo.srcObject = stream;
    stream.getTracks().forEach(track => peer.addTrack(track, stream));
  });

peer.ontrack = e => {
  remoteVideo.srcObject = e.streams[0];
};

peer.onicecandidate = e => {
  if (e.candidate) {
    socket.emit("candidate", e.candidate);
  }
};

socket.on("offer", async offer => {
  await peer.setRemoteDescription(new RTCSessionDescription(offer));
  const answer = await peer.createAnswer();
  await peer.setLocalDescription(answer);
  socket.emit("answer", answer);
});

socket.on("answer", answer => {
  peer.setRemoteDescription(new RTCSessionDescription(answer));
});

socket.on("candidate", candidate => {
  peer.addIceCandidate(new RTCIceCandidate(candidate));
});

async function startCall() {
  const offer = await peer.createOffer();
  await peer.setLocalDescription(offer);
  socket.emit("offer", offer);
}

startCall();
