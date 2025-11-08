/**
 * App Chat
 */

// This is a simple example of a pre-shared key.
// In a real application, you should use a key exchange mechanism like Diffie-Hellman.
const sharedKey = 'my-super-secret-key';
let cryptoKey;

async function importKey() {
    const keyData = new TextEncoder().encode(sharedKey);
    // use sha-256 to derive a 32-byte key
    const digest = await crypto.subtle.digest('SHA-256', keyData);
    cryptoKey = await crypto.subtle.importKey(
        'raw',
        digest,
        { name: 'AES-GCM' },
        false,
        ['encrypt', 'decrypt']
    );
}

async function encryptMessage(message) {
    const iv = crypto.getRandomValues(new Uint8Array(12));
    const encodedMessage = new TextEncoder().encode(message);
    const encryptedMessage = await crypto.subtle.encrypt(
        { name: 'AES-GCM', iv },
        cryptoKey,
        encodedMessage
    );
    // return a single buffer with iv and encrypted message
    const buffer = new Uint8Array(iv.length + encryptedMessage.byteLength);
    buffer.set(iv, 0);
    buffer.set(new Uint8Array(encryptedMessage), iv.length);
    return buffer;
}

async function decryptMessage(encryptedBuffer) {
    const iv = encryptedBuffer.slice(0, 12);
    const encryptedMessage = encryptedBuffer.slice(12);
    const decryptedMessage = await crypto.subtle.decrypt(
        { name: 'AES-GCM', iv },
        cryptoKey,
        encryptedMessage
    );
    return new TextDecoder().decode(decryptedMessage);
}

importKey();

'use strict';

