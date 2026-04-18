
<?php defined('CAMPUSLINK') or die(); ?>

<style>
.an-page{display:grid;grid-template-columns:1fr 360px;gap:1.25rem;}
@media(max-width:900px){.an-page{grid-template-columns:1fr;}}

/* Send form card */
.an-send-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;
overflow:hidden;position:sticky;top:1rem;height:fit-content;}
.an-send-head{padding:0.9rem 1.1rem;border-bottom:1px solid #e2e8f0;
background:linear-gradient(135deg,#1a56db,#0e9f6e);}
.an-send-head h3{font-size:0.88rem;font-weight:800;color:#fff;margin:0;}
.an-send-head p{font-size:0.72rem;color:rgba(255,255,255,0.8);margin:0.2rem 0 0;}
.an-send-body{padding:1.1rem;}
.an-fg{margin-bottom:0.9rem;}
.an-fg label{display:block;font-size:0.75rem;font-weight:700;color:#374151;margin-bottom:0.3rem;}
.an-fg input,.an-fg select,.an-fg textarea{width:100%;padding:0.6rem 0.85rem;
border:1.5px solid #e2e8f0;border-radius:9px;font-size:0.82rem;outline:none;
transition:border 0.2s;box-sizing:border-box;font-family:inherit;color:#1e293b;}
.an-fg input:focus,.an-fg select:focus,.an-fg textarea:focus{border-color:#1a56db;}
.an-fg textarea{resize:vertical;min-height:80px;}
.an-send-btn{width:100%;padding:0.75rem;background:linear-gradient(135deg,#1a56db,#0e9f6e);
color:#fff;border:none;border-radius:9px;font-weight:800;font-size:0.85rem;
cursor:pointer;transition:opacity 0.2s;}
.an-send-btn:hover{opacity:0.88;}

/* Log section */
.an-log-head{display:flex;align-items:center;justify-content:space-between;
flex-wrap:wrap;gap:0.75rem;margin-bottom:1rem;}
.an-log-head h2{font-size:1rem;font-weight:900;color:#1e293b;margin:0;}
.an-filters{display:flex;gap:0.4rem;flex-wrap:wrap;margin-bottom:1rem;}
.an-filter{padding:0.35rem 0.85rem;border-radius:20px;font-size:0.75rem;
font-weight:700;text-decoration:none;border:1.5px solid #e2e8f0;
color:#64748b;background:#fff;transition:all 0.15s;}
.an-filter:hover,.an-filter.active{background:#1a56db;color:#fff;border-color:#1a56db;}
.an-table-wrap{background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;}
.an-table{width:100%;border-collapse:collapse;font-size:0.8rem;}
.an-table th{padding:0.65rem 1rem;text-align:left;font-size:0.7rem;font-weight:700;
color:#64748b;text-transform:uppercase;letter-spacing:0.05em;
background:#f8fafc;border-bottom:1px solid #e2e8f0;}
.an-table td{padding:0.75rem 1rem;border-bottom:1px solid #f1f5f9;
color:#374151;vertical-align:middle;}
.an-table tr:last-child td{border-bottom:none;}
.an-table tr:hover td{background:#f8fafc;}
.an-title{font-weight:700;color:#1e293b;font-size:0.82rem;}
.an-msg{font-size:0.75rem;color:#64748b;margin-top:0.15rem;}
.badge{display:inline-block;padding:2px 8px;border-radius:20px;font-size:0.68rem;font-weight:700;}
.b-unread{background:#dbeafe;color:#1d4ed8;}
.b-read{background:#f1f5f9;color:#64748b;}
.b-vendor{background:#f0fdf4;color:#166534;}
.b-user{background:#eff6ff;color:#1a56db;}
.b-admin{background:#fef3c7;color:#92400e;}
.an-empty{text-align:center;padding:2.5rem;color:#94a3b8;}
.an-empty .ei{font-size:2rem;margin-bottom:0.5rem;}
.pag{display:flex;justify-content:center;gap:0.4rem;margin-top:1rem;flex-wrap:wrap;}
.pag a,.pag span{padding:0.4rem 0.8rem;border-radius:8px;border:1px solid #e2e8f0;
font-size:0.78rem;font-weight:600;text-decoration:none;color:#374151;}
.pag a:hover{background:#1a56db;color:#fff;border-color:#1a56db;}
.pag .active{background:#1a56db;color:#fff;border-color:#1a56db;}
.pag .disabled{opacity:0.4;pointer-events:none;}

/* Flash */
.flash-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;
padding:0.65rem 0.9rem;border-radius:9px;font-size:0.8rem;font-weight:600;margin-bottom:1rem;}
.flash-error{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;
padding:0.65rem 0.9rem;border-radius:9px;font-size:0.8rem;font-weight:600;margin-bottom:1rem;}

/* Stats strip */
.an-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:0.75rem;margin-bottom:1.25rem;}
.an-stat{background:#fff;border:1px solid #e2e8f0;border-radius:10px;
padding:0.85rem;text-align:center;}
.an-stat .sv{font-size:1.25rem;font-weight:900;color:#1e293b;}
.an-stat .sl{font-size:0.68rem;color:#64748b;font-weight:600;}
</style>

<?php
$flashes = Session::getAllFlash();
foreach ($flashes as $type => $messages):
    if (!is_array($messages)) $messages = [$messages];
    foreach ($messages as $message):
        if (empty($message)) continue;
        $cls = $type === 'error' ? 'flash-error' : 'flash-success';
?>
<div class="<?= $cls ?>"><?= e($message) ?></div>
<?php endforeach; ?>
<?php endforeach; ?>

<div style="margin-bottom:1.25rem;">
    <h1 style="font-size:1.2rem;font-weight:900;color:#1e293b;margin:0 0 0.25rem;">
        📢 Notifications
    </h1>
    <p style="font-size:0.82rem;color:#64748b;margin:0;">
        Send notifications and view the full notification log
    </p>
</div>

<!-- Stats -->
<div class="an-stats">
    <div class="an-stat">
        <div class="sv"><?= (int)$total ?></div>
        <div class="sl">Total Sent</div>
    </div>
    <div class="an-stat">
        <div class="sv"><?= (int)($unreadCount ?? 0) ?></div>
        <div class="sl">Unread</div>
    </div>
    <div class="an-stat">
        <div class="sv"><?= (int)($todayCount ?? 0) ?></div>
        <div class="sl">Sent Today</div>
    </div>
</div>

<div class="an-page">

    <!-- Log -->
    <div>
        <div class="an-log-head">
            <h2>Notification Log</h2>
            <span style="font-size:0.78rem;color:#64748b;">
                <?= (int)$total ?> total
            </span>
        </div>

        <!-- Filters -->
        <div class="an-filters">
            <a href="<?= SITE_URL ?>/admin/notifications"
               class="an-filter <?= !($filterType??'') ? 'active' : '' ?>">
                All
            </a>
            <a href="<?= SITE_URL ?>/admin/notifications?type=vendor"
               class="an-filter <?= ($filterType??'')==='vendor' ? 'active' : '' ?>">
                Vendors
            </a>
            <a href="<?= SITE_URL ?>/admin/notifications?type=user"
               class="an-filter <?= ($filterType??'')==='user' ? 'active' : '' ?>">
                Students
            </a>
            <a href="<?= SITE_URL ?>/admin/notifications?read=0"
               class="an-filter <?= isset($_GET['read']) && $_GET['read']==='0' ? 'active' : '' ?>">
                Unread Only
            </a>
        </div>

        <div class="an-table-wrap">
            <?php if (!empty($notifications)): ?>
            <table class="an-table">
                <thead>
                    <tr>
                        <th>Recipient</th>
                        <th>Notification</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notifications as $n): ?>
                    <tr>
                        <td>
                            <span class="badge b-<?= e($n['recipient_type']) ?>">
                                <?= ucfirst(e($n['recipient_type'])) ?>
                            </span>
                            <div style="font-size:0.72rem;color:#94a3b8;margin-top:0.2rem;">
                                ID #<?= (int)$n['recipient_id'] ?>
                                <?php if (!empty($n['recipient_name'])): ?>
                                · <?= e($n['recipient_name']) ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="an-title">
                                <?= e($n['title'] ?? 'Notification') ?>
                            </div>
                            <div class="an-msg">
                                <?= e(substr($n['message'] ?? '', 0, 70)) ?>
                                <?= strlen($n['message'] ?? '') > 70 ? '…' : '' ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge <?= $n['is_read'] ? 'b-read' : 'b-unread' ?>">
                                <?= $n['is_read'] ? 'Read' : 'Unread' ?>
                            </span>
                        </td>
                        <td style="white-space:nowrap;color:#94a3b8;font-size:0.75rem;">
                            <?= date('d M Y', strtotime($n['created_at'])) ?>
                            <div><?= date('g:ia', strtotime($n['created_at'])) ?></div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="an-empty">
                <div class="ei">📢</div>
                <h3 style="color:#475569;font-size:0.95rem;margin:0 0 0.4rem;">
                    No notifications yet
                </h3>
                <p style="font-size:0.8rem;margin:0;">
                    Use the form on the right to send your first notification.
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if (isset($pag) && $pag['total_pages'] > 1): ?>
        <div class="pag">
            <?php if ($pag['has_prev']): ?>
            <a href="?page=<?= $pag['prev_page'] ?>">← Prev</a>
            <?php else: ?>
            <span class="disabled">← Prev</span>
            <?php endif; ?>

            <?php for (
                $p = max(1, $pag['current_page']-2);
                $p <= min($pag['total_pages'], $pag['current_page']+2);
                $p++
            ): ?>
            <a href="?page=<?= $p ?>"
               class="<?= $p===$pag['current_page']?'active':'' ?>">
                <?= $p ?>
            </a>
            <?php endfor; ?>

            <?php if ($pag['has_next']): ?>
            <a href="?page=<?= $pag['next_page'] ?>">Next →</a>
            <?php else: ?>
            <span class="disabled">Next →</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Send Form -->
    <div>
        <div class="an-send-card">
            <div class="an-send-head">
                <h3>📨 Send Notification</h3>
                <p>Send a message to a vendor or student</p>
            </div>
            <div class="an-send-body">
                <form method="POST"
                      action="<?= SITE_URL ?>/admin/notifications/send">
                    <input type="hidden" name="csrf_token"
                           value="<?= CSRF::token() ?>">

                    <div class="an-fg">
                        <label>Recipient Type *</label>
                        <select name="recipient_type"
                                id="recipientType"
                                onchange="loadRecipients(this.value)"
                                required>
                            <option value="">Select type</option>
                            <option value="vendor">Vendor</option>
                            <option value="user">Student</option>
                            <option value="all_vendors">All Vendors</option>
                            <option value="all_users">All Students</option>
                            <option value="all">Everyone</option>
                        </select>
                    </div>

                    <div class="an-fg" id="recipientIdGroup"
                         style="display:none;">
                        <label>Select Recipient *</label>
                        <select name="recipient_id" id="recipientId">
                            <option value="">Loading...</option>
                        </select>
                    </div>

                    <div class="an-fg">
                        <label>Notification Type</label>
                        <select name="type">
                            <option value="info">ℹ️ Info</option>
                            <option value="warning">⚠️ Warning</option>
                            <option value="success">✅ Success</option>
                            <option value="reminder">⏰ Reminder</option>
                            <option value="system">⚙️ System</option>
                        </select>
                    </div>

                    <div class="an-fg">
                        <label>Title *</label>
                        <input type="text" name="title"
                               placeholder="Notification title"
                               required maxlength="100">
                    </div>

                    <div class="an-fg">
                        <label>Message *</label>
                        <textarea name="message"
                                  placeholder="Write your message here..."
                                  required maxlength="500"></textarea>
                    </div>

                    <div class="an-fg">
                        <label>Link (optional)</label>
                        <input type="text" name="link"
                               placeholder="e.g. /vendor/dashboard">
                    </div>

                    <button type="submit" class="an-send-btn">
                        📨 Send Notification
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
// Show/hide recipient ID selector based on type
function loadRecipients(type) {
    var group = document.getElementById('recipientIdGroup');
    var select = document.getElementById('recipientId');

    if (type === 'vendor' || type === 'user') {
        group.style.display = 'block';
        select.innerHTML = '<option value="">Loading...</option>';

        fetch('<?= SITE_URL ?>/admin/notifications/recipients?type=' + type)
            .then(r => r.json())
            .then(data => {
                select.innerHTML = '<option value="">Select recipient</option>';
                data.forEach(function(item) {
                    select.innerHTML +=
                        '<option value="' + item.id + '">' +
                        item.name + ' — ' + item.email +
                        '</option>';
                });
            })
            .catch(function() {
                select.innerHTML = '<option value="">Failed to load</option>';
            });
    } else {
        group.style.display = 'none';
        select.innerHTML = '';
    }
}
</script>
