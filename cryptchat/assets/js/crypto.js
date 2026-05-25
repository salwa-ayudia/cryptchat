function arrayBufferToBase64(buffer) {
  let binary = "";
  const bytes = new Uint8Array(buffer);

  for (let i = 0; i < bytes.byteLength; i++) {
    binary += String.fromCharCode(bytes[i]);
  }

  return btoa(binary);
}
1

function base64ToArrayBuffer(base64) {
  const binary = atob(base64);
  const bytes = new Uint8Array(binary.length);

  for (let i = 0; i < binary.length; i++) {
    bytes[i] = binary.charCodeAt(i);
  }

  return bytes.buffer;
}

// generate key pair (public key dan private)
async function generateKeyPair() {
  try {
    const keyPair = await crypto.subtle.generateKey(
      {
        name: "ECDH",
        namedCurve: "P-256",
      },
      true,
      ["deriveKey", "deriveBits"],
    );

    const publicKey = await crypto.subtle.exportKey("spki", keyPair.publicKey);

    const privateKey = await crypto.subtle.exportKey(
      "pkcs8",
      keyPair.privateKey,
    );

    return {
      publicKey: arrayBufferToBase64(publicKey),
      privateKey: arrayBufferToBase64(privateKey),
    };
  } catch (error) {
    console.error(error);
  }
}


async function derivePasswordKey(password, saltBase64) {
  const enc = new TextEncoder();

  const keyMaterial = await crypto.subtle.importKey(
    "raw",
    enc.encode(password),
    "PBKDF2",
    false,
    ["deriveKey"],
  );

  return crypto.subtle.deriveKey(
    {
      name: "PBKDF2",
      salt: base64ToArrayBuffer(saltBase64),
      iterations: 100000,
      hash: "SHA-256",
    },
    keyMaterial,
    {
      name: "AES-GCM",
      length: 256,
    },
    true,
    ["encrypt", "decrypt"],
  );
}


async function encryptPrivateKey(privateKey, password) {
  const salt = crypto.getRandomValues(new Uint8Array(16));

  const key = await derivePasswordKey(password, arrayBufferToBase64(salt));

  const iv = crypto.getRandomValues(new Uint8Array(12));

  const encrypted = await crypto.subtle.encrypt(
    {
      name: "AES-GCM",
      iv,
    },
    key,
    new TextEncoder().encode(privateKey),
  );

  return {
    ciphertext: arrayBufferToBase64(encrypted),
    iv: arrayBufferToBase64(iv),
    salt: arrayBufferToBase64(salt),
  };
}


async function decryptPrivateKey(ciphertext, iv, password, salt) {
  try {
    if (!ciphertext || !iv || !password || !salt) {
      throw new Error("Data dekripsi private key tidak lengkap.");
    }

    const key = await derivePasswordKey(password, salt);

    const decrypted = await crypto.subtle.decrypt(
      {
        name: "AES-GCM",
        iv: base64ToArrayBuffer(iv),
      },
      key,
      base64ToArrayBuffer(ciphertext),
    );

    const privateKey = new TextDecoder().decode(decrypted);

    if (!privateKey || privateKey.trim() === "") {
      throw new Error("Private key hasil dekripsi kosong.");
    }

    return privateKey;
  } catch (error) {
    console.error(error);
    alert("Gagal mendekripsi private key:", error);
    return null;
  }
}

async function importPrivateKey(base64Key) {
  return crypto.subtle.importKey(
    "pkcs8",
    base64ToArrayBuffer(base64Key),
    {
      name: "ECDH",
      namedCurve: "P-256",
    },
    true,
    ["deriveBits"],
  );
}


async function importPublicKey(base64Key) {
  return crypto.subtle.importKey(
    "spki",
    base64ToArrayBuffer(base64Key),
    {
      name: "ECDH",
      namedCurve: "P-256",
    },
    true,
    [],
  );
}


async function computeSharedSecret(privateKey, publicKey) {
  return crypto.subtle.deriveBits(
    // deriveBits --> shared secret mentah
    {
      name: "ECDH",
      public: publicKey,
    },
    privateKey,
    256, // bit
  );
}


async function createSessionKey(targetUserId, conversationId) {
  const response = await fetch(
    `../api/get_public_key.php?user_id=${targetUserId}`,
  );

  const data = await response.json();

  if (!data.success) {
    throw new Error("Public key target tidak ditemukan");
  }

  const privateKeyBase64 = sessionStorage.getItem("privateKey");

  if (!privateKeyBase64) {
    throw new Error("Private key tidak ditemukan");
  }

  const privateKey = await importPrivateKey(privateKeyBase64);

  const publicKey = await importPublicKey(data.public_key);

  const sharedSecret = await computeSharedSecret(privateKey, publicKey);

  return await deriveSessionKey(sharedSecret, conversationId);
}


