<?php

/**
 * Created by Reliese Model.
 * Date: Thu, 12 Jul 2018 22:39:27 +0000.
 */

namespace App\Models\Lookup;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class FileTypeTranslation
 * 
 * @property int $id
 * @property int $file_type_id
 * @property string $locale
 * @property string $value
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \App\Models\Lookup\FileType $file_type
 */
class FileTypeTranslation extends Eloquent {

    protected $casts = [
        'file_type_id' => 'int'
    ];
    protected $fillable = [];

    public function file_type() {
        return $this->belongsTo(\App\Models\Lookup\FileType::class);
    }

}