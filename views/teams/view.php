<?php
/** @var \Ilch\View $this */
$team = $this->get('team');
$members = $this->get('members');
$user = $this->getUser();
$canManageTeams = $user && ($user->isAdmin() || $user->hasAccess('module_tournament') || $user->hasAccess('tournament_team_manage'));
$isCaptain = $user && (int)$user->getId() === (int)$team['captain_user_id'];
$playerCount = is_array($members) ? count($members) : 0;
$teamViewCss = 'application/modules/tournament/static/css/team-view.css';
$teamViewCssVersion = @filemtime(dirname(__DIR__, 2) . '/static/css/team-view.css') ?: time();
?>
<link rel="stylesheet" href="<?=$this->getBaseUrl($teamViewCss . '?v=' . $teamViewCssVersion) ?>">

<div class="tk-team-view">
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary btn-sm rounded-0" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'tournaments', 'action' => 'index']) ?>"><?=$this->getTrans('menuTournament') ?></a>
        <?php if ($canManageTeams): ?>
            <a class="btn btn-outline-secondary btn-sm rounded-0" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'teams', 'action' => 'index']) ?>"><?=$this->getTrans('myTeams') ?></a>
            <a class="btn btn-outline-secondary btn-sm rounded-0" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'teams', 'action' => 'create']) ?>"><?=$this->getTrans('createTeam') ?></a>
        <?php endif; ?>
    </div>

    <div class="card tk-card rounded-0 mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="fw-semibold"><?=$this->escape($team['name']) ?></span>
            <?php if ($isCaptain): ?>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-secondary btn-sm rounded-0" href="<?=$this->getUrl(['action' => 'edit', 'id' => $team['id']]) ?>"><?=$this->getTrans('editTeam') ?></a>
                    <form method="POST" action="<?=$this->getUrl(['action' => 'del', 'id' => $team['id']]) ?>" onsubmit="return confirm('<?=$this->escape($this->getTrans('teamDeleteConfirm')) ?>');">
                        <?=$this->getTokenField() ?>
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-0"><?=$this->getTrans('deleteTeam') ?></button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <div class="tk-team-logo">
                    <?php if (!empty($team['logo'])): ?>
                        <img src="<?=$this->getBaseUrl($team['logo']) ?>" alt="<?=$this->getTrans('teamLogoAlt') ?>">
                    <?php else: ?>
                        <span><?=strtoupper(substr((string)$team['name'], 0, 1)) ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <h1 class="h3 mb-2"><?=$this->escape($team['name']) ?></h1>
                    <span class="badge text-bg-secondary rounded-0"><?=$this->getTrans('tag') ?>: <?=$this->escape($team['tag'] ?: '-') ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card tk-card rounded-0 h-100">
                <div class="card-body">
                    <div class="small text-secondary text-uppercase fw-semibold mb-1"><?=$this->getTrans('tag') ?></div>
                    <div class="fw-semibold"><?=$this->escape($team['tag'] ?: '-') ?></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card tk-card rounded-0 h-100">
                <div class="card-body">
                    <div class="small text-secondary text-uppercase fw-semibold mb-1"><?=$this->getTrans('discord') ?></div>
                    <div class="fw-semibold"><?=$this->escape($team['contact_discord'] ?: '-') ?></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card tk-card rounded-0 h-100">
                <div class="card-body">
                    <div class="small text-secondary text-uppercase fw-semibold mb-1"><?=$this->getTrans('email') ?></div>
                    <div class="fw-semibold"><?=$this->escape($team['contact_email'] ?: '-') ?></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card tk-card rounded-0 h-100">
                <div class="card-body">
                    <div class="small text-secondary text-uppercase fw-semibold mb-1"><?=$this->getTrans('playersCount') ?></div>
                    <div class="fw-semibold"><?=$playerCount ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card tk-card rounded-0">
        <div class="card-header">
            <h4 class="h6 mb-0"><?=$this->getTrans('members') ?></h4>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($members as $member): ?>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <a class="tk-member-link" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'members', 'action' => 'view', 'id' => (int)$member['id']]) ?>">
                            <div class="card tk-card rounded-0 h-100">
                                <div class="card-body">
                                    <div class="fw-semibold mb-1"><?=$this->escape($member['nickname'] ?: ('User#' . $member['user_id'])) ?></div>
                                    <div class="small text-secondary text-uppercase"><?=$this->escape($member['role']) ?></div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
