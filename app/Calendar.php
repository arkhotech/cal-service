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
    
    public $timestamps = false;
    
	protected $fillable = array(
        'name',
        'owner_id',
        'owner_name',
        'is_group',
        'schedule',
        'time_attention',
        'concurrency',
        'ignore_non_working_days',
        'appkey',
        'domain'
    );
    
    protected $hidden = array(
        'appkey',
        'domain',
        'status'
    );
}
