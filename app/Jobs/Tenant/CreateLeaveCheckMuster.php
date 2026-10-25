<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\GeneralConfiguration;
use App\Models\Tenant\LeaveCheckMuster;
use App\Models\Tenant\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use UnexpectedValueException;

class CreateLeaveCheckMuster implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels {
        SerializesModels::__unserialize as private restoreSerializedProperties;
    }

    protected $userId;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 3600;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return (string) $this->userId;
    }

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->userId = $user->getKey();
    }

    public function __unserialize(array $values): void
    {
        $legacyUserKey = "\0*\0user";
        if (array_key_exists($legacyUserKey, $values)) {
            $legacyUser = $values[$legacyUserKey];
            if (! $legacyUser instanceof ModelIdentifier) {
                throw new UnexpectedValueException('Invalid serialized user in CreateLeaveCheckMuster job.');
            }

            $this->userId = $legacyUser->id;
            unset($values[$legacyUserKey]);
        }

        $this->restoreSerializedProperties($values);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::query()->findOrFail($this->userId);

        DB::beginTransaction();
        try {
            if ($user?->last_check_date != null && $user?->last_check_id != null) {
                if (LeaveCheckMuster::where('user_id', $user?->id)->where('is_checked', false)->orderBy('check_date', 'desc')->first() == null) {
                    $user_leave_check_muster = LeaveCheckMuster::where('user_id', $user?->id)
                        ->where('is_checked', true)
                        ->orderBy('check_date', 'desc')
                        ->first();
                    if ($user?->grade_wise_leave?->is_monthly) {
                        if ($user_leave_check_muster) {
                            $leave_check_date = GeneralConfiguration::where('key', 'monthly_leave_check_date')?->first();
                            if ($leave_check_date?->value != null) {
                                if (Carbon::today()->format('d') == $leave_check_date?->value && Carbon::today()->format('Y-m-d') != $user_leave_check_muster->check_date) {
                                    LeaveCheckMuster::create([
                                        'user_id' => $user?->id,
                                        'check_date' => Carbon::today()->format('Y-m-d'),
                                    ]);
                                    Log::channel('attprocess')->debug('Crete Leave Muster For | '.$user?->name.'('.$user?->code.')');
                                }
                            } else {
                                $new_check_date = $user_leave_check_muster->check_date->modify('+1 month')->format('Y-m-d');
                                LeaveCheckMuster::create([
                                    'user_id' => $user?->id,
                                    'check_date' => $new_check_date,
                                ]);
                                Log::channel('attprocess')->debug('Crete Leave Muster For | '.$user?->name.'('.$user?->code.')');
                            }
                        }
                    } else {
                        if ($user_leave_check_muster) {
                            $leave_check_date = GeneralConfiguration::where('key', 'yearly_leave_check_date')?->first();
                            if ($leave_check_date?->value !== null) {
                                if (Carbon::today()->format('d') == $leave_check_date?->value && Carbon::today()->format('Y-m-d') != $user_leave_check_muster->check_date) {
                                    LeaveCheckMuster::create([
                                        'user_id' => $user?->id,
                                        'check_date' => Carbon::today()->format('Y-m-d'),
                                    ]);
                                    Log::channel('attprocess')->debug('Crete Leave Muster For | '.$user?->name.'('.$user?->code.')');
                                }
                            } else {
                                $new_check_date = $user_leave_check_muster->check_date->modify('+1 year')->format('Y-m-d');
                                LeaveCheckMuster::create([
                                    'user_id' => $user?->id,
                                    'check_date' => $new_check_date,
                                ]);
                                Log::channel('attprocess')->debug('Crete Leave Muster For | '.$user?->name.'('.$user?->code.')');
                            }
                        }
                    }
                }
            } else {
                if (LeaveCheckMuster::where('user_id', $user?->id)->where('is_checked', false)->orderBy('check_date', 'desc')->first() == null) {
                    LeaveCheckMuster::create([
                        'user_id' => $user?->id,
                        'check_date' => $user?->join_date,
                    ]);
                    Log::channel('attprocess')->debug('Crete Leave Muster For | '.$user?->name.'('.$user?->code.')');
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('attprocess')->debug('Error Credit Leave Balance. '.Str::limit($e->getMessage(), 200));
        }
    }
}
