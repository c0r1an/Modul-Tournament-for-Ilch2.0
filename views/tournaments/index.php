<?php
/** @var \Ilch\View $this */
$tournaments = $this->get('tournaments');
$selectedStatus = (string)($this->get('selectedStatus') ?? '');
$statusOptions = $this->get('statusOptions') ?: ['draft', 'registration_open', 'registration_closed', 'running', 'finished', 'archived'];
$user = $this->getUser();
$canManageTeams = $user && ($user->isAdmin() || $user->hasAccess('module_tournament') || $user->hasAccess('tournament_team_manage'));
$indexUrl = $this->getUrl('tournament/tournaments/index');
?>
<div class="mb-3 d-flex flex-wrap gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'tournaments', 'action' => 'index']) ?>"><?=$this->getTrans('menuTournament') ?></a>
    <?php if ($canManageTeams): ?>
        <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'teams', 'action' => 'index']) ?>"><?=$this->getTrans('myTeams') ?></a>
        <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'teams', 'action' => 'create']) ?>"><?=$this->getTrans('createTeam') ?></a>
    <?php endif; ?>
</div>

<h1><?=$this->getTrans('menuTournament') ?></h1>

<form id="tournament-status-filter-form" class="row g-2 align-items-end mb-3">
    <div class="col-sm-6 col-md-4 col-xl-3">
        <label class="form-label" for="status-filter"><?=$this->getTrans('status') ?></label>
        <select class="form-select" id="status-filter">
            <option value="<?=$this->escape($indexUrl) ?>"><?=$this->getTrans('allWithoutArchived') ?></option>
            <?php foreach ($statusOptions as $status): ?>
                <option value="<?=$this->escape($this->getUrl('tournament/tournaments/index/status/' . $status)) ?>" <?=$selectedStatus === $status ? 'selected' : '' ?>><?=$this->getTrans($status) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary" type="submit"><?=$this->getTrans('filter') ?></button>
    </div>
    <?php if ($selectedStatus !== ''): ?>
        <div class="col-auto">
            <a class="btn btn-outline-secondary" href="<?=$indexUrl ?>"><?=$this->getTrans('resetFilter') ?></a>
        </div>
    <?php endif; ?>
</form>
<script>
    (function () {
        var form = document.getElementById('tournament-status-filter-form');
        var select = document.getElementById('status-filter');
        if (!form || !select) {
            return;
        }
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var target = select.value || '<?=$this->escape($indexUrl) ?>';
            window.location.href = target;
        });
    })();
</script>

<?php if ($tournaments): ?>
<div class="row g-3">
    <?php foreach ($tournaments as $tournament): ?>
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <?php if (!empty($tournament['banner'])): ?>
                    <img src="<?=$this->getBaseUrl($tournament['banner']) ?>" class="card-img-top" alt="<?=$this->escape($tournament['title']) ?>" style="height: 150px; object-fit: cover;">
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-0"><?=$this->escape($tournament['title']) ?></h5>
                        <span class="badge text-bg-secondary ms-2"><?=$this->getTrans($tournament['status']) ?></span>
                    </div>
                    <p class="card-text mb-1"><strong><?=$this->getTrans('game') ?>:</strong> <?=$this->escape($tournament['game']) ?></p>
                    <p class="card-text mb-3"><strong><?=$this->getTrans('startAt') ?>:</strong> <?=!empty($tournament['start_at']) ? $this->escape(date('d.m.Y H:i', strtotime($tournament['start_at']))) : '-' ?></p>
                    <div class="mt-auto">
                        <a class="btn btn-outline-primary btn-sm" href="<?=$this->getUrl(['action' => 'view', 'id' => $tournament['id']]) ?>"><?=$this->getTrans('overview') ?></a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<p><?=$this->getTrans('noTournaments') ?></p>
<?php endif; ?>
