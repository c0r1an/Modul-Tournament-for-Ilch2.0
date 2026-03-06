<?php
/** @var \Ilch\View $this */
$team = $this->get('team');
$members = $this->get('members');
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
<h1><?=$this->getTrans('editTeam') ?>: <?=$this->escape($team['name']) ?></h1>

<form method="POST" action="" class="mb-4" enctype="multipart/form-data">
    <?=$this->getTokenField() ?>
    <div class="mb-3">
        <label class="form-label" for="name"><?=$this->getTrans('name') ?></label>
        <input class="form-control" type="text" name="name" id="name" value="<?=$this->escape($team['name']) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="tag"><?=$this->getTrans('tag') ?></label>
        <input class="form-control" type="text" name="tag" id="tag" value="<?=$this->escape($team['tag']) ?>">
    </div>
    <div class="mb-3">
        <label class="form-label" for="contact_discord"><?=$this->getTrans('discord') ?></label>
        <input class="form-control" type="text" name="contact_discord" id="contact_discord" value="<?=$this->escape($team['contact_discord']) ?>">
    </div>
    <div class="mb-3">
        <label class="form-label" for="contact_email"><?=$this->getTrans('email') ?></label>
        <input class="form-control" type="email" name="contact_email" id="contact_email" value="<?=$this->escape($team['contact_email']) ?>">
    </div>
    <div class="mb-3">
        <label class="form-label" for="logo"><?=$this->getTrans('logo') ?></label>
        <input class="form-control" type="file" name="logo" id="logo" accept=".png,.jpg,.jpeg,.webp">
        <?php if (!empty($team['logo'])): ?>
            <div class="mt-2">
                <img src="<?=$this->getBaseUrl($team['logo']) ?>" alt="<?=$this->getTrans('teamLogoAlt') ?>" style="max-width: 120px; height: auto;">
            </div>
        <?php endif; ?>
    </div>
    <button class="btn btn-primary" type="submit"><?=$this->getTrans('save') ?></button>
</form>

<h4><?=$this->getTrans('members') ?></h4>
<div class="table-responsive">
    <table class="table table-striped">
        <thead><tr><th><?=$this->getTrans('nickname') ?></th><th><?=$this->getTrans('role') ?></th><th></th></tr></thead>
        <tbody>
        <?php foreach ($members as $member): ?>
        <tr>
            <td><?=$this->escape($member['nickname'] ?: ($this->getTrans('user') . '#' . $member['user_id'])) ?></td>
            <td><?=$this->escape($member['role']) ?></td>
            <td>
                <a class="btn btn-sm btn-outline-secondary" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'members', 'action' => 'view', 'id' => (int)$member['id']]) ?>"><?=$this->getTrans('profile') ?></a>
                <?php if ($member['role'] !== 'captain'): ?>
                <form method="POST" action="" style="display:inline-block;">
                    <?=$this->getTokenField() ?>
                    <input type="hidden" name="remove_member_id" value="<?=$member['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger" type="submit"><?=$this->getTrans('remove') ?></button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<h5><?=$this->getTrans('addMemberWithUsername') ?></h5>
<form method="POST" action="">
    <?=$this->getTokenField() ?>
    <div class="input-group">
        <input class="form-control" type="text" name="username" placeholder="<?=$this->getTrans('usernameOrNickname') ?>" required>
        <button class="btn btn-outline-secondary" type="submit"><?=$this->getTrans('add') ?></button>
    </div>
</form>
