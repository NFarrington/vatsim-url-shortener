<?= '<?php' ?>

// @formatter:off

// https://www.jetbrains.com/help/phpstorm/ide-advanced-metadata.html
namespace PHPSTORM_META {

<?php if (count($users)): ?>
    override(\Illuminate\Http\Request::user(), map([
    <?php foreach ($users as $user): ?>
        '<?= $user['name'] ?>' => \<?= $user['entity'] ?>::class,
    <?php endforeach; ?>
    ]));
<?php endif; ?>

}
