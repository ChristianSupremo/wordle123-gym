<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
require_once 'includes/helpers/table_sort_helper.php';
check_login();

$action = $_GET['action'] ?? 'list';
$member_id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    include 'controllers/member_save.php';
}

include 'controllers/member_fetch.php';

if ($action === 'list') {
    $status_filter = $_GET['status_filter'] ?? 'All';
    $search = trim($_GET['search'] ?? '');
    $sort_config = get_members_sort_config();
    $sort_params = get_sort_params($sort_config);
    $members = get_members($pdo, $status_filter, $search, $sort_params);
}

include 'includes/header.php';
?>

<?php if ($action === 'list'): ?>

<div class="page-header">
    <h1 class="page-title">
        Members
        <?php
        if (!empty($sort_params['original'])) {
            echo get_sort_indicator($sort_params['original'], $sort_params['order'], $sort_config['column_labels']);
        }
        ?>
    </h1>
    <?php echo render_clear_sort_button(); ?>
</div>

<div class="page-actions">
    <div class="search-filter-group">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Search member..." id="globalSearchInput"
                   data-page="members.php" data-param="search"
                   value="<?= htmlspecialchars($search) ?>">
        </div>

        <div class="filter-dropdown">
            <button class="filter-btn" onclick="toggleFilter('members')">
                <i class="bi bi-funnel"></i> Filter <i class="bi bi-chevron-down"></i>
            </button>
            <div class="filter-dropdown-content" id="filterDropdown_members">
                <form method="GET" action="members.php">
                    <input type="hidden" name="action" value="list">
                    <?php if (!empty($sort_params['column'])): ?>
                        <input type="hidden" name="sort_by" value="<?= htmlspecialchars($sort_params['column']) ?>">
                        <input type="hidden" name="sort_order" value="<?= htmlspecialchars($sort_params['order']) ?>">
                    <?php endif; ?>
                    <?php if (!empty($search)): ?>
                        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    <?php endif; ?>
                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="All" id="filterAll" <?= $status_filter == 'All' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label for="filterAll">All Status</label>
                    </div>
                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="Active" id="filterActive" <?= $status_filter == 'Active' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label for="filterActive">Active</label>
                    </div>
                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="Inactive" id="filterInactive" <?= $status_filter == 'Inactive' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label for="filterInactive">Inactive</label>
                    </div>
                    <div class="filter-option">
                        <input type="radio" name="status_filter" value="Pending" id="filterPending" <?= $status_filter == 'Pending' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label for="filterPending">Pending</label>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <a href="#" class="add-member-btn" onclick="event.preventDefault(); openAddModal();">
        <i class="bi bi-plus-lg"></i> Add Member
    </a>
</div>

