<?php include 'PHP/adminDashboardPHP.php' ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Admin Dashboard</title>
<link rel="stylesheet" href="CSS/header.css" />
<link rel="stylesheet" href="CSS/adminDashboard.css" />
</head>
<body>

<header>
    <div class="back-btn" onclick="window.location.href = 'home.php';">
        <img class="back-btn-img" src="ASSETS/IMG/UI/BackButton.png" alt="Back">
    </div>
    <div class="header-logo">ADMIN<span>PANEL</span></div>
    <nav class="nav-tabs">
        <a href="?section=approve&sort=date"
           class="nav-tab <?= $section==='approve' ? 'active' : '' ?>">
            ✦ Approve Posts
            <span class="tab-badge <?= $count_pending===0?'zero':'' ?>"><?= $count_pending ?></span>
        </a>
        <a href="?section=post_reports&sort=<?= htmlspecialchars($sort) ?>"
           class="nav-tab <?= $section==='post_reports' ? 'active blue' : '' ?>">
            ⚑ Post Reports
            <span class="tab-badge <?= $count_preports===0?'zero':'' ?>"><?= $count_preports ?></span>
        </a>
        <a href="?section=comment_reports&sort=<?= htmlspecialchars($sort) ?>"
           class="nav-tab <?= $section==='comment_reports' ? 'active red' : '' ?>">
            ⚐ Comment Reports
            <span class="tab-badge <?= $count_creports===0?'zero':'' ?>"><?= $count_creports ?></span>
        </a>
        <a href="?section=users&page=1"
           class="nav-tab <?= $section==='users' ? 'active purple' : '' ?>">
            ◈ Users
            <?php if ($count_restricted > 0): ?>
            <span class="tab-badge"><?= $count_restricted ?></span>
            <?php endif; ?>
        </a>
    </nav>
</header>

<main class="page-wrapper">

<?php if ($section==='approve'): ?>

    <div class="toolbar">
        <input class="search-field" type="text" id="searchInput"
               placeholder="Search pending posts…" oninput="filterCards()" />
        <div class="sort-bar">
            <span class="sort-label">Sort:</span>
            <a href="?section=approve&sort=date"
               class="sort-pill <?= $sort==='date'?'active':'' ?>">Date ↓</a>
        </div>
    </div>
    <h2 class="section-title">Approve Posts</h2>

    <?php if (empty($pending_posts)): ?>
        <div class="empty-state"><span class="icon">✔</span>No posts awaiting approval.</div>
    <?php else: ?>
    <div class="item-list" id="cardList">
    <?php foreach ($pending_posts as $i => $post):
        $dt  = new DateTime($post['DataCreazione']);
        $img = htmlspecialchars($post['Img']??'');
    ?>
        <div class="list-card"
             data-id="<?= $post['Id'] ?>"
             data-search="<?= htmlspecialchars(strtolower($post['Title'].' '.$post['author'])) ?>">
            <div class="card-thumb">
                <?php if($img): ?><img src="<?= $img ?>" alt="" />
                <?php else: ?><div class="thumb-placeholder">🖼</div><?php endif; ?>
                <span class="card-rank">#<?= $i+1 ?></span>
            </div>
            <div class="card-body">
                <div class="card-row">
                    <div class="card-info">
                        <div class="card-title"><?= htmlspecialchars($post['Title']) ?></div>
                        <div class="card-author">by <span><?= htmlspecialchars($post['author']) ?></span></div>
                        <?php if ($post['tags']): ?>
                        <div class="card-tags">
                            <?php foreach ($post['tags'] as $j=>$tag): ?>
                            <span class="tag <?= $j===0?'accent':($j===1?'blue':'') ?>">
                                <?= htmlspecialchars($tag) ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-stat">
                        <div class="stat-main accent"><?= $dt->format('H:i') ?></div>
                        <div class="stat-sub"><strong><?= $dt->format('d M Y') ?></strong>Submitted</div>
                    </div>
                </div>
                <div class="card-actions">
                    <button class="action-btn view" onclick="previewPost(<?= $post['Id'] ?>)">👁 Preview</button>
                    <button class="action-btn reject"
                            onclick="confirmAction('reject_post',<?= $post['Id'] ?>,'<?= htmlspecialchars(addslashes($post['Title'])) ?>')">
                        ✕ Reject
                    </button>
                    <button class="action-btn approve"
                            onclick="confirmAction('approve_post',<?= $post['Id'] ?>,'<?= htmlspecialchars(addslashes($post['Title'])) ?>')">
                        ✔ Approve
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>

