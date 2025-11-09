// chat/assets/js/custom.js
// This file will contain all custom JavaScript logic for AJAX calls and dynamic content updates.

$(document).ready(function() {
    // Base URL for AJAX calls
    const BASE_URL = ''; // This should be dynamically set if needed, or handled by relative paths

    // --- Login Form Submission ---
    $('form[name="login_form"]').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const email = form.find('input[name="email"]').val();
        const password = form.find('input[name="password"]').val();

        $.ajax({
            url: 'index.php?action=login',
            type: 'POST',
            dataType: 'json',
            data: { email: email, password: password },
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    window.location.href = 'index.php?page=chat'; // Redirect to chat page
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('An error occurred during login: ' + xhr.responseText);
            }
        });
    });

    // --- Register Form Submission ---
    $('form[name="register_form"]').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const username = form.find('input[name="username"]').val();
        const email = form.find('input[name="email"]').val();
        const password = form.find('input[name="password"]').val();
        const confirm_password = form.find('input[name="confirm_password"]').val();

        if (password !== confirm_password) {
            alert('Passwords do not match.');
            return;
        }

        $.ajax({
            url: 'index.php?action=register',
            type: 'POST',
            dataType: 'json',
            data: { username: username, email: email, password: password },
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message + ' You can now log in.');
                    window.location.href = 'index.php?page=login'; // Redirect to login page
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('An error occurred during registration: ' + xhr.responseText);
            }
        });
    });

    // --- Logout Functionality ---
    // Assuming there's a logout button/link with id="logout-btn"
    $('#logout-btn').on('click', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'index.php?action=logout',
            type: 'POST', // Or GET, depending on how it's handled
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    window.location.href = 'index.php?page=login'; // Redirect to login page
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('An error occurred during logout: ' + xhr.responseText);
            }
        });
    });

    // --- Dynamic Contact Loading ---
    let currentChatFriendId = null;
    let pollingInterval = null;

    function loadContacts() {
        $.ajax({
            url: 'index.php?action=get_contacts',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const contactListDiv = $('#contact-list');
                    contactListDiv.empty(); // Clear existing contacts

                    if (response.contacts.length === 0) {
                        contactListDiv.append('<div class="text-center p-3 text-muted">No contacts found.</div>');
                    } else {
                        response.contacts.forEach(function(contact) {
                            const contactHtml = `
                                <div class="messenger-item">
                                    <a href="#" class="messenger-link" data-user-id="${contact.user_id}">
                                        <div class="messenger-media">
                                            <img alt="" src="https://via.placeholder.com/50" class="mw-100 mh-100 rounded-pill">
                                        </div>
                                        <div class="messenger-info">
                                            <div class="messenger-name">${contact.username}</div>
                                            <div class="messenger-text"></div> <!-- Last message placeholder -->
                                        </div>
                                        <div class="messenger-time-badge">
                                            <div class="messenger-time"></div>
                                            <div class="messenger-badge"></div>
                                        </div>
                                    </a>
                                </div>
                            `;
                            contactListDiv.append(contactHtml);
                        });
                    }
                } else {
                    console.error('Failed to load contacts:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading contacts:', xhr.responseText);
            }
        });
    }

    // --- Dynamic Message Loading ---
    function loadMessages(friendId) {
        if (!friendId) return;

        $.ajax({
            url: 'index.php?action=get_messages',
            type: 'GET',
            dataType: 'json',
            data: { user_id: friendId },
            success: function(response) {
                if (response.status === 'success') {
                    const chatMessagesDiv = $('#chat-messages');
                    chatMessagesDiv.empty(); // Clear existing messages
                    $('#no-messages-placeholder').remove(); // Remove placeholder if messages exist

                    $('#chat-contact-name').text(response.friend_info.username);
                    // Update status based on last_seen (simplified for now)
                    $('#chat-contact-status').text('Last seen: ' + response.friend_info.last_seen);

                    if (response.messages.length === 0) {
                        chatMessagesDiv.append('<div class="text-center p-3 text-muted">No messages yet.</div>');
                    } else {
                        response.messages.forEach(function(message) {
                            const isSender = message.sender_id == response.current_user_id;
                            const messageClass = isSender ? 'reply' : '';
                            const messageHtml = `
                                <div class="widget-chat-item ${messageClass}">
                                    ${!isSender ? '<div class="widget-chat-media"><img src="https://via.placeholder.com/50" alt=""></div>' : ''}
                                    <div class="widget-chat-content">
                                        ${!isSender ? `<div class="widget-chat-name">${response.friend_info.username}</div>` : ''}
                                        <div class="widget-chat-message last">
                                            ${message.message}
                                        </div>
                                        <div class="widget-chat-status">${message.created_at}</div>
                                    </div>
                                </div>
                            `;
                            chatMessagesDiv.append(messageHtml);
                        });
                    }
                    // Scroll to bottom
                    const scrollbarDiv = $('.messenger-content-body [data-scrollbar="true"]');
                    if (scrollbarDiv.length) {
                        scrollbarDiv[0].scrollTop = scrollbarDiv[0].scrollHeight - scrollbarDiv[0].clientHeight;
                    }
                } else {
                    console.error('Failed to load messages:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading messages:', xhr.responseText);
            }
        });
    }

    // --- Send Message Functionality ---
    function sendMessage() {
        const messageText = $('#message-input').val().trim();
        if (!messageText || !currentChatFriendId) {
            return;
        }

        $.ajax({
            url: 'index.php?action=send_message',
            type: 'POST',
            dataType: 'json',
            data: { receiver_id: currentChatFriendId, message: messageText },
            success: function(response) {
                if (response.status === 'success') {
                    $('#message-input').val(''); // Clear input
                    loadMessages(currentChatFriendId); // Reload messages to show the new one
                } else {
                    alert('Failed to send message: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('An error occurred while sending message: ' + xhr.responseText);
            }
        });
    }

    $('#send-message-btn').on('click', sendMessage);
    $('#message-input').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            sendMessage();
            e.preventDefault(); // Prevent form submission
        }
    });

    // --- Contact Click Handler ---
    $(document).on('click', '.messenger-link', function(e) {
        e.preventDefault();
        $('.messenger-link').removeClass('active');
        $(this).addClass('active');
        currentChatFriendId = $(this).data('user-id');
        loadMessages(currentChatFriendId);

        // Start polling for new messages
        if (pollingInterval) {
            clearInterval(pollingInterval);
        }
        pollingInterval = setInterval(function() {
            loadMessages(currentChatFriendId);
        }, 5000); // Poll every 5 seconds
    });

    // Initial load for chat page
    if (window.location.search.includes('page=chat')) {
        loadContacts();
        // Optionally load messages for the first contact if available
        // Or display a "Select a contact" message
    }
});
