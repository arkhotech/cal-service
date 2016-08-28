<?php

/**
 * Repository BlockScheduleRepository
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Repositories;

use Log;
use DB;
use App\BlockSchedule;
use App\App;
use Illuminate\Support\Facades\Cache;
use \Illuminate\Database\QueryException;

class BlockScheduleRepository
{
    public function listBlockSchedule()
    {
    }

    public function createBlockSchedule()
    {
        
    }
    
    public function destroyBlockSchedule()
    {
        
    }
    
    /**
     * Valida si hay bloqueo de horario en una fecha especifica
     * 
     * @param string $appkey
     * @param string $domain
     * @param int $calendar_id
     * @param date $start_date
     * @param date $end_date     
     * @return bool
     */
    public function validateBlock($appkey, $domain, $calendar_id, $start_date, $end_date)
    {
        $res = true;        
        
        try {
            if ((int)$calendar_id > 0 && $start_date && $end_date) {
                $start_date = new \DateTime($start_date);
                $start_date = $start_date->format('Y-m-d H:i:s');
                $end_date = new \DateTime($end_date);
                $end_date = $end_date->format('Y-m-d H:i:s');
            
                $ttl = (int)config('calendar.cache_ttl');
                $cache_id = sha1('cacheIsScheduleBlock_'.$calendar_id.'_'.$start_date.'_'.$end_date);
                $res = Cache::get($calendar_id);
                
                if ($res === null) {
                    $blocks = BlockSchedule::where('end_date', '>=', date('Y-m-d H:i:s'))
                          ->where('start_date', '<=', $end_date)
                          ->Where('end_date', '>=', $start_date)                          
                          ->where('calendar_id', $calendar_id)->get();
                    
                    $res = $blocks->count() ? true : false;
                    
                    $tag = sha1($appkey.'_'.$domain);
                    Cache::tags([$tag])->put($cache_id, $res, $ttl);
                }
            }
        } catch (QueryException $qe) {
            Log::error('code: ' .  $qe->getCode() . ' Message: ' . $qe->getMessage());
        } catch (Exception $e) {
            Log::error('code: ' .  $qe->getCode() . ' Message: ' . $qe->getMessage());
        }        
        
        return $res;
    }
}
