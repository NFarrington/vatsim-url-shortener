<?php

namespace Tests\Unit\Models\Concerns;

use App\Models\Model;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @covers \App\Models\Concerns\Revisionable
 */
class RevisionableTest extends TestCase
{
    use RefreshDatabase;

    private $revisionable;

    function setUp(): void
    {
        parent::setUp();
        $this->revisionable = new class extends Model {
            protected $tracked = ['my_property'];
        };
    }

    /** @test */
    function registers_model_events_on_boot()
    {
        $this->revisionable::setEventDispatcher(new Dispatcher());

        $this->revisionable->bootRevisionable();

        assertTrue($this->revisionable::getEventDispatcher()->hasListeners('eloquent.saving: '.get_class($this->revisionable)));
        assertTrue($this->revisionable::getEventDispatcher()->hasListeners('eloquent.saved: '.get_class($this->revisionable)));
    }

    /** @test */
    function tracks_revisions()
    {
        $this->revisionable->id = 1;
        $this->revisionable->my_property = 'myOriginalValue';
        $this->revisionable->syncOriginal();

        $this->revisionable->my_property = 'myNewValue';
        $this->revisionable::getEventDispatcher()->dispatch('eloquent.saving: '.get_class($this->revisionable), $this->revisionable);
        $this->revisionable::getEventDispatcher()->dispatch('eloquent.saved: '.get_class($this->revisionable), $this->revisionable);

        $this->assertDatabaseHas('revisions', [
            'model_id' => 1,
            'key' => 'my_property',
            'old_value' => 'myOriginalValue',
            'new_value' => 'myNewValue',
        ]);
    }
}
