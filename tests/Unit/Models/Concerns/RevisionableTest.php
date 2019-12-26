<?php

namespace Tests\Unit\Models\Concerns;

use App\Models\Concerns\Revisionable;
use Illuminate\Database\Eloquent\Model;
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
            use Revisionable;
            protected $tracked = ['my_property'];
        };
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

    /** @test */
    function does_not_track_non_revisionable_properties()
    {
        $this->revisionable->id = 1;
        $this->revisionable->my_untracked_property = 'myOriginalValue';
        $this->revisionable->syncOriginal();

        $this->revisionable->my_untracked_property = 'myNewValue';
        $this->revisionable::getEventDispatcher()->dispatch('eloquent.saving: '.get_class($this->revisionable), $this->revisionable);
        $this->revisionable::getEventDispatcher()->dispatch('eloquent.saved: '.get_class($this->revisionable), $this->revisionable);

        $this->assertDatabaseMissing('revisions', [
            'model_id' => 1,
            'key' => 'my_untracked_property',
        ]);
    }
}