async function deriveSessionKey(sharedBits, conversationId) {
  // import raw shared bits ke HKDF
  const importedKey = await crypto.subtle.importKey(
    "raw",
    sharedBits,
    "HKDF",
    false,
    ["deriveKey"],
  );

  // derive AES key
  return await crypto.subtle.deriveKey(
    {
      name: "HKDF",
      hash: "SHA-256",

      salt: new TextEncoder().encode("cryptchat-" + conversationId),

      info: new TextEncoder().encode("session-key"),
    },

    importedKey,

    {
      name: "AES-GCM",
      length: 256,
    },

    true,
    ["encrypt", "decrypt"],
  );
}


async function encryptMessage(message, sessionKey) {
  const iv = crypto.getRandomValues(new Uint8Array(12));

  const encrypted = await crypto.subtle.encrypt(
    {
      name: "AES-GCM",
      iv,
    },
    sessionKey,
    new TextEncoder().encode(message),
  );

  return {
    ciphertext: arrayBufferToBase64(encrypted),
    iv: arrayBufferToBase64(iv),
  };
}


async function decryptMessage(ciphertext, iv, sessionKey) {
  try {
    const decrypted = await crypto.subtle.decrypt(
      {
        name: "AES-GCM",
        iv: base64ToArrayBuffer(iv),
      },
      sessionKey,
      base64ToArrayBuffer(ciphertext),
    );

    return new TextDecoder().decode(decrypted);
  } catch {
    return "[Decrypt Failed]";
  }
}


async function generateFingerprint(targetUserId) {
  try {
    const privateKeyBase64 = sessionStorage.getItem("privateKey");

    if (!privateKeyBase64) {
      throw new Error("Private key tidak ditemukan");
    }

    const privateKey = await importPrivateKey(privateKeyBase64);

    const response = await fetch(
      `../api/get_public_key.php?user_id=${targetUserId}`,
    );

    const data = await response.json();

    if (!data.success) {
      throw new Error("Public key target tidak ditemukan");
    }

    const publicKey = await importPublicKey(data.public_key);

    const sharedSecret = await computeSharedSecret(privateKey, publicKey);

    const sessionKey = await deriveSessionKey(sharedSecret, conversationId);

    const rawKey = await crypto.subtle.exportKey("raw", sessionKey);

    const hashBuffer = await crypto.subtle.digest("SHA-256", rawKey);

    const hashArray = Array.from(new Uint8Array(hashBuffer));

    return hashArray
      .map((b) => b.toString(16).padStart(2, "0"))
      .join("")
      .match(/.{1,4}/g)
      .join(" ");
  } catch (error) {
    console.error("Fingerprint error:", error);
    return "Fingerprint unavailable";
  }
}


async function sendEncryptedMessage() {
  try {
    const input = document.getElementById("messageInput");

    const message = input.value;
    if (!message.trim()) {
      return;
    }

    const sessionKey = await createSessionKey(targetUserId, conversationId);

    const encrypted = await encryptMessage(message, sessionKey);

    const formData = new FormData();

    formData.append("conversation_id", conversationId);
    formData.append("ciphertext", encrypted.ciphertext);
    formData.append("iv", encrypted.iv);

    const response = await fetch("../api/send_message.php", {
      method: "POST",
      body: formData,
    });

    const data = await response.json();

    if (!response.ok || !data.success) {
      throw new Error(data.message || "Pesan gagal dikirim.");
    }

    input.value = "";
    await loadMessages();
  } catch (error) {
    console.error(error);

    alert(
      "Gagal mengenkripsi pesan:" + error.message + "\nSilahkan login kembali.",
    );

    sessionStorage.clear();

    window.location.href = "login.php";
  }
}


async function loadMessages() {
  try {
    const chatBox = document.getElementById("chatBox");

    chatBox.textContent = "";

    const sessionKey = await createSessionKey(targetUserId, conversationId);

    if (!sessionKey) {
      console.error("Session key gagal dibuat");
      return;
    }

    const response = await fetch(
      `../api/get_messages.php?conversation_id=${conversationId}`,
    );

    const data = await response.json();

    if (!response.ok || !data.success) {
      throw new Error(data.message || "Gagal mengambil pesan.");
    }

    for (const msg of data.messages) {
      const plaintext = await decryptMessage(
        msg.ciphertext,
        msg.iv,
        sessionKey,
      );

      const isMine = msg.sender_id == currentUserId;

      const messageRow = document.createElement("div");

      messageRow.className = `message-row ${isMine ? "mine" : "other"}`;

      const messageBubble = document.createElement("div");

      messageBubble.className = "message-bubble";

      messageBubble.textContent = plaintext;

      messageRow.appendChild(messageBubble);
      chatBox.appendChild(messageRow);
    }

    chatBox.scrollTop = chatBox.scrollHeight;
  } catch (error) {
    console.error("Load message gagal:", error);
  }
}