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
use DB;
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
     * Obtiene todos los calendarios por nombre de agenda o propietario
     * 
     * @param string $appkey
     * @param string $domain
     * @param int $text
     * @return Collection
     */
    public function searchByName($appkey, $domain, $text)
    {
        $res = array();
        
        try {
            $text = trim(strtolower($text));
            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheCalendarSearchByName_'.$appkey.'_'.$domain.'_'.$this->sanitizeString($text));
            
            $res = Cache::remember($cache_id, $ttl, function() use($appkey, $domain, $text) {
                if (!empty($text)) {                    
                    $calendars = Calendar::where(function ($query) use ($appkey, $domain) {
                        return $query->where('appkey', '=', $appkey)
                              ->Where('domain', '=', $domain)
                              ->where('status', 1);
                    })->where(function ($query) use ($text) {
                        return $query->Where('name', 'LIKE', '%'.$text.'%')
                              ->orWhere('owner_name', 'LIKE', '%'.$text.'%');
                    })->orderBy('name', 'asc')->get();

                    $res['data'] = $calendars;
                    $res['count'] = $calendars->count();
                    $res['error'] = null;
                }
                
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
                
                Cache::flush();
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
                
                Cache::flush();
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
                
                Cache::flush();
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
    
    /**
     * Retorna un booleano si un calendario tiene citas disponibles
     * en una fecha dada
     * 
     * @param mixed $id null/int
     * @return boolean
     */
    public function hasAvailableAppointmentByDate($appkey, $domain, $date)
    {
        $resp = true;
        try {
            if (!empty($appkey) && !empty($domain) && !empty($date)) {
                $ttl = (int)config('calendar.cache_ttl');
                $cache_id = sha1('cacheCalendarAppointmentByDate_'.$appkey.'_'.$domain.'_'.$date);

                $appointments = Cache::remember($cache_id, $ttl, function() use($appkey, $domain, $date) {
                    $sql = 'SELECT c.id, a.appoinment_time FROM calendars c
                    INNER JOIN appointments a
                    ON c.id = a.calendar_id
                    WHERE c.appkey = ? AND c.domain = ? AND 
                    c.ignore_non_working_days = 0 AND DATE(a.appoinment_time) = ?';
                    $results = DB::select($sql, array($appkey, $domain, $date));
                    
                    return $results;
                });

                $resp = count($appointments) ? true : false;
            }
        } catch (Exception $e) {
            Log::error('code: ' .  $e->getCode() . ' Message: ' . $e->getMessage());
        }        
        
        return $resp;
    }
    
    public function sanitizeString($string)
    {

        $string = trim($string);

        $string = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $string
        );

        $string = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $string
        );

        $string = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $string
        );

        $string = str_replace(
            array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $string
        );

        $string = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $string
        );

        $string = str_replace(
            array('ñ', 'Ñ', 'ç', 'Ç'),
            array('n', 'N', 'c', 'C',),
            $string
        );

        return $string;
    }
}
