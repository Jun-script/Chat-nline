<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html
  lang="en"
  class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="assets/"
  data-template="vertical-menu-template">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>Chat</title>
    <meta name="description" content="" />
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon/favicon.ico" />
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&family=Rubik:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet" />
    <!-- Icons -->
    <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css" />
    <link rel="stylesheet" href="assets/vendor/fonts/fontawesome.css" />
    <link rel="stylesheet" href="assets/vendor/fonts/flag-icons.css" />
    <!-- Core CSS -->
    <link rel="stylesheet" href="assets/vendor/css/rtl/core.css" class="template-customizer-core-css" />
    <link rel="stylesheet" href="assets/vendor/css/rtl/theme-default.css" class="template-customizer-theme-css" />
    <link rel="stylesheet" href="assets/css/demo.css" />
    <!-- Vendors CSS -->
    <link rel="stylesheet" href="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    <link rel="stylesheet" href="assets/vendor/libs/typeahead-js/typeahead.css" />
    <link rel="stylesheet" href="assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.css" />
    <!-- Page CSS -->
    <link rel="stylesheet" href="assets/vendor/css/pages/app-chat.css" />
    <!-- Helpers -->
    <script src="assets/vendor/js/helpers.js"></script>
    <script src="assets/vendor/js/template-customizer.js"></script>
    <script src="assets/js/config.js"></script>
  </head>
  <body>
    <div class="app-chat card overflow-hidden">
      <div class="row g-0">
        <div class="col app-chat-contacts app-sidebar flex-grow-0 overflow-hidden border-end" id="app-chat-contacts">
            <div class="sidebar-body">
            <div class="d-flex align-items-center me-3 me-lg-0">
              <div class="flex-shrink-0 avatar avatar-online">
                <img src="assets/img/avatars/1.png" alt="Avatar" class="rounded-circle" />
              </div>
              <div class="flex-grow-1 ms-3">
                <h6 class="mb-0"><?php echo htmlspecialchars($_SESSION["username"]); ?></h6>
              </div>
              <a href="logout.php" class="btn btn-icon btn-sm"><i class="bx bx-log-out"></i></a>
            </div>
            <div class="d-flex align-items-center mt-3 px-2">
              <div class="input-group input-group-merge">
                <span class="input-group-text" id="basic-addon-search31"><i class="bx bx-search"></i></span>
                <input
                  type="text"
                  class="form-control"
                  placeholder="Search..."
                  aria-label="Search..."
                  aria-describedby="basic-addon-search31"
                  id="chat-search-input"
                />
              </div>
            </div>
                <ul class="list-unstyled chat-contact-list" id="contact-list">
                    <li class="chat-contact-list-item chat-contact-list-item-title">
                        <h5 class="text-primary mb-0">Contacts</h5>
                    </li>
                    <li class="contact-list-item-0 d-none">
                      <h6 class="text-muted mb-0">No contacts found</h6>
                    </li>
                    <?php
                    require_once "config.php";
                    $sql = "SELECT id, username FROM users WHERE id != ?";
                    if ($stmt = mysqli_prepare($link, $sql)) {
                        mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
                        if (mysqli_stmt_execute($stmt)) {
                            $result = mysqli_stmt_get_result($stmt);
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo '<li class="chat-contact-list-item" data-id="' . $row['id'] . '" data-username="' . $row['username'] . '">';
                                echo '<a class="d-flex align-items-center">';
                                echo '<div class="flex-shrink-0 avatar avatar-online">';
                                echo '<img src="assets/img/avatars/1.png" alt="Avatar" class="rounded-circle" />';
                                echo '</div>';
                                echo '<div class="chat-contact-info flex-grow-1 ms-3">';
                                echo '<h6 class="chat-contact-name text-truncate m-0">' . $row['username'] . '</h6>';
                                echo '</div>';
                                echo '</a>';
                                echo '</li>';
                            }
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
        <div class="col app-chat-history bg-body">
          <div class="chat-history-wrapper">
            <div class="video-container" style="display: flex; justify-content: space-around; padding: 10px; background: #f0f0f0;">
              <video id="localVideo" autoplay muted style="width: 45%; border: 1px solid #ccc;"></video>
              <video id="remoteVideo" autoplay style="width: 45%; border: 1px solid #ccc;"></video>
            </div>
            <div class="chat-history-header border-bottom">
              <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex overflow-hidden align-items-center">
                  <div class="flex-shrink-0 avatar">
                    <img
                      src="assets/img/avatars/2.png"
                      alt="Avatar"
                      class="rounded-circle"
                      data-bs-toggle="sidebar"
                      data-overlay
                      data-target="#app-chat-sidebar-right" />
                  </div>
                  <div class="chat-contact-info flex-grow-1 ms-3">
                    <h6 class="m-0">Felecia Rower</h6>
                    <small class="text-muted">Last seen: 1 hours ago</small>
                  </div>
                </div>
                <button id="callButton" class="btn btn-primary">Call</button>
              </div>
            </div>
            <div class="chat-history-body bg-body">
              <ul class="list-unstyled chat-history mb-0">
              </ul>
            </div>
            <div class="chat-history-footer shadow-sm">
              <form class="form-send-message d-flex justify-content-between align-items-center">
                <input
                  class="form-control message-input border-0 me-3 shadow-none"
                  placeholder="Type your message here" />
                <div class="message-actions d-flex align-items-center">
                  <i class="speech-to-text bx bx-microphone bx-sm cursor-pointer"></i>
                  <label for="attach-doc" class="form-label mb-0">
                    <i class="bx bx-paperclip bx-sm cursor-pointer mx-3"></i>
                    <input type="file" id="attach-doc" hidden />
                  </label>
                  <button class="btn btn-primary d-flex send-msg-btn">
                    <i class="bx bx-paper-plane me-md-1 me-0"></i>
                    <span class="align-middle d-md-inline-block d-none">Send</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script>
        const userId = <?php echo json_encode($_SESSION['id']); ?>;
        const username = <?php echo json_encode($_SESSION['username']); ?>;
    </script>
    <!-- Core JS -->
    <script src="assets/vendor/libs/jquery/jquery.js"></script>
    <script src="assets/vendor/libs/popper/popper.js"></script>
    <script src="assets/vendor/js/bootstrap.js"></script>
    <script src="assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="assets/vendor/libs/hammer/hammer.js"></script>
    <script src="assets/vendor/libs/i18n/i18n.js"></script>
    <script src="assets/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="assets/vendor/js/menu.js"></script>
    <!-- Vendors JS -->
    <script src="assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.js"></script>
    <!-- Main JS -->
    <script src="assets/js/main.js"></script>
    <!-- Page JS -->
    <script src="js/app-chat.js"></script>
  </body>
</html>