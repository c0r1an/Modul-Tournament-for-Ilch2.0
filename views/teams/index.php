<?php
/** @var \Ilch\View $this */
$teams = $this->get('teams');
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
<h1><?=$this->getTrans('myTeams') ?></h1>
<p><a class="btn btn-primary" href="<?=$this->getUrl(['action' => 'create']) ?>"><?=$this->getTrans('createTeam') ?></a></p>

<?php if ($teams): ?>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th><?=$this->getTrans('logo') ?></th>
                <th><?=$this->getTrans('title') ?></th>
                <th><?=$this->getTrans('tag') ?></th>
                <th><?=$this->getTrans('actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($teams as $team): ?>
            <tr>
                <td>
                    <?php if (!empty($team['logo'])): ?>
                        <img src="<?=$this->getBaseUrl($team['logo']) ?>" alt="<?=$this->getTrans('teamLogoAlt') ?>" style="width:44px;height:44px;object-fit:cover;border-radius:6px;">
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?=$this->escape($team['name']) ?></td>
                <td><?=$this->escape($team['tag']) ?></td>
                <td>
                    <a href="<?=$this->getUrl(['action' => 'view', 'id' => $team['id']]) ?>"><?=$this->getTrans('view') ?></a> |
                    <a href="<?=$this->getUrl(['action' => 'edit', 'id' => $team['id']]) ?>"><?=$this->getTrans('edit') ?></a> |
                    <form method="POST" action="<?=$this->getUrl(['action' => 'del', 'id' => $team['id']]) ?>" style="display:inline-block;" onsubmit="return confirm('<?=$this->escape($this->getTrans('teamDeleteConfirm')) ?>');">
                        <?=$this->getTokenField() ?>
                        <button type="submit" class="btn btn-link p-0 text-danger align-baseline"><?=$this->getTrans('delete') ?></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