<?php elseif ($section==='post_reports'): ?>

    <div class="toolbar">
        <input class="search-field" type="text" id="searchInput"
               placeholder="Search reported posts…" oninput="filterCards()" />
        <div class="sort-bar">
            <span class="sort-label">Sort:</span>
            <a href="?section=post_reports&sort=date"    class="sort-pill <?= $sort==='date'?'active':''    ?>">Date</a>
            <a href="?section=post_reports&sort=likes"   class="sort-pill <?= $sort==='likes'?'active':''   ?>">Post Likes</a>
            <a href="?section=post_reports&sort=reports" class="sort-pill <?= $sort==='reports'?'active':'' ?>">Report Count</a>
        </div>
    </div>
    <h2 class="section-title blue">Post Reports</h2>

    <?php if (empty($post_reports)): ?>
        <div class="empty-state"><span class="icon">⚑</span>No post reports to review.</div>
    <?php else: ?>
    <div class="item-list" id="cardList">
    <?php foreach ($post_reports as $i => $rep):
        $dt      = new DateTime($rep['last_report']);
        $img     = htmlspecialchars($rep['post_image']??'');
        $reasons = implode(' · ', array_slice(explode(' | ',$rep['reasons']??''),0,2));
    ?>
        <div class="list-card"
             data-id="<?= $rep['post_id'] ?>"
             data-search="<?= htmlspecialchars(strtolower($rep['post_title'].' '.$rep['post_author'])) ?>">
            <div class="card-thumb">
                <?php if($img): ?><img src="<?= $img ?>" alt="" />
                <?php else: ?><div class="thumb-placeholder">🖼</div><?php endif; ?>
                <span class="card-rank">#<?= $i+1 ?></span>
            </div>
            <div class="card-body">
                <div class="card-row">
                    <div class="card-info">
                        <div class="card-title"><?= htmlspecialchars($rep['post_title']) ?></div>
                        <div class="card-author">by <span><?= htmlspecialchars($rep['post_author']) ?></span></div>
                        <?php if($reasons): ?>
                        <div class="reason-badge">⚠ <?= htmlspecialchars($reasons) ?></div>
                        <?php endif; ?>
                        <div class="card-tags" style="margin-top:6px">
                            <span class="tag danger">⚑ <?= (int)$rep['report_count'] ?> reports</span>
                            <span class="tag blue">♥ <?= number_format((int)$rep['post_likes']) ?> likes</span>
                        </div>
                    </div>
                    <div class="card-stat">
                        <div class="stat-main blue"><?= $dt->format('H:i') ?></div>
                        <div class="stat-sub blue"><strong><?= $dt->format('d M Y') ?></strong>Last Report</div>
                    </div>
                </div>
                <div class="card-actions">
                    <button class="action-btn view" onclick="previewPost(<?= $rep['post_id'] ?>)">👁 View Post</button>
                    <button class="action-btn dismiss"
                            onclick="confirmAction('dismiss_post_report',<?= $rep['post_id'] ?>,'<?= htmlspecialchars(addslashes($rep['post_title'])) ?>')">
                        ✕ Dismiss Reports
                    </button>
                    <button class="action-btn delete"
                            onclick="confirmAction('delete_reported_post',<?= $rep['post_id'] ?>,'<?= htmlspecialchars(addslashes($rep['post_title'])) ?>')">
                        🗑 Delete Post
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>

