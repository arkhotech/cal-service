<?php

/**
 * Repository BlockScheduleRepository
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Repositories;

use Log;
use App\BlockSchedule;
use App\Calendar;
use Illuminate\Support\Facades\Cache;
use \Illuminate\Database\QueryException;

class BlockScheduleRepository
{
    /**
     * Lista todos los blockschedules
     * 
     * @param string $appkey
     * @param string $domain
     * @return Collection
     */
    public function listBlockSchedule($appkey, $domain)
    {
      $res = array();

        try {

            $ttl = (int)config('calendar.cache_ttl');
            $cache_id = sha1('cacheBlockScheduleList_'.$appkey.'_'.$domain);
            $tag = sha1($appkey.'_'.$domain);
            $res = Cache::tags($tag)->get($cache_id);
                    
            if ($res === null) {
                
                if (!empty($appkey) && !empty($domain)) {
                    $blockSchedule = BlockSchedule::where('appkey', $appkey)
                        ->where('domain', $domain)->get();
                } else {
                    $blockSchedule = BlockSchedule::all();
                }
                
                $res['data'] = $blockSchedule;
                $res['count'] = $blockSchedule->count();                
                $res['error'] = null;

                Cache::tags([$tag])->put($cache_id, $res, $ttl);
            }
        } catch (QueryException $qe) {
            $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }        
        
        return $res;
    }
    
    /**
     * Crea un nuevo registro de tipo block schedule
     * 
     * @param string $appkey
     * @param string $domain
     * @param array $data     
     * @return Collection
     */
    public function createBlockSchedule($appkey, $domain, $data)
    {
        $res = array();
        
        try {            
            $calendar = Calendar::where('id', $data['calendar_id'])->get();
            
            if ($calendar->count() > 0) {
                $start_date = new \DateTime($data['start_date']);
                $start_date = $start_date->format('Y-m-d H:i:s');
                $end_date = new \DateTime($data['end_date']);
                $end_date = $end_date->format('Y-m-d H:i:s');
                
                if ($start_date >= date('Y-m-d H:i:s')) {
                    if ($end_date > $start_date) {
                        $data['created_date'] = date('Y-m-d H:i:s');
                        BlockSchedule::create($data);
                        $res['error'] = null;

                        $tag = sha1($appkey.'_'.$domain);
                        Cache::tags($tag)->flush();                    
                    } else {
                        $res['error'] = new \Exception('', 2080);
                    }
                } else {
                    $res['error'] = new \Exception('', 2090);
                }
            } else {
                $res['error'] = new \Exception('', 1010);
            }
        } catch (QueryException $qe) {
            $res['error'] = $qe;
        } catch (Exception $e) {
            $res['error'] = $e;
        }
        
        return $res;        
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
