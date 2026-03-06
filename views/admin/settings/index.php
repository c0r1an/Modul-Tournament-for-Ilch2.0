<?php
/** @var \Ilch\View $this */
$bracketTheme = $this->get('bracketTheme');
?>
<h1><?=$this->getTrans('settings') ?></h1>

<form method="POST" action="">
    <?=$this->getTokenField() ?>
    <div class="row mb-3">
        <label for="bracketTheme" class="col-xl-3 col-form-label"><?=$this->getTrans('bracketTheme') ?></label>
        <div class="col-xl-4">
            <select class="form-select" name="bracket_theme" id="bracketTheme">
                <option value="light" <?=$bracketTheme === 'light' ? 'selected' : '' ?>><?=$this->getTrans('themeLight') ?></option>
                <option value="dark" <?=$bracketTheme === 'dark' ? 'selected' : '' ?>><?=$this->getTrans('themeDark') ?></option>
            </select>
        </div>
    </div>
    <?=$this->getSaveBar() ?>
</form>
