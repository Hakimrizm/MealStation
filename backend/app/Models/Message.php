<template>
  <div class="page">

    <!-- NAVBAR -->
    <div class="navbar navbar-blue">
      <div class="navbar-inner">

        <div class="left">
          <a class="link icon-only" @click=${goBack}>
            <i class="icon icon-back"></i>
          </a>
        </div>

        <div class="title">${toko.nama}</div>

      </div>
    </div>

    <!-- CHAT -->
    <div class="page-content messages-content">

      <div class="messages">

        ${messages.map(msg => $h`
          <div class="message message-${msg.sender_id == userId ? 'sent' : 'received'}">

            <div class="message-bubble">

              ${msg.product ? $h`
                <div class="bubble-product">
                  <img src="${msg.product.image}" />
                  <div>
                    <div>${msg.product.name}</div>
                    <div>Rp ${msg.product.price}</div>
                  </div>
                </div>
              ` : ''}

              <div>${msg.message}</div>

            </div>

          </div>
        `)}

      </div>

    </div>

    <!-- INPUT -->
    <div class="toolbar messagebar">
      <div class="toolbar-inner">

        <textarea id="chatInput" placeholder="Ketik pesan..."></textarea>

        <a class="link send-btn-blue" @click=${sendMessage}>
          Kirim
        </a>

      </div>
    </div>

  </div>
</template>

<script>
export default (props, { $f7, $on, $update }) => {

  const BASE_URL = "http://10.103.98.238:8000";

  let userId = null;
  let receiverId = null;
  let messages = [];
  let toko = { nama: "Chat" };

  async function getUser() {
    const res = await fetch(`${BASE_URL}/api/user`, {
      headers: { Authorization: `Bearer ${localStorage.token}` }
    });
    const data = await res.json();
    userId = data.id;
    $update();
  }

  async function loadChat() {
    const res = await fetch(`${BASE_URL}/api/chat/${receiverId}`, {
      headers: { Authorization: `Bearer ${localStorage.token}` }
    });

    const data = await res.json();
    messages = data.messages;
    $update();
  }

  async function sendMessage() {
    const input = document.getElementById("chatInput");
    const text = input.value.trim();

    if (!text) return;

    await fetch(`${BASE_URL}/api/chat/send`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${localStorage.token}`
      },
      body: JSON.stringify({
        receiver_id: receiverId,
        message: text
      })
    });

    input.value = "";
    await loadChat(); // sync dari server
  }

  function goBack() {
    $f7.views.main.router.back();
  }

  $on("pageInit", async (page) => {
    receiverId = page.route.params.id;

    await getUser();
    await loadChat();

    setInterval(loadChat, 3000);
  });

  return $render;
};
</script>

<style>
.navbar-blue { background:#0061a8; color:white; }

.message-bubble {
  padding:10px;
  border-radius:12px;
  max-width:80%;
}

.message-sent .message-bubble {
  background:#0061a8;
  color:white;
}

.message-received .message-bubble {
  background:white;
}
</style>