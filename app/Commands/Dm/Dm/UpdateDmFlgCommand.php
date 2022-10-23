<?php

namespace App\Commands\Dm\Dm;

use App\Models\TargetCars;
use App\Commands\Command;

/**
 * DM送付不要チェックの更新処理
 *
 * @author yhatsutori
 */
class UpdateDmFlgCommand extends Command{
//
//    /**
//     * コンストラクタ
//     * @param instance $requestObj
//     */
//    public function __construct( $requestObj ){
//        $this->requestObj = $requestObj;
//    }
//
//    /**
//     * メインの処理
//     */
//    public function handle(){
//        foreach( $this->requestObj->targets as $dm_target ){
//            \Log::debug("UpdateDmFlgCommand");
//            \Log::debug($dm_target);
//
//            $targetCarsMObj = TargetCars::findOrFail( $dm_target['id'] );
//
//            $targetCarsMObj->tgc_dm_flg = $dm_target['is_dm_flg']; // 不要チェックのフラグ
//            $targetCarsMObj->tgc_dm_unnecessary_reason = $dm_target['tgc_dm_unnecessary_reason']; // 不要理由
//
//            // 不要チェックのある時だけ操作
//            if( $targetCarsMObj->tgc_dm_flg == 0 ){
//                $targetCarsMObj->tgc_dm_flg = NULL;
//                $targetCarsMObj->tgc_dm_unnecessary_reason = NULL;
//            }
//
//            $targetCarsMObj->save();
//        }
//    }
//
}
