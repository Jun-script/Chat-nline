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
    let contactPollingInterval = null;

    // Helper function to format time
    function formatTime(timestamp) {
        if (!timestamp) return '';
        const now = new Date();
        const messageDate = new Date(timestamp);

        const diffSeconds = Math.floor((now - messageDate) / 1000);
        const diffMinutes = Math.floor(diffSeconds / 60);
        const diffHours = Math.floor(diffMinutes / 60);
        const diffDays = Math.floor(diffHours / 24);

        if (diffSeconds < 60) {
            return 'Just now';
        } else if (diffMinutes < 60) {
            return `${diffMinutes} min ago`;
        } else if (diffHours < 24) {
            return `${diffHours} hour ago`;
        } else if (diffDays === 1) {
            return 'Yesterday';
        } else {
            return messageDate.toLocaleDateString('en-GB'); // DD/MM/YYYY
        }
    }

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
                            const lastMessageText = contact.last_message ? contact.last_message : '';
                            const lastMessageTime = contact.last_message_time ? formatTime(contact.last_message_time) : '';
                            const unreadCount = contact.unread_count > 0 ? contact.unread_count : '';
                            const badgeClass = contact.unread_count > 0 ? '' : 'empty';

                            const contactHtml = `
                                <div class="messenger-item">
                                    <a href="#" class="messenger-link" data-user-id="${contact.user_id}">
                                        <div class="messenger-media">
                                            <img alt="" src="${contact.profile_image || 'https://via.placeholder.com/50'}" class="mw-100 mh-100 rounded-pill">
                                        </div>
                                        <div class="messenger-info">
                                            <div class="messenger-name">${contact.username}</div>
                                            <div class="messenger-text">${lastMessageText}</div>
                                        </div>
                                        <div class="messenger-time-badge">
                                            <div class="messenger-time">${lastMessageTime}</div>
                                            ${contact.unread_count > 0 ? `<div class="messenger-badge">${contact.unread_count}</div>` : ''}
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
                            let statusIcon = '';
                            if (isSender) {
                                if (message.is_read == 1) {
                                    statusIcon = '<i class="fa fa-circle-check"></i>'; // Read (colored circle check)
                                } else if (message.is_delivered == 1) {
                                    statusIcon = '<i class="fa fa-check"></i>'; // Delivered (single check)
                                } else {
                                    statusIcon = '<i class="fa fa-check"></i>'; // Sent (single check)
                                }
                            }

                            const messageHtml = `
                                <div class="widget-chat-item ${messageClass}">
                                    ${!isSender ? '<div class="widget-chat-media"><img src="https://via.placeholder.com/50" alt=""></div>' : ''}
                                    <div class="widget-chat-content">
                                        ${!isSender ? `<div class="widget-chat-name">${response.friend_info.username}</div>` : ''}
                                        <div class="widget-chat-message last">
                                            ${message.message}
                                            <span class="message-status-icon-wrapper">${statusIcon}</span>
                                        </div>
                                        <div class="message-timestamp-hover" style="display: none;">
                                            ${message.created_at}
                                        </div>
                                    </div>
                                </div>
                            `;
                            chatMessagesDiv.append(messageHtml);
                        });

                        // Add hover functionality for timestamps
                        chatMessagesDiv.find('.widget-chat-item').each(function() {
                            const $messageItem = $(this);
                            $messageItem.find('.widget-chat-message').hover(
                                function() {
                                    $messageItem.find('.message-timestamp-hover').show();
                                },
                                function() {
                                    $messageItem.find('.message-timestamp-hover').hide();
                                }
                            );
                        });
                    }
                    // Scroll to bottom
                    const scrollbarDiv = $('.messenger-content-body [data-scrollbar="true"]');
                    if (scrollbarDiv.length) {
                        scrollbarDiv[0].scrollTop = scrollbarDiv[0].scrollHeight - scrollbarDiv[0].clientHeight;
                    }

                    // Mark messages as read for the current chat friend
                    $.ajax({
                        url: 'index.php?action=mark_messages_as_read',
                        type: 'POST',
                        dataType: 'json',
                        data: { sender_id: currentChatFriendId },
                        success: function(readResponse) {
                            if (readResponse.status === 'success') {
                                // Reload contacts to update any unread counts in the sidebar
                                loadContacts();
                            } else {
                                console.error('Failed to mark messages as read after loading:', readResponse.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error marking messages as read after loading:', xhr.responseText);
                        }
                    });
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

        $('#message-input').val(''); // Clear input immediately

        const chatMessagesDiv = $('#chat-messages');
        const tempMessageId = 'temp-msg-' + Date.now() + Math.random().toString(36).substr(2, 9);

        // Immediately display the message with a pending (clock) icon
        const pendingMessageHtml = `
            <div class="widget-chat-item reply" data-temp-id="${tempMessageId}">
                <div class="widget-chat-content">
                    <div class="widget-chat-message last">
                        ${messageText}
                        <span class="message-status-icon-wrapper"><i class="fa fa-clock"></i></span>
                    </div>
                    <div class="message-timestamp-hover" style="display: none;">
                        ${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                    </div>
                </div>
            </div>
        `;
        chatMessagesDiv.append(pendingMessageHtml);

        // Add hover functionality for the newly added message
        const $pendingMessageItem = chatMessagesDiv.find(`[data-temp-id="${tempMessageId}"]`);
        $pendingMessageItem.find('.widget-chat-message').hover(
            function() {
                $pendingMessageItem.find('.message-timestamp-hover').show();
            },
            function() {
                $pendingMessageItem.find('.message-timestamp-hover').hide();
            }
        );

        // Remove "No messages yet." placeholder if it exists
        chatMessagesDiv.find('.text-muted:contains("No messages yet.")').remove();

        // Scroll to bottom
        const scrollbarDiv = $('.messenger-content-body [data-scrollbar="true"]');
        if (scrollbarDiv.length) {
            scrollbarDiv[0].scrollTop = scrollbarDiv[0].scrollHeight - scrollbarDiv[0].clientHeight;
        }

        $.ajax({
            url: 'index.php?action=send_message',
            type: 'POST',
            dataType: 'json',
            data: { receiver_id: currentChatFriendId, message: messageText },
            success: function(response) {
                if (response.status === 'success') {
                    const new_message = response.new_message;
                    const current_user_id = response.current_user_id;

                    const isSender = new_message.sender_id == current_user_id;
                    let statusIcon = '';
                    if (isSender) {
                        if (new_message.is_read == 1) {
                            statusIcon = '<i class="fa fa-circle-check"></i>'; // Read (colored circle check)
                        } else if (new_message.is_delivered == 1) {
                            statusIcon = '<i class="fa fa-check"></i>'; // Delivered (single check)
                        } else {
                            statusIcon = '<i class="fa fa-check"></i>'; // Sent (single check)
                        }
                    }

                    // Find the pending message and update its status and timestamp
                    const $pendingMessage = $(`[data-temp-id="${tempMessageId}"]`);
                    if ($pendingMessage.length) {
                        $pendingMessage.find('.message-status-icon-wrapper').html(statusIcon);
                        $pendingMessage.find('.message-timestamp-hover').html(new_message.created_at);
                    }
                    
                    loadContacts(); // Reload contacts to update last message and time
                } else {
                    // If server returns an error, keep the clock icon or change to an error icon
                    alert('Failed to send message: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                // If AJAX call fails, the clock icon remains, indicating pending/failed
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
        const clickedLink = $(this); // Store reference to the clicked link

        loadMessages(currentChatFriendId);

        // Mark messages as read for the selected contact
        $.ajax({
            url: 'index.php?action=mark_messages_as_read',
            type: 'POST',
            dataType: 'json',
            data: { sender_id: currentChatFriendId },
            success: function(response) {
                if (response.status === 'success') {
                    // Clear the unread badge in the UI
                    clickedLink.find('.messenger-badge').remove();
                    // Reload contacts to update any other unread counts if necessary
                    loadContacts(); 
                } else {
                    console.error('Failed to mark messages as read:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error marking messages as read:', xhr.responseText);
            }
        });

        // Start polling for new messages
        if (pollingInterval) {
            clearInterval(pollingInterval);
        }
        if (contactPollingInterval) { // Clear contact polling if a specific chat is selected
            clearInterval(contactPollingInterval);
        }
        pollingInterval = setInterval(function() {
            loadMessages(currentChatFriendId);
            loadContacts(); // Also update contacts in the sidebar
        }, 5000); // Poll every 5 seconds
    });

    // --- Sidebar Toggle Functionality ---
    $('#sidebarToggle').on('click', function() {
        $('body').toggleClass('sidebar-collapsed');
        const icon = $(this).find('i');
        if ($('body').hasClass('sidebar-collapsed')) {
            icon.removeClass('fa-chevron-left').addClass('fa-chevron-right');
        } else {
            icon.removeClass('fa-chevron-right').addClass('fa-chevron-left');
        }
    });

    // --- Load All Contacts for Modal ---
    function loadAllContacts() {
        $.ajax({
            url: 'index.php?action=get_all_contacts', // Assuming a new action for all contacts
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    const allContactsListDiv = $('#all-contacts-list');
                    allContactsListDiv.empty();

                    if (response.contacts.length === 0) {
                        allContactsListDiv.append('<div class="text-center p-3 text-muted">No contacts found.</div>');
                    } else {
                        response.contacts.forEach(function(contact) {
                            const contactHtml = `
                                <div class="messenger-item">
                                    <a href="#" class="messenger-link" data-user-id="${contact.user_id}">
                                        <div class="messenger-media">
                                            <img alt="" src="${contact.profile_image || 'https://via.placeholder.com/50'}" class="mw-100 mh-100 rounded-pill">
                                        </div>
                                        <div class="messenger-info">
                                            <div class="messenger-name">${contact.username}</div>
                                        </div>
                                    </a>
                                </div>
                            `;
                            allContactsListDiv.append(contactHtml);
                        });
                    }
                } else {
                    console.error('Failed to load all contacts:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading all contacts:', xhr.responseText);
            }
        });
    }

    // Trigger loading all contacts when the modal is shown
    $('#contactsModal').on('show.bs.modal', function () {
        loadAllContacts();
    });

    // --- Contacts Modal Search Functionality ---
    $('#contactsModal input[type="text"]').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('#all-contacts-list .messenger-item').each(function() {
            const contactName = $(this).find('.messenger-name').text().toLowerCase();
            if (contactName.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // --- Chat List Search Functionality ---
    $('.messenger-sidebar-header input[type="text"]').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('#contact-list .messenger-item').each(function() {
            const contactName = $(this).find('.messenger-name').text().toLowerCase();
            if (contactName.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Initial load for chat page
    if (window.location.search.includes('page=chat')) {
        loadContacts();
        // Start polling for contacts even if no chat is selected
        contactPollingInterval = setInterval(function() {
            loadContacts();
        }, 5000);
        // Optionally load messages for the first contact if available
        // Or display a "Select a contact" message
    }

    // --- Profile Picture Upload Functionality with Cropper.js ---
    let cropper; // Global variable to hold the Cropper instance

    $(document).on('change', '#profile-picture-upload', function() {
        const fileInput = this;
        if (fileInput.files && fileInput.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imageToCrop').attr('src', e.target.result);
                $('#imageCropModal').modal('show');
            };
            reader.readAsDataURL(fileInput.files[0]);
        }
    });

    $('#imageCropModal').on('shown.bs.modal', function() {
        const image = document.getElementById('imageToCrop');
        cropper = new Cropper(image, {
            aspectRatio: 1, // Square crop
            viewMode: 1, // Restrict the crop box to not exceed the canvas
            autoCropArea: 0.8, // 80% of the image
            responsive: true,
            background: false,
            zoomable: true,
            movable: true,
        });
    }).on('hidden.bs.modal', function() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        // Clear the file input so that selecting the same file again triggers the change event
        $('#profile-picture-upload').val('');
    });

    $('#cropAndUpload').on('click', function() {
        if (cropper) {
            cropper.getCroppedCanvas({
                width: 250, // Desired width for the uploaded image
                height: 250, // Desired height for the uploaded image
            }).toBlob(function(blob) {
                const formData = new FormData();
                formData.append('profile_picture', blob, 'profile.jpg'); // 'profile.jpg' is the filename

                $.ajax({
                    url: 'index.php?action=upload_profile_picture', // New PHP endpoint
                    type: 'POST',
                    data: formData,
                    processData: false, // Important: tell jQuery not to process the data
                    contentType: false, // Important: tell jQuery not to set contentType
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            alert(response.message);
                            // Update the profile picture source
                            $('#user-profile-img').attr('src', response.profile_picture_url + '?' + new Date().getTime()); // Add timestamp to bust cache
                            // Reload contacts and messages to reflect new profile picture
                            loadContacts();
                            if (currentChatFriendId) {
                                loadMessages(currentChatFriendId);
                            }
                            $('#imageCropModal').modal('hide'); // Hide the modal after successful upload
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Profile picture upload error:', {
                            status: xhr.status,
                            statusText: status,
                            error: error,
                            responseText: xhr.responseText
                        });
                        alert('An error occurred during profile picture upload: ' + xhr.responseText);
                    }
                });
            }, 'image/jpeg'); // Specify the image format
        }
    });
});