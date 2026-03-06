<?php
/** @var \Ilch\View $this */
$member = $this->get('member');
$team = $this->get('team');
$profile = $this->get('profile') ?: [];
$displayName = $this->get('displayName');
$shownNickname = $profile['nickname'] ?? ($member['nickname'] ?? '');
$shownName = $profile['full_name'] ?? ($displayName ?: '-');
$fallbackMember = $this->getTrans('memberNumber', (string)$member['id']);
$user = $this->getUser();
$canManageTeams = $user && ($user->isAdmin() || $user->hasAccess('module_tournament') || $user->hasAccess('tournament_team_manage'));
?>
<div class="mb-3 d-flex flex-wrap gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'tournaments', 'action' => 'index']) ?>"><?=$this->getTrans('menuTournament') ?></a>
    <?php if ($canManageTeams): ?>
        <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'teams', 'action' => 'index']) ?>"><?=$this->getTrans('myTeams') ?></a>
        <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'teams', 'action' => 'create']) ?>"><?=$this->getTrans('createTeam') ?></a>
    <?php endif; ?>
</div>
<h1><?=$this->escape($shownNickname !== '' ? $shownNickname : ($displayName ?: $fallbackMember)) ?></h1>

<p><strong><?=$this->getTrans('team') ?>:</strong> <a href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'teams', 'action' => 'view', 'id' => (int)$team['id']]) ?>"><?=$this->escape($team['name']) ?></a></p>

<div class="card">
    <div class="card-body">
        <p><strong><?=$this->getTrans('name') ?>:</strong> <?=$this->escape($shownName) ?></p>
        <p><strong><?=$this->getTrans('nickname') ?>:</strong> <?=$this->escape($shownNickname ?: '-') ?></p>
        <p><strong><?=$this->getTrans('age') ?>:</strong> <?=$this->escape((string)($profile['age'] ?? '-')) ?></p>
        <p><strong><?=$this->getTrans('gender') ?>:</strong> <?=$this->escape($profile['gender'] ?? '-') ?></p>
        <p><strong><?=$this->getTrans('homepage') ?>:</strong>
            <?php if (!empty($profile['homepage'])): ?>
                <a href="<?=$this->escape($profile['homepage']) ?>" target="_blank" rel="noopener noreferrer"><?=$this->escape($profile['homepage']) ?></a>
            <?php else: ?>
                -
            <?php endif; ?>
        </p>
        <p><strong><?=$this->getTrans('socialLinks') ?>:</strong><br><?=nl2br($this->escape($profile['social_links'] ?? '-')) ?></p>
        <p><strong><?=$this->getTrans('playedGames') ?>:</strong><br><?=nl2br($this->escape($profile['games'] ?? '-')) ?></p>
        <p><strong><?=$this->getTrans('aboutMe') ?>:</strong><br><?=nl2br($this->escape($profile['bio'] ?? '-')) ?></p>
    </div>
</div>

<?php if ($this->get('canEdit')): ?>
    <p class="mt-3">
        <a class="btn btn-outline-secondary" href="<?=$this->getUrl(['action' => 'edit', 'id' => (int)$member['id']]) ?>"><?=$this->getTrans('edit') ?></a>
    </p>
<?php endif; ?>
