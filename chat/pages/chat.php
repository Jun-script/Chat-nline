<!-- BEGIN #content -->
<div id="content" class="app-content p-0">
	<div class="messenger">
		<div class="messenger-sidebar">
			<div class="messenger-sidebar-header">
				<div class="position-relative w-100">
					<button type="submit" class="btn position-absolute top-0 text-body"><i class="fa fa-search"></i></button>
					<input type="text" class="form-control rounded-pill ps-35px" placeholder="Search Messenger">
				</div>
				<button id="add-contact-btn" class="btn btn-theme ms-2"><i class="fa fa-user-plus"></i></button>
			</div>
			<div class="messenger-sidebar-body">
				<div data-scrollbar="true" data-height="100%" id="contact-list-container">
					<!-- Dynamic contacts will be loaded here -->
				</div>
			</div>
		</div>
		<div class="messenger-content">
			<div class="messenger-content-header">
				<div class="messenger-content-header-mobile-toggler">
					<a href="#" data-toggle="messenger-content" class="me-2">
						<i class="fa fa-chevron-left"></i>
					</a>
				</div>
				<div class="messenger-content-header-media">
					<div class="media bg-theme text-theme-color rounded-pill fs-20px fw-bold">
						<i class="fa fa-robot"></i>
					</div>
				</div>
				<div class="messenger-content-header-info">
					Mobile App Development Group
					<small>10 members</small>
				</div>
				<div class="messenger-content-header-btn">
					<a href="#" class="btn btn-link"><i class="fa fa-search"></i></a>
					<div class="dropdown">
						<a href="#" class="btn btn-link" data-bs-toggle="dropdown"><i class="fa fa-ellipsis"></i></a>
						<div class="dropdown-menu">
							<a href="#" class="dropdown-item d-flex align-items-center"><i class="fa fa-pencil my-n1 me-3"></i> Edit</a>
							<a href="#" class="dropdown-item d-flex align-items-center"><i class="fa fa-info-circle my-n1 me-3"></i> Info</a>
							<a href="#" class="dropdown-item d-flex align-items-center"><i class="fa fa-bell my-n1 me-3"></i> Mute</a>
							<a href="#" class="dropdown-item d-flex align-items-center"><i class="fa fa-circle-xmark fs-5 my-n1 me-3"></i> Clear chat history</a>
							<div class="dropdown-divider"></div>
							<a href="#" class="dropdown-item d-flex align-items-center"><i class="fa fa-trash fs-5 my-n1 me-3"></i> Delete and leave</a>
						</div>
					</div>
				</div>
			</div>
			<div class="messenger-content-body" id="message-container">
				<div data-scrollbar="true" data-height="100%">
					<div class="widget-chat">
						<div class="widget-chat-date">YESTERDAY</div>
						<div class="widget-chat-item">
							<div class="widget-chat-media"><img src="assets/img/user/user-5.jpg" alt=""></div>
							<div class="widget-chat-content">
								<div class="widget-chat-name">Ann Gray</div>
								<div class="widget-chat-message last">
									Hey folks, please check your emails. I have shared you the slide.
								</div>
								<div class="widget-chat-status">Yesterday 3:25PM</div>
							</div>
						</div>
						<div class="widget-chat-item reply">
							<div class="widget-chat-content">
								<div class="widget-chat-message last">
									No problem. I will be sure to take notes.
								</div>
								<div class="widget-chat-status">2:22PM</div>
							</div>
						</div>
						<div class="widget-chat-item">
							<div class="widget-chat-media"><img src="assets/img/user/user-1.jpg" alt=""></div>
							<div class="widget-chat-content">
								<div class="widget-chat-name">Roberto Lambert</div>
								<div class="widget-chat-message last">
									Hey Gabe, can you forward me the meeting notes?
								</div>
								<div class="widget-chat-status">4:30PM</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="messenger-content-footer">
				<form class="input-group position-relative" id="message-form">
					<button class="btn border-0 position-absolute top-0 bottom-0 start-0 z-2 text-body" id="trigger"><i class="far fa-face-smile"></i></button>
					<input type="text" class="form-control rounded-start ps-45px z-1" id="input" placeholder="Write a message...">
					<button class="btn btn-theme fs-13px fw-semibold" type="submit">Send <i class="fa fa-paper-plane"></i></button>
				</form>
			</div>
		</div>
	</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const contactListContainer = document.getElementById('contact-list-container');
    const messageContainer = document.getElementById('message-container').querySelector('[data-scrollbar="true"]');
    const chatHeaderInfo = document.querySelector('.messenger-content-header-info');
    const chatHeaderMedia = document.querySelector('.messenger-content-header-media');
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('input');
    const addContactBtn = document.getElementById('add-contact-btn');

    let currentFriendId = null; // To keep track of the currently active chat partner

    // Function to load contacts
    function loadContacts() {
        if (!contactListContainer) return;

        fetch('api/get_contacts.php')
            .then(response => response.json())
            .then(data => {
                contactListContainer.innerHTML = ''; // Clear
                if (data.status === 'success' && data.contacts.length > 0) {
                    data.contacts.forEach(contact => {
                        const contactItem = document.createElement('div');
                        contactItem.className = 'messenger-item';
                        contactItem.innerHTML = `
                            <a href="#" data-user-id="${contact.user_id}" data-username="${contact.username}" class="messenger-link">
                                <div class="messenger-media"><img alt="${contact.username}" src="assets/img/user/user-${Math.floor(Math.random() * 8) + 1}.jpg" class="mw-100 mh-100 rounded-pill"></div>
                                <div class="messenger-info">
                                    <div class="messenger-name">${contact.username}</div>
                                </div>
                                <div class="messenger-time-badge"></div>
                            </a>`;
                        contactListContainer.appendChild(contactItem);
                    });
                } else {
                    contactListContainer.innerHTML = '<div class="text-center text-muted p-3">No contacts found.</div>';
                }
            })
            .catch(error => {
                console.error('Error fetching contacts:', error);
                contactListContainer.innerHTML = '<div class="text-center text-danger p-3">Error loading contacts.</div>';
            });
    }

    // Function to load messages for a specific user
    function loadMessages(friendId) {
        if (!messageContainer) return;
        
        currentFriendId = friendId; // Set the current friend ID

        messageContainer.innerHTML = '<div class="text-center text-muted p-3">Loading messages...</div>';

        fetch(`api/get_messages.php?user_id=${friendId}`)
            .then(response => response.json())
            .then(data => {
                messageContainer.innerHTML = ''; // Clear loading message
                if (data.status === 'success') {
                    // Update header
                    chatHeaderInfo.innerHTML = `${data.friend_info.username} <small>Online</small>`;
                    chatHeaderMedia.innerHTML = `<div class="messenger-media"><img alt="${data.friend_info.username}" src="assets/img/user/user-2.jpg" class="mw-100 mh-100 rounded-pill"></div>`;

                    if (data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            const messageDiv = document.createElement('div');
                            const isReply = msg.sender_id == data.current_user_id;
                            messageDiv.className = isReply ? 'widget-chat-item reply' : 'widget-chat-item';
                            
                            const messageContent = `
                                <div class="widget-chat-content">
                                    <div class="widget-chat-message last">${msg.message}</div>
                                    <div class="widget-chat-status">${new Date(msg.created_at).toLocaleTimeString()}</div>
                                </div>
                            `;
                            
                            // For received messages, add the user avatar
                            const mediaContent = isReply ? '' : `<div class="widget-chat-media"><img src="assets/img/user/user-2.jpg" alt=""></div>`;

                            messageDiv.innerHTML = mediaContent + messageContent;
                            messageContainer.appendChild(messageDiv);
                        });
                    } else {
                        messageContainer.innerHTML = '<div class="text-center text-muted p-3">This is the beginning of your conversation.</div>';
                    }
                    // Scroll to the bottom
                    messageContainer.scrollTop = messageContainer.scrollHeight;
                } else {
                    messageContainer.innerHTML = `<div class="text-center text-danger p-3">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error fetching messages:', error);
                messageContainer.innerHTML = '<div class="text-center text-danger p-3">Error loading messages.</div>';
            });
    }

    // Event delegation for clicking on a contact
    contactListContainer.addEventListener('click', function(e) {
        const link = e.target.closest('.messenger-link');
        if (link) {
            e.preventDefault();
            
            // Remove active class from all other links
            document.querySelectorAll('.messenger-link.active').forEach(activeLink => {
                activeLink.classList.remove('active');
            });
            // Add active class to the clicked link
            link.classList.add('active');

            const userId = link.dataset.userId;
            loadMessages(userId);
        }
    });

    // Event listener for sending messages
    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!currentFriendId) {
            alert('Please select a contact to send a message.');
            return;
        }

        const messageText = messageInput.value.trim();
        if (messageText === '') {
            return; // Don't send empty messages
        }

        const formData = new FormData();
        formData.append('receiver_id', currentFriendId);
        formData.append('message', messageText);

        fetch('api/send_message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                messageInput.value = ''; // Clear input field

                // Optimistically add the new message to the chat window
                const msg = data.new_message;
                const messageDiv = document.createElement('div');
                messageDiv.className = 'widget-chat-item reply'; // Always 'reply' for sent messages
                messageDiv.innerHTML = `
                    <div class="widget-chat-content">
                        <div class="widget-chat-message last">${msg.message}</div>
                        <div class="widget-chat-status">${new Date(msg.created_at).toLocaleTimeString()}</div>
                    </div>
                `;
                messageContainer.appendChild(messageDiv);
                messageContainer.scrollTop = messageContainer.scrollHeight; // Scroll to bottom
            } else {
                console.error('Error sending message:', data.message);
                alert('Error sending message: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Network error sending message:', error);
            alert('Network error sending message.');
        });
    });

    // Event listener for adding contacts
    addContactBtn.addEventListener('click', function() {
        const friendshipCode = prompt('Enter the friendship code of the person you want to add:');
        if (friendshipCode) {
            const formData = new FormData();
            formData.append('friendship_code', friendshipCode);

            fetch('api/add_contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    loadContacts(); // Refresh contact list
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Network error adding contact:', error);
                alert('Network error adding contact.');
            });
        }
    });

    // Initial load
    loadContacts();
});
</script>
<!-- END #content -->