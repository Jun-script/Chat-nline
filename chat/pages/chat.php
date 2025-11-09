                <div class="messenger-content">
                    <div class="messenger-content-header">
                        <div class="messenger-content-header-media">
                            <img id="chat-contact-img" src="https://via.placeholder.com/40" alt="Chat Contact" class="rounded-pill me-2">
                        </div>
                        <div class="messenger-content-header-info">
                            <span id="chat-contact-name">Select a contact</span>
                            <small id="chat-contact-status"></small>
                        </div>
                        <div class="messenger-content-header-btn">
                            <a href="#" class="btn btn-link"><i class="fa fa-phone"></i></a>
                            <a href="#" class="btn btn-link"><i class="fa fa-video"></i></a>
                        </div>
                    </div>
                    <div class="messenger-content-body">
                        <div data-scrollbar="true" data-height="100%">
                            <div class="widget-chat" id="chat-messages">
                                <!-- Dynamic messages will be loaded here by JavaScript -->
                                <div class="text-center p-3 text-muted" id="no-messages-placeholder">No messages yet.</div>
                            </div>
                        </div>
                    </div>
                    <div class="messenger-content-footer">
                        <div class="input-group position-relative">
                            <button class="btn border-0 position-absolute top-0 bottom-0 start-0 z-2 text-body" id="trigger"><i class="far fa-face-smile"></i></button>
                            <input type="text" class="form-control rounded-start ps-45px z-1" id="message-input" name="message" placeholder="Write a message...">
                            <button class="btn btn-theme fs-13px fw-semibold" type="button" id="send-message-btn">Send <i class="fa fa-paper-plane"></i></button>
                        </div>
                    </div>
                </div>

<!-- Profile Picture Upload Modal -->
<div class="modal fade" id="profilePictureModal" tabindex="-1" aria-labelledby="profilePictureModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="profilePictureModalLabel">Upload Profile Picture</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="file" id="profileImageInput" accept="image/*" class="form-control mb-3">
        <div class="img-container">
          <img id="imageToCrop" src="" alt="Picture to crop" style="max-width: 100%;">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="uploadCroppedImage">Upload</button>
      </div>
    </div>
  </div>
</div>