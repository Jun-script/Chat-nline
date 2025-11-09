<div class="messenger-sidebar">
                    <div class="messenger-sidebar-header">
                        <div class="position-relative w-100 d-flex align-items-center">
                            <!-- Label ve input'u kaldırıp direkt img'ye click event bağlayacağız -->
                            <label for="profile-picture-upload" style="cursor: pointer;">
                                <img id="user-profile-img" src="<?php echo htmlspecialchars($_SESSION['profile_image'] ?? 'https://via.placeholder.com/40'); ?>" 
                                     alt="My Profile" class="rounded-pill me-2">
                            </label>
                            <input type="file" id="profile-picture-upload" accept="image/*" style="display: none;">
                            <!-- added id to username for click handler -->
                            <span id="sidebar-username" class="text-body fw-bold" style="cursor: pointer;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'My Name'); ?></span>
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
                            <input type="text" class="form-control rounded-pill ps-35px" placeholder="Search in chats...">
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
                                <!-- Friendship Code Section - YENİ EKLENEN -->
                                <div class="mb-3">
                                    <label class="form-label">Your Friendship Code</label>
                                    <div class="input-group">
                                        <input type="text" id="your-friendship-code" class="form-control" readonly>
                                        <button class="btn btn-outline-secondary" type="button" id="copy-code-btn" title="Copy to clipboard">
                                            <i class="fa fa-copy"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Share this code with friends to let them add you</small>
                                </div>

                                <!-- Add Friend Section - YENİ EKLENEN -->
                                <div class="mb-3">
                                    <label class="form-label">Add Friend</label>
                                    <div class="input-group">
                                        <input type="text" id="add-friend-code" class="form-control" placeholder="Enter friendship code">
                                        <button class="btn btn-primary" type="button" id="add-friend-btn">Add</button>
                                    </div>
                                    <div id="add-friend-msg" class="small mt-1" style="display:none;"></div>
                                </div>
                                <hr>
                                <!-- YENİ EKLENEN KISIM SONU -->

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

                <!-- Profile Picture Modal -->
                <div class="modal fade" id="profilePictureModal">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Profil Fotoğrafını Düzenle</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-0">
                                <div id="crop-container" style="max-height: 400px;">
                                    <img id="imageToCrop" src="" style="max-width: 100%; display: block;">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                <button type="button" class="btn btn-primary" id="uploadCroppedImage">Kaydet</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- added: Edit profile modal -->
                <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Profile Edit Form -->
                <form id="edit-profile-form">
                    <!-- Profile Info -->
                    <div class="mb-3">
                        <label for="edit-username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="edit-username" name="username">
                    </div>
                    <div class="mb-3">
                        <label for="edit-email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit-email" name="email" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="edit-country" class="form-label">Country</label>
                        <select class="form-select" id="edit-country" name="country">
                            <option value="">Select country</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-city" class="form-label">City</label>
                        <select class="form-select" id="edit-city" name="city">
                            <option value="">Select city</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-gender" class="form-label">Gender</label>
                        <select class="form-select" id="edit-gender" name="gender">
                            <option value="">Select gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit-dob" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="edit-dob" name="dob">
                    </div>

                    <hr>

                    <!-- Change Password Section -->
                    <div class="mb-3">
                        <label for="edit-current-password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="edit-current-password" name="current_password">
                        <small class="text-muted">Required only if changing password</small>
                    </div>
                    <div class="mb-3">
                        <label for="edit-new-password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="edit-new-password" name="new_password">
                    </div>
                    <div class="mb-3">
                        <label for="edit-confirm-password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="edit-confirm-password" name="confirm_password">
                    </div>

                    <!-- Account Info (Read Only) -->
                    <div class="mb-3">
                        <label class="form-label">Account Created</label>
                        <div id="edit-created-at" class="form-control-plaintext">-</div>
                    </div>

                    <!-- Error/Success Messages -->
                    <div id="edit-profile-error" class="alert alert-danger" style="display:none;"></div>
                    <div id="edit-profile-success" class="alert alert-success" style="display:none;"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="edit-profile-form" class="btn btn-primary" id="save-profile-btn">Save changes</button>
            </div>
        </div>
    </div>
</div>