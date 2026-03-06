<?php
/** @var \Ilch\View $this */
$tournaments = $this->get('tournaments');
?>
<h1><?=$this->getTrans('menuAdminTournament') ?></h1>
<p><a class="btn btn-primary" href="<?=$this->getUrl(['action' => 'treat']) ?>"><?=$this->getTrans('createTournament') ?></a></p>
<p><a class="btn btn-outline-secondary" href="<?=$this->getUrl(['action' => 'seed']) ?>"><?=$this->getTrans('seedTeams') ?></a></p>

<?php if ($tournaments): ?>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th><?=$this->getTrans('id') ?></th>
                <th><?=$this->getTrans('actions') ?></th>
                <th><?=$this->getTrans('title') ?></th>
                <th><?=$this->getTrans('game') ?></th>
                <th><?=$this->getTrans('startAt') ?></th>
                <th><?=$this->getTrans('status') ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($tournaments as $tournament): ?>
            <tr>
                <td><?=$tournament['id'] ?></td>
                <td>
                    <?=$this->getEditIcon(['action' => 'treat', 'id' => $tournament['id']]) ?>
                    <a href="<?=$this->getUrl(['action' => 'teams', 'id' => $tournament['id']]) ?>"><?=$this->getTrans('teams') ?></a> |
                    <a href="<?=$this->getUrl(['action' => 'bracket', 'id' => $tournament['id']]) ?>"><?=$this->getTrans('bracket') ?></a> |
                    <?=$this->getDeleteIcon(['action' => 'del', 'id' => $tournament['id']]) ?>
                </td>
                <td><?=$this->escape($tournament['title']) ?></td>
                <td><?=$this->escape($tournament['game']) ?></td>
                <td><?=!empty($tournament['start_at']) ? $this->escape(date('d.m.Y H:i', strtotime($tournament['start_at']))) : '-' ?></td>
                <td><span class="badge text-bg-secondary"><?=$this->getTrans($tournament['status']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
