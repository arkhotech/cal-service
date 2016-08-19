<?php

/**
 * Repository Calendar
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Repositories;

use App\Calendar;
use App\App;
use Illuminate\Support\Facades\Cache;
use \Illuminate\Database\QueryException;

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
        } catch (QueryException $qe) {
            $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }        
        
        return $res;
    }
    
    /**
     * crea un nuevo registro de tipo calendario
     * 
     * @param array $data
     * @return Collection
     */
    public function createCalendar($data)
    {
        $res = array();
        
        try {            
            $apps = App::where('appkey', $data['appkey'])
                            ->where('domain', $data['domain'])
                            ->where('status', 1)->value('appkey');
            
            if ($apps) {
                $data['status'] = 1;
                $calendar = Calendar::create($data);
                $res['id'] = $calendar->id;
                $res['error'] = null;
            } else {
                $res['error'] = new \Exception('', 1030);
            }
        } catch (QueryException $qe) {
            if ($qe->getCode() == 23000) {
                $res['error'] = new \Exception('', 1040);
            } else {
                $res['error'] = $qe;
            }
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;
    }
}
