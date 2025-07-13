// assets/js/chat.js - User chat logic (polling, notifications, file, typing)
let lastId = 0;
const chatBox = document.getElementById('chatBox');
const chatMessages = document.getElementById('chatMessages');
const chatForm = document.getElementById('chatForm');
const chatInput = document.getElementById('chatInput');
const chatSound = document.getElementById('chatSound');
const attachFileBtn = document.getElementById('attachFileBtn');
const chatFile = document.getElementById('chatFile');

function fetchMessages() {
  fetch('includes/chat.php?action=fetch&last_id=' + lastId)
    .then(r => r.json())
    .then(data => {
      data.messages.forEach(msg => {
        const div = document.createElement('div');
        div.textContent = `[${msg.timestamp}] ${msg.from_user}: ${msg.message}`;
        chatMessages.appendChild(div);
        lastId = msg.id;
        if (msg.from_user !== 'me') {
          chatSound.play();
          if (Notification.permission === 'granted') {
            new Notification('Pesan baru', { body: msg.message });
          }
        }
      });
      chatMessages.scrollTop = chatMessages.scrollHeight;
    });
}
setInterval(fetchMessages, 2000);
chatForm.onsubmit = e => {
  e.preventDefault();
  fetch('includes/chat.php', {
    method: 'POST',
    body: new FormData(chatForm)
  }).then(() => {
    chatInput.value = '';
    fetchMessages();
  });
};
attachFileBtn.onclick = () => chatFile.click();
chatFile.onchange = () => {
  const form = new FormData();
  form.append('file', chatFile.files[0]);
  fetch('includes/chat.php', { method: 'POST', body: form }).then(fetchMessages);
};
if (Notification.permission !== 'granted') Notification.requestPermission();
