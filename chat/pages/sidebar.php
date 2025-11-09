                <div class="messenger-sidebar">
                    <div class="messenger-sidebar-header">
                        <div class="position-relative w-100">
                            <img src="https://via.placeholder.com/40" alt="My Profile" class="rounded-pill me-2">
                            <span class="text-body fw-bold"><?php echo $_SESSION['username'] ?? 'My Name'; ?></span>
                            <a href="index.php?action=logout" class="btn btn-sm btn-outline-secondary ms-auto" title="Logout">
                                <i class="fas fa-sign-out-alt"></i>
                            </a>
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
                                                    <img alt="" src="https://via.placeholder.com/50" class="mw-100 mh-100 rounded-pill">
                                                </div>
                                                <div class="messenger-info">
                                                    <div class="messenger-name"><?php echo htmlspecialchars($contact['username']); ?></div>
                                                    <div class="messenger-text">Last message...</div>
                                                </div>
                                                <div class="messenger-time-badge">
                                                    <div class="messenger-time"></div>
                                                    <div class="messenger-badge"></div>
                                                </div>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>