<div class="members-table-container">
    <table class="members-table">
        <thead>
            <tr>
                <?php
                echo render_sortable_header('Member ID', 'MemberID', $sort_params['original'], $sort_params['order'], 'members.php');
                echo render_sortable_header('Join Date&Time', 'JoinDate', $sort_params['original'], $sort_params['order'], 'members.php');
                echo render_sortable_header('Full Name', 'FullName', $sort_params['original'], $sort_params['order'], 'members.php');
                echo render_sortable_header('Contact Number', 'PhoneNo', $sort_params['original'], $sort_params['order'], 'members.php');
                ?>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($members) > 0): ?>
                <?php foreach ($members as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['MemberID']) ?></td>
                    <td>
                        <div class="date-cell">
                            <span class="date-main"><?= !empty($m['createdAt']) ? date('M d, Y', strtotime($m['createdAt'])) : 'N/A' ?></span>
                            <span class="date-time"><?= !empty($m['createdAt']) ? date('g:i A', strtotime($m['createdAt'])) : '' ?></span>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($m['FirstName'] . ' ' . $m['LastName']) ?></td>
                    <td><?= htmlspecialchars($m['PhoneNo'] ?? 'N/A') ?></td>
                    <td>
                        <span class="status-badge <?= strtolower($m['MembershipStatus']) ?>">
                            <?= htmlspecialchars($m['MembershipStatus']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view-member-btn" onclick="viewMember(<?= (int) $m['MemberID'] ?>)">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="action-btn edit-member-btn" onclick="editMember(<?= (int) $m['MemberID'] ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a class="action-btn" href="member_qr.php?id=<?= (int) $m['MemberID'] ?>"
                               title="View QR card" aria-label="View QR card for <?= htmlspecialchars($m['FirstName'] . ' ' . $m['LastName'], ENT_QUOTES, 'UTF-8') ?>">
                                <i class="bi bi-qr-code"></i>
                            </a>
                            <button type="button" class="action-btn" title="Create member portal invitation"
                                    aria-label="Create member portal invitation"
                                    onclick="createMemberInvitation(<?= (int) $m['MemberID'] ?>, <?= htmlspecialchars(json_encode($m['FirstName'] . ' ' . $m['LastName']), ENT_QUOTES, 'UTF-8') ?>)">
                                <i class="bi bi-person-plus"></i>
                            </button>
                            <button type="button" class="action-btn" title="Enable or disable member portal access"
                                    aria-label="Manage member portal access"
                                    onclick="manageMemberAccess(<?= (int) $m['MemberID'] ?>, <?= htmlspecialchars(json_encode($m['FirstName'] . ' ' . $m['LastName']), ENT_QUOTES, 'UTF-8') ?>)">
                                <i class="bi bi-person-lock"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center; padding:40px; color:#718096;">No members found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="pagination-container">
        <div class="pagination-info">Showing <?= count($members) ?> results</div>
        <div class="pagination">
            <button class="pagination-btn" disabled><i class="bi bi-chevron-left"></i></button>
            <button class="pagination-btn active">1</button>
        </div>
    </div>
</div>

<?php else: ?>
<?php include 'includes/member_form.php'; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/member_view_modal.php'; ?>
<?php include 'includes/member_edit_modal.php'; ?>
<?php include 'includes/member_add_modal.php'; ?>

<!-- Member portal invitation modal -->
<div id="memberInviteModal" class="member-invite-modal" aria-hidden="true">
    <div class="member-invite-backdrop" onclick="closeMemberInviteModal()"></div>
    <section class="member-invite-dialog" role="dialog" aria-modal="true" aria-labelledby="memberInviteTitle">
        <button type="button" class="member-invite-close" onclick="closeMemberInviteModal()" aria-label="Close">&times;</button>
        <h2 id="memberInviteTitle">Member portal invitation</h2>
        <p id="memberInviteMessage">Generate a secure activation link for this member.</p>
        <div id="memberInviteResult" hidden>
            <label for="memberInviteLink">Activation link</label>
            <div class="member-invite-link-row">
                <input id="memberInviteLink" type="text" readonly>
                <button type="button" class="add-member-btn" onclick="copyMemberInviteLink()">Copy link</button>
            </div>
            <small id="memberInviteExpiry"></small>
            <p class="member-invite-note">Share this link with the member through your preferred channel. No email will be sent automatically. The link expires after 48 hours and can only be used once.</p>
        </div>
        <div id="memberInviteError" class="member-invite-error" hidden></div>
        <div class="member-invite-actions">
            <button type="button" class="filter-btn" onclick="closeMemberInviteModal()">Close</button>
        </div>
    </section>
</div>
<style>
.member-invite-modal{display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;padding:20px}
.member-invite-modal.is-open{display:flex}
.member-invite-backdrop{position:absolute;inset:0;background:rgba(0,0,0,.65)}
.member-invite-dialog{position:relative;width:min(560px,100%);background:var(--card-bg,#202b3b);color:var(--text-color,#fff);border:1px solid var(--border-color,#40506a);border-radius:12px;padding:26px;box-shadow:0 20px 60px rgba(0,0,0,.35)}
.member-invite-dialog h2{font-size:20px;margin:0 32px 10px 0}
.member-invite-dialog p{color:var(--muted-text,#aab6c8);line-height:1.5}
.member-invite-close{position:absolute;right:16px;top:12px;background:none;border:0;color:inherit;font-size:28px;cursor:pointer}
.member-invite-dialog label{display:block;margin:18px 0 8px;font-size:13px}
.member-invite-link-row{display:flex;gap:8px}
.member-invite-link-row input{min-width:0;flex:1;background:var(--input-bg,#182333);color:inherit;border:1px solid var(--border-color,#40506a);border-radius:6px;padding:10px}
.member-invite-note{font-size:12px;margin-top:14px}
.member-invite-error{margin-top:16px;color:#ff7777}
.member-invite-actions{display:flex;justify-content:flex-end;margin-top:22px}
@media(max-width:520px){.member-invite-link-row{flex-direction:column}}
</style>
<script>
async function createMemberInvitation(memberId, memberName) {
    const modal=document.getElementById('memberInviteModal');
    const message=document.getElementById('memberInviteMessage');
    const result=document.getElementById('memberInviteResult');
    const error=document.getElementById('memberInviteError');
    modal.classList.add('is-open'); modal.setAttribute('aria-hidden','false');
    result.hidden=true; error.hidden=true;
    message.textContent='Creating an invitation for '+memberName+'...';
    try {
        const endpoint='controllers/member_invite.php';
        const tokenResponse=await fetch(endpoint,{credentials:'same-origin',cache:'no-store'});
        const tokenData=await tokenResponse.json();
        if(!tokenResponse.ok||!tokenData.success) throw new Error(tokenData.message||'Could not prepare the invitation.');
        const form=new FormData();
        form.append('csrf_token',tokenData.csrf_token); form.append('member_id',String(memberId));
        const response=await fetch(endpoint,{method:'POST',body:form,credentials:'same-origin'});
        const data=await response.json();
        if(!response.ok||!data.success) throw new Error(data.message||'Could not create the invitation.');
        document.getElementById('memberInviteLink').value=new URL(data.activation_path,window.location.href).href;
        document.getElementById('memberInviteExpiry').textContent='Expires: '+data.expires_at;
        result.hidden=false; message.textContent='An activation link has been created for '+data.member_name+'.';
    } catch(err) { message.textContent='The invitation could not be created.'; error.textContent=err.message; error.hidden=false; }
}
async function manageMemberAccess(memberId, memberName) {
    const endpoint='controllers/member_account_status.php';
    try {
        const res=await fetch(endpoint+'?member_id='+encodeURIComponent(memberId),{credentials:'same-origin',cache:'no-store'});
        const info=await res.json();
        if(!res.ok||!info.success) throw new Error(info.message||'Could not load portal access.');
        if(!info.has_account) { toast.error(memberName+' does not have a portal account yet. Use the invite button to create one.'); return; }
        const disabling=info.status==='Active';
        const ok=await confirm.show({
            title: disabling ? 'Disable Portal Access' : 'Enable Portal Access',
            message: disabling ? memberName+' will be signed out and unable to use the member portal.' : 'Allow '+memberName+' to sign in to the member portal again?',
            confirmText: disabling ? 'Disable' : 'Enable',
            cancelText: 'Cancel',
            type: disabling ? 'warning' : 'info'
        });
        if(!ok) return;
        const form=new FormData();
        form.append('csrf_token',info.csrf_token); form.append('member_id',String(memberId));
        form.append('status', disabling ? 'Disabled' : 'Active');
        const r2=await fetch(endpoint,{method:'POST',body:form,credentials:'same-origin'});
        const d=await r2.json();
        if(!r2.ok||!d.success) throw new Error(d.message||'Could not update portal access.');
        toast.success(d.message);
    } catch(err) { toast.error(err.message); }
}
async function copyMemberInviteLink() {
    const input=document.getElementById('memberInviteLink');
    try { await navigator.clipboard.writeText(input.value); }
    catch(err) { input.select(); document.execCommand('copy'); }
}
function closeMemberInviteModal() {
    const modal=document.getElementById('memberInviteModal');
    modal.classList.remove('is-open'); modal.setAttribute('aria-hidden','true');
}
</script>
