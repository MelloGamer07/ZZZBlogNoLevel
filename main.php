<?php
    $hostname = "localhost";
    $username = "root";
    $password = "";
    $database = "my_zzzblog";

    $conn = mysqli_connect($hostname, $username, $password, $database);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $articlesPerPage = 20;
    $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

    // Search & sort parameters
    $search  = isset($_GET['search']) ? trim($_GET['search']) : '';
    $sortBy  = isset($_GET['sort'])   ? $_GET['sort']         : 'date_desc';
    $validSorts = ['date_desc', 'date_asc', 'likes_desc', 'likes_asc'];
    if (!in_array($sortBy, $validSorts)) { $sortBy = 'date_desc'; }

    // Build WHERE clause
    $searchCondition = "a.Pubblicato = 1";
    if ($search !== '') {
        $searchSafe      = mysqli_real_escape_string($conn, $search);
        $searchCondition .= " AND a.Title LIKE '%" . $searchSafe . "%'";
    }

    // ORDER BY
    $orderByMap = [
        'date_desc'  => "a.DataCreazione DESC",
        'date_asc'   => "a.DataCreazione ASC",
        'likes_desc' => "likes_count DESC, a.DataCreazione DESC",
        'likes_asc'  => "likes_count ASC,  a.DataCreazione DESC",
    ];
    $orderBy = $orderByMap[$sortBy];

    // Count total for pagination
    $countResult = mysqli_query($conn,
        "SELECT COUNT(*) AS total
         FROM Articolo a
         WHERE $searchCondition"
    );
    $countRow      = mysqli_fetch_assoc($countResult);
    $totalArticles = $countRow['total'];
    $totalPages    = max(1, ceil($totalArticles / $articlesPerPage));

    $currentPage = min($currentPage, $totalPages);
    $offset      = ($currentPage - 1) * $articlesPerPage;

    $query = "
        SELECT
            a.Id AS ArticoloId,
            u.Id AS UtenteId,
            a.Img,
            a.Title,
            a.Descrizione,
            a.DataCreazione,
            u.Username,
            u.Avatar,
            COUNT(la.IdArticolo) AS likes_count
        FROM Articolo a
        JOIN Utente u ON a.IdUtente = u.Id
        LEFT JOIN LikeArticolo la ON la.IdArticolo = a.Id
        WHERE $searchCondition
        GROUP BY a.Id, u.Id
        ORDER BY $orderBy
        LIMIT $articlesPerPage OFFSET $offset
    ";

    $result = mysqli_query($conn, $query);

    // Build a query-string that preserves search & sort across page links
    function buildQueryString($params) {
        $parts = [];
        foreach ($params as $k => $v) {
            if ($v !== '' && $v !== null) {
                $parts[] = urlencode($k) . '=' . urlencode($v);
            }
        }
        return $parts ? '?' . implode('&', $parts) : '?';
    }

    function renderNavbar($currentPage, $totalPages, $search, $sortBy) {
        if ($totalPages <= 0) return;

        $prevPage = $currentPage - 1;
        $nextPage = $currentPage + 1;

        $window    = 2;
        $startPage = max(1, $currentPage - $window);
        $endPage   = min($totalPages, $currentPage + $window);

        $hash = '#InterKnot/';

        $base = ['search' => $search, 'sort' => $sortBy];

        echo '<nav class="pagination-nav">';

        if ($currentPage > 1) {
            $qs = buildQueryString(array_merge($base, ['page' => $prevPage]));
            echo '<a class="page-btn" href="' . $qs . $hash . '">&#8592; Prev</a>';
        } else {
            echo '<span class="page-btn disabled">&#8592; Prev</span>';
        }

        if ($startPage > 1) {
            $qs = buildQueryString(array_merge($base, ['page' => 1]));
            echo '<a class="page-btn" href="' . $qs . $hash . '">1</a>';
            if ($startPage > 2) {
                echo '<span class="page-ellipsis">…</span>';
            }
        }

        for ($i = $startPage; $i <= $endPage; $i++) {
            $activeClass = ($i === $currentPage) ? ' active' : '';
            $qs = buildQueryString(array_merge($base, ['page' => $i]));
            echo '<a class="page-btn' . $activeClass . '" href="' . $qs . $hash . '">' . $i . '</a>';
        }

        if ($endPage < $totalPages) {
            if ($endPage < $totalPages - 1) {
                echo '<span class="page-ellipsis">…</span>';
            }
            $qs = buildQueryString(array_merge($base, ['page' => $totalPages]));
            echo '<a class="page-btn" href="' . $qs . $hash . '">' . $totalPages . '</a>';
        }

        if ($currentPage < $totalPages) {
            $qs = buildQueryString(array_merge($base, ['page' => $nextPage]));
            echo '<a class="page-btn" href="' . $qs . $hash . '">Next &#8594;</a>';
        } else {
            echo '<span class="page-btn disabled">Next &#8594;</span>';
        }

        echo '</nav>';
    }
