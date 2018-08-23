<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 12 Jul 2018 22:39:27 +0000.
 */

namespace App\Models;

/**
 * Class ManagerTranslation
 *
 * @property int $id
 * @property string $locale
 * @property string $about_me
 * @property string $greatest_accomplishment
 * @property string $branch
 * @property string $division
 * @property string $position
 * @property string $leadership_style
 * @property string $employee_learning
 * @property string $expectations
 * @property int $manager_id
 * @property string $work_experience
 * @property string $education
<<<<<<< HEAD
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property \App\Models\Manager $manager
 */
class ManagerTranslation extends Eloquent
{
=======
 * @property \Jenssegers\Date\Date $created_at
 * @property \Jenssegers\Date\Date $updated_at
 *
 * @property \App\Models\Manager $manager
 */
class ManagerTranslation extends BaseModel {
>>>>>>> dev

    protected $casts = [
        'manager_id' => 'int'
    ];
    protected $fillable = [
        'about_me',
        'greatest_accomplishment',
        'branch',
        'division',
        'position',
        'leadership_style',
        'employee_learning',
        'expectations',
        'work_experience',
        'education'
    ];

    public function manager()
    {
        return $this->belongsTo(\App\Models\Manager::class);
    }
}
