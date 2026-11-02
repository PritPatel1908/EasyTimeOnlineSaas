<?php

namespace Tests\Feature;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CloneFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('clone_form_test_records', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('clone_form_test_records');

        parent::tearDown();
    }

    public function test_clone_helper_returns_the_requested_source_without_creating_a_record(): void
    {
        $source = CloneFormTestRecord::query()->create(['name' => 'Original']);
        $request = Request::create('/create?clone=' . $source->id);

        $clone = (new CloneFormTestController)->resolveClone($request);

        $this->assertSame($source->id, $clone?->id);
        $this->assertSame('Original', $clone?->name);
        $this->assertSame(1, CloneFormTestRecord::query()->count());
    }
}

class CloneFormTestRecord extends Model
{
    protected $table = 'clone_form_test_records';

    protected $guarded = [];
}

class CloneFormTestController extends Controller
{
    public function resolveClone(Request $request): ?Model
    {
        return $this->cloneForCreate($request, CloneFormTestRecord::class);
    }
}
