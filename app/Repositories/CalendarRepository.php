<?php

/**
 * Repository Calendar
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Repositories;

use Log;
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
                    $res['count'] = $calendars->count();
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
     * Obtiene todos los calendarios por una appkey y domain
     *      
     * @param int $id
     * @return Collection
     */
    public function listCalendarById($id)
    {
        $res = array();
        
        try {            
            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheCalendarListById_'.$id);
            
            $res = Cache::remember($cache_id, $ttl, function() use($id) {
                if ((int)$id > 0) {
                    $calendars = Calendar::find($id);
                    
                    $res['data'] = $calendars;
                    $res['count'] = 1;
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
     * Crea un nuevo registro de tipo calendario
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
    
    /**
     * Actualiza un nuevo registro de tipo calendario
     * 
     * @param array $data
     * @param int $id
     * @return Collection
     */
    public function updateCalendar($data, $id)
    {
        $res = array();
        
        try {
            
            if (!$this->hasAvailableAppointments($id)) {
                unset($data['appkey']);
                unset($data['domain']);
                unset($data['status']);

                $calendar = Calendar::where('id', $id)->update($data);
                $res['error'] = $calendar === false ? new \Exception('', 500) : null;
            } else {
                $res['error'] = new \Exception('', 1050);
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
    
    /**
     * Deshabilita un registro de tipo calendario
     *      
     * @param int $id
     * @return Collection
     */
    public function disableCalendar($id)
    {
        $res = array();
        
        try {
            
            if (!$this->hasAvailableAppointments($id)) {

                $calendar = Calendar::where('id', $id)->update(array('status' => 0));
                $res['error'] = $calendar === false ? new \Exception('', 500) : null;
            } else {
                $res['error'] = new \Exception('', 1060);
            }
        } catch (QueryException $qe) {            
                $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;
    }
    
    /**
     * Retorna un booleano si un calendario tiene citas disponibles
     * 
     * @param mixed $id null/int
     * @return boolean
     */
    public function hasAvailableAppointments($id)
    {
        $resp = true;
        try {
            if ((int)$id > 0) {
                $ttl = (int)config('calendar.cache_ttl');
                $cache_id = sha1('cacheCalendarAppointment_'.$id);

                $appointments = Cache::remember($cache_id, $ttl, function() use($id) {
                    $results = Calendar::find($id)
                            ->appointments()
                            ->where('is_canceled', '<>', 1)
                            ->where('appoinment_time', '>=', date('Y-m-d H:i:s'))->get();

                    return $results;
                });

                $resp = $appointments->count() ? true : false;
            }
        } catch (Exception $e) {
            Log::error('code: ' .  $e->getCode() . ' Message: ' . $e->getMessage());
        }        
        
        return $resp;
    }
}
