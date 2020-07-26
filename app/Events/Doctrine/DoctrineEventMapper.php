<?php

namespace App\Events\Doctrine;

use App\Entities\Revision;
use App\Events\UrlRetrieved;
use App\Events\UrlSaved;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Illuminate\Support\Facades\Auth;

class DoctrineEventMapper implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
            Events::postLoad,
            Events::postPersist,
            Events::postUpdate,
        ];
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (method_exists($entity, 'getTrackedProperties')) {
                $trackedProperties = $entity->getTrackedProperties();
                $changeSet = $uow->getEntityChangeSet($entity);
                $trackableChangeSet = array_intersect_key($changeSet, array_flip($trackedProperties));
                foreach ($trackableChangeSet as $property => $values) {
                    $oldValue = $this->stringValue(
                        method_exists($values[0], 'getId') ? $values[0]->getId() : $values[0]
                    );
                    $newValue = $this->stringValue(
                        method_exists($values[1], 'getId') ? $values[1]->getId() : $values[1]
                    );
                    if ($oldValue !== $newValue) {
                        $revision = new Revision();
                        $revision->setModelId($entity->getId());
                        $revision->setModelType(get_class($entity));
                        $revision->setPropertyName($property);
                        $revision->setOldValue($oldValue);
                        $revision->setNewValue($newValue);
                        $revision->setUser(Auth::check() ? Auth::user() : null);
                        $em->persist($revision);

                        // required during onFlush() event handling
                        $revisionClassMetadata = $em->getClassMetadata(get_class($revision));
                        $uow->computeChangeSet($revisionClassMetadata, $revision);
                    }
                }
            }
        }
    }

    private function stringValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return is_scalar($value) ? (string) $value : json_encode($value);
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof \App\Entities\Url) {
            event(new UrlRetrieved($entity));
        }
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->postSave($args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->postSave($args);
    }

    public function postSave(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof \App\Entities\Url) {
            event(new UrlSaved($entity));
        }
    }
}