<?php elseif ($section==='comment_reports'): ?>

    <div class="toolbar">
        <input class="search-field" type="text" id="searchInput"
               placeholder="Search comment reports…" oninput="filterCards()" />
        <div class="sort-bar">
            <span class="sort-label">Sort:</span>
            <a href="?section=comment_reports&sort=date"          class="sort-pill <?= $sort==='date'?'active':''          ?>">Date</a>
            <a href="?section=comment_reports&sort=comment_likes" class="sort-pill <?= $sort==='comment_likes'?'active':'' ?>">Comment Likes</a>
            <a href="?section=comment_reports&sort=reports"       class="sort-pill <?= $sort==='reports'?'active':''       ?>">Report Count</a>
        </div>
    </div>
    <h2 class="section-title red">Comment Reports</h2>

    <?php if (empty($comment_reports)): ?>
        <div class="empty-state"><span class="icon">⚐</span>No comment reports to review.</div>
    <?php else: ?>
    <div class="item-list" id="cardList">
    <?php foreach ($comment_reports as $i => $rep):
        $dt  = new DateTime($rep['last_report']);
        $img = htmlspecialchars($rep['post_image']??'');
    ?>
        <div class="list-card"
             data-id="<?= $rep['comment_id'] ?>"
             data-search="<?= htmlspecialchars(strtolower($rep['post_title'].' '.$rep['comment_author'].' '.$rep['comment_text'])) ?>">
            <div class="card-thumb">
                <?php if($img): ?><img src="<?= $img ?>" alt="" />
                <?php else: ?><div class="thumb-placeholder">🖼</div><?php endif; ?>
                <span class="card-rank">#<?= $i+1 ?></span>
            </div>
            <div class="card-body">
                <div class="card-row">
                    <div class="card-info">
                        <div class="card-title-sm">on: <?= htmlspecialchars($rep['post_title']) ?></div>
                        <div class="comment-block"><?= htmlspecialchars($rep['comment_text']) ?></div>
                        <span class="comment-author-link"
                              onclick="window.location.href='ProfilePage.php?id=<?= $rep['comment_author_id'] ?>#user/<?= $rep['comment_author_id'] ?>';"
                              style="cursor:pointer;">
                            ↳ <?= htmlspecialchars($rep['comment_author']) ?>
                        </span>
                        <div class="card-tags" style="margin-top:4px">
                            <span class="tag danger">⚑ <?= (int)$rep['report_count'] ?> reports</span>
                            <span class="tag blue">♥ <?= (int)$rep['comment_likes'] ?> likes</span>
                        </div>
                    </div>
                    <div class="card-stat">
                        <div class="stat-main red"><?= $dt->format('H:i') ?></div>
                        <div class="stat-sub red"><strong><?= $dt->format('d M Y') ?></strong>Last Report</div>
                    </div>
                </div>
                <div class="card-actions">
                    <button class="action-btn view" onclick="previewPost(<?= $rep['post_id'] ?>)">👁 View Post</button>
                    <button class="action-btn dismiss"
                            onclick="confirmAction('dismiss_comment_report',<?= $rep['comment_id'] ?>,'<?= htmlspecialchars(addslashes($rep['comment_author'])) ?>')">
                        ✕ Dismiss Reports
                    </button>
                    <button class="action-btn delete"
                            onclick="confirmAction('delete_comment',<?= $rep['comment_id'] ?>,'<?= htmlspecialchars(addslashes($rep['comment_author'])) ?>')">
                        🗑 Delete Comment
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>

