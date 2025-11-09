// chat/assets/js/custom.js
// This file will contain all custom JavaScript logic for AJAX calls and dynamic content updates.

$(document).ready(function() {
    // Base URL for AJAX calls
    const BASE_URL = ''; 
    let currentChatFriendId = null;
    let pollingInterval = null;
    let contactPollingInterval = null;
    let cropper = null;
    // replace occurrences like 'https://via.placeholder.com/50' / 'https://via.placeholder.com/40'
    // with local default path:
    const LOCAL_PLACEHOLDER = 'assets/img/user/default.jpg';

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
                                            <img alt="" src="${contact.profile_image || LOCAL_PLACEHOLDER}" class="mw-100 mh-100 rounded-pill">
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
                    $('#chat-contact-img').attr('src', response.friend_info.profile_image || LOCAL_PLACEHOLDER);
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
                                    ${!isSender ? `<div class="widget-chat-media"><img src="${response.friend_info.profile_image || LOCAL_PLACEHOLDER}" alt=""></div>` : ''}
                                    <div class="widget-chat-content">
                                        ${!isSender ? `<div class="widget-chat-name">${response.friend_info.username}</div>` : ''}
                                        <div class="widget-chat-message last">
                                            ${message.message}
                                            ${statusIcon}
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
                        <i class="fa fa-clock"></i>
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
                        // Extract the class from the statusIcon string and apply it directly to the <i> element
                        const iconClassMatch = statusIcon.match(/class="([^"]*)"/);
                        if (iconClassMatch && iconClassMatch[1]) {
                            $pendingMessage.find('.widget-chat-message i').attr('class', iconClassMatch[1]);
                        }
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
                                            <img alt="" src="${contact.profile_image || LOCAL_PLACEHOLDER}" class="mw-100 mh-100 rounded-pill">
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
        
        // Load friendship code
        $.ajax({
            url: 'index.php?action=get_profile',
            type: 'GET',
            dataType: 'json'
        }).done(function(res) {
            if (res && res.status === 'success' && res.user) {
                $('#your-friendship-code').val(res.user.friendship_code || '');
            }
        });
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
    $('.messenger-sidebar-search input[type="text"]').on('keyup', function() {
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

    // --- Profile picture upload & crop ---
    (function() {
        // ensure single cropper variable in the file scope (already defined above)
        // click avatar -> open file selector
        $(document).on('click', '#user-profile-img', function() {
            $('#profile-picture-upload').click();
        });

        // when user selects file
        $(document).on('change', '#profile-picture-upload', function(e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            if (!file.type || !file.type.startsWith('image/')) {
                alert('Lütfen bir resim dosyası seçin.');
                $(this).val('');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(ev) {
                // cleanup any previous cropper
                if (cropper) {
                    try { cropper.destroy(); } catch (err) {}
                    cropper = null;
                }

                const img = document.getElementById('imageToCrop');
                if (!img) {
                    alert('Crop image element not found.');
                    return;
                }

                img.src = ev.target.result;

                // show modal then initialize cropper once when modal fully shown
                $('#profilePictureModal').off('shown.bs.modal'); // remove previous handlers
                $('#profilePictureModal').one('shown.bs.modal', function() {
                    // safe initialize
                    try {
                        cropper = new Cropper(img, {
                            aspectRatio: 1,
                            viewMode: 2,
                            autoCropArea: 0.9,
                            responsive: true,
                            background: false,
                            movable: true,
                            zoomable: true,
                            rotatable: false,
                            scalable: false
                        });
                    } catch (err) {
                        console.error('Cropper init error:', err);
                        alert('Kırpma başlatılamadı.');
                    }
                });

                $('#profilePictureModal').modal('show');
            };
            reader.readAsDataURL(file);
        });

        // modal hidden -> cleanup
        $('#profilePictureModal').on('hidden.bs.modal', function() {
            if (cropper) {
                try { cropper.destroy(); } catch (err) {}
                cropper = null;
            }
            $('#profile-picture-upload').val('');
            $('#imageToCrop').attr('src', '');
        });

        // upload cropped image
        $(document).on('click', '#uploadCroppedImage', function() {
            if (!cropper) {
                console.error('No cropper instance when trying to upload');
                alert('Resim seçilmedi veya kırpma hatası.');
                return;
            }

            let canvas;
            try {
                canvas = cropper.getCroppedCanvas({ width: 500, height: 500, imageSmoothingQuality: 'high' });
            } catch (err) {
                console.error('getCroppedCanvas error:', err);
                alert('Kırpma oluşturulamadı.');
                return;
            }

            if (!canvas) {
                alert('Kırpma başarısız.');
                return;
            }

            // ensure toBlob support fallback
            function canvasToBlob(canvas, cb) {
                if (canvas.toBlob) {
                    canvas.toBlob(cb, 'image/jpeg', 0.9);
                } else {
                    // fallback convert dataURL -> blob
                    const dataURL = canvas.toDataURL('image/jpeg', 0.9);
                    const byteString = atob(dataURL.split(',')[1]);
                    const ab = new ArrayBuffer(byteString.length);
                    const ia = new Uint8Array(ab);
                    for (let i = 0; i < byteString.length; i++) ia[i] = byteString.charCodeAt(i);
                    cb(new Blob([ab], { type: 'image/jpeg' }));
                }
            }

            $('#uploadCroppedImage').prop('disabled', true).text('Yükleniyor...');

            canvasToBlob(canvas, function(blob) {
                const formData = new FormData();
                formData.append('profile_picture', blob, 'profile.jpg');

                $.ajax({
                    url: 'index.php?action=upload_profile_picture',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json'
                }).done(function(response) {
                    console.log('Upload response:', response);
                    if (response && response.status === 'success') {
                        const newUrl = response.profile_picture_url + '?t=' + Date.now();
                        $('#user-profile-img').attr('src', newUrl);
                        if (typeof loadContacts === 'function') loadContacts();
                        $('#profilePictureModal').modal('hide');

                        // optional: show non-blocking message
                        console.info('Profil fotoğrafı başarıyla yüklendi.');
                    } else {
                        console.warn('Upload failed:', response && response.message ? response.message : 'Unknown');
                        $('#uploadCroppedImage').prop('disabled', false).text('Kaydet');
                        // show inline error UI instead of alert (implement toast if needed)
                    }
                }).fail(function(xhr) {
                    console.error('Upload AJAX failed:', xhr);
                    // remove blocking alert; use console and optionally a toast
                    $('#uploadCroppedImage').prop('disabled', false).text('Kaydet');
                    // you can show a small inline error element here if desired
                }).always(function() {
                    // already handled above; keep as safety
                    $('#uploadCroppedImage').prop('disabled', false).text('Kaydet');
                });
            });
        }); // uploadCroppedImage click handler sonu
    })(); // IIFE sonu

    // load locations data once and populate country/city selects in profile modal
    let _locationsCache = null;
    function loadLocations(cb) {
        if (_locationsCache) {
            return cb(_locationsCache);
        }
        $.getJSON('assets/data/locations.json').done(function(data) {
            _locationsCache = data;
            cb(_locationsCache);
        }).fail(function() {
            console.error('Failed to load locations.json');
            cb({});
        });
    }

    function populateCountryCity(countryValue, cityValue) {
        loadLocations(function(loc) {
            const countries = Object.keys(loc);
            const $country = $('#edit-country');
            const $city = $('#edit-city');
            $country.empty();
            $country.append('<option value="">Select country</option>');
            countries.forEach(c => $country.append(`<option value="${c}">${c}</option>`));
            if (countryValue) $country.val(countryValue);
            // populate cities for selected country
            const cities = (loc[countryValue] || []);
            $city.empty();
            if (!cities.length) {
                $city.append('<option value="">No cities</option>');
            } else {
                $city.append('<option value="">Select city</option>');
                cities.forEach(ct => $city.append(`<option value="${ct}">${ct}</option>`));
                if (cityValue) $city.val(cityValue);
            }
        });
    }

    // Open edit profile modal when username clicked
    $(document).on('click', '#sidebar-username', function(e) {
        e.preventDefault();
        $('#edit-profile-error').hide().text('');
        $('#edit-profile-success').hide().text('');
        $('#save-profile-btn').prop('disabled', false).text('Save changes');
        $('#add-friend-msg').hide().text('');

        $.ajax({
            url: 'index.php?action=get_profile',
            type: 'GET',
            dataType: 'json'
        }).done(function(res) {
            if (res && res.status === 'success' && res.user) {
                const u = res.user;
                $('#edit-username').val(u.username || '');
                $('#edit-email').val(u.email || '');
                // Friendship code eklendi
                $('#your-friendship-code').val(u.friendship_code || '');
                
                // server-side may store country only; try to split if stored as "country|city"
                let countryVal = u.country || '';
                let cityVal = '';
                if (countryVal && countryVal.indexOf('|') !== -1) {
                    const parts = countryVal.split('|');
                    countryVal = parts[0] || '';
                    cityVal = parts[1] || '';
                }
                $('#edit-gender').val(u.gender || '');
                $('#edit-dob').val(u.dob || '');
                // created_at display
                $('#edit-created-at').text(u.created_at ? (new Date(u.created_at)).toLocaleString() : '-');
                // populate selects via locations data
                populateCountryCity(countryVal, cityVal);
                $('#edit-current-password').val('');
                $('#edit-new-password').val('');
                $('#edit-confirm-password').val('');
                // ensure bootstrap modal API available
                if (typeof $().modal === 'function') {
                    $('#editProfileModal').modal('show');
                } else if (window.bootstrap && bootstrap.Modal) {
                    const modalEl = document.getElementById('editProfileModal');
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                } else {
                    console.error('Bootstrap modal not available.');
                }
            } else {
                console.error('Failed to load profile:', res && res.message);
            }
        }).fail(function(xhr) {
            console.error('Error fetching profile:', xhr.responseText || xhr.statusText);
        });
    });

    /* old
    $(document).on('click', '#copy-code-btn', function() {
        const codeInput = document.getElementById('your-friendship-code');
        if (codeInput) {
            codeInput.select();
            try {
                document.execCommand('copy');
                // Geçici başarı göstergesi
                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.html('<i class="fa fa-check"></i>');
                setTimeout(() => {
                    $btn.html(originalHtml);
                }, 1500);
            } catch (err) {
                console.error('Copy failed:', err);
            }
        }
    });*/

    // Yeni eklenen kod: Copy butonu işlevselliği
    $(document).on('click', '#copy-code-btn', async function() {
        const code = $('#your-friendship-code').val();
        if (code) {
            try {
                await navigator.clipboard.writeText(code);
                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.html('<i class="fa fa-check"></i>');
                setTimeout(() => {
                    $btn.html(originalHtml);
                }, 1500);
            } catch (err) {
                console.error('Copy failed:', err);
                // Fallback to old method
                const codeInput = document.getElementById('your-friendship-code');
                if (codeInput) {
                    codeInput.select();
                    document.execCommand('copy');
                }
            }
        }
    });

    // when country select changes, update city select
    $(document).on('change', '#edit-country', function() {
        const country = $(this).val();
        loadLocations(function(loc) {
            const cities = loc[country] || [];
            const $city = $('#edit-city');
            $city.empty();
            if (!cities.length) {
                $city.append('<option value="">No cities</option>');
            } else {
                $city.append('<option value="">Select city</option>');
                cities.forEach(ct => $city.append(`<option value="${ct}">${ct}</option>`));
            }
        });
    });

    // Add friend button in profile modal
    $(document).on('click', '#add-friend-btn', function() {
        const code = $('#add-friend-code').val().trim();
        $('#add-friend-msg').hide().removeClass('text-success text-danger').text('');
        if (!code) {
            $('#add-friend-msg').addClass('text-danger').text('Please enter a friendship code.').show();
            return;
        }
        $(this).prop('disabled', true).text('Adding...');
        $.ajax({
            url: 'index.php?action=add_contact',
            type: 'POST',
            dataType: 'json',
            data: { friendship_code: code }
        }).done(function(res) {
            if (res && res.status === 'success') {
                $('#add-friend-msg').addClass('text-success').text(res.message || 'Contact added.').show();
                // refresh contacts list
                if (typeof loadContacts === 'function') loadContacts();
            } else {
                $('#add-friend-msg').addClass('text-danger').text(res && res.message ? res.message : 'Failed to add contact.').show();
            }
        }).fail(function(xhr) {
            let msg = 'Server error';
            try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
            $('#add-friend-msg').addClass('text-danger').text(msg).show();
        }).always(function() {
            $('#add-friend-btn').prop('disabled', false).text('Add');
        });
    });

    // Submit profile edit form (include city -> send as country|city to keep DB change-free)
    $(document).on('submit', '#edit-profile-form', function(e) {
        e.preventDefault();
        $('#edit-profile-error').hide().text('');
        $('#edit-profile-success').hide().text('');
        $('#save-profile-btn').prop('disabled', true).text('Saving...');

        const country = $('#edit-country').val() || '';
        const city = $('#edit-city').val() || '';
        // send combined value to server so DB schema doesn't need immediate change
        const combinedCountry = country ? (country + (city ? '|' + city : '')) : '';

        const formData = {
            username: $('#edit-username').val().trim(),
            country: combinedCountry,
            gender: $('#edit-gender').val(),
            dob: $('#edit-dob').val(),
            current_password: $('#edit-current-password').val(),
            new_password: $('#edit-new-password').val(),
        };
        formData.confirm_password = $('#edit-confirm-password').val();

        $.ajax({
            url: 'index.php?action=update_profile',
            type: 'POST',
            dataType: 'json',
            data: formData
        }).done(function(res) {
            if (res && res.status === 'success' && res.user) {
                $('#edit-profile-success').show().text(res.message || 'Saved.');
                $('#sidebar-username').text(res.user.username);
                if (res.user.profile_image) {
                    $('#user-profile-img').attr('src', res.user.profile_image + '?t=' + Date.now());
                }
                setTimeout(function() {
                    $('#editProfileModal').modal('hide');
                }, 700);
            } else {
                $('#edit-profile-error').show().text(res && res.message ? res.message : 'Failed to save');
            }
        }).fail(function(xhr) {
            let msg = 'Server error';
            try { msg = JSON.parse(xhr.responseText).message || msg; } catch (e) {}
            $('#edit-profile-error').show().text(msg);
        }).always(function() {
            $('#save-profile-btn').prop('disabled', false).text('Save changes');
        });
    });

    // Initial load for chat page
    if (window.location.search.includes('page=chat')) {
        loadContacts();
        contactPollingInterval = setInterval(function() {
            loadContacts();
        }, 5000);
    }

    // Mobile view management
    function initializeMobileView() {
        const $sidebar = $('.messenger-sidebar');
        const $content = $('.messenger-content');
        
        // Back button handler
        $('#backToContacts').on('click', function() {
            $sidebar.removeClass('hidden');
            $content.addClass('hidden');
        });

        // Contact click handler for mobile
        $(document).on('click', '.messenger-link', function() {
            if (window.innerWidth < 768) {
                $sidebar.addClass('hidden');
                $content.removeClass('hidden');
            }
        });

        // Handle resize
        $(window).on('resize', function() {
            if (window.innerWidth >= 768) {
                $sidebar.removeClass('hidden');
                $content.removeClass('hidden');
            }
        });
    }

    // Initialize mobile view
    initializeMobileView();
}); // Closing the document ready function

// Ensure to close any additional functions or blocks if necessary