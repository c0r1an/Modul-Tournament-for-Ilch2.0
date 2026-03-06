<?php
/** @var \Ilch\View $this */
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
<h1><?=$this->getTrans('createTeam') ?></h1>
<form method="POST" action="" enctype="multipart/form-data">
    <?=$this->getTokenField() ?>
    <div class="mb-3">
        <label class="form-label" for="name"><?=$this->getTrans('name') ?></label>
        <input class="form-control" type="text" name="name" id="name" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="tag"><?=$this->getTrans('tag') ?></label>
        <input class="form-control" type="text" name="tag" id="tag">
    </div>
    <div class="mb-3">
        <label class="form-label" for="contact_discord"><?=$this->getTrans('discord') ?></label>
        <input class="form-control" type="text" name="contact_discord" id="contact_discord">
    </div>
    <div class="mb-3">
        <label class="form-label" for="contact_email"><?=$this->getTrans('email') ?></label>
        <input class="form-control" type="email" name="contact_email" id="contact_email">
    </div>
    <div class="mb-3">
        <label class="form-label" for="logo"><?=$this->getTrans('logo') ?></label>
        <input class="form-control" type="file" name="logo" id="logo" accept=".png,.jpg,.jpeg,.webp">
    </div>
    <button class="btn btn-primary" type="submit"><?=$this->getTrans('save') ?></button>
</form>
