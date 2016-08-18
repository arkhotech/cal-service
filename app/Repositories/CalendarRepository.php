<?php

/**
 * Repository Calendar
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Repositories;

use App\Calendar;
use Illuminate\Support\Facades\Cache;

class CalendarRepository
{
    /**
     * Obtiene todos los calendarios por una appkey y domain
     * 
     * @param string $appkey
     * @param string $domain
     * @param int $page
     * @return Collection
     */
    public function listCalendar($appkey, $domain, $page)
    {
        $res = array();
        $page = (int)$page;
        
        try {
            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheCalendarList_'.$appkey.'_'.$domain.'_'.$page);
            
            $res = Cache::remember($cache_id, $ttl, function() use($appkey, $domain, $page) {
                if ($page !== 0) {                    
                    $per_page = (int)config('calendar.per_page');            

                    $calendars = Calendar::where('appkey', $appkey)
                            ->where('domain', $domain)
                            ->where('status', 1)
                            ->paginate($per_page);
                    
                    $res['data'] = $calendars->items();
                    $res['count'] = $calendars->total();
                } else {
                    $calendars = Calendar::where('appkey', $appkey)
                            ->where('domain', $domain)
                            ->where('status', 1)->get();
                    
                    $res['data'] = $calendars;
                    $res['count'] = count($calendars);                    
                }
                $res['error'] = null;
                
                return $res;
            });
        } catch (Exception $e) {
            $res['error'] = $e;
        }        
        
        return $res;
    }
}