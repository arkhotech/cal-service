<?php

/**
 * Model Calendar
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{    
	protected $table = 'calendars';
    
	protected $fillable = array(
        'name',
        'owner_id',
        'owner_name',
        'is_group',
        'schedule',
        'time_attention',
        'concurrency',
        'ignore_non_working_days',
        'app_key',
        'domain'
    );
    
    protected $hidden = array(
        'app_key',
        'domain',
        'status'
    );
}
