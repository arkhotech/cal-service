<?php

/**
 * Controller Calendar
 * 
 * @author Geovanni Escalante <gescalante@arkho.tech>
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\BlockScheduleRepository;
use App\Response as Resp;
use Validator;

class BlockScheduleController extends Controller
{
    /**
     * Instancia de BlockScheduleRepository
     *
     * @var BlockScheduleRepository
     */
    protected $blockSchedules;

    /**
     * Crea una nueva instancia Controller
     *
     * @param BlockScheduleRepository  $blockSchedule
     * @return void
     */
    public function __construct(BlockScheduleRepository $blockSchedules)
    {
        $this->blockSchedules = $blockSchedules;
    }
}
