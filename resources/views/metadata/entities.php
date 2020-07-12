<?= '<?php' ?>

// @formatter:off

// https://www.jetbrains.com/help/phpstorm/ide-advanced-metadata.html
namespace PHPSTORM_META {

<?php if (count($factories)): ?>
    override(\entity(0), map([
    '' => '@FactoryBuilder',
    <?php foreach ($factories as $factory): ?>
        '<?= $factory->getName() ?>' => \<?= $factory->getName() ?>FactoryBuilder::class,
    <?php endforeach; ?>
    ]));
<?php endif; ?>

}
