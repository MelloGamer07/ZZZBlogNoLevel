<?php
    session_start();

    $Username = $_SESSION['Username'] ?? "Guest";
    $IDUser = intval($_SESSION['IdUsername'] ?? -1);
    $isGuest = ($IDUser === -1);
    $_SESSION['IdUsername'] = $IDUser;
    $IDAvatar = intval($_SESSION['IdAvatar'] ?? 0);

    $hostname = "localhost";
    $username = "root";
    $password = "";
    $database = "my_zzzblog";

    $conn = mysqli_connect($hostname, $username, $password, $database);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $profileId = isset($_GET['id']) ? intval($_GET['id']) : $IDUser;

    if ($profileId === -1) {
        die("No user specified.");
    }

    $userResult = mysqli_query($conn, "
        SELECT Id, Username, Avatar, Ruolo, DataCreazione, XP, Descrizione
        FROM Utente 
        WHERE Id = $profileId
    ");
    $utente = mysqli_fetch_assoc($userResult);

    if (!$utente) {
        die("User not found.");
    }

    $isOwnProfile = ($IDUser === intval($utente['Id']));

    $isFollowing = false;
    if (!$isOwnProfile && $IDUser !== -1) {
        $followCheck = $conn->prepare("SELECT 1 FROM Follow WHERE IdUtente = ? AND IDUtenteFollow = ?");
        $followCheck->bind_param("ii", $IDUser, $profileId);
        $followCheck->execute();
        $followCheck->store_result();
        $isFollowing = $followCheck->num_rows > 0;
        $followCheck->close();
    }

    $articlesResult = mysqli_query($conn, "
        SELECT Id, Title, Img 
        FROM Articolo 
        WHERE IdUtente = $profileId AND Pubblicato = TRUE 
        ORDER BY DataCreazione DESC
        LIMIT 6
    ");

    $friendsPerPage = 15;
    $friendPage     = max(1, intval($_GET['fp'] ?? 1));
    $friendOffset   = ($friendPage - 1) * $friendsPerPage;

    $totalFriendsResult = mysqli_query($conn, "
        SELECT COUNT(*) AS cnt
        FROM Follow
        WHERE IdUtente = $profileId
    ");
    $totalFriends = (int)(mysqli_fetch_assoc($totalFriendsResult)['cnt'] ?? 0);
    $totalFriendPages = max(1, (int)ceil($totalFriends / $friendsPerPage));

    $friendsResult = mysqli_query($conn, "
        SELECT u.Id, u.Username, u.Avatar
        FROM Follow f
        JOIN Utente u ON u.Id = f.IDUtenteFollow
        WHERE f.IdUtente = $profileId
        ORDER BY u.Username ASC
        LIMIT $friendsPerPage OFFSET $friendOffset
    ");

    $friendsList = [];
    while ($fr = mysqli_fetch_assoc($friendsResult)) {
        $friendsList[] = $fr;
    }

    $avatarSrc = "ASSETS/IMG/Avatars/Avatar" . $utente['Avatar'] . ".png";
    $avatarFallback = "ASSETS/IMG/Avatars/Avatar0.png";
    if (!file_exists(__DIR__ . '/' . $avatarSrc)) {
        $avatarSrc = $avatarFallback;
    }

    $profileDesc = htmlspecialchars($utente['Descrizione'] ?? '');
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="CSS/ProfilePage.css">
</head>
<body>

    <header>
        <div class="back-btn" id="backBtn">
            <img class="back-btn-img" src="ASSETS/IMG/UI/BackButton.png" alt="Back">
        </div>
        <div class="search-btn" onclick="openSearchModal();">
            <p>Search Users</p>
        </div>
        <div class="friend-list-btn" id="friendList" onclick="openFriendListModal()">
            <p>Friend List</p>
        </div>
        <?php if ($isOwnProfile): ?>
        <div class="logout-btn" onclick="openLogoutModal();">
            <p>Logout</p>
        </div>
        <?php endif; ?>
    </header>

    <div class="modal-overlay" id="friendListModalOverlay" onclick="closeFriendListModal(event);">
        <div class="friend-list-modal">
            <div class="friend-list-modal-header">
                <h2 class="friend-list-modal-title">Friend List</h2>
                <span class="friend-list-modal-count" id="friendListCount"></span>
            </div>
            <div class="friend-list-results" id="friendListResults">
                <p class="search-placeholder">Loading...</p>
            </div>
            <div class="friend-list-pagination" id="friendListPagination"></div>
        </div>
    </div>


    <!-- Logout Confirmation Modal -->
    <div class="logout-modal-overlay" id="logoutModalOverlay">
        <div class="logout-modal">
            <h2 class="logout-modal-title">Log Out?</h2>
            <p class="logout-modal-body">Are you sure you want to log out?</p>
            <div class="logout-modal-actions">
                <button class="logout-cancel-btn" onclick="closeLogoutModal();">Cancel</button>
                <button class="logout-confirm-btn" onclick="window.location.href='PHP/logout.php';">Log Out</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="searchModalOverlay" onclick="closeSearchModal(event);">
        <div class="search-modal">
            <input 
                class="search-input" 
                id="searchInput" 
                type="text" 
                placeholder="Search users..." 
                oninput="searchUsers(this.value);"
                autocomplete="off"
            >
            <div class="search-results" id="searchResults">
                <p class="search-placeholder">Start typing to search...</p>
            </div>
        </div>
    </div>

    <div class="edit-modal-overlay" id="editModalOverlay">
        <div class="edit-modal">
            <h2 class="edit-modal-title">Edit Profile</h2>

            <div class="edit-avatar-row">
                <img class="edit-avatar-preview" id="editAvatarPreview"
                     src="<?php echo $avatarSrc; ?>"
                     alt="Avatar preview">
                <button class="edit-change-avatar-btn" onclick="openAvatarPicker();">
                    Change Avatar
                </button>
            </div>

            <div class="edit-field">
                <label for="editUsername">Username</label>
                <input type="text"
                       id="editUsername"
                       maxlength="30"
                       value="<?php echo htmlspecialchars($utente['Username']); ?>"
                       oninput="updateCharCount('editUsername','usernameCount',30);"
                       placeholder="Your username">
                <span class="edit-char-count" id="usernameCount">
                    <?php echo mb_strlen($utente['Username']); ?>/30
                </span>
            </div>

            <div class="edit-field">
                <label for="editDesc">Description</label>
                <textarea id="editDesc"
                          maxlength="160"
                          oninput="updateCharCount('editDesc','descCount',160);"
                          placeholder="Tell the world about yourself..."><?php echo $profileDesc; ?></textarea>
                <span class="edit-char-count" id="descCount">
                    <?php echo mb_strlen($utente['Descrizione'] ?? ''); ?>/160
                </span>
            </div>

            <div class="edit-feedback" id="editFeedback"></div>

            <div class="edit-modal-actions">
                <button class="edit-cancel-btn" onclick="closeEditModal();">Cancel</button>
                <button class="edit-save-btn" id="editSaveBtn" onclick="saveProfile();">Save Changes</button>
            </div>
        </div>
    </div>

    <div class="avatar-picker-overlay" id="avatarPickerOverlay">
        <div class="avatar-picker-modal">
            <div class="avatar-picker-header">
                <h3 class="avatar-picker-title">Choose Avatar</h3>
                <button class="avatar-picker-back" onclick="closeAvatarPicker();">← Back</button>
            </div>
            <div class="avatar-grid" id="avatarGrid">
            </div>
        </div>
    </div>

    <div class="profile-container">
        <div class="profile-header">
            <div class="UID">UID: <?php echo $utente['Id']; ?></div>
            <?php if ($isOwnProfile): ?>
                <button class="edit-info-btn" onclick="openEditModal();">Edit Info</button>
                <form method="POST" action="PHP/eliminaAccount.php">
                    <input type="hidden" name="user_id" value=<?php echo $IDUser; ?>>
                    <button class="eliminate-account-btn" onclick="openCheckModal(event);">Delete Account</button>
                </form>
            <?php else: ?>
                <button class="follow-btn <?php echo $isFollowing ? 'following' : ''; ?>" 
                        id="followBtn" 
                        onclick="toggleFollow(<?php echo $utente['Id']; ?>);">
                    <p id="follow-btn-text"><?php echo $isFollowing ? 'Unfollow' : 'Follow'; ?></p>
                </button>
            <?php endif; ?>
        </div>

        <div class="profile-body">
            <div class="profile-main">
                <div class="profile-avatar">
                    <img class="pfp" id="profilePfp" src="<?php echo $avatarSrc; ?>" alt="Avatar">
                </div>
                <div class="profile-username" id="profileUsername"><?php echo htmlspecialchars($utente['Username']); ?></div>

                <div class="profile-level-container">
                    <div class="profile-lvlBar-container">
                        <div class="profile-lvlBar-completition"></div>
                    </div>
                    <div class="profile-level-badge">
                        <h2 class="profile-user-lvl">1</h2>
                        <p class="profile-level-tag">LEVEL</p>
                    </div>
                </div>

                <div class="profile-desc" id="profileDesc"><?php echo $profileDesc ?: htmlspecialchars($utente['Username']); ?></div>
            </div>

            <div class="profile-user-posts">
            <?php
                if (mysqli_num_rows($articlesResult) === 0) {
                    echo '<p class="no-posts">No posts yet.</p>';
                } else {
                    while ($row = mysqli_fetch_assoc($articlesResult)) {
                        $imgPath = __DIR__ . '/' . $row['Img'];
                        if (empty($row['Img']) || !file_exists($imgPath)) {
                            $row['Img'] = 'ASSETS/IMG/UI/plus.png';
                        }
                        $title = mb_strimwidth($row['Title'], 0, 30, '...');
                        echo '
                        <div class="post-container" onclick="goToPost('. $row['Id'].')">
                            <img class="img" src="' . $row['Img'] . '" alt="">
                            <h2 class="post-title">' . $title . '</h2>
                        </div>
                        ';
                    }
                }
                mysqli_close($conn);
            ?>
            </div>
        </div>
    </div>
    
    <?php if ($isOwnProfile): ?>
    <div class="ModalCheckCommento" id="ModalCheckCommentoDestroy">
        <h5 class="XModalCheck">X</h5>
        <p>Are you sure you want to eliminate your account?</p>
        <button class="confirm"> Destroy </button>
    </div>

    <div class="Background">
    </div>

    <script>
        let selectedForm = null;
        let CheckModal = null; 
        const Background = document.querySelector('.Background');

        function openCheckModal(e) {
            e.preventDefault();
            selectedForm = e.currentTarget.closest('form');
            CheckModal = document.getElementById('ModalCheckCommentoDestroy');

            CheckModal.style.display = "flex";
            Background.style.display = "flex";
        }
        document.querySelectorAll('.Background, .XModalCheck').forEach(btn => {
            btn.addEventListener('click', closeCheckModal);
        });
        function closeCheckModal() {
            CheckModal.style.display = "none";
            Background.style.display = "none";
        }

        document.querySelectorAll('.confirm').forEach(btn => {
            btn.addEventListener('click', () => {
                if (selectedForm) {
                    selectedForm.submit();
                }
            });
        });

    </script>
    <?php endif; ?>

    <script>
        
        <?php
        $isGuest = ($IDUser === -1);
        ?>

        window.PAGE_DATA = {
            profileId:   <?= intval($utente['Id']) ?>,
            avatarIndex: <?= intval($utente['Avatar']) ?>,
            isFollowing: <?= $isFollowing ? 'true' : 'false' ?>,
            isOwnProfile: <?= $isOwnProfile ? 'true' : 'false' ?>,
            isGuest:     <?= $isGuest ? 'true' : 'false' ?>,
            friends: <?= json_encode($friendsList) ?>,
            friendsTotalPages: <?= $totalFriendPages ?>,
            friendsCurrentPage: <?= $friendPage ?>,
            friendsTotal: <?= $totalFriends ?>
        };
    </script>
    <script src="JS/profilePage.js"></script>

</body>
</html>