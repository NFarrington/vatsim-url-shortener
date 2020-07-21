<?php foreach ($factories as $factory): ?>
    namespace <?=$factory->getNamespaceName()?> {
    /**
    * @method \Illuminate\Support\Collection|<?=$factory->getShortName()?>[]|<?=$factory->getShortName()?> create($attributes = [])
    * @method \Illuminate\Support\Collection|<?=$factory->getShortName()?>[]|<?=$factory->getShortName()?> make($attributes = [])
    */
    class <?=$factory->getShortName()?>FactoryBuilder extends \LaravelDoctrine\ORM\Testing\FactoryBuilder {}
    }
<?php endforeach; ?>
