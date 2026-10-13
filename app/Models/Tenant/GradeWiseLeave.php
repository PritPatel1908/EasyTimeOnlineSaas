<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Tenant;

use Carbon\Carbon;

/**
 * Class GradeWiseLeave
 *
 * @property int $id
 * @property string $name
 * @property int $financial_year_id
 * @property bool|null $is_monthly
 * @property bool|null $allow_leave_encashment
 * @property bool|null $allow_leave_lapse
 * @property string|null $deleted_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class GradeWiseLeave extends LeaveGroup
{
}
