<?php
/** @var \Ilch\View $this */
$entry = $this->get('entry');
$maxTeamsOptions = [2, 4, 8, 16, 32, 64, 128];
$currentMaxTeams = (int)($entry['max_teams'] ?? 8);
if (!in_array($currentMaxTeams, $maxTeamsOptions, true)) {
    $currentMaxTeams = 8;
}
?>
<h1><?=($entry ? $this->getTrans('editTournament') : $this->getTrans('createTournament')) ?></h1>
<form method="POST" action="">
    <?=$this->getTokenField() ?>
    <div class="row">
        <div class="col-xl-6 mb-3">
            <label class="form-label" for="title"><?=$this->getTrans('title') ?></label>
            <input class="form-control" type="text" id="title" name="title" value="<?=$this->escape($entry['title'] ?? '') ?>" required>
        </div>
        <div class="col-xl-6 mb-3">
            <label class="form-label" for="slug"><?=$this->getTrans('slug') ?></label>
            <input class="form-control" type="text" id="slug" name="slug" value="<?=$this->escape($entry['slug'] ?? '') ?>">
        </div>
    </div>
    <div class="row">
        <div class="col-xl-6 mb-3">
            <label class="form-label" for="game"><?=$this->getTrans('game') ?></label>
            <input class="form-control" type="text" id="game" name="game" value="<?=$this->escape($entry['game'] ?? '') ?>" required>
        </div>
        <div class="col-xl-3 mb-3">
            <label class="form-label" for="team_size">
                <?=$this->getTrans('teamSize') ?>
                <span class="text-muted ms-1"
                      title="<?=$this->escape($this->getTrans('teamSizeHint')) ?>"
                      aria-label="<?=$this->escape($this->getTrans('teamSizeHint')) ?>">
                    <i class="fa-solid fa-circle-info"></i>
                </span>
            </label>
            <input class="form-control" type="number" min="1" id="team_size" name="team_size" value="<?=$this->escape($entry['team_size'] ?? 5) ?>" required>
        </div>
        <div class="col-xl-3 mb-3">
            <label class="form-label" for="max_teams">
                <?=$this->getTrans('maxTeams') ?>
                <span class="text-muted ms-1"
                      title="<?=$this->escape($this->getTrans('maxTeamsHint')) ?>"
                      aria-label="<?=$this->escape($this->getTrans('maxTeamsHint')) ?>">
                    <i class="fa-solid fa-circle-info"></i>
                </span>
            </label>
            <select class="form-select" id="max_teams" name="max_teams" required>
                <?php foreach ($maxTeamsOptions as $option): ?>
                    <option value="<?=$option ?>" <?=$currentMaxTeams === $option ? 'selected' : '' ?>><?=$option ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-6 mb-3">
            <label class="form-label" for="start_at"><?=$this->getTrans('startAt') ?></label>
            <input class="form-control" type="datetime-local" id="start_at" name="start_at" value="<?=!empty($entry['start_at']) ? date('Y-m-d\TH:i', strtotime($entry['start_at'])) : '' ?>" required>
        </div>
        <div class="col-xl-3 mb-3">
            <label class="form-label" for="checkin_required">
                <?=$this->getTrans('checkin') ?>
                <span class="text-muted ms-1"
                      title="<?=$this->escape($this->getTrans('checkinHint')) ?>"
                      aria-label="<?=$this->escape($this->getTrans('checkinHint')) ?>">
                    <i class="fa-solid fa-circle-info"></i>
                </span>
            </label>
            <select class="form-select" id="checkin_required" name="checkin_required">
                <option value="0" <?=(!empty($entry) && (int)$entry['checkin_required'] === 1) ? '' : 'selected' ?>><?=$this->getTrans('no') ?></option>
                <option value="1" <?=(!empty($entry) && (int)$entry['checkin_required'] === 1) ? 'selected' : '' ?>><?=$this->getTrans('yes') ?></option>
            </select>
        </div>
        <div class="col-xl-3 mb-3">
            <label class="form-label" for="status">
                <?=$this->getTrans('status') ?>
                <span class="text-muted ms-1"
                      title="<?=$this->escape($this->getTrans('tournamentStatusHint')) ?>"
                      aria-label="<?=$this->escape($this->getTrans('tournamentStatusHint')) ?>">
                    <i class="fa-solid fa-circle-info"></i>
                </span>
            </label>
            <select class="form-select" id="status" name="status">
                <?php foreach (['draft', 'registration_open', 'registration_closed', 'running', 'finished', 'archived'] as $status): ?>
                    <option value="<?=$status ?>" <?=(!empty($entry['status']) && $entry['status'] === $status) ? 'selected' : '' ?>><?=$this->getTrans($status) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="ck_1"><?=$this->getTrans('rules') ?></label>
        <textarea class="form-control ckeditor"
                  name="rules"
                  id="ck_1"
                  toolbar="ilch_html"
                  rows="8"><?=$this->escape($entry['rules'] ?? '') ?></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label" for="banner"><?=$this->getTrans('banner') ?></label>
        <div class="input-group">
            <input class="form-control"
                   type="text"
                   name="banner"
                   id="selectedImage"
                   placeholder="<?=$this->getTrans('mediaPathPlaceholder') ?>"
                   value="<?=$this->escape($entry['banner'] ?? '') ?>">
            <span class="input-group-text">
                <a id="media" href="javascript:media()"><i class="fa-regular fa-image"></i></a>
            </span>
        </div>
        <?php if (!empty($entry['banner'])): ?>
            <div class="mt-2">
                <img src="<?=$this->getBaseUrl($entry['banner']) ?>" alt="<?=$this->getTrans('banner') ?>" style="max-width: 320px; height: auto;">
            </div>
        <?php endif; ?>
    </div>
    <button class="btn btn-primary" type="submit"><?=$this->getTrans('save') ?></button>
</form>

<?=$this->getDialog('mediaModal', $this->getTrans('media'), '<iframe style="border:0;"></iframe>') ?>
<script>
    <?=$this->getMedia()
        ->addMediaButton($this->getUrl('admin/media/iframe/index/type/single/'))
        ->addUploadController($this->getUrl('admin/media/index/upload'))
    ?>
</script>