?>

<form class="search-sort-bar" method="GET" action="">
    <input type="hidden" name="page" value="1">

    <div class="search-wrapper">
        <input
            type="text"
            name="search"
            class="search-input"
            placeholder="Search articles"
            value="<?= htmlspecialchars($search) ?>"
            autocomplete="off"
        >
        <?php if ($search !== ''): ?>
            <a class="search-clear" href="?sort=<?= urlencode($sortBy) ?>&page=1#InterKnot/" title="Clear search">&#x2715;</a>
        <?php endif; ?>
    </div>

    <div class="sort-wrapper">
        <label class="sort-label">Sort by :</label>
        <button type="submit" name="sort" value="date_desc"
            class="sort-btn<?= $sortBy === 'date_desc' ? ' active' : '' ?>"
        >Newest</button>
        <button type="submit" name="sort" value="date_asc"
            class="sort-btn<?= $sortBy === 'date_asc' ? ' active' : '' ?>"
        >Oldest</button>
        <button type="submit" name="sort" value="likes_desc"
            class="sort-btn<?= $sortBy === 'likes_desc' ? ' active' : '' ?>"
        >Most liked</button>
        <button type="submit" name="sort" value="likes_asc"
            class="sort-btn<?= $sortBy === 'likes_asc' ? ' active' : '' ?>"
        >Least liked</button>
    </div>
</form>

<?php renderNavbar($currentPage, $totalPages, $search, $sortBy); ?>

<div class="posts-container">
<?php
    if (mysqli_num_rows($result) === 0): ?>
        <p class="no-results">No articles found<?= $search !== '' ? ' matching your search' : '' ?>.</p>
    <?php
    else:
        while ($row = mysqli_fetch_assoc($result)) {
            $imgPath = __DIR__ . '/' . $row['Img'];

            if (!file_exists($imgPath) || empty($row['Img'])) {
                $row['Img'] = 'ASSETS/IMG/UI/plus.png';
            }

            echo '
            <div class="post-container" id="' . $row['ArticoloId'] . '">
                <div class="post" onclick="openModal(this);">
                    <div class="post-images">
                        <img id="post-image-preview" src="' . $row['Img'] . '" alt="">
                    </div>

                    <div class="user-info">
                        <img id="user-pfp" src="ASSETS/IMG/Avatars/Avatar' . $row['Avatar'] . '.png" alt="">
                        <h4 id="user-name">' . $row['Username'] . '</h4>
                    </div>

                    <div class="post-content">
                        <h3 class="post-title">' . $row['Title'] . '</h3>
                        <p class="post-desc">' . $row['Descrizione'] . '</p>
                    </div>
                </div>
            </div>
            ';
        }
    endif;

    mysqli_close($conn);
?>
</div>

<?php renderNavbar($currentPage, $totalPages, $search, $sortBy); ?>