document.addEventListener('DOMContentLoaded', function () {
  (function () {
    const ws = new WebSocket('ws://127.0.0.1:8080?userId=' + userId);
    let chatWithUserId = null;

    ws.onopen = () => {
        console.log('Connected to the signaling server');
    };

    ws.onmessage = async event => {
        console.log('Message from server ', event.data);
        const message = JSON.parse(event.data);

        if (!peerConnection && (message.type === 'offer' || message.type === 'candidate')) {
            createPeerConnection();
        }

        switch (message.type) {
            case 'offer':
                await peerConnection.setRemoteDescription(new RTCSessionDescription(message.payload));
                const answer = await peerConnection.createAnswer();
                await peerConnection.setLocalDescription(answer);
                ws.send(JSON.stringify({ to: chatWithUserId, type: 'answer', payload: answer }));
                break;
            case 'answer':
                await peerConnection.setRemoteDescription(new RTCSessionDescription(message.payload));
                break;
            case 'candidate':
                await peerConnection.addIceCandidate(new RTCIceCandidate(message.payload));
                break;
            case 'text-message':
                const decryptedMessage = await decryptMessage(new Uint8Array(message.payload));
                // Display the message in the chat window
                let renderMsg = document.createElement('div');
                renderMsg.className = 'chat-message-text mt-2';
                renderMsg.innerHTML = '<p class="mb-0 text-break">' + decryptedMessage + '</p>';
                document.querySelector('.chat-history-body .chat-history').appendChild(renderMsg);
                scrollToBottom();
                break;
        }
    };

    ws.onclose = () => {
        console.log('Disconnected from the signaling server');
    };

    const localVideo = document.getElementById('localVideo');
    const remoteVideo = document.getElementById('remoteVideo');
    let localStream;

    async function startMedia() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            localVideo.srcObject = stream;
            localStream = stream;
        } catch (error) {
            console.error('Error accessing media devices.', error);
        }
    }

    startMedia();

    let peerConnection;

    const configuration = {
        iceServers: [
            {
                urls: 'stun:stun.l.google.com:19302'
            }
        ]
    };

    function createPeerConnection() {
        peerConnection = new RTCPeerConnection(configuration);

        peerConnection.onicecandidate = event => {
            if (event.candidate) {
                ws.send(JSON.stringify({ to: chatWithUserId, type: 'candidate', payload: event.candidate }));
            }
        };

        peerConnection.ontrack = event => {
            remoteVideo.srcObject = event.streams[0];
        };

        if (localStream) {
            localStream.getTracks().forEach(track => {
                peerConnection.addTrack(track, localStream);
            });
        }
    }


    const callButton = document.getElementById('callButton');
    if(callButton) {
        callButton.addEventListener('click', async () => {
            if (!chatWithUserId) {
                alert('Please select a user to call.');
                return;
            }
            createPeerConnection();
            const offer = await peerConnection.createOffer();
            await peerConnection.setLocalDescription(offer);
            ws.send(JSON.stringify({ to: chatWithUserId, type: 'offer', payload: offer }));
        });
    }

    const chatContactsBody = document.querySelector('.app-chat-contacts .sidebar-body'),
      chatContactListItems = [].slice.call(
        document.querySelectorAll('.chat-contact-list-item:not(.chat-contact-list-item-title)')
      ),
      chatHistoryBody = document.querySelector('.chat-history-body'),
      chatSidebarLeftBody = document.querySelector('.app-chat-sidebar-left .sidebar-body'),
      chatSidebarRightBody = document.querySelector('.app-chat-sidebar-right .sidebar-body'),
      chatUserStatus = [].slice.call(document.querySelectorAll(".form-check-input[name='chat-user-status']")),
      chatSidebarLeftUserAbout = $('.chat-sidebar-left-user-about'),
      formSendMessage = document.querySelector('.form-send-message'),
      messageInput = document.querySelector('.message-input'),
      searchInput = document.querySelector('.chat-search-input'),
      speechToText = $('.speech-to-text'), // ! jQuery dependency for speech to text
      userStatusObj = {
        active: 'avatar-online',
        offline: 'avatar-offline',
        away: 'avatar-away',
        busy: 'avatar-busy'
      };

    // Initialize PerfectScrollbar
    // ------------------------------

    // Chat contacts scrollbar
    if (chatContactsBody) {
      new PerfectScrollbar(chatContactsBody, {
        wheelPropagation: false,
        suppressScrollX: true
      });
    }

    // Chat history scrollbar
    if (chatHistoryBody) {
      new PerfectScrollbar(chatHistoryBody, {
        wheelPropagation: false,
        suppressScrollX: true
      });
    }

    // Sidebar left scrollbar
    if (chatSidebarLeftBody) {
      new PerfectScrollbar(chatSidebarLeftBody, {
        wheelPropagation: false,
        suppressScrollX: true
      });
    }

    // Sidebar right scrollbar
    if (chatSidebarRightBody) {
      new PerfectScrollbar(chatSidebarRightBody, {
        wheelPropagation: false,
        suppressScrollX: true
      });
    }

    // Scroll to bottom function
    function scrollToBottom() {
      chatHistoryBody.scrollTo(0, chatHistoryBody.scrollHeight);
    }
    scrollToBottom();

    // User About Maxlength Init
    if (chatSidebarLeftUserAbout.length) {
      chatSidebarLeftUserAbout.maxlength({
        alwaysShow: true,
        warningClass: 'label label-success bg-success text-white',
        limitReachedClass: 'label label-danger',
        separator: '/',
        validate: true,
        threshold: 120
      });
    }

    // Update user status
    chatUserStatus.forEach(el => {
      el.addEventListener('click', e => {
        let chatLeftSidebarUserAvatar = document.querySelector('.chat-sidebar-left-user .avatar'),
          value = e.currentTarget.value;
        //Update status in left sidebar user avatar
        chatLeftSidebarUserAvatar.removeAttribute('class');
        Helpers._addClass('avatar avatar-xl ' + userStatusObj[value] + '', chatLeftSidebarUserAvatar);
        //Update status in contacts sidebar user avatar
        let chatContactsUserAvatar = document.querySelector('.app-chat-contacts .avatar');
        chatContactsUserAvatar.removeAttribute('class');
        Helpers._addClass('flex-shrink-0 avatar ' + userStatusObj[value] + ' me-3', chatContactsUserAvatar);
      });
    });

    // Select chat or contact
    chatContactListItems.forEach(chatContactListItem => {
      // Bind click event to each chat contact list item
      chatContactListItem.addEventListener('click', e => {
        // Remove active class from chat contact list item
        chatContactListItems.forEach(chatContactListItem => {
          chatContactListItem.classList.remove('active');
        });
        // Add active class to current chat contact list item
        const clickedUser = e.currentTarget;
        clickedUser.classList.add('active');
        chatWithUserId = clickedUser.dataset.id;
        const username = clickedUser.dataset.username;
        document.querySelector('.chat-contact-info .m-0').textContent = username;
        const chatHistoryBody = document.querySelector('.chat-history-body .chat-history');
        chatHistoryBody.innerHTML = '';

        fetch('get_messages.php?to=' + chatWithUserId)
            .then(response => response.json())
            .then(messages => {
                messages.forEach(async message => {
                    const decryptedMessage = await decryptMessage(new Uint8Array(message.message.split(',').map(Number)));
                    let renderMsg = document.createElement('li');
                    renderMsg.className = 'chat-message ' + (message.sender_id == userId ? 'chat-message-right' : '');
                    let messageHtml = '<div class="d-flex overflow-hidden">';
                    if (message.sender_id != userId) {
                        messageHtml += '<div class="user-avatar flex-shrink-0 me-3"><div class="avatar avatar-sm"><img src="assets/img/avatars/1.png" alt="Avatar" class="rounded-circle" /></div></div>';
                    }
                    messageHtml += '<div class="chat-message-wrapper flex-grow-1">';
                    messageHtml += '<div class="chat-message-text">';
                    messageHtml += '<p class="mb-0 text-break">' + decryptedMessage + '</p>';
                    messageHtml += '</div>';
                    messageHtml += '</div>';
                    if (message.sender_id == userId) {
                        messageHtml += '<div class="user-avatar flex-shrink-0 ms-3"><div class="avatar avatar-sm"><img src="assets/img/avatars/1.png" alt="Avatar" class="rounded-circle" /></div></div>';
                    }
                    messageHtml += '</div>';
                    renderMsg.innerHTML = messageHtml;
                    chatHistoryBody.appendChild(renderMsg);
                });
                scrollToBottom();
            });
      });
    });

    // Filter Chats
    if (searchInput) {
      searchInput.addEventListener('keyup', e => {
        let searchValue = e.currentTarget.value.toLowerCase(),
          searchChatListItemsCount = 0,
          searchContactListItemsCount = 0,
          chatListItem0 = document.querySelector('.chat-list-item-0'),
          contactListItem0 = document.querySelector('.contact-list-item-0'),
          searchChatListItems = [].slice.call(
            document.querySelectorAll('#chat-list li:not(.chat-contact-list-item-title)')
          ),
          searchContactListItems = [].slice.call(
            document.querySelectorAll('#contact-list li:not(.chat-contact-list-item-title)')
          );

        // Search in chats
        searchChatContacts(searchChatListItems, searchChatListItemsCount, searchValue, chatListItem0);
        // Search in contacts
        searchChatContacts(searchContactListItems, searchContactListItemsCount, searchValue, contactListItem0);
      });
    }

    // Search chat and contacts function
    function searchChatContacts(searchListItems, searchListItemsCount, searchValue, listItem0) {
      searchListItems.forEach(searchListItem => {
        let searchListItemText = searchListItem.textContent.toLowerCase();
        if (searchValue) {
          if (-1 < searchListItemText.indexOf(searchValue)) {
            searchListItem.classList.add('d-flex');
            searchListItem.classList.remove('d-none');
            searchListItemsCount++;
          } else {
            searchListItem.classList.add('d-none');
          }
        } else {
          searchListItem.classList.add('d-flex');
          searchListItem.classList.remove('d-none');
          searchListItemsCount++;
        }
      });
      // Display no search fount if searchListItemsCount == 0
      if (searchListItemsCount == 0) {
        listItem0.classList.remove('d-none');
      } else {
        listItem0.classList.add('d-none');
      }
    }

    // Send Message
    formSendMessage.addEventListener('submit', e => {
      e.preventDefault();
      if (messageInput.value && chatWithUserId) {
        encryptMessage(messageInput.value).then(encrypted => {
            ws.send(JSON.stringify({ to: chatWithUserId, type: 'text-message', payload: Array.from(encrypted) }));
        });

        // Create a div and add a class
        let renderMsg = document.createElement('div');
        renderMsg.className = 'chat-message-text mt-2';
        renderMsg.innerHTML = '<p class="mb-0 text-break">' + messageInput.value + '</p>';
        document.querySelector('.chat-history-body .chat-history').appendChild(renderMsg);
        messageInput.value = '';
        scrollToBottom();
      }
    });

    // on click of chatHistoryHeaderMenu, Remove data-overlay attribute from chatSidebarLeftClose to resolve overlay overlapping issue for two sidebar
    let chatHistoryHeaderMenu = document.querySelector(".chat-history-header [data-target='#app-chat-contacts']"),
      chatSidebarLeftClose = document.querySelector('.app-chat-sidebar-left .close-sidebar');
    if(chatHistoryHeaderMenu) {
        chatHistoryHeaderMenu.addEventListener('click', e => {
          chatSidebarLeftClose.removeAttribute('data-overlay');
        });
    }
    // }

    // Speech To Text
    if (speechToText.length) {
      var SpeechRecognition = SpeechRecognition || webkitSpeechRecognition;
      if (SpeechRecognition !== undefined && SpeechRecognition !== null) {
        var recognition = new SpeechRecognition(),
          listening = false;
        speechToText.on('click', function () {
          const $this = $(this);
          recognition.onspeechstart = function () {
            listening = true;
          };
          if (listening === false) {
            recognition.start();
          }
          recognition.onerror = function (event) {
            listening = false;
          };
          recognition.onresult = function (event) {
            $this.closest('.form-send-message').find('.message-input').val(event.results[0][0].transcript);
          };
          recognition.onspeechend = function (event) {
            listening = false;
            recognition.stop();
          };
        });
      }
    }
  })();
});