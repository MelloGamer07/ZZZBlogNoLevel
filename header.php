<header>
    <div class="profile-btn" id="profile-btn" onclick="handleProfileClick()">
        <div class="profile-btn-extra">
            <?php echo '<img class="pfp" src="ASSETS/IMG/Avatars/Avatar' . $IDAvatar . '.png">' ?>
            <div class="user-data">
                <p id="username"><?= htmlspecialchars($Username) ?></p>
                <div class="lvlBar-container">
                    <div class="lvlBar-completition"></div>
                </div>
                <div class="user-level-container">
                    <h1 id="user-lvl">1</h1>
                    <p id="level-tag">LEVEL</p>
                </div>
            </div>
        </div>
    </div>

    <h1 class="title-page" id="title" data-text="Home page">Home page</h1>

    <?php include 'PHP/enterBtn.php'; ?>
</header>