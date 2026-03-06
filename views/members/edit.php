<?php
/** @var \Ilch\View $this */
$member = $this->get('member');
$team = $this->get('team');
$profile = $this->get('profile') ?: [];
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
<h1><?=$this->getTrans('edit') ?>: <?=$this->escape($member['nickname'] ?: $fallbackMember) ?></h1>
<p><strong><?=$this->getTrans('team') ?>:</strong> <a href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'teams', 'action' => 'view', 'id' => (int)$team['id']]) ?>"><?=$this->escape($team['name']) ?></a></p>

<form method="POST" action="">
    <?=$this->getTokenField() ?>
    <div class="row">
        <div class="col-xl-6 mb-3">
            <label class="form-label" for="full_name"><?=$this->getTrans('name') ?></label>
            <input class="form-control" type="text" id="full_name" name="full_name" value="<?=$this->escape($profile['full_name'] ?? '') ?>">
        </div>
        <div class="col-xl-6 mb-3">
            <label class="form-label" for="nickname"><?=$this->getTrans('nickname') ?></label>
            <input class="form-control" type="text" id="nickname" name="nickname" value="<?=$this->escape($profile['nickname'] ?? ($member['nickname'] ?? '')) ?>">
        </div>
    </div>
    <div class="row">
        <div class="col-xl-4 mb-3">
            <label class="form-label" for="age"><?=$this->getTrans('age') ?></label>
            <input class="form-control" type="number" min="0" id="age" name="age" value="<?=$this->escape((string)($profile['age'] ?? '')) ?>">
        </div>
        <div class="col-xl-8 mb-3">
            <label class="form-label" for="gender"><?=$this->getTrans('gender') ?></label>
            <input class="form-control" type="text" id="gender" name="gender" value="<?=$this->escape($profile['gender'] ?? '') ?>">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="homepage"><?=$this->getTrans('homepage') ?></label>
        <input class="form-control" type="url" id="homepage" name="homepage" value="<?=$this->escape($profile['homepage'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label" for="social_links"><?=$this->getTrans('socialLinks') ?></label>
        <textarea class="form-control" id="social_links" name="social_links" rows="4" placeholder="https://..."><?=$this->escape($profile['social_links'] ?? '') ?></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label" for="games"><?=$this->getTrans('playedGames') ?></label>
        <textarea class="form-control" id="games" name="games" rows="3"><?=$this->escape($profile['games'] ?? '') ?></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label" for="bio"><?=$this->getTrans('aboutMe') ?></label>
        <textarea class="form-control" id="bio" name="bio" rows="5"><?=$this->escape($profile['bio'] ?? '') ?></textarea>
    </div>
    <button class="btn btn-primary" type="submit"><?=$this->getTrans('save') ?></button>
</form>