<?php elseif ($section==='users'): ?>

    <?php
    $pag_base = '?section=users&usort='.urlencode($userSort).'&udir='.urlencode($userSortDir).($userSearch!==''?'&usearch='.urlencode($userSearch):'').'&page=';
    $sortBase = '?section=users&page=1'.($userSearch!==''?'&usearch='.urlencode($userSearch):'');
    $sortUrl = fn(string $key) => $sortBase.'&usort='.$key.'&udir='.($userSort===$key && $userSortDir==='desc' ? 'asc' : 'desc');
    $sortArrow = fn(string $key) => $userSort===$key ? ($userSortDir==='desc' ? ' ↓' : ' ↑') : '';
    ?>

    <div class="toolbar">
        <form method="GET" style="display:contents">
            <input type="hidden" name="section" value="users" />
            <input type="hidden" name="usort"   value="<?= htmlspecialchars($userSort) ?>" />
            <input class="search-field" type="text" name="usearch"
                   placeholder="Search by username or email…"
                   value="<?= htmlspecialchars($userSearch) ?>"
                   onkeydown="if(event.key==='Enter')this.form.submit()" />
        </form>
        <div class="sort-bar">
            <span class="sort-label">Sort:</span>
            <a href="<?= $sortUrl('joined') ?>"   class="sort-pill <?= $userSort==='joined'?'active':''   ?>">Joined<?=   $sortArrow('joined')   ?></a>
            <a href="<?= $sortUrl('username') ?>" class="sort-pill <?= $userSort==='username'?'active':'' ?>">Username<?= $sortArrow('username') ?></a>
            <a href="<?= $sortUrl('xp') ?>"       class="sort-pill <?= $userSort==='xp'?'active':''       ?>">XP<?=       $sortArrow('xp')       ?></a>
            <a href="<?= $sortUrl('posts') ?>"    class="sort-pill <?= $userSort==='posts'?'active':''    ?>">Posts<?=    $sortArrow('posts')    ?></a>
            <a href="<?= $sortUrl('reports') ?>"  class="sort-pill <?= $userSort==='reports'?'active':''  ?>">Reports<?=  $sortArrow('reports')  ?></a>
        </div>
    </div>

    <h2 class="section-title purple">Users
        <span style="-webkit-text-fill-color:var(--muted);font-size:.75rem;margin-left:10px">
            <?= number_format($users_total) ?> total
        </span>
    </h2>

    <?php if (empty($users_list)): ?>
        <div class="empty-state"><span class="icon">◈</span>No users found.</div>
    <?php else: ?>

    <div class="user-list">
    <?php foreach ($users_list as $i => $u):
        $globalRank = $userOffset + $i + 1;
        $dt         = new DateTime($u['DataCreazione']);
        $ruolo      = $u['Ruolo'];
        $isBanned   = (int)($u['is_banned'] ?? 0) > 0;
        $isSuspended= ($ruolo === 'onThinIce');
        $isNormal   = !$isBanned && !$isSuspended;
        $roleLabel  = $isBanned ? 'Banned' : ($isSuspended ? 'Suspended' : 'User');
        $roleCss    = $isBanned ? 'banned' : ($isSuspended ? 'onThinIce' : 'user');
        $reportCount= (int)($u['report_count'] ?? 0);
    ?>
        <div class="user-row" data-id="<?= $u['Id'] ?>" data-role="<?= htmlspecialchars($ruolo) ?>">

            <div class="user-row-rank">#<?= $globalRank ?></div>

            <div class="user-avatar-wrap">
                <img class="user-avatar"
                     src="ASSETS/IMG/Avatars/Avatar<?= (int)$u['Avatar'] ?>.png"
                     alt=""
                     onerror="this.src='ASSETS/IMG/Avatars/Avatar0.png'" />
            </div>

            <div class="user-info-col">
                <div class="user-name"
                     onclick="window.location.href='ProfilePage.php?id=<?= $u['Id'] ?>#user/<?= $u['Id'] ?>';"
                     style="cursor:pointer">
                    <?= htmlspecialchars($u['Username']) ?>
                </div>
                <div class="user-email"><?= htmlspecialchars($u['Email']) ?></div>
                <div class="user-meta">
                    <span class="role-badge <?= htmlspecialchars($roleCss) ?>"><?= $roleLabel ?></span>
                    <span style="font-size:.68rem;color:var(--muted)">
                        Joined <?= $dt->format('d M Y') ?>
                    </span>
                </div>
            </div>

            <div class="user-stat-group">
                <div class="user-stat">
                    <div class="user-stat-val accent"><?= number_format((int)$u['XP']) ?></div>
                    <div class="user-stat-label">XP</div>
                </div>
                <div class="user-stat">
                    <div class="user-stat-val purple"><?= (int)$u['post_count'] ?></div>
                    <div class="user-stat-label">Posts</div>
                </div>
                <div class="user-stat">
                    <div class="user-stat-val <?= $reportCount > 0 ? 'style="color:var(--red)"' : '' ?>"><?= $reportCount ?></div>
                    <div class="user-stat-label">Reports</div>
                </div>
                <div class="user-stat">
                    <div class="user-stat-val" style="font-size:.75rem">#<?= $u['Id'] ?></div>
                    <div class="user-stat-label">UID</div>
                </div>
            </div>

            <div class="user-actions">
                <?php if ($isNormal): ?>
                    <button class="action-btn suspend"
                            onclick="confirmAction('suspend_user',<?= $u['Id'] ?>,'<?= htmlspecialchars(addslashes($u['Username'])) ?>')">
                        ⚠ Suspend
                    </button>
                    <button class="action-btn ban"
                            onclick="confirmAction('ban_user',<?= $u['Id'] ?>,'<?= htmlspecialchars(addslashes($u['Username'])) ?>')">
                        ✕ Ban
                    </button>
                <?php elseif ($isSuspended): ?>
                    <button class="action-btn restore"
                            onclick="confirmAction('unsuspend_user',<?= $u['Id'] ?>,'<?= htmlspecialchars(addslashes($u['Username'])) ?>')">
                        ✔ Unsuspend
                    </button>
                    <button class="action-btn ban"
                            onclick="confirmAction('ban_user',<?= $u['Id'] ?>,'<?= htmlspecialchars(addslashes($u['Username'])) ?>')">
                        ✕ Ban
                    </button>
                <?php elseif ($isBanned): ?>
                    <button class="action-btn restore"
                            onclick="confirmAction('unban_user',<?= $u['Id'] ?>,'<?= htmlspecialchars(addslashes($u['Username'])) ?>')">
                        ✔ Unban
                    </button>
                    <button class="action-btn suspend"
                            onclick="confirmAction('suspend_user',<?= $u['Id'] ?>,'<?= htmlspecialchars(addslashes($u['Username'])) ?>')">
                        ⚠ Suspend
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    </div>

    <?php if ($users_pages > 1):
        $w = 2; $ps = max(1,$userPage-$w); $pe = min($users_pages,$userPage+$w); ?>
    <nav class="user-pagination">
        <?php if ($userPage>1): ?>
            <a class="pag-btn" href="<?= $pag_base.($userPage-1) ?>">← Prev</a>
        <?php else: ?><span class="pag-btn disabled">← Prev</span><?php endif; ?>

        <?php if ($ps>1): ?>
            <a class="pag-btn" href="<?= $pag_base.'1' ?>">1</a>
            <?php if ($ps>2): ?><span class="pag-ellipsis">…</span><?php endif; ?>
        <?php endif; ?>

        <?php for ($p=$ps;$p<=$pe;$p++): ?>
            <a class="pag-btn <?= $p===$userPage?'active-pag':'' ?>"
               href="<?= $pag_base.$p ?>"><?= $p ?></a>
        <?php endfor; ?>

        <?php if ($pe<$users_pages): ?>
            <?php if ($pe<$users_pages-1): ?><span class="pag-ellipsis">…</span><?php endif; ?>
            <a class="pag-btn" href="<?= $pag_base.$users_pages ?>"><?= $users_pages ?></a>
        <?php endif; ?>

        <?php if ($userPage<$users_pages): ?>
            <a class="pag-btn" href="<?= $pag_base.($userPage+1) ?>">Next →</a>
        <?php else: ?><span class="pag-btn disabled">Next →</span><?php endif; ?>
    </nav>
    <?php endif; ?>

    <?php endif;?>

<?php endif; ?>

</main>

<!-- POST PREVIEW WRAPPER (receives addPost.php HTML) -->
<div id="modal-wrapper"></div>

<!-- PREVIEW LOADING OVERLAY -->
<div class="preview-loading" id="previewLoading">
    <div class="preview-spinner"></div>
</div>

<!-- CONFIRM MODAL -->
<div class="confirm-overlay" id="confirmOverlay">
    <div class="confirm-modal">
        <div class="confirm-title" id="confirmTitle">Confirm Action</div>
        <div class="confirm-body"  id="confirmBody">Are you sure?</div>
        <div class="confirm-actions">
            <button class="action-btn view"    onclick="closeConfirm()">Cancel</button>
            <button class="action-btn approve" id="confirmBtn" onclick="executeAction()">Confirm</button>
        </div>
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script src="JS/adminDashboard.js"></script>
</body>
</html>