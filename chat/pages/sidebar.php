                <div class="messenger-sidebar">
                    <div class="messenger-sidebar-header">
                        <div class="position-relative w-100 d-flex align-items-center">
                            <label for="profile-picture-upload" style="cursor: pointer;">
                                <img id="user-profile-img" src="<?php echo htmlspecialchars($_SESSION['profile_image'] ?? 'https://via.placeholder.com/40'); ?>" alt="My Profile" class="rounded-pill me-2">
                            </label>
                            <input type="file" id="profile-picture-upload" accept="image/*" style="display: none;">
                            <span class="text-body fw-bold"><?php echo $_SESSION['username'] ?? 'My Name'; ?></span>
                            <a href="index.php?action=logout" class="btn btn-sm btn-outline-secondary ms-auto me-2" title="Logout">
                                <i class="fas fa-sign-out-alt"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-secondary" id="sidebarToggle">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                        </div>
                    </div>
                    <div class="messenger-sidebar-search">
                        <div class="position-relative w-100">
                            <button type="submit" class="btn position-absolute top-0 text-body"><i class="fa fa-search"></i></button>
                            <input type="text" class="form-control rounded-pill ps-35px" placeholder="Search Chats">
                        </div>
                    </div>
                    <div class="messenger-sidebar-body">
                        <div data-scrollbar="true" data-height="100%">
                            <div id="contact-list">
                                <!-- Dynamic contacts will be loaded here by JavaScript -->
                                <?php if (empty($contacts)): ?>
                                    <div class="text-center p-3 text-muted">No contacts found.</div>
                                <?php else: ?>
                                    <?php foreach ($contacts as $contact): ?>
                                        <div class="messenger-item">
                                            <a href="#" data-toggle="messenger-content" class="messenger-link" data-user-id="<?php echo $contact['user_id']; ?>">
                                                <div class="messenger-media">
                                                    <img alt="" src="<?php echo htmlspecialchars($contact['profile_image'] ?? 'https://via.placeholder.com/50'); ?>" class="mw-100 mh-100 rounded-pill">
                                                </div>
                                                <div class="messenger-info">
                                                    <div class="messenger-name"><?php echo htmlspecialchars($contact['username']); ?></div>
                                                    <div class="messenger-text">Last message...</div>
                                                </div>
                                                <div class="messenger-time-badge">
                                                    <div class="messenger-time"><?php echo $contact['last_message_time'] ? date('H:i', strtotime($contact['last_message_time'])) : ''; ?></div>
                                                    <?php if (!empty($contact['unread_count']) && $contact['unread_count'] > 0): ?>
                                                        <div class="messenger-badge"><?php echo $contact['unread_count']; ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="messenger-sidebar-footer">
                        <a href="#" class="btn btn-link text-center w-100" data-bs-toggle="modal" data-bs-target="#contactsModal">My Contacts</a>
                    </div>
                </div>

                <!-- Contacts Modal -->
                <div class="modal fade" id="contactsModal" tabindex="-1" aria-labelledby="contactsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="contactsModalLabel">My Contacts</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="position-relative w-100 mb-3">
                                    <button type="submit" class="btn position-absolute top-0 text-body"><i class="fa fa-search"></i></button>
                                    <input type="text" class="form-control rounded-pill ps-35px" placeholder="Search Contacts">
                                </div>
                                <div id="all-contacts-list">
                                    <!-- All contacts will be loaded here -->
                                    <div class="text-center p-3 text-muted">No contacts found.</